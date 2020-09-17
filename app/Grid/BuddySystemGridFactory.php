<?php

namespace App\Grid;

use App\Model\DuplicateException;
use App\Model\MaxLimitException;
use App\Model\PluginRepository;
use App\Model\UniversityRepository;
use App\Model\UserRepository;
use Nette\Database\Context;

class BuddySystemGridFactory extends Grid
{
    private $userRepository;
    private $pluginRepository;
    private $universityRepository;
    
    /**
     * BuddySystemGridFactory constructor.
     * @param Context $database
     * @param UserRepository $userRepository
     * @param UniversityRepository $universityRepository
     * @param PluginRepository $pluginRepository
     */
    public function __construct(Context $database,
                                UserRepository $userRepository,
                                UniversityRepository $universityRepository,
                                PluginRepository $pluginRepository)
    {
        parent::__construct($database);
        $this->pluginRepository = $pluginRepository;
        $this->userRepository = $userRepository;
        $this->universityRepository = $universityRepository;
    }

    public function createBuddyRequestGrid(callable $onSuccess)
    {
        $university = $this->userRepository->university;
        $buddySettings = $this->pluginRepository->getBuddySettings($university);
        $buddyRequest = $this->pluginRepository->getAllBuddyRequests($university);

        $grid = $this->createDatagrid();
        $grid->setColumnsHideable();

        $grid->setPrimaryKey("id");
        $grid->setOuterFilterRendering();
        $grid->setDataSource($buddyRequest);
        $grid->setRememberState(FALSE);

        $this->showBuddyRequest($grid, $buddySettings);

        if ($buddySettings["show_faculty"]) {
            $grid->showFaculty($this->universityRepository->getAllFaculties($university));
        }

        if ($buddySettings["show_state"]) {
            $grid->showCountry();
        }

        if ($this->userRepository->isInRole("admin")) {
            $grid->showDeleteRequest(function ($id) use ($onSuccess) {
                $this->pluginRepository->deleteBuddyRequest($id);
                $onSuccess("You just delete connection", "info");
            });
        }


        $this->showBuddyTakeButton($grid, function ($international) use ($onSuccess) {

            $internationalEmail = $this->database->table("buddy_request")
                ->where("id", $international)
                ->select("data_user")->fetch();

            $university = $this->userRepository->university;

            try {
                $this->pluginRepository->takeBuddyRequestWithLimit($this->userRepository->getId(), $international, $university);
            } catch (DuplicateException $e) {
                $onSuccess("This buddy request is already taken.", "red", $internationalEmail);
                return;
            } catch (MaxLimitException $e) {
                $onSuccess("You just reached your limit of buddies.", "red", $internationalEmail);
                return;
            }

            $onSuccess("You just made new Buddy connection!", 'info', $internationalEmail);
        });


        if ($this->userRepository->isAllowed("Admin:BuddyManagement", "manualConnection")) {
            $grid->setItemsDetail(__DIR__ . '/templates/grid.item.detail.latte')
                ->setIcon("")
                ->setText("Assign member")
                ->setClass("btn btn-sm btn-primary ajax");
        }

        return $grid;
    }

    private function showBuddyRequest($grid, $buddySettings)
    {
        $grid->addColumnText('description', 'Description')
            ->setTemplate(__DIR__ . '/templates/grid.request.latte', ["settings" => $buddySettings]);
    }

    private function showBuddyTakeButton($grid, callable $onSuccess)
    {
        $grid->addActionCallback('buddyTake', 'Take', function ($id) use ($onSuccess) {
            $onSuccess($id);
            die;
        })
            ->setClass('btn btn-sm btn-primary ajax')
            ->setConfirm(function () {
                return 'Do you really want to take this user as a Buddy?';
            });
    }

    public function createBuddyScoreboardGrid()
    {
        $university = $this->userRepository->university;
        $scoreboard = $this->database->table("buddy_match")
            ->select("member AS data_user")
            ->where("university", $university)
            ->select('COUNT(member) AS count_member, member')
            ->group("member")
            ->order("count_member DESC");

        $grid = $this->createDatagrid();
        $grid->setColumnsHideable();
        $grid->setPrimaryKey("data_user");
        $grid->setDataSource($scoreboard);

        $grid->showProfileColumnWithEmail('member');

        $grid->addColumnText('count_member', 'Connections', "count_member")
            ->setSortable("count_member");

        $grid->addFilterDateRange("created", "Date range", "created");

        return $grid;
    }

    public function createBuddyConnectionsGrid(callable $onSuccess)
    {
        $university = $this->userRepository->university;
        $connections = $this->pluginRepository->getAllBuddyConnections($university);
        $grid = $this->createDatagrid();
        $grid->setColumnsHideable();
        $grid->setDataSource($connections);


        $this->showInternationalProfile($grid);
        $this->showMemberProfile($grid);

        $grid->showDeleteRequest(function ($id) use ($onSuccess) {
            $this->pluginRepository->deleteBuddyMatch($id);
            $onSuccess("You just delete connection", "info");
        });

        if ($this->userRepository->isAllowed("Admin:BuddyManagement", "manualConnection")) {
            $grid->setItemsDetail(__DIR__ . '/templates/grid.item.detail.latte')
                ->setIcon("")
                ->setText("Assign member")
                ->setClass("btn btn-sm btn-primary ajax");
        }

        return $grid;
    }

