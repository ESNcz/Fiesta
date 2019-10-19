<?php

namespace App\Model;

class AdminRepository extends Repository
{

    /**
     * List of visible plugins for side menu
     * @param $university
     * @return mixed
     */
    public function getAllAdmins($university)
    {
        return $this->database->table("role_assignment")
            ->where("role", "admin")
            ->where("user.user_id.university", $university)
            ->where("NOT (user.user_id.status?)", "uncompleted");
    }

    public function deleteAdmin($id)
    {
        $count = $this->database->table("role_assignment")
            ->where("data_user", $id)
            ->where("role", "admin")->delete();

        return $count;
    }
}