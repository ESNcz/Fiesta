<?php

namespace App\Grid;


use App\Model\EditorRepository;
use App\Model\UniversityRepository;
use Nette\Database\Context;

class EditorGridFactory extends Grid
{
    private $editorRepository;
    private $universityRepository;

    /**
     * AdminGridFactory constructor.
     * @param UniversityRepository $universityRepository
     * @param EditorRepository $editorRepository
     */
    public function __construct(UniversityRepository $universityRepository,
                                EditorRepository $editorRepository)
    {
        $this->editorRepository = $editorRepository;
        $this->universityRepository = $universityRepository;
    }

    public function createRoleSettingsGrid($university)
    {

        $grid = $this->createDatagrid();
        $data = $this->editorRepository->getAllEditors($university);


        $grid->setPrimaryKey("data_user");
        $grid->setDataSource($data);

        $grid->showProfileColumnWithEmail();

        return $grid;
    }
}