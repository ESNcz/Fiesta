<?php

namespace App\Model;

use Nette\Database\ConstraintViolationException;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;

class PluginRepository extends Repository
{

    /**
     * List of visible plugins for side menu
     * @param $university
     * @return mixed
     */
    public function getActivePluginsForSideMenu($university)
    {
        $getModules = $this->database
            ->table("university")
            ->get($university)
            ->related("module_assignment", "university")
            ->order("module.order ASC");

        $modules["menu"] = $getModules;

        foreach ($getModules as $module) {
            $submenu = $module->ref("module", "module")->related("module_submenu.module_id")->order("order ASC")->fetchPairs("action", "title");
            $modules["submenu"][$module["module"]] = $submenu;
            if (!empty($submenu)) $modules["has_submenu"][] = $module["module"];
        }

        if (empty($modules["has_submenu"])) $modules["has_submenu"] = array();

        return $modules;
    }

    /**
     * List of visible modules
     */
    public function getAllPlugins()
    {
        $result = $this->database
            ->table('module')->where("visibility", TRUE);
        return $result;
    }

    /**
     * Switch status of plugin
     *
     * @param $module
     * @param $university
     */
    public function switchStatusOfPlugin($module, $university)
    {

        if (in_array($module, $this->getPluginsDependsOnUniversity($university))) {
            $this->disablePlugin($module, $university);
        } else {
            $this->activatePlugin($module, $university);
        }

        switch ($module) {
            case "BuddyManagement":
                $this->database->query('INSERT INTO buddy_settings', [
                    'university_id' => $university
                ], 'ON DUPLICATE KEY UPDATE', [
                    'university_id' => $university
                ]);
                break;
            case "PickupManagement":
                $this->database->query('INSERT INTO pickup_settings', [
                    'university_id' => $university
                ], 'ON DUPLICATE KEY UPDATE', [
                    'university_id' => $university
                ]);
                break;
        }
    }

    /**
     * Get active plugins depends on university
     * @param $university
     * @return array
     */
    public function getPluginsDependsOnUniversity($university)
    {
        $result = $this->database->table("module_assignment")
            ->where("university", $university)
            ->fetchPairs(null, "module");
        return $result;
    }

    private function disablePlugin($module, $university)
    {
        $this->database->table("module_assignment")
            ->where("university", $university)
            ->where("module", $module)
            ->delete();
    }

    private function activatePlugin($module, $university)
    {
        $this->database->table("module_assignment")->insert([
            'university' => $university,
            'module' => $module,
        ]);
    }

    /**************************************************************************/
    /********** Buddy Management **********************************************/
    /**************************************************************************/

    /**
     * Setting for Buddy module depends on university
     * @param $university
     * @return array
     */
    public function getBuddySettings($university)
    {
        return $this->database->table("buddy_settings")->get($university)->toArray();
    }

    public function getBuddyRequest($email)
    {
        return $this->database->table("buddy_request")
            ->where("data_user", $email)->fetch();
    }

    /**
     * @param $university
     * @return Selection
     */
    public function getAllBuddyRequests($university)
    {
        return $this->database->table("buddy_request")
            ->where("data_user.user.university", $university)
            ->where('take', false);
    }

    public function deleteBuddyRequest($id)
    {
        $this->database->table("buddy_request")->where("id", $id)->delete();
    }

    public function deleteBuddyMatch($id)
    {
        $request = $this->database->table("buddy_match")
            ->where("id", $id)->fetch();


        $this->database->table("buddy_request")->where("data_user", $request["international"])->update([
            'take' => false
        ]);

        $request->delete();
    }

    public function getUserLimit($member)
    {
        $date = DateTime::from(time());
        return $this->database
            ->table("buddy_match")
            ->where("member", $member)
            ->where("created > ?", $date->modify("- 3 months"))
            ->count();
    }

    /**
     * Update connection fo buddy connection
     *
     * @param $member
     * @param $id
     */
    public function updateBuddyConnection($member, $id)
    {
        $this->database->table("buddy_match")->where("id", $id)->update([
            'member' => $member
        ]);
    }

