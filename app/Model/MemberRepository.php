<?php

namespace App\Model;

use Nette\Database\Table\Selection;

class MemberRepository extends Repository
{

    /**
     * @return Selection
     */
    function getAllMembers()
    {
        return $this->database->table("role_assignment")
            ->where("role", "member")
            ->where("NOT (user.user_id.status?)", "uncompleted");
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

    /**
     * Change user role (typically member->international)
     *
     * @param $id string user id
     * @param $old string old user role
     * @param $new string new user role to assign
     */
    public function changeUserRole($id, $old, $new)
    {
        $this->database->table("role_assignment")
            ->where('data_user', $id)
            ->where('role', $old)
            ->update(['role' => $new]);
    }
}