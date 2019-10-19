<?php

namespace App\Forms;

use App\Model\UserRepository;
use Nette\Database\Context;

class BuddyFormFactory
{
    private $renderer;
    private $userRepository;
    private $database;

    /**
     * BuddyFormFactory constructor.
     * @param Context $database
     * @param DefaultFormRenderer $renderer
     * @param UserRepository $userRepository
     */
    public function __construct(Context $database,
                                DefaultFormRenderer $renderer,
                                UserRepository $userRepository)
    {

        $this->renderer = $renderer;
        $this->userRepository = $userRepository;
        $this->database = $database;
    }

    public function createBuddyRequest(callable $onSuccess, $text)
    {
        $form = $this->renderer->create();

        if (isset($text["description"])) {
            $form->addTextArea('text', 'Description:', "30", "10")
                ->setDefaultValue($text["description"])
                ->setRequired();
        } else {
            $form->addTextArea('text', 'Description:', "30", "10")
                ->setRequired();
        }

        $form->addSubmit('send', "Send my buddy request");

        $form->onSuccess[] = function ($form, $values) use ($onSuccess) {
            $onSuccess($values);
        };

        return $form;
    }

    public function setMaxLimit(callable $onSuccess)
    {
        $maxLimit = $this->database
            ->table("buddy_settings")
            ->where("university_id", $this->userRepository->university)
            ->fetchField("limit");
        $form = $this->renderer->create();

        $form->addInteger("limit", "Set max limit")
            ->setDefaultValue($maxLimit)
            ->addRule($form::MIN, null, 0)
            ->setRequired();


        $form->addSubmit('send', 'SAVE');

        $form->onSuccess[] = function ($form, $values) use ($onSuccess) {
            $this->database->table("buddy_settings")
                ->where("university_id", $this->userRepository->university)
                ->update([
                    "limit" => $values["limit"]
                ]);
            $onSuccess();
        };

        return $form;
    }
}