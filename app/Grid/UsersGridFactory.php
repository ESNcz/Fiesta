<?php

namespace App\Grid;

use App\Mailing\MailFactory;
use App\Model\InternationalRepository;
use App\Model\MemberRepository;
use App\Model\UniversityRepository;
use App\Model\UserRepository;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Ublaboo\DataGrid\Row;

class UsersGridFactory extends Grid
{
    private $userRepository;
    private $memberRepository;
    private $universityRepository;
    private $university;
    private $mailFactory;
    private $internationalRepository;

    /**
     * UsersGridFactory constructor.
     * @param UserRepository          $userRepository
     * @param MemberRepository        $memberRepository
     * @param InternationalRepository $internationalRepository
     * @param MailFactory             $mailFactory
     * @param UniversityRepository    $universityRepository
     */
    public function __construct(UserRepository $userRepository,
                                MemberRepository $memberRepository,
                                InternationalRepository $internationalRepository,
                                MailFactory $mailFactory,
                                UniversityRepository $universityRepository)
    {
        $this->userRepository = $userRepository;
        $this->memberRepository = $memberRepository;
        $this->universityRepository = $universityRepository;
        $this->mailFactory = $mailFactory;
        $this->internationalRepository = $internationalRepository;
    }

    public function createAllMembersGrid(callable $onSuccess)
    {
        $members = $this->memberRepository->getAllMembers();
        $grid = $this->createDatagrid();
        $grid->setColumnsHideable();

        $grid->setPrimaryKey("data_user");

        if ($this->userRepository->isInRole("globalAdmin")) {
            $grid->setDataSource($members);
        } else {
            $grid->setDataSource($members->where("status", array("active", "enabled")));
        }

        $grid->showProfileColumnWithoutEmail();
        $grid->showCountry();
        $grid->showHomeUniversity();

        if ($this->userRepository->isInRole("globalAdmin")) {
            $grid->showEditableStatus(function ($id, $status) use ($onSuccess) {
                $this->changeStatus($id, $status);
                $onSuccess(true, $status);
            });
        } else {
            $grid->showSimplifiedStatus();
        }

        return $grid;
    }

    /**
     * Change status (active, pending, banned)
     * @param $id
     * @param $status
     */
    private function changeStatus($id, $status)
    {
        $this->memberRepository->changeUserStatus($id, $status);

        switch ($status) {
            case "active":
                $this->mailFactory->profileActivated($id);
                break;
            case "pending":
                $this->mailFactory->profilePending($id);
                break;
            case "banned":
                $this->mailFactory->profileBanned($id);
                break;
        }
    }

    public function createAllLocalMembersGrid(callable $onSuccess)
    {

        $university = $this->userRepository->university;
        $members = $this->memberRepository->getAllMembers()->where("university", $university);

        $grid = $this->createDatagrid();
        $grid->setColumnsHideable();

        $grid->setPrimaryKey("data_user");
        if ($this->userRepository->isAdministrator()) {
            $grid->setDataSource($members);
        } else {
            $grid->setDataSource($members->where("status", array("active", "enabled")));
        }

        $grid->showProfileColumnWithEmail();
        $grid->showCountry();
        $grid->showFaculty($this->universityRepository->getAllFaculties($university));


        if ($this->userRepository->isAdministrator()) {
            $this->addChangeMemberToInternationalAction($grid, function ($id) use ($onSuccess) {
                $this->memberRepository->changeUserRole($id, 'member', 'international');

                $onSuccess("Member role was changed to international.");
            });

            $grid->showEditableStatus(function ($id, $status) use ($onSuccess) {
                $this->changeStatus($id, $status);
                $onSuccess("Status was updated to $status.");
            });

            $grid->showExportCsv();
        } else {
            $grid->showSimplifiedStatus();
        }

        return $grid;
    }

    public function createAllInternationalsGrid(callable $onSuccess)
    {

        $university = $this->userRepository->university;
        $internationals = $this->internationalRepository->getAllInternational()->where("university", $university);

        $grid = $this->createDatagrid();
        $grid->setColumnsHideable();

        $grid->setPrimaryKey("data_user");
        if ($this->userRepository->isAdministrator()) {
            $grid->setDataSource($internationals);
        } else {
            $grid->setDataSource($internationals->where("status", array("active", "enabled")));
        }

        $grid->showProfileColumnWithEmail();
        $grid->showCountry();
        $grid->showESNCard();
        $grid->showPhoneNumber();
        $grid->showFaculty($this->universityRepository->getAllFaculties($university));
        $grid->showRegisteredDate();


        if ($this->userRepository->isAdministrator()) {

            $grid->showEditableStatus(function ($id, $status) use ($onSuccess) {
                $this->changeStatus($id, $status);
                $onSuccess(true, $status);
            });

            $grid->showExportCsv();
        } else {
            $grid->showSimplifiedStatus();
        }

        return $grid;
    }

    /**
     * Adds grid action to switch pending members (wrongly registered as members) to internationals.
     * @param          $grid MyDataGrid instance
     * @param callable $onSuccess called with row id after action submit
     */
    private function addChangeMemberToInternationalAction($grid, callable $onSuccess)
    {
        $grid
            ->addActionCallback('switchToInternational', 'SWITCH TO INTERNATIONAL', function ($id) use ($onSuccess) {
                $onSuccess($id);
            })
            ->setClass('btn btn-sm btn-outline ajax')
            ->setTitle('Change role of this user to international (typically used for international students mistakenly registered as members).')
            ->setConfirm(function () {
                return 'Do you want to switch this user to international?';
            });

        $grid->allowRowsAction('switchToInternational', function (ActiveRow $row) {
            return $row->ref('data_user')->ref('user', 'user_id')->status == 'pending';
        });
    }
}