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
}