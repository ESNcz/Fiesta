<?php

namespace App\Grid;

use App\Model\PluginRepository;
use App\Model\UserRepository;
use Nette\Database\Context;
use Nette\Database\Table\Selection;
use Ublaboo\DataGrid\Column\ColumnText;

class EventGridFactory extends Grid
{
    private $userRepository;
    private $pluginRepository;

    /**
     * EventGridFactory constructor.
     * @param UserRepository   $userRepository
     * @param PluginRepository $pluginRepository
     */
    public function __construct(UserRepository $userRepository,
                                PluginRepository $pluginRepository)
    {
        $this->userRepository = $userRepository;
        $this->pluginRepository = $pluginRepository;
    }

    /**
     * Creates grid of users with paid fee or all registered people if event is free.
     * @param $id int
     * @return MyDataGrid
     */
    public function createGuestListGrid($id)
    {
        $grid = $this->createDatagrid();

        $list = $this->pluginRepository->getGuestList($id);

        return $this->setupUserGridForEvent($grid, $list, $id);
    }

    /**
     * Creates grid of users with NOT paid fee or all registered people if event is free.
     * @param $id int
     * @return MyDataGrid
     */
    public function createRegisteredListGrid($id)
    {
        $grid = $this->createDatagrid();

        $list = $this->pluginRepository->getRegisteredList($id);

        return $this->setupUserGridForEvent($grid, $list, $id);
    }

    /**
     * @param MyDataGrid $grid
     * @param Selection  $dataSource
     * @param int        $id
     * @return MyDataGrid
     */
    protected function setupUserGridForEvent(MyDataGrid $grid, Selection $dataSource, $id)
    {
        $grid->setPrimaryKey("data_user");
        $grid->setDataSource($dataSource);

        if ($this->userRepository->isAdministrator()) {
            $grid->showProfileColumnWithEmail();
        } else {
            $grid->showProfileColumnWithoutEmail();
        }

        if ($this->userRepository->isAdministrator()) {
            if (!$this->pluginRepository->isEventFree($id)) {
                $grid->addColumnStatus('status', '')
                    ->addOption("paid", 'Paid')
                    ->setClass('btn-success')
                    ->endOption()
                    ->addOption("unpaid", 'Unpaid')
                    ->setClass('btn-danger')
                    ->endOption();
            }

            $grid->addAction('delete', '', 'deleteUser!', ["id" => "data_user", "name" => "data_user.name"])
                ->setIcon('times-circle')
                ->setTitle('Delete')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirm('Do you really want to delete  %s?', 'data_user.name');

            $column_name = (new ColumnText($grid, 'name', 'data_user.name', 'Name'));
            $column_surname = (new ColumnText($grid, 'surname', 'data_user.surname', 'Surname'));
            $column_esncard = (new ColumnText($grid, 'surname', 'data_user.esn_card', 'ESNCard'));
            $column_phone = (new ColumnText($grid, 'surname', 'data_user.phone_number', 'Phone number'));
            $column_email = (new ColumnText($grid, 'surname', 'user.user_id', 'Email'));


            $grid->addExportCsvFiltered('Csv export', "event.csv", "UTF-8", ";", true)
                ->setTitle('Csv export')
                ->setColumns([
                    $column_name,
                    $column_surname,
                    $column_esncard,
                    $column_phone,
                    $column_email
                ]);
        }

        $grid->addFilterMultiSelect('status', 'Status:', [
            "paid" => 'Paid',
            "unpaid" => 'Unpaid'
        ]);
        return $grid;
    }
}