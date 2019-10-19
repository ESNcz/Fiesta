<?php

namespace App\Admin\Presenters;

use App\Forms\DefaultFormRenderer;
use App\Model\UniversityRepository;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use stdClass;

/**
 * Class HomepagePresenter
 * @package App\Admin\Presenters
 */
class HomepagePresenter extends BasePresenter
{
    private $universityRepository;
    private $database;

    /**
     * HomepagePresenter constructor.
     * @param Context $database
     * @param UniversityRepository $universityRepository
     */
    public function __construct(Context $database, UniversityRepository $universityRepository)
    {
        $this->universityRepository = $universityRepository;
        $this->database = $database;
    }

    public function updateDashboard(Form $form, stdClass $values)
    {
        $this->database->table("university")->get($this->userRepository->university)->update([
            'dashboard' => $values["text"]
        ]);
    }

    function renderDefault()
    {
        $checkData = $this->userRepository->getIdentity()->data;
        $checkData["image"] = $this->userRepository->getIdentity();

        $this->template->dashboardInfo = $this->universityRepository->getUniversity($this->userRepository->university);
        $this->template->profileStrength = $this->userRepository->getProfileStrength($checkData);
    }

    protected function createComponentUpdateDashboard()
    {
        $university = $this->universityRepository->getUniversity($this->userRepository->university);
        $form = new DefaultFormRenderer();
        $form = $form->create();

        $form->addTextArea('text', '', "30", "1000")
            ->setDefaultValue($university["dashboard"])
            ->setHtmlAttribute("id", 'summernote');

        $form->addSubmit('send', "Save");
        $form->onSuccess[] = [$this, 'updateDashboard'];
        return $form;
    }
}
