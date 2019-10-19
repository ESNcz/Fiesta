<?php

namespace App\Model;

class CountryRepository extends Repository
{

    /**
     * Get all countries
     * @return array
     */
    public function getAllCountries()
    {
        return $this->database
            ->table('country')
            ->order("name")
            ->fetchPairs("code_id", "name");
    }
}