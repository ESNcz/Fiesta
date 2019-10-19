<?php

namespace App\Admin\Presenters;

use App\Forms\BuddyFormFactory;
use App\Grid\BuddySystemGridFactory;
use App\Model\DuplicateException;
use App\Model\MaxLimitException;
use App\Model\PluginRepository;
use Nette\Database\Context;
use Nette\Forms\Container;

/**
 * Class BuddyManagementPresenter
 * @package App\Admin\Presenters
 */
class BuddyManagementPresenter extends BasePresenter
{
    private $database;
    private $buddySettings;
    private $pluginRepository;
    private $buddySystemGridFactory;
    private $buddyFormFactory;

    /**
     * BuddyManagementPresenter constructor.
     * @param Context $database
     * @param PluginRepository $pluginRepository
     * @param BuddySystemGridFactory $buddySystemGridFactory
     * @param BuddyFormFactory $buddyFormFactory
     */
    public function __construct(Context $database,
                                PluginRepository $pluginRepository,
                                BuddySystemGridFactory $buddySystemGridFactory,
                                BuddyFormFactory $buddyFormFactory)
    {
        $this->pluginRepository = $pluginRepository;
        $this->buddySystemGridFactory = $buddySystemGridFactory;
        $this->database = $database;
        $this->buddyFormFactory = $buddyFormFactory;
    }

    function startup()
    {
        parent::startup();
        $buddySettings = $this->pluginRepository->getBuddySettings($this->userRepository->university);
        $this->buddySettings = $buddySettings;
        $this->template->settings = $this->buddySettings;

        if ($this->name == "Admin:BuddyManagement" && $this->action == "request") {
            if (!$this->userRepository->isAllowed("Admin:BuddyManagement", "manualConnection") && $buddySettings["show_manual"]) {
                $this->flashMessage("<b>Ops,</b> you do not have permission to view this page ", "red");
                $this->redirect('Homepage:default');
            }
        }
    }

    public function createComponentViewMyBuddyConnections($name)
    {

        if ($this->userRepository->isInRole("member")) {
            $grid = $this->buddySystemGridFactory->createMemberBuddyConnections();
            $this->addComponent($grid, $name);
        }

        if ($this->userRepository->isInRole("international")) {
            $grid = $this->buddySystemGridFactory->createInternationalBuddyConnections();
            $this->addComponent($grid, $name);
        }
    }

    public function createComponentBuddySystem($name)
    {
        $grid = $this->buddySystemGridFactory->createBuddyRequestGrid(function ($message, $type, $international) {
            if ($type == "info") {
                $result = $this->database->table("pickup_request")
                    ->where("data_user", $international["data_user"])
                    ->where("take", false)
                    ->fetch();

                if (isset($result["id"])) {
                    $setup = $this->getSession('pickUp');
                    $setup[$result["id"]] = [
                        "id" => $result["id"],
                        "date_arrival" => $result["date_arrival"],
                        "place_arrival" => $result["place_arrival"],
                        "description" => $result["description"]
                    ];
                }
            }

            $this->flashMessage($message, $type);
            $this->redirect('this');
        });

        $presenter = $this;
        $this->addAssignMemberButton($presenter, $grid);

        $this->addComponent($grid, $name);
    }

    private function addAssignMemberButton($presenter, $grid)
    {
        if ($this->userRepository->isAllowed("Admin:BuddyManagement", "manualConnection")) {
            return $grid->setItemsDetailForm(function (Container $container) use ($grid, $presenter) {
                $container->addHidden('id');
                $container->addSelect('member')
                    ->getControlPrototype()->setAttribute('class', "buddy-manual");

                $container->addSubmit('save', 'Select')
                    ->onClick[] = function ($button) use ($grid, $presenter) {
                    $values = $button->getParent()->getValues();
                    $getMember = $this->getHttpRequest()->getPost("items_detail_form");

                    foreach ($getMember as $key => $value) {
                        if (isset($value["save"])) {
                            if (isset($value["member"])) {
                                try {
                                    $this->pluginRepository->takeBuddyRequest($value["member"], $values["id"], $this->userRepository->university);
                                    $this->flashMessage("You just assign Buddy connection!", 'info');
                                } catch (DuplicateException $e) {
                                    $this->database->table("buddy_match")->where("id", $values["id"])->update([
                                        'member' => $value["member"]
                                    ]);
                                    $this->flashMessage("You just update Buddy connection!", 'info');
                                } catch (MaxLimitException $e) {
                                    $this->flashMessage("You just reached your limit of buddies.", "red");
                                }
                            } else {
                                $this->flashMessage("You need to choose member!", 'red');
                            }
                        }
                    }

                    $this->redirect('this');
                };
            });
        }
    }

    function handleTakePickUp($id)
    {
        $session = $this->getSession('pickUp');
        unset($session[$id]);
        $member = $this->userRepository->getId();

        try {
            $this->pluginRepository->takePickUpRequest($member, $id, $this->userRepository->university);
            $this->flashMessage("You just made new Pick Up connection!", 'info');

        } catch (DuplicateException $e) {
            $this->flashMessage("This buddy request is already taken.", "red");
        }

        $this->redirect('this');
    }

    /**
     * Component with soreboard
     *
     * @param $name
     */
    public function createComponentBuddyScoreboard($name)
    {
        $grid = $this->buddySystemGridFactory->createBuddyScoreboardGrid();
        $this->addComponent($grid, $name);
    }


    /**
     * Component to view all connection
     *
     * @param $name
     */
    public function createComponentViewAllBuddyConnections($name)
    {
        $grid = $this->buddySystemGridFactory->createBuddyConnectionsGrid(function ($message, $type) {
            $this->flashMessage($message, $type);
            $this->redirect('this');
        });

        $presenter = $this;
        $this->addAssignMemberButton($presenter, $grid);
        $this->addComponent($grid, $name);
    }

    public function createComponentExample($name)
    {
        $grid = $this->buddySystemGridFactory->createExample($this->buddySettings);
        $this->addComponent($grid, $name);
    }

    /**
     * Component to create Buddy request
     */
    public function createComponentCreateBuddyRequest()
    {
        $text = $this->pluginRepository->getBuddyRequest($this->userRepository->getId());
        return $this->buddyFormFactory->createBuddyRequest(function ($values) {
            $values["data_user"] = $this->userRepository->getId();
            $this->pluginRepository->createBuddyRequest($values);
            $this->flashMessage("Your Request was created", "info");
            $this->redirect("this");
        }, $text);
    }

    function renderRequest()
    {
        $session = $this->getSession('pickUp');
        $hasItem = false;
        foreach ($session as $key) {
            $hasItem = true;
        }

        if ($hasItem) {
            $this->template->pickupRequest = $session;
        }
        $this->template->alreadyTaken = $this->pluginRepository->getUserLimit($this->getUser()->getId());
    }

    function renderSettings()
    {
        $buddyTitle = array(
            'show_manual' => "Manual coordination",
            'show_image' => "Show avatar",
            'show_university' => "Show Home University",
            'show_state' => "Show Country",
            'show_faculty' => "Show Faculty",
            'show_gender' => "Show Gender"
        );

        $this->template->buddyTitle = $buddyTitle;
    }

    public function handleChangeStatus($type, $status)
    {
        $this->flashMessage("You just change Buddy status!", 'info');

        $this->pluginRepository->changeBuddyStatus($type, $status, $this->userRepository->university);
        $this->redirect('this');


    }

    public function createComponentSetMaxLimit()
    {
        return $this->buddyFormFactory->setMaxLimit(function () {
            $this->redirect('this');
        });
    }
}
