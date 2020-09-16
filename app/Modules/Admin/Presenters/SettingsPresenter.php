<?php

namespace App\Admin\Presenters;

use App\Forms\DefaultFormRenderer;
use App\Forms\ProfileFormFactory;
use App\Forms\UploadImageFactory;
use App\Grid\AdminGridFactory;
use App\Grid\EditorGridFactory;
use App\Model\AdminRepository;
use App\Model\DuplicateException;
use App\Model\EditorRepository;
use App\Model\PluginRepository;
use Exception;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;
use Ublaboo\DataGrid\Exception\DataGridException;

/**
 * Class SettingsPresenter
 * @package App\Admin\Presenters
 */
class SettingsPresenter extends BasePresenter
{
    private $uploadImageFactory;
    private $profileFormFactory;
    private $adminGridFactory;
    private $adminRepository;
    private $editorGridFactory;
    private $editorRepository;
    private $pluginRepository;

    /**
     * SettingsPresenter constructor.
     * @param UploadImageFactory $uploadImageFactory
     * @param ProfileFormFactory $profileFormFactory
     * @param AdminGridFactory $adminGridFactory
     * @param AdminRepository $adminRepository
     * @param PluginRepository $pluginRepository
     * @param EditorGridFactory $editorGridFactory
     * @param EditorRepository $editorRepository
     */
    public function __construct(UploadImageFactory $uploadImageFactory,
                                ProfileFormFactory $profileFormFactory,
                                AdminGridFactory $adminGridFactory,
                                AdminRepository $adminRepository,
                                PluginRepository $pluginRepository,
                                EditorGridFactory $editorGridFactory,
                                EditorRepository $editorRepository)
    {

        $this->uploadImageFactory = $uploadImageFactory;
        $this->profileFormFactory = $profileFormFactory;
        $this->adminGridFactory = $adminGridFactory;
        $this->adminRepository = $adminRepository;
        $this->editorGridFactory = $editorGridFactory;
        $this->editorRepository = $editorRepository;
        $this->pluginRepository = $pluginRepository;
    }

    /**
     * Component with list of admin users just from specific university
     * @param $name
     * @throws DataGridException
     */
    public function createComponentRoleAdmin($name)
    {
        $grid = $this->adminGridFactory->createRoleSettingsGrid($this->userRepository->university);
        $this->addComponent($grid, $name);

        $grid->addAction('delete', '', 'deleteAdmin!')
            ->setIcon('fas fa-trash')
            ->setTitle('Delete')
            ->setClass('btn btn-xs btn-danger ajax')
            ->setConfirm('Remove admin role from %s?', 'data_user.name:data_user');
    }

    /**
     * Component for adding admin role by id
     * @return Form
     */
    public function createComponentAddAdmin()
    {
        $form = new DefaultFormRenderer();
        $form = $form->create();

        $form->addSelect('admin', 'Assign an Admin Role:')
            ->setAttribute('class', "member-autocomplete");


        $form->addSubmit('send', "Add");

        $form->onSuccess[] = function (Form $form, $values) {
            $id = $form->getHttpData($form::DATA_LINE, 'admin');

            try {
                $this->userRepository->setNewRole($id, "admin");
            } catch (DuplicateException $e) {
                $this->flashMessage("This user is already admin.", "red");
                return;
            }

            $this->flashMessage("New admin was added.", "info");

            $this->redirect("this");
        };

        return $form;
    }

