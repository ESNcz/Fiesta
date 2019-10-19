<?php

namespace App\Model;

class InternationalRepository extends Repository
{

    /**
     * List of visible plugins for side menu
     * @return mixed
     */
    public function getAllInternational()
    {
        return $this->database->table("role_assignment")
            ->where("role", "international")
            ->where("NOT (user.user_id.status?)", "uncompleted");
    }

    public function deleteInternational($id)
    {
        $count = $this->database->table("role_assignment")
            ->where("data_user", $id)
            ->where("role", "international")->delete();

        return $count;
    }

    /**
     * Change user status (active, banned, pending etc.)
     *
     * @param $id
     * @param $status
     */
    public function changeUserStatus($id, $status)
    {
        $this->database->table("user")
            ->where('user_id', $id)
            ->update(['status' => $status]);
    }
}