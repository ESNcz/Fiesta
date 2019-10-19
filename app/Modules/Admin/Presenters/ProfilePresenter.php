<?php

namespace App\Admin\Presenters;

use App\Model\UniversityRepository;


/**
 * Class ProfilePresenter
 * @package App\Admin\Presenters
 */
class ProfilePresenter extends BasePresenter
{
    private $university;


    /**
     * HomepagePresenter constructor.
     *
     * @param UniversityRepository $university
     */
    public function __construct(UniversityRepository $university)
    {
        $this->university = $university;
    }

    function renderDefault()
    {
        $this->template->university = $this->userRepository->getUniversityById($this->getUser()->getId());
    }


    function renderView($signature)
    {
        $id = $this->userRepository->getIdBySignature($signature);

        if ($id == null) {
            $this->redirect("Homepage:default");
        }

        $filename = "images/avatar/{$signature}.jpg";
        if (file_exists($filename)) {
            $this->template->urlForAvatar = "/" . $filename;
        } else {
            $this->template->urlForAvatar = "/images/avatar/empty.jpg";
        }

        $this->template->userData = $this->userRepository->getDataById($id);
        $this->template->specificUser = $this->userRepository->getUserById($id);
        $this->template->roles = $this->userRepository->getRolesById($id);
        $this->template->university = $this->userRepository->getUniversityById($id);
    }
}