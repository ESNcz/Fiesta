<?php

namespace App\Model;

class UniversityRepository extends Repository
{

    public function getAllUniversities()
    {
        $result = $this->database
            ->table("university")
            ->fetchPairs("id", "name");

        return $result;
    }

    public function isUniversityValid($university)
    {
        return $this->database->table("university")->where("id", $university)->count();
    }

    public function getUniversity($university)
    {
        return $this->database->table("university")->get($university);
    }

    public function getAllFaculties($university)
    {
        $result["long"] = $this->database
            ->table("faculty")
            ->where("university_id ?", $university)
            ->fetchPairs("id", "faculty");

        $result["short"] = $this->database
            ->table("faculty")
            ->where("university_id ?", $university)
            ->fetchPairs("id", "faculty_shortcut");

        return $result;
    }
}