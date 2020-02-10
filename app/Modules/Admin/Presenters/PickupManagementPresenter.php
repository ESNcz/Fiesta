<?php

namespace App\Admin\Presenters;

use App\Forms\PickUpFormFactory;
use App\Grid\PickUpSystemGridFactory;
use App\Model\DuplicateException;
use App\Model\MaxLimitException;
use App\Model\PluginRepository;
use Nette\Database\Context;
use Nette\Forms\Container;
use Nette\Utils\DateTime;

/**
 * Class PickupManagementPresenter
 * @package App\Admin\Presenters
 */
class PickupManagementPresenter extends BasePresenter
{
    private $database;
    private $pickUpSettings;
    private $pluginRepository;
    private $pickUpSystemGridFactory;
    private $pickUpFormFactory;

    /**
     * PickUpManagementPresenter constructor.
     * @param Context $database
     * @param PluginRepository $pluginRepository
     * @param PickUpSystemGridFactory $pickUpSystemGridFactory
     * @param PickUpFormFactory $pickUpFormFactory
     */
    public function __construct(Context $database,
                                PluginRepository $pluginRepository,
                                PickUpSystemGridFactory $pickUpSystemGridFactory,
                                PickUpFormFactory $pickUpFormFactory)
    {
        $this->pluginRepository = $pluginRepository;
        $this->pickUpSystemGridFactory = $pickUpSystemGridFactory;
        $this->database = $database;
        $this->pickUpFormFactory = $pickUpFormFactory;
    }

    function startup()
    {
        parent::startup();
        $pickUpSettings = $this->pluginRepository->getpickUpSettings($this->userRepository->university);
        $this->pickUpSettings = $pickUpSettings;
        $this->template->settings = $this->pickUpSettings;

        if ($this->name == "Admin:PickupManagement" && $this->action == "request") {
            if (!$this->userRepository->isAllowed("Admin:PickupManagement", "manualConnection") && $pickUpSettings["show_manual"]) {
                $this->flashMessage("<b>Ops,</b> you do not have permission to view this page ", "red");
                $this->redirect('Homepage:default');
            }
        }
    }

    public function createComponentViewMyPickUpConnections($name)
    {

        if ($this->userRepository->isInRole("member")) {
            $grid = $this->pickUpSystemGridFactory->createMemberPickUpConnections();
            $this->addComponent($grid, $name);
        }

        if ($this->userRepository->isInRole("international")) {
            $grid = $this->pickUpSystemGridFactory->createInternationalPickUpConnections();
            $this->addComponent($grid, $name);
        }
    }

    public function createComponentPickUpSystem($name)
    {
        $grid = $this->pickUpSystemGridFactory->createPickUpRequestGrid(function ($message, $type, $international) {
            $result = $this->database->table("buddy_request")
                ->where("data_user", $international["data_user"])
                ->where("take", false)
                ->fetch();

            if (isset($result["id"])) {
                $setup = $this->getSession('buddyRequest');
                $setup[$result["id"]] = [
                    "id" => $result["id"],
                    "description" => $result["description"]
                ];
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
        if ($this->userRepository->isAllowed("Admin:PickupManagement", "manualConnection")) {
            return $grid->setItemsDetailForm(function (Container $container) use ($grid, $presenter) {
                $container->addHidden('id');
                $container->addSelect('member')
                    ->getControlPrototype()->setAttribute('class', "PickUp-manual");

                $container->addSubmit('save', 'Select')
                    ->onClick[] = function ($button) use ($grid, $presenter) {
                    $values = $button->getParent()->getValues();
                    $getMember = $this->getHttpRequest()->getPost("items_detail_form");

                    foreach ($getMember as $key => $value) {
                        if (isset($value["save"])) {
                            if (isset($value["member"])) {
                                try {
                                    $this->pluginRepository->takePickUpRequest($value["member"], $values["id"], $this->userRepository->university);
                                    $this->flashMessage("You just assign pick up connection!", 'info');
                                } catch (DuplicateException $e) {
                                    $this->database->table("PickUp_match")->where("id", $values["id"])->update([
                                        'member' => $value["member"]
                                    ]);
                                    $this->flashMessage("You just update pick up connection!", 'info');
                                } catch (MaxLimitException $e) {
                                    $this->flashMessage("Over limit.", "red");
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

    function handleTakeBuddy($id)
    {
        $session = $this->getSession('buddyRequest');
        unset($session[$id]);
        $member = $this->userRepository->getId();

        try {
            $this->pluginRepository->takeBuddyRequestWithLimit($member, $id, $this->userRepository->university);
            $this->flashMessage("You just made new buddy connection!", 'info');

        } catch (DuplicateException $e) {
            $this->flashMessage("This buddy request is already taken.", "red");
        } catch (MaxLimitException $e) {
            $this->flashMessage("You just reached your limit of buddies.", "red");
        }

        $this->redirect('this');
    }

    /**
     * Component with soreboard
     *
     * @param $name
     */
    public function createComponentPickUpScoreboard($name)
    {
        $grid = $this->pickUpSystemGridFactory->createPickUpScoreboardGrid();
        $this->addComponent($grid, $name);
    }


    /**
     * Component to view all connection
     *
     * @param $name
     */
    public function createComponentViewAllPickUpConnections($name)
    {
        $grid = $this->pickUpSystemGridFactory->createPickUpConnectionsGrid(function ($message, $type) {
            $this->flashMessage($message, $type);
            $this->redirect('this');
        });

        $presenter = $this;
        $this->addAssignMemberButton($presenter, $grid);
        $this->addComponent($grid, $name);
    }

    public function createComponentExample($name)
    {
        $grid = $this->pickUpSystemGridFactory->createExample($this->pickUpSettings);
        $this->addComponent($grid, $name);
    }

    /**
     * Component to create PickUp request
     */
    public function createComponentCreatePickUpRequest()
    {
        $text = $this->pluginRepository->getPickUpRequest($this->userRepository->getId());
        
        return $this->pickUpFormFactory->createPickUpRequest(function ($values) {
            $values["data_user"] = $this->userRepository->getId();
            $values["date_arrival"] = DateTime::createFromFormat("j. n. Y H:i", $values["date_arrival"]);

            $this->pluginRepository->createPickUpRequest($values);
            $this->flashMessage("Your Request was created", "info");
            $this->redirect("this");
        }, $text);
    }

    function renderRequest()
    {
        $session = $this->getSession('buddyRequest');
        $hasItem = false;
        foreach ($session as $key) {
            $hasItem = true;
        }

        if ($hasItem) {
            $this->template->buddyRequest = $session;
        }
        $this->template->alreadyTaken = $this->pluginRepository->getUserLimit($this->getUser()->getId());
    }

    function renderSettings()
    {
        $pickUpTitle = array(
            'show_manual' => "Manual coordination",
            'show_image' => "Show avatar",
            'show_university' => "Show Home University",
            'show_state' => "Show Country",
            'show_faculty' => "Show Faculty",
            'show_gender' => "Show Gender"
        );

        $this->template->pickUpTitle = $pickUpTitle;
    }

    public function handleChangeStatus($type, $status)
    {
        $this->flashMessage("You just change PickUp status!", 'info');

        $this->pluginRepository->changePickUpStatus($type, $status, $this->userRepository->university);
        $this->redirect('this');


    }
}