    private function showInternationalProfile($grid)
    {
        $grid->addColumnText('international', 'International', "international")
            ->setTemplate(__DIR__ . '/templates/grid.international.profile.latte')
            ->setSortable("international")
            ->setFilterText()
            ->setCondition(function ($fluent, $value) {
                $fluent->where("concat_ws(' ', international.name, international.surname) LIKE ? OR international LIKE ?", ['%' . $value . '%', '%' . $value . '%']);
            });
    }

    private function showMemberProfile($grid)
    {
        $grid->addColumnText('member', 'ESN member', "member")
            ->setTemplate(__DIR__ . '/templates/grid.member.profile.latte')
            ->setSortable("member")
            ->setFilterText()
            ->setCondition(function ($fluent, $value) {
                $fluent->where("concat_ws(' ', member.name, member.surname) LIKE ? OR member LIKE ?", ['%' . $value . '%', '%' . $value . '%']);
            });
    }

    public function createMemberBuddyConnections()
    {
        $university = $this->userRepository->university;
        $connections = $this->pluginRepository->getAllBuddyConnections($university)->where("member", $this->userRepository->getId());

        $faculties = $this->universityRepository->getAllFaculties($university);
        $faculties["multiselect"] = $faculties["short"];
        $faculties["short"][""] = "Unknown";

        $grid = $this->createDatagrid();
        $grid->setColumnsHideable();
        $grid->setDataSource($connections);
        $grid->setOuterFilterRendering();
        $grid->setRememberState(FALSE);

        $this->showInternationalProfile($grid);

        $grid->addColumnText('description', 'Description', "buddy_request.description:international");

        $grid->addColumnText('phone_number', 'Phone', "data_user.phone_number:international")
            ->setSortable("international.phone_number")
            ->setFilterText('international.phone_number');

        $grid->addColumnText('country', 'Country', "data_user.country_id:international")
            ->setTemplate(__DIR__ . '/templates/grid.flagForConnections.latte')
            ->setSortable("international.country_id")
            ->setFilterText("international.country_id.name");

        $grid->addColumnText('faculty_id', 'Faculty', "data_user.faculty_id:international")
            ->setSortable("international.faculty_id")
            ->setReplacement($faculties["short"]);

        $grid->addFilterMultiSelect('faculty_id', 'Faculty', $faculties["multiselect"], "international.faculty_id");

        return $grid;
    }

    public function createInternationalBuddyConnections()
    {
        $university = $this->userRepository->university;
        $connections = $this->pluginRepository->getAllBuddyConnections($university)->where("international", $this->userRepository->getId());

        $faculties = $this->universityRepository->getAllFaculties($university);
        $faculties["multiselect"] = $faculties["short"];
        $faculties["short"][""] = "Unknown";

        $grid = $this->createDatagrid();
        $grid->setColumnsHideable();
        $grid->setDataSource($connections);
        $grid->setOuterFilterRendering();
        $grid->setRememberState(FALSE);

        $this->showMemberProfile($grid);

        $grid->addColumnText('phone_number', 'Phone', "data_user.phone_number:member")
            ->setSortable("member.phone_number")
            ->setFilterText('member.phone_number');

        $grid->addColumnText('country', 'Country', "data_user.country_id:member")
            ->setTemplate(__DIR__ . '/templates/grid.flagForConnections.latte')
            ->setSortable("member.country_id")
            ->setFilterText("member.country_id.name");

        $grid->addColumnText('faculty_id', 'Faculty', "data_user.faculty_id:member")
            ->setSortable("international.faculty_id")
            ->setReplacement($faculties["short"]);

        $grid->addFilterMultiSelect('faculty_id', 'Faculty', $faculties["multiselect"], "member.faculty_id");

        return $grid;
    }

    public function createExample($buddySettings)
    {
        $grid = $this->createDatagrid();

        $example = array(
            ['id' => 1, 'faculty' => 'LAW']
        );

        $grid->setDataSource($example);

        $grid->setColumnsHideable();

        $grid->addColumnText('description', 'Description')
            ->setTemplate(__DIR__ . '/templates/grid.request.example.latte', ["settings" => $buddySettings]);

        if ($buddySettings["show_state"]) {
            $grid->addColumnText('country', 'Country')
                ->setTemplate(__DIR__ . '/templates/grid.flag.example.latte');
        }

        if ($buddySettings["show_faculty"]) {
            $grid->addColumnText('faculty', 'Faculty');
        }

        return $grid;
    }
}