    /**
     * Create Buddy request
     *
     * @param $values
     */
    public function createBuddyRequest($values)
    {
        $this->database->query('INSERT INTO buddy_request', [
            'data_user' => $values["data_user"],
            'description' => $values["text"],
        ], 'ON DUPLICATE KEY UPDATE', [
            'description' => $values["text"],
        ]);
    }

    public function takeBuddyRequestWithLimit($member, $international, $university)
    {
        $date = DateTime::from(time());

        $userLimit = $this->database
            ->table("buddy_match")
            ->where("member", $member)
            ->where("created > ?", $date->modify("- 3 months"))
            ->count();

        $maxLimit = $this->database
            ->table("buddy_settings")
            ->where("university_id", $university)
            ->fetchField("limit");

        if ($userLimit < $maxLimit) {
            try {
                $this->database->beginTransaction();
                $request = $this->database->table("buddy_request")->where("id", $international);

                $request->update([
                    'take' => true
                ]);

                $user = $request->select("data_user")->fetch();
                $this->database->table("buddy_match")->insert([
                    'member' => $member,
                    'international' => $user["data_user"],
                    'university' => $university,
                ]);

                $this->database->commit();
            } catch (ConstraintViolationException $e) {
                $this->database->rollBack();
                throw new DuplicateException;
            }

        } else throw new MaxLimitException;

        return $user;
    }

    public function takeBuddyRequestWithoutLimit($member, $international, $university)
    {
        try {
            $this->database->beginTransaction();
            $request = $this->database->table("buddy_request")->where("id", $international);

            $request->update([
                'take' => true
            ]);

            $user = $request->select("data_user")->fetch();
            $this->database->table("buddy_match")->insert([
                'member' => $member,
                'international' => $user["data_user"],
                'university' => $university,
            ]);

            $this->database->commit();
        } catch (ConstraintViolationException $e) {
            $this->database->rollBack();
            throw new DuplicateException;
        }

        return $user;
    }

    /**
     * @param $university
     * @return Selection
     */
    public function getAllBuddyConnections($university)
    {
        $result = $this->database->table("buddy_match")
            ->where("university", $university);

        return $result;
    }

    public function changeBuddyStatus($type, $status, $university)
    {
        return $this->database->table("buddy_settings")->get($university)->update([
            $type => !$status
        ]);
    }

    /**************************************************************************/
    /********** PickUp Management *********************************************/
    /**************************************************************************/

    /**
     * Setting for Pick up module depends on university
     * @param $university
     * @return array
     */
    public function getPickUpSettings($university)
    {
        return $this->database->table("pickup_settings")->get($university)->toArray();
    }

    /**
     * @param $university
     * @return Selection
     */
    public function getAllPickUpRequests($university)
    {
        return $this->database->table("pickup_request")
            ->where("data_user.user.university", $university)
            ->where('take', false);
    }

    public function getPickUpRequest($email)
    {
        return $this->database->table("pickup_request")
            ->where("data_user", $email)->fetch();
    }

    public function deletePickUpRequest($id)
    {
        $this->database->table("pickup_request")->where("id", $id)->delete();
    }

    public function deletePickUpMatch($id)
    {
        $request = $this->database->table("pickup_match")
            ->where("id", $id)->fetch();


        $this->database->table("pickup_request")->where("data_user", $request["international"])->update([
            'take' => false
        ]);

        $request->delete();
    }

    public function takePickUpRequest($member, $international, $university)
    {
        try {
            $this->database->beginTransaction();
            $request = $this->database->table("pickup_request")->where("id", $international);

            $request->update([
                'take' => true
            ]);

            $user = $request->select("data_user")->fetch();

            $this->database->table("pickup_match")->insert([
                'member' => $member,
                'international' => $user["data_user"],
                'university' => $university,
            ]);

            $this->database->commit();
        } catch (ConstraintViolationException $e) {
            $this->database->rollBack();
            throw new DuplicateException;
        }

        return $user;
    }

