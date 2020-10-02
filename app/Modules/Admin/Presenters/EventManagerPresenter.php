<?php

namespace App\Admin\Presenters;


use App\Forms\DefaultFormRenderer;
use App\Forms\EventFormFactory;
use App\Grid\EventGridFactory;
use App\Model\DuplicateException;
use App\Model\NoLeaderException;
use App\Model\Paginator;
use App\Model\PluginRepository;
use Nette\Application\AbortException;
use Nette\Forms\Form;
use Nette\Utils\DateTime;
use Ublaboo\DataGrid\Column\ColumnStatus;
use Ublaboo\DataGrid\Exception\DataGridColumnNotFoundException;

/**
 * Class EventManagerPresenter
 * @package App\Admin\Presenters
 */
class EventManagerPresenter extends BasePresenter
{

    private $renderer;
    private $eventGridFactory;
    private $pluginRepository;
    private $eventFormFactory;

    /**
     * EventManagerPresenter constructor.
     * @param DefaultFormRenderer $renderer
     * @param EventFormFactory    $eventFormFactory
     * @param EventGridFactory    $eventGridFactory
     * @param PluginRepository    $pluginRepository
     */
    public function __construct(DefaultFormRenderer $renderer,
                                EventFormFactory $eventFormFactory,
                                EventGridFactory $eventGridFactory,
                                PluginRepository $pluginRepository)
    {
        $this->renderer = $renderer;
        $this->eventGridFactory = $eventGridFactory;
        $this->pluginRepository = $pluginRepository;
        $this->eventFormFactory = $eventFormFactory;
    }

    /**
     * @param $name
     */
    public function createComponentGuestList($name)
    {
        $id = $this->getParameter('event');

        $grid = $this->eventGridFactory->createGuestListGrid($id);

        try {
            /** @var ColumnStatus $status */
            $status = $grid->getColumn('status');
            $status->onChange[] = [$this, 'changeUserInEventStatus'];
        } catch (DataGridColumnNotFoundException $e) {
        }

        $this->addComponent($grid, $name);
    }

    /**
     * @return \Nette\Application\UI\Form
     */
    public function createComponentAddGuest()
    {
        return $this->eventFormFactory->createAddGuest(function ($id) {
            try {
                $this->pluginRepository->addGuest($id, $this->getParameter('event'));
            } catch (DuplicateException $e) {
                $this->flashMessage("You cannot add guest twice.", "red");
                return;
            }
            $this->flashMessage("Your add new user.", "info");
            $this->redirect("EventManager:view", ["event" => $this->getParameter('event')]);
        });
    }

    public function handleRegisterForEvent()
    {

        $result = $this->pluginRepository->registerForEvent($this->getParameter('event'), $this->userRepository->getId());

        switch ($result) {
            case "registered":
                $this->flashMessage("Your join to the event.", "info");
                break;
            case "deleted":
                $this->flashMessage("Your left from the event.", "info");
                break;
            case "outOfLimit":
                $this->flashMessage("Out of limit", "red");
                break;
        }

        $this->redirect("EventManager:view", ["event" => $this->getParameter('event')]);
    }

    /**
     * @param $id
     * @param $name
     * @throws AbortException
     */
    public function handleDeleteUser($id, $name)
    {

        $this->pluginRepository->deleteUserFromEvent($id, $this->getParameter('event'));
        $this->flashMessage("User $name was deleted from guest list", 'info');

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this->redrawControl('registrationButton');
            $this->redrawControl('attendCounter');
            $this['guestList']->reload();
        } else {
            $this->redirect('this');
        }
    }

    /**
     * @param $status
     * @throws AbortException
     */
    public function handleCloseRegistration($status)
    {
        $this->pluginRepository->closeRegistration($status, $this->getParameter('event'));

        if ($status === "yes") $this->flashMessage("You open registration", "info");
        else $this->flashMessage("You close registration.", "info");

        $this->redirect("this");
    }

    public function handleDeleteEvent()
    {
        $this->pluginRepository->deleteEvent($this->getParameter('event'));
        $this->flashMessage("Event was successfully delete", 'success');
        $this->redirect('EventManager:default');
    }

    function renderDefault($page = 1)
    {
        $upcomingEvents = $this->pluginRepository->upcomingEvents($this->userRepository->university);

        $lastPage = 0;
        $this->template->upcomingEvents = $upcomingEvents->page($page, 5, $lastPage);
        $pagination = new Paginator($page, $lastPage);

        $this->template->user_vote = $upcomingEvents;
        $this->template->page = $page;
        $this->template->steps = $pagination->getSteps();
        $this->template->lastPage = $lastPage;

    }

    function renderPast($page = 1)
    {
        $upcomingEvents = $this->pluginRepository->pastEvents($this->userRepository->university);

        $lastPage = 0;
        $this->template->upcomingEvents = $upcomingEvents->page($page, 5, $lastPage);
        $pagination = new Paginator($page, $lastPage);

        $this->template->user_vote = $upcomingEvents;
        $this->template->page = $page;
        $this->template->steps = $pagination->getSteps();
        $this->template->lastPage = $lastPage;

    }

    /**
     * @param $event
     */
    function renderView($event)
    {
        $this->template->isUserRegisteredForEvent = $this->pluginRepository->isUserRegisteredForEvent($event, $this->userRepository->getId());
        $this->template->event = $this->pluginRepository->getEvent($event);
        $this->template->attenders = $this->pluginRepository->getCountAttenders($event);
    }

    function renderEdit($event)
    {
    }

    /**
     * @return Form
     */
    protected function createComponentNewEvent()
    {

        return $this->eventFormFactory->createAddEvent(function ($values) {
            try {
                $values["event"] = DateTime::createFromFormat("j. n. Y H:i", $values["event"]);
                $values["start"] = DateTime::createFromFormat("j. n. Y", $values["start"]);
                $this->pluginRepository->setEvent($values, $this->userRepository->university);
            } catch (NoLeaderException $e) {
                $this->flashMessage("You have to choose leader.", "red");
                return;
            }

            $this->flashMessage("Your event is created.", "green");
            $this->redirect("EventManager:default");
        });
    }

    /**
     * @return \Nette\Application\UI\Form
     */
    protected function createComponentEditEvent()
    {
        $event = $this->pluginRepository->getEvent($this->getParameter('event'));

        return $this->eventFormFactory->createEditEvent($event, function ($values) {
            try {
                $values["event"] = DateTime::createFromFormat("j. n. Y H:i", $values["event"]);
                $values["start"] = DateTime::createFromFormat("j. n. Y", $values["start"]);
                $this->pluginRepository->updateEvent($values, $this->getParameter('event'), $this->userRepository->university);
            } catch (NoLeaderException $e) {
                $this->flashMessage("You have to choose leader.", "red");
                return;
            }

            $this->flashMessage("Your event is updated.", "green");
            $this->redirect("EventManager:view", ["event" => $this->getParameter('event')]);
        });
    }


    public function changeUserInEventStatus($userId, $status)
    {
        $event = $this->getParameter('event');
        $this->pluginRepository->changeUserPaidForEvent($event, $userId, $status);
        $this->flashMessage("Status was updated to $status.", 'success');

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this['guestList']->redrawItem($userId);
        } else {
            $this->redirect('this');
        }
    }
}