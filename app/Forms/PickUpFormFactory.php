<?php

namespace App\Forms;

use App\Model\UserRepository;
use Nette\Database\Context;

class PickUpFormFactory
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

    public function createPickUpRequest(callable $onSuccess, $text)
    {
        $form = $this->renderer->create();

        if (isset($text["description"]) && isset($text["place_arrival"]) && $text["date_arrival"]) {
            $form->addDate('date_arrival', 'Date Arrival:')
                ->setDefaultValue(date("j. n. Y H:i", strtotime($text["date_arrival"])))
                ->setHtmlAttribute("id", 'datetimepickerfrom')
                ->setRequired("Please provide an arrival date.");


            $form->addText('place_arrival', 'Place Arrival:')
                ->setDefaultValue($text["place_arrival"])
                ->setRequired("Please provide a place arrival.");

            $form->addTextArea('text', 'Description:',"30","10")
                ->setDefaultValue($text["description"])
                ->setRequired();
        } else {
            $form->addDate('date_arrival', 'Date Arrival:')
                ->setHtmlAttribute("id", 'datetimepickerfrom')
                ->setRequired("Please provide an arrival date.");


            $form->addText('place_arrival', 'Place Arrival:')
                ->setRequired("Please provide a place arrival.");

            $form->addTextArea('text', 'Description:',"30","10")
                ->setRequired();
        }

        $form->addSubmit('send', "Send my Pick Up request");

        $form->onSuccess[] = function ($form, $values) use ($onSuccess) {
            $onSuccess($values);
        };

        return $form;
    }
}