    /**
     * Update connection fo buddy connection
     *
     * @param $member
     * @param $id
     */
    public function updatePickUpConnection($member, $id)
    {
        $this->database->table("pickup_match")->where("id", $id)->update([
            'member' => $member
        ]);
    }

    /**
     * Create Buddy request
     *
     * @param $values
     */
    public function createPickUpRequest($values)
    {
        $this->database->query('INSERT INTO pickup_request', [
            'data_user' => $values["data_user"],
            'date_arrival' => $values["date_arrival"],
            'place_arrival' => $values["place_arrival"],
            'description' => $values["text"],
        ], 'ON DUPLICATE KEY UPDATE', [
            'date_arrival' => $values["date_arrival"],
            'place_arrival' => $values["place_arrival"],
            'description' => $values["text"],
        ]);
    }

    /**
     * @param $university
     * @return Selection
     */
    public function getAllPickUpConnections($university)
    {
        $result = $this->database->table("pickup_match")
            ->where("university", $university);

        return $result;
    }

    public function changePickUpStatus($type, $status, $university)
    {
        return $this->database->table("pickup_settings")->get($university)->update([
            $type => !$status
        ]);
    }

    /**************************************************************************/
    /********** Event Management **********************************************/
    /**************************************************************************/

    public function isEventFree($event)
    {
        $event = $this->getEvent($event);
        return $event["price_with_esn"] === 0 && $event["price_without_esn"] === 0;
    }

    /**
     * Get specific event
     *
     * @param $id
     *
     * @return false|ActiveRow
     */
    public function getEvent($id)
    {
        $result = $this->database->table("event")->where("id", $id)
            ->fetch();
        return $result;
    }

    /**
     * List of upcoming events
     * @param $university
     * @return Selection
     */
    public function upcomingEvents($university)
    {
        $result = $this->database->table("event")
            ->where("university", $university)
            ->where("event_date >= NOW()")
            ->order("event_date DESC")
            ->select('event.*, count(:event_list.data_user) guest_count, (price_with_esn = 0 AND price_without_esn = 0) for_free')
            ->group('id');
        return $result;
    }

    /**
     * List of all past events
     * @param $university
     * @return Selection
     */
    public function pastEvents($university)
    {
        $result = $this->database->table("event")
            ->where("university", $university)
            ->where("event_date < NOW()")->order("event_date DESC");
        return $result;
    }

    public function setEvent($values, $university)
    {
        try {
            $this->database->beginTransaction();
            $this->database->table("event")->insert([
                'title' => $values["title"],
                'user_id' => $values["leader"],
                'capacity' => $values["capacity"],
                'location' => $values["location"],
                'event_date' => DateTime::from($values["event"]),
                'price_with_esn' => $values["priceESN"],
                'price_without_esn' => $values["price"],
                'registration_start' => DateTime::from($values["start"]),
                'description' => $values["text"],
                "university" => $university
            ]);
            $this->database->commit();
        } catch (ConstraintViolationException $e) {
            $this->database->rollBack();
            throw new NoLeaderException;
        }
    }

    /**
     * Update specific event
     *
     * @param $value
     * @param $id
     *
     * @param $university
     * @throws NoLeaderException
     */
    public function updateEvent($value, $id, $university)
    {
        try {
            $this->database->beginTransaction();
            if (isset($value["leader"])) {
                $this->database->table("event")->where("id", $id)->update([
                    'title' => $value["title"],
                    'user_id' => $value["leader"],
                    'capacity' => $value["capacity"],
                    'location' => $value["location"],
                    'event_date' => DateTime::from($value["event"]),
                    'price_with_esn' => $value["priceESN"],
                    'price_without_esn' => $value["price"],
                    'registration_start' => DateTime::from($value["start"]),
                    'description' => $value["text"],
                    "university" => $university
                ]);
            } else {
                $this->database->table("event")->where("id", $id)->update([
                    'title' => $value["title"],
                    'capacity' => $value["capacity"],
                    'location' => $value["location"],
                    'event_date' => DateTime::from($value["event"]),
                    'price_with_esn' => $value["priceESN"],
                    'price_without_esn' => $value["price"],
                    'registration_start' => DateTime::from($value["start"]),
                    'description' => $value["text"],
                    "university" => $university
                ]);
            }
            $this->database->commit();
        } catch (ConstraintViolationException $e) {
            $this->database->rollBack();
            throw new NoLeaderException;
        }
    }

