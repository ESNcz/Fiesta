<?php

namespace App\Grid;


use App\Model\AdminRepository;
use App\Model\UniversityRepository;
use Nette\Database\Context;

class AdminGridFactory extends Grid
{
    private $adminRepository;
    private $universityRepository;

    /**
     * AdminGridFactory constructor.
     * @param UniversityRepository $universityRepository
     * @param AdminRepository $adminRepository
     */
    public function __construct(UniversityRepository $universityRepository,
                                AdminRepository $adminRepository) {
        $this->adminRepository = $adminRepository;
        $this->universityRepository = $universityRepository;
    }

    public function createRoleSettingsGrid($university)
    {

        $grid = $this->createDatagrid();
        $data = $this->adminRepository->getAllAdmins($university);


        $grid->setPrimaryKey("data_user");
        $grid->setDataSource($data);

        $grid->showProfileColumnWithEmail();

        return $grid;
    }
}