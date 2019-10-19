<?php
/**
 * Created by PhpStorm.
 * User: thanh.dolong
 * Date: 19/02/2018
 * Time: 17:08
 */

namespace App\Admin\Presenters;

use App\Grid\UsersGridFactory;

/**
 * Class UsersPresenter
 * @package App\Admin\Presenters
 */
class UserPresenter extends BasePresenter
{
    /**
     * @var UsersGridFactory
     */
    private $usersGridFactory;

    /**
     * UserPresenter constructor.
     * @param UsersGridFactory $usersGridFactory
     */
    public function __construct(UsersGridFactory $usersGridFactory)
    {

        $this->usersGridFactory = $usersGridFactory;
    }

    /**
     * List with all active members
     * @param $name
     */
    public function createComponentRoleMembers($name)
    {
        $grid = $this->usersGridFactory->createAllMembersGrid(function ($isSucceed, $status) {

            if ($isSucceed) {
                $this->flashMessage("Status was updated to $status.", 'success');
            }

            $this->redirect('this');
        });

        $this->addComponent($grid, $name);
    }

    /**
     * List with all local members
     * @param $name
     */
    public function createComponentRoleLocalMembers($name)
    {
        $grid = $this->usersGridFactory->createAllLocalMembersGrid(function ($isSucceed, $status) {

            if ($isSucceed) {
                $this->flashMessage("Status was updated to $status.", 'success');
            }

            $this->redirect('this');
        });
        $this->addComponent($grid, $name);
    }

    /**
     * List with all internationals
     * @param $name
     */
    public function createComponentRoleInternationals($name)
    {
        $grid = $this->usersGridFactory->createAllInternationalsGrid(function ($isSucceed, $status) {

            if ($isSucceed) {
                $this->flashMessage("Status was updated to $status.", 'success');
            }

            $this->redirect('this');
        });
        $this->addComponent($grid, $name);
    }

    function renderInternationals()
    {
    }

}