    public function addGuest($id, $event)
    {
        try {
            $this->database->beginTransaction();
            $this->database->table("event_list")->insert([
                'event' => $event,
                'data_user' => $id,
            ]);
            $this->database->commit();
        } catch (ConstraintViolationException $e) {
            $this->database->rollBack();
            throw new DuplicateException;
        }
    }

    public function registerForEvent($event, $user)
    {
        $result = $this->isUserRegisteredForEvent($event, $user);
        $attenders = $this->getCountAttenders($event);
        $capacity = $this->getCapacityOfEvent($event);

        if ($result && $result->status == 'paid') {
            return "alreadyPaid";
        } else if ($result) {
            $result->delete();
            return "deleted";
        } else {
            if ($capacity["capacity"] === 0 || $attenders < $capacity["capacity"]) {
                $this->database->table("event_list")->insert([
                    'event' => $event,
                    'data_user' => $user
                ]);
                return "registered";
            } else return "outOfLimit";
        }
    }

    /**
     * Check, if specific user is registered to the event
     *
     * @param $event
     * @param $user
     *
     * @return false|ActiveRow
     */
    public function isUserRegisteredForEvent($event, $user)
    {
        $result = $this->database->table("event_list")
            ->where("data_user", $user)
            ->where("event", $event)->fetch();

        return $result;
    }

    /**
     * Get number of attender for event
     *
     * @param $id
     *
     * @return int
     */
    public function getCountAttenders($id)
    {
        return $this->getGuestList($id)->count("data_user");
    }

    /**
     * Returns list of users able to attend event - with paid fee in case of paid event
     * or all users for events for free.
     * @param $id int
     * @return Selection
     */
    public function getGuestList($id)
    {
        if ($this->isEventFree($id)) {
            return $this->database->table("event_list")->where("event", $id);
        } else {
            return $this->database->table("event_list")->where("event", $id)->where('status', 'paid');
        }
    }

    /**
     * Returns list of users that do not pay for event yet.
     * @param $id int
     * @return Selection
     */
    public function getRegisteredList($id)
    {
        return $this->database->table("event_list")->where("event", $id)->where('status', 'unpaid');
    }

    /**
     * Capacity of the event
     *
     * @param $event
     *
     * @return false|ActiveRow
     */
    public function getCapacityOfEvent($event)
    {
        return $this->database->table("event")->get($event);
    }

    /**
     * Delete user from the event
     *
     * @param $id
     * @param $event
     */
    public function deleteUserFromEvent($id, $event)
    {
        $this->getGuestList($event)->where("data_user", $id)->delete();
    }

    /**
     * Close registration process
     *
     * @param $status
     * @param $id
     */
    public function closeRegistration($status, $id)
    {
        if ($status === "no") $this->getEvent($id)->update([
            "registration_end" => "yes"
        ]);
        else $this->getEvent($id)->update([
            "registration_end" => "no"
        ]);
    }

    /**
     * Change status of user in guest list (paid/unpaid)
     *
     * @param $id int event ID
     * @param $user string user ID
     * @param $status string new status
     *
     * @return int
     */
    public function changeUserPaidForEvent($id, $user, $status)
    {
        return $this->database
            ->table("event_list")
            ->where("data_user", $user)
            ->where("event", $id)
            ->update(['status' => $status]);
    }

    /**
     * Delete event
     *
     * @param $id
     */
    public function deleteEvent($id)
    {
        $this->getEvent($id)->delete();
    }

    /**
     * @param $university
     * @return Selection
     */
    public function getExternalLinks($university)
    {
        return $this->database
            ->table("links")
            ->where("university", $university)
            ->select("url, title");
    }

    public function removeExternalLink($url, $university)
    {
        $this->database
            ->table("links")
            ->where("url", $url)
            ->where("university", $university)
            ->delete();
    }
}