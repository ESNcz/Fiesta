<?php

namespace App\Model;

use Nette\Database\ConstraintViolationException;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Security\IAuthenticator;
use Nette\Security\IAuthorizator;
use Nette\Security\Identity;
use Nette\Security\IUserStorage;
use Nette\Security\Passwords;
use Nette\Security\User;
use Nette\SmartObject;

class UserRepository extends User
{
    use SmartObject;

    const
        TABLE_NAME = 'user',
        TABLE_MODULES = "module_assignment",
        COLUMN_STATUS = 'status',
        COLUMN_PASSWORD_HASH = 'password',
        COLUMN_EMAIL = 'user_id',
        COLUMN_ROLE = 'role',
        COLUMN_UNIVERSITY = 'university',
        COLUMN_VALID = 'valid',
        COLUMN_LAST_LOGIN = 'last_login';
    public $university;
    private $database;
    private $storage;

    /**
     * User constructor.
     * @param Context             $database
     * @param IUserStorage        $storage
     * @param IAuthenticator|null $authenticator
     * @param IAuthorizator|null  $authorizator
     */
    public function __construct(Context $database, IUserStorage $storage,
                                IAuthenticator $authenticator = NULL, IAuthorizator $authorizator = NULL)
    {
        parent::__construct($storage, $authenticator, $authorizator);

        $this->database = $database;
        $this->storage = $storage;

        if ($storage->isAuthenticated()) {
            $this->university = $storage->getIdentity()->university;
        }
    }

    /**
     * @return FALSE|ActiveRow
     */
    public function getUser()
    {
        return $this->getUserById($this->getId());
    }

    /**
     * Get specific user with ID parameter
     *
     * @param $id
     * @return ActiveRow|FALSE
     */
    public function getUserById($id)
    {
        $result = $this->database
            ->table('user')
            ->get($id);
        return $result;
    }

    /**
     * Get id by signature
     *
     * @param $signature
     * @return ActiveRow|FALSE
     */
    public function getIdBySignature($signature)
    {
        return $this->database
            ->table("user")
            ->where("signature", $signature)
            ->select("user_id")->fetch();
    }

    /**
     * @return FALSE|ActiveRow
     */
    public function getData()
    {
        return $this->getDataById($this->getId());
    }

    /**
     * Get user information from specific user
     *
     * @param $id
     * @return ActiveRow|FALSE
     */
    public function getDataById($id)
    {
        $result = $this->database
            ->table('data_user')
            ->get($id);
        return $result;
    }

    /**
     * @return FALSE|ActiveRow
     */
    public function getUniversity()
    {
        return $this->getUniversityById($this->getId());
    }

    /**
     * Get on what university user study
     *
     * @param $id
     * @return ActiveRow|nil
     */
    public function getUniversityById($id)
    {
        $user = $this->getUserById($id);
        if ($user == false) {
            return null;
        }

        return $user->ref("university", "university");
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        if (!$this->getId()) return [$this->guestRole];
        return $this->getRolesById($this->getId());
    }

    /**
     * Get roles
     *
     * @param $id
     * @return string[]
     */
    public function getRolesById($id)
    {
        return $this->database->table("role_assignment")->where("data_user", $id)->fetchPairs(null, "role");
    }

    public function getProfileStrength($filledData)
    {
        /*
         * image 10%
         * esn_card 10%
         * country_id 10%
         * phone 10%
         * birthday 5%
         * home_university 5%
         */

        $markup10 = array(
            "image" => "Add a profile picture",
            "phone_number" => "Add your phone number",
            "country_id" => "Add your country",
            "esn_card" => "Get your ESNCard"
        );

        $markup5 = array(
            "birthday" => "Add your birthday",
            "home_university" => "Add your home university"
        );

        $data["complete"] = 50;

        foreach ($markup10 as $key => $value) {
            if ($filledData[$key] && $filledData[$key] != "Unknown") {
                $data["complete"] += 10;
                unset($markup10[$key]);
            }
        }

        foreach ($markup5 as $key => $value) {
            if ($key === "birthday" && strtotime($filledData[$key]) > 0) {
                $data["complete"] += 5;
                unset($markup5[$key]);
                continue;
            }

            if ($key === "home_university" && $this->isInRole("member") && $filledData[$key] != "Unknown") {
                $data["complete"] += 5;
                unset($markup5[$key]);
                continue;
            }

            if (isset($filledData[$key]) && $key !== "birthday") {
                $data["complete"] += 5;
                unset($markup5[$key]);
            }
        }

        $data["missing"] = array_merge($markup10, $markup5);

        return $data;
    }

    public function isRoleValid($role)
    {
        $result = in_array($role, array("international", "member"));
        return $result;
    }

    public function isAdministrator()
    {
        return $this->isInRole("globalAdmin") ||
            $this->isInRole("admin") ||
            $this->isInRole("editor");
    }

