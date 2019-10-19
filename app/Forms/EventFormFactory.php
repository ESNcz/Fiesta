<?php

namespace App\Forms;

use App\Model\UserRepository;
use Nette\Database\Context;
use Nette\Forms\Form;

class EventFormFactory
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

    /**
     * @param callable $onSuccess
     *
     * @return Form
     */
    public function createAddEvent(callable $onSuccess)
    {

        $form = $this->renderer->create();
        $form->addText('title', 'Event Title:')
            ->addRule(Form::MAX_LENGTH, null, 20)
            ->setRequired("Please provide an event name.");

        $form->addSelect('leader', 'Event Leader:')
            ->setPrompt("Leader")
            ->setAttribute('class', "section-autocomplete");

        $form->addText('location', 'Location:')
            ->setRequired("A specific location helps guests know where to go.");

        $form->addInteger('capacity', 'Capacity:')
            ->setDefaultValue(0)
            ->addRule(Form::MIN, null, 0)
            ->setRequired();

        $form->addDate('event', 'Event date:')
            ->setHtmlAttribute("id", 'datetimepickerfrom')
            ->setRequired("Please provide an event date.");

        $form->addInteger('priceESN', 'Price with ESNcard:')
            ->setDefaultValue(0)
            ->addRule(Form::MIN, null, 0)
            ->setRequired();

        $form->addInteger('price', 'Price without ESNcard:')
            ->setDefaultValue(0)
            ->addRule(Form::MIN, null, 0)
            ->setRequired();

        $form->addDate('start', 'Registration start:')
            ->setHtmlAttribute("id", 'datetimepickerstart')
            ->setRequired();

        $form->addTextArea('text', 'Event Description:', "30", "10")
            ->setHtmlAttribute("id", 'summernote');

        $form->addSubmit('send', "Create Event");


        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $values["leader"] = $form->getHttpData($form::DATA_LINE, 'leader');
            $onSuccess($values);
        };

        return $form;
    }

    public function createAddGuest(callable $onSuccess)
    {

        $form = $this->renderer->create();

        $form->addSelect('guest', 'Invite new guest:')
            ->setAttribute('class', "user-autocomplete");


        $form->addSubmit('send', "Invite User");


        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $values = $form->getHttpData($form::DATA_LINE, 'guest');
            $onSuccess($values);
        };

        return $form;
    }

    public function createEditEvent($event, callable $onSuccess)
    {
        $form = $this->renderer->create();
        $form->addText('title', 'Event Title:')
            ->addRule(Form::MAX_LENGTH, null, 20)
            ->setDefaultValue($event["title"])
            ->setRequired("Please provide an event name.");

        $form->addSelect('leader', 'Actual Event Leader: ' . $event->ref("data_user", "user_id")->name . " " . $event->ref("data_user", "user_id")->surname)
            ->setAttribute('class', "member-autocomplete");

        $form->addText('location', 'Location:')
            ->setDefaultValue($event["location"])
            ->setRequired("A specific location helps guests know where to go.");

        $form->addInteger('capacity', 'Capacity:')
            ->setDefaultValue($event["capacity"])
            ->addRule(Form::MIN, null, 0)
            ->setRequired();

        $form->addDate('event', 'Event date:')
            ->setDefaultValue(date("j. n. Y H:i", strtotime($event["event_date"])))
            ->setHtmlAttribute("id", 'datetimepickerfrom')
            ->setRequired("Please provide an event date.");

        $form->addInteger('priceESN', 'Price with ESNcard:')
            ->setDefaultValue($event["price_with_esn"])
            ->addRule(Form::MIN, null, 0)
            ->setRequired();

        $form->addInteger('price', 'Price without ESNcard:')
            ->setDefaultValue($event["price_without_esn"])
            ->addRule(Form::MIN, null, 0)
            ->setRequired();

        $form->addDate('start', 'Registration start:')
            ->setDefaultValue(date("j. n. Y", strtotime($event["registration_start"])))
            ->setHtmlAttribute("id", 'datetimepickerstart')
            ->setRequired();

        $form->addTextArea('text', 'Event Description:', "30", "10")
            ->setDefaultValue($event["description"])
            ->setHtmlAttribute("id", 'summernote');

        $form->addSubmit('send', "Update Event");


        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $values["leader"] = $form->getHttpData($form::DATA_LINE, 'leader');
            $onSuccess($values);
        };

        return $form;
    }
}