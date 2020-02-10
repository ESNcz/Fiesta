<?php

namespace App\Grid;

use Nette\Database\Context;
use Ublaboo\DataGrid\Column\ColumnText;
use Ublaboo\DataGrid\DataGrid;

abstract class Grid
{
    protected $database;
    protected $grid;

    /**
     * Grid constructor.
     * @param Context $database
     */
    public function __construct(Context $database) {
        $this->database = $database;
    }

    protected function createDatagrid()
    {
        $grid = new MyDataGrid();
        $grid->setTemplateFile(__DIR__ . '/templates/datagrid.latte');
        return $grid;
    }
}

class MyDataGrid extends DataGrid
{
    function showProfileColumnWithEmail()
    {
        $this->addColumnText('name', 'Name')
            ->setTemplate(__DIR__ . '/templates/grid.profile.latte')
            ->setSortable("data_user.name")
            ->setFilterText(['data_user.name', "data_user.surname", "user.user_id"]);
    }

    function showProfileColumnWithoutEmail()
    {
        $this->addColumnText('name', 'Name')
            ->setTemplate(__DIR__ . '/templates/grid.esnmembers.latte')
            ->setSortable("data_user.name")
            ->setFilterText()
            ->setCondition(function ($fluent, $value) {
                $fluent->where("concat_ws(' ', data_user.name, data_user.surname) LIKE ? OR user.user_id LIKE ?", ['%' . $value . '%', '%' . $value . '%']);
            });
    }

    function showCountry()
    {
        $this->addColumnText('country', 'Country', "data_user.country_id")
            ->setTemplate(__DIR__ . '/templates/grid.flag.latte')
            ->setSortable("data_user.country_id.name")
            ->setFilterText('data_user.country_id.name');
    }

    function showRegisteredDate()
    {
        $this->addColumnDateTime('registered', 'Registered', "data_user.registered")
            ->setSortable()
            ->setFilterDateRange();
    }

    function showHomeUniversity()
    {
        $this->addColumnText('university', 'Home University')
            ->setRenderer(function ($item) {
                return $item->ref("user", "data_user")->ref("university", "university")->name;
            })
            ->setSortable("user.user_id.university")
            ->setFilterText('user.user_id.university.name');
    }

    function showFaculty($faculties)
    {
        $faculties["multiselect"] = $faculties["short"];
        $faculties["short"][""] = "Unknown";

        $this->addColumnText('faculty', 'Faculty', "data_user.faculty_id")
            ->setSortable("data_user.faculty_id")
            ->setReplacement($faculties["short"])
            ->setFilterMultiSelect($faculties["multiselect"]);
    }

    function showExportCsv()
    {
        $column_name = new ColumnText($this, 'name', 'data_user.name', 'Name');
        $column_surname = (new ColumnText($this, 'surname', 'data_user.surname', 'Surname'));
        $column_birthday = (new ColumnText($this, 'birthday', 'data_user.birthday', 'Birthday'));
        $column_country = (new ColumnText($this, 'country', 'data_user.country_id', 'Country'));
        $column_email = (new ColumnText($this, 'email', 'user.user_id', 'Email'));
        $column_registered = (new ColumnText($this, 'registered', 'data_user.registered', 'Registered'));


        $this->addExportCsvFiltered('Csv export', "fiesta.csv", "UTF-8", ";", true)
            ->setTitle('Csv export')
            ->setColumns([
                $column_name,
                $column_surname,
                $column_birthday,
                $column_email,
                $column_country,
                $column_registered
            ]);
    }

    function showEditableStatus(callable $onSuccess)
    {

        $this->addColumnStatus('status', 'Status', "user.status:data_user")
            ->setSortable("user.user_id.status")
            ->addOption("active", 'Active')
            ->setClass('btn-success')
            ->endOption()
            ->addOption("pending", 'Pending')
            ->setClass('btn-warning')
            ->endOption()
            ->addOption("enabled", 'Disabled')
            ->setClass('btn-secondary')
            ->endOption()
            ->addOption("banned", 'Banned')
            ->setClass('btn-danger')
            ->endOption()
            ->onChange[] = function ($id, $new_value) use ($onSuccess) {
            $onSuccess($id, $new_value);
            die;
        };

        $this->addFilterMultiSelect('status', 'Status:', ([
            "active" => 'Active',
            "pending" => 'Pending',
            "banned" => 'Banned',
            "enabled" => 'Disabled'
        ]), "user.user_id.status");
    }

    function showDeleteRequest(callable $onSuccess)
    {
        $this->addActionCallback("delete", "", function ($id) use ($onSuccess) {
            $onSuccess($id);
            die;
        })
            ->setIcon('trash')
            ->setTitle('Delete')
            ->setClass('btn btn-xs btn-danger')
            ->setConfirm('Do you really want to delete this request?');
    }

    function showESNCard()
    {
        $this->addColumnText('esn', 'ESN Card', "data_user.esn_card")
            ->setSortable("data_user.esn_card")
            ->setReplacement(['' => 'Unknown'])
            ->setFilterText('data_user.esn_card');
    }

    function showPhoneNumber()
    {
        $this->addColumnText('phone', 'Phone', "data_user.phone_number")
            ->setSortable("data_user.phone_number")
            ->setFilterText('data_user.phone_number');
    }

    function showSimplifiedStatus()
    {
        $this->addColumnText('status', 'Status', "user.status:data_user")
            ->setSortable("user.user_id.status")
            ->setReplacement([
                "active" => "Active",
                "enabled" => 'Alumni']);

        $this->addFilterMultiSelect('status', 'Status:', ([
            "active" => 'Active',
            "enabled" => 'Alumni'
        ]), "user.user_id.status");
    }
}