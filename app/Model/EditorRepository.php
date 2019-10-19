<?php

namespace App\Model;

class EditorRepository extends Repository
{

    /**
     * List of visible plugins for side menu
     * @param $university
     * @return mixed
     */
    public function getAllEditors($university)
    {
        return $this->database->table("role_assignment")
            ->where("role", "editor")
            ->where("user.user_id.university", $university)
            ->where("NOT (user.user_id.status?)", "uncompleted");
    }

    public function deleteEditor($id)
    {
        $count = $this->database->table("role_assignment")
            ->where("data_user", $id)
            ->where("role", "editor")->delete();

        return $count;
    }
}