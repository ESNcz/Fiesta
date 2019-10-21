<?php
/**
 * Created by PhpStorm.
 * User: thanh.dolong
 * Date: 19/02/2018
 * Time: 17:08
 */

namespace App\Admin\Presenters;

use App\Forms\DefaultFormRenderer;
use App\Grid\UsersGridFactory;
use App\Model\UniversityRepository;
use Kdyby\Translation\Translator;
use Nette\Forms\Form;

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
     * @var UniversityRepository
     */
    private $universityRepository;
    /**
     * @var Translator
     */
    private $translator;

    /**
     * UserPresenter constructor.
     * @param UsersGridFactory $usersGridFactory
     * @param Translator $translator
     * @param UniversityRepository $universityRepository
     */
    public function __construct(UsersGridFactory $usersGridFactory,
                                Translator $translator,
                                UniversityRepository $universityRepository)
    {

        $this->usersGridFactory = $usersGridFactory;
        $this->universityRepository = $universityRepository;
        $this->translator = $translator;
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

    public function createComponentTransferInternational()
    {
        $university = $this->universityRepository->getAllUniversities();

        $form = new DefaultFormRenderer();
        $form = $form->create();

        $form->addProtection();


        $form->addSelect('international', 'Transfer international:')
            ->setAttribute('class', "international-autocomplete");

        $form->addSelect('section', "Which university you want to send to?", $university)
            ->setPrompt("University")
            ->setRequired("Don't forget to choose ESN section");


        $form->addSubmit('send', "TRANSFER");
        $form->onSuccess[] = function (Form $form, $values) {
            $values["email"] = $form->getHttpData($form::DATA_LINE, 'international');
            $this->userRepository->transferUser($values);
            $this->flashMessage("Your transfer user.", "info");
            $this->redirect("this");
        };

        return $form;
    }

    public function createComponentTransferMember()
    {
        $university = $this->universityRepository->getAllUniversities();

        $form = new DefaultFormRenderer();
        $form = $form->create();

        $form->addProtection();

        $form->addSelect('member', 'Transfer member:')
            ->setAttribute('class', "member-autocomplete");

        $form->addSelect('section', "Which university you want to send to?", $university)
            ->setPrompt("University")
            ->setRequired("Don't forget to choose ESN section");


        $form->addSubmit('send', "TRANSFER");
        $form->onSuccess[] = function (Form $form, $values) {
            $values["email"] = $form->getHttpData($form::DATA_LINE, 'member');
            $this->userRepository->transferUser($values);
            $this->flashMessage("Your transfer user.", "info");
            $this->redirect("this");
        };

        return $form;
    }

    function renderInternationals()
    {
    }

}