    /**
     * @param $values
     *
     * @throws DuplicateException
     */
    public function setUserData($values)
    {
        try {
            $keys = [
                'name',
                'surname',
                'phone_number',
                'gender',
                'country_id',
                'home_university',
                'birthday',
                'esn_card',
                'phone_number',
                'faculty_id',
                'facebook_url',
                'twitter_url',
                'instagram_url',
                'description'];

            $this->database->beginTransaction();
            $user = $this->database->table("data_user")
                ->where("user_id", $this->getId());

            foreach ($keys as $key) {
                if (array_key_exists($key, $values)) {

                    $user->update([
                        $key => $values[$key]
                    ]);
                };
            }

            $this->database->commit();
        } catch (ConstraintViolationException $e) {
            $this->database->rollBack();
            throw new DuplicateException;
        }
    }

    /**
     * Update logged user information
     *
     * @param $value
     *
     * @return void
     * @throws DuplicateException
     */
    public function updateInformation($value)
    {
        try {
            $this->database->beginTransaction();
            $user = $this->database->table("data_user")->where("user_id", $value->id);
            if (isset($value->newId)) {
                if ($value->id !== $value->newId) {
                    $this->database->table("user")->where("user_id", $value->id)->update([
                        'user_id' => $value->newId
                    ]);
                }
            }

            $user->update([
                'name' => $value->name,
                'surname' => $value->surname,
                'phone_number' => $value->phone_number,
                'gender' => $value->gender,
                'country_id' => $value->country,
                'birthday' => $value->birthday,
                'esn_card' => $value->esn_card,
                'faculty_id' => $value->faculty,
                'home_university' => $value->home_university,
                'facebook_url' => $value->facebook,
                'twitter_url' => $value->twitter,
                'instagram_url' => $value->instagram,
                'description' => $value->text
            ]);
            $this->database->commit();
        } catch (ConstraintViolationException $e) {
            $this->database->rollBack();
            throw new DuplicateException;
        }
    }

    public function setUser($values)
    {
        try {
            $keys = [
                'user_id',
                'status',
                'password',
                'university'];

            $this->database->beginTransaction();
            $user = $this->database->table("user")
                ->where("user_id", $values["user_id"]);

            foreach ($keys as $key) {
                if (array_key_exists($key, $values)) {
                    $user->update([
                        $key => $values[$key]
                    ]);
                };
            }

            if (isset($values["new_email"])) {
                $user->update([
                    "user_id" => $values["new_email"]
                ]);

                if ($this->getId() == $values["user_id"]) {
                    $this->getIdentity()->id = $values["new_email"];
                }
            }

            $this->database->commit();
        } catch (ConstraintViolationException $e) {
            $this->database->rollBack();
            throw new DuplicateException;
        }
    }

    public function setStatus($status, $user)
    {
        $this->database->table("user")
            ->where("user_id", $user)
            ->update([
                "status" => $status
            ]);
    }

    public function refreshIdentity()
    {
        $id = $this->getId();
        if ($id != false) {
            $user = $this->database->table("user")
                ->select("status , university, signature, last_login, valid")
                ->get($id)->toArray();

            $roles = $this->database->table("role_assignment")->where("data_user", $id)->fetchPairs(null, "role");

            $data = $this->database->table('data_user')->get($id)->toArray();

            $identity = new Identity($id, $roles, array_merge($user, $data));

            $this->storage->setIdentity($identity);
        }
    }

    /**
     * @param $string
     * @param $university
     * @return Selection
     */
    public function getListByRoleAndUniversity($string, $university)
    {
        $result = $this->database->table("role_assignment")
            ->where("role", $string)
            ->where("user.user_id.university", $university);
        return $result;
    }

    public function setNewPassword($email, $password)
    {
        $user = $this->database->table("user")->get($email);
        $user->update([
            "password" => Passwords::hash($password)
        ]);
    }

    public function transferUser($values)
    {
        $user = $this->database->table("user")->get($values["email"]);
        $user->update([
            "university" => $values["section"]
        ]);
    }

    /**
     * Set new role
     *
     * @param $id
     * @param $role
     *
     * @throws DuplicateException
     */
    public function setNewRole($id, $role)
    {
        try {
            $this->database->table("role_assignment")->insert([
                "data_user" => $id,
                "role" => $role]);
        } catch (ConstraintViolationException $e) {
            throw new DuplicateException;
        }
    }

    /**
     * Returns filepath to avatar if file exists
     * and NULL otherwise (non existing file/not logged user/not given signature)
     *
     * @param string|null $signature signature of specific user
     * @return string|void
     */
    public function getProfileAvatar($signature = NULL)
    {
        if (!$signature && $identity = $this->getIdentity()) {
            $signature = $identity->signature;
        }
        if (!$signature) return;

        $path = "images/avatar/{$signature}.jpg";

        return file_exists($path) ? $path : NULL;
    }
}