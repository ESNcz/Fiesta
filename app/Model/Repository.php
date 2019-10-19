<?php

namespace App\Model;

use Nette\Database\Context;
use Nette\SmartObject;

abstract class Repository
{
    use SmartObject;

    /** @var Context */
    protected $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }
}