    /**
     * Delete admin role from user
     * @param $data_user
     * @throws AbortException
     */
    public function handleDeleteAdmin($data_user)
    {

        $this->adminRepository->deleteAdmin($data_user);
        $this->flashMessage("User was removed from admin role", 'info');

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this['roleAdmin']->reload();
        } else {
            $this->redirect('this');
        }
    }

    /**
     * Component with list of editor users just from specific university
     * @param $name
     * @throws DataGridException
     */
    public function createComponentRoleEditor($name)
    {
        $grid = $this->editorGridFactory->createRoleSettingsGrid($this->userRepository->university);
        $this->addComponent($grid, $name);

        $grid->addAction('delete', '', 'deleteEditor!')
            ->setIcon('fas fa-trash')
            ->setTitle('Delete')
            ->setClass('btn btn-xs btn-danger ajax')
            ->setConfirm('Remove admin role from %s?', 'data_user.name:data_user');
    }

    /**
     * Component for adding editor role by id
     * @return Form
     */
    public function createComponentAddEditor()
    {
        $form = new DefaultFormRenderer();
        $form = $form->create();

        $form->addSelect('editor', 'Assign an Editor Role:')
            ->setAttribute('class', "member-autocomplete");


        $form->addSubmit('send', "Add");

        $form->onSuccess[] = function (Form $form, $values) {
            $id = $form->getHttpData($form::DATA_LINE, 'editor');

            try {
                $this->userRepository->setNewRole($id, "editor");
            } catch (DuplicateException $e) {
                $this->flashMessage("This user is already editor.", "red");
                return;
            }

            $this->flashMessage("New editor was added.", "info");

            $this->redirect("this");
        };

        return $form;
    }

    /**
     * Delete editor role from user
     * @param $data_user
     * @throws AbortException
     */
    public function handleDeleteEditor($data_user)
    {
        $this->editorRepository->deleteEditor($data_user);
        $this->flashMessage("User was removed from editor role", 'info');

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
            $this['roleEditor']->reload();
        } else {
            $this->redirect('this');
        }
    }

    /**
     * Change status of module (activate/deactivate)
     * @param $moduleID
     * @param $name
     * @throws AbortException
     */
    function handleChangePluginStatus($moduleID, $name)
    {
        $this->flashMessage("$name was updated.", 'success');
        $this->pluginRepository->switchStatusOfPlugin($moduleID, $this->userRepository->university);
        $this->redirect('this');
    }

    /**
     * Rended Modules - module page
     */
    function renderPlugins()
    {
        $this->template->modules = $this->pluginRepository->getAllPlugins();
        $this->template->checkModules = $this->pluginRepository
            ->getPluginsDependsOnUniversity($this->userRepository->university);
    }

    /**
     * Render edit profile - setting page
     * @param $signature
     * @throws Exception
     */
    function renderEdit($signature)
    {
        $filename = "images/avatar/{$signature}.jpg";
        if (file_exists($filename)) {
            if ($this->isAjax()) {
                $date = new DateTime();
                $this->template->urlForAvatar = "/{$filename}?time={$date->getTimestamp()}";
                $this->template->sideMenuFade = "in";
                $this->redrawControl("avatar");
            } else {
                $this->template->urlForAvatar = "/" . $filename;
            }
        } else {
            $this->template->urlForAvatar = "/images/avatar/empty.jpg";
        }
    }

    function renderDefault() {}
    /**
     * Create component for edit profile - logged user (form)
     * @return Form
     */
    protected function createComponentEditMyProfileForm()
    {
        return $this->profileFormFactory->createEditMyProfileForm(function () {
            $this->flashMessage("Your information has been successfully saved.", "green");
            $this->redirect('this');
        });
    }

    /**
     * Create component for edit profile - anyone (form)
     * @return Form
     */
    protected function createComponentEditProfileForm()
    {
        $id = $this->userRepository->getIdBySignature($this->getParameter('signature'));

        return $this->profileFormFactory->createEditProfile($id,function () {
            $this->flashMessage("Your information has been successfully saved.", "green");
            $this->redirect('this');
        });
    }

    /**
     * Create component for upload avatar - logged user
     * @return Form
     */
    protected function createComponentUploadMyImage()
    {
        return $this->uploadImageFactory->uploadUserImage(function () {
            if (!$this->isAjax()) {
                $this->redirect('this');
            } else {
                $this->redrawControl("sidemenu");
            }
        });
    }

    /**
     * Create component for upload avatar - anyone
     * @return Form
     */
    protected function createComponentUploadImage()
    {
        $signature = $this->getParameter('signature');
        return $this->uploadImageFactory->uploadImage($signature, function () {
            if(!$this->isAjax()){
                $this->redirect('this');
            } else {
                $this->redrawControl("sidemenu");
            }
        });
    }
}
