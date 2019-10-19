<?php

namespace App\Admin\Presenters;

use App\Model\PluginRepository;
use App\Model\UserRepository;
use Nette;
use Nette\Security\IUserStorage;
use Nette\Utils\DateTime;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var UserRepository @inject */
    public $userRepository;

    /** @var PluginRepository @inject */
    public $module;

    function startup()
    {
        parent::startup();
        if ($this->getUser()->getLogoutReason() === IUserStorage::INACTIVITY) {
            if ($this->getUser()->isLoggedIn()) {
                $this->getUser()->logout(true);
                $this->flashMessage("I haven't seen you for ages! <br> We miss you. Please log back in", "green");
                $this->redirect(':Admin:Sign:in');
            }
        }

        if ($this->getUser()->isLoggedIn() == false) {
            if ($this->onSignPage() == false) {
                $this->redirect('Sign:in');
            }
        } else {
            $this->userRepository->refreshIdentity();
            if (!$this->onSignPage()) {
                switch ($this->getUser()->getIdentity()->status) {
                    case "active":
                        if ($this->onStatusPage()) $this->redirect(":Admin:Homepage:default");
                        break;
                    case "pending":
                        if (!$this->onStatusPage()) $this->redirect(":Admin:Status:pending");
                        break;
                    case "enabled":
                        if (!$this->onStatusPage()) $this->redirect(":Admin:Status:enabled");
                        break;
                    case "banned":
                        if (!$this->onStatusPage()) $this->redirect(":Admin:Status:banned");
                        break;
                    case "uncompleted":
                        if ($this->onRegistrationComplete()) $this->redirect(":Admin:Sign:continue");
                        break;
                }
            }

            if (!$this->getUser()->isAllowed($this->name, $this->action)) {
                $this->flashMessage("<b>Ops,</b> you do not have permission to view this page ", "red");
                $this->redirect('Homepage:default');
            }
        }
    }

    /**
     * User is on Sign page
     * @return bool
     */
    protected function onSignPage()
    {
        return false;
    }

    /**
     * User is on Status page
     * @return bool
     */
    protected function onStatusPage()
    {
        return false;
    }

    /**
     * Check if Registration is complete
     * @return bool
     */
    protected function onRegistrationComplete()
    {
        return true;
    }

    function beforeRender()
    {
        parent::beforeRender();
        bdump($this->name, "Presenter");
        bdump($this->action, "Action");

        if ($this->getUser()->isLoggedIn() && isset($this->getUser()->getIdentity()->signature)) {

            if (!$this->onSignPage() && !$this->onStatusPage()) {
                $this->template->userInformation = $this->userRepository->getData();
                $this->template->sideMenu = $this->module->getActivePluginsForSideMenu($this->userRepository->university);
            }

            $filename = "images/avatar/{$this->getUser()->getIdentity()->signature}.jpg";

            if (file_exists($filename)) {
                $date = new DateTime();
                if ($this->isAjax()) {
                    $this->template->urlAvatar = "/{$filename}?time={$date->getTimestamp()}";
                    $this->template->sideMenuFade = "in";
                    $this->redrawControl("avatar");
                } else {
                    $this->template->urlAvatar = "/{$filename}?time={$date->getTimestamp()}";
                }
                $this->getUser()->identity->hasImage = true;
            } else {
                $this->template->urlAvatar = "/images/avatar/empty.jpg";
                if (!is_null($this->getUser()->getIdentity())) $this->getUser()->identity->hasImage = false;
            }
        }

        $this->template->addFilter('money', function ($val) {
            return number_format($val, 0, '', ' ') . ' Kč';
        });
    }
}