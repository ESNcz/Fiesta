<?php
namespace App\Admin\Presenters;

use App\Forms\DefaultFormRenderer;
use App\Grid\Grid;
use App\Model\PluginRepository;
use Nette\Database\Context;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Forms\Form;
use Ublaboo\DataGrid\DataGrid;

class ExternalLinkPresenter extends BasePresenter {
    private $database;
    private $pluginRepository;

    /**
     * ExternalLinkPresenter constructor.
     * @param Context $database
     * @param PluginRepository $pluginRepository
     */
    public function __construct(Context $database, PluginRepository $pluginRepository)
    {
        $this->database = $database;
        $this->pluginRepository = $pluginRepository;
    }

    protected function createComponentExternalLink()
    {
        $form = new DefaultFormRenderer();
        $form = $form->create();

        $form->addHidden("section",$this->userRepository->university);

        $form->addText("url","URL")
            ->addRule(Form::URL, "Please enter a valid URL")
            ->setRequired("Please provide an URL");

        $form->addText("text", "Link text")
            ->addRule(Form::MAX_LENGTH, null, 42)
            ->setRequired("Please provide a text");

        $form->addSubmit('send', "Add link");

        $form->onSuccess[] = function (Form $form, $values) {
            bdump($values);
            try {
                $this->database->beginTransaction();
                $this->database->table("links")->insert([
                    "university" => $values["section"],
                    "url" => $values["url"],
                    "title" => $values["text"]
                ]);
                $this->database->commit();
                $this->flashMessage("Your link is created.", "green");
            } catch (UniqueConstraintViolationException $e) {

                $this->database->rollBack();
                $this->flashMessage("This link is already added", "red");
            }
            $this->redirect("this");
        };

        return $form;
    }

    protected function createComponentShowExternalLinks($name)
    {
        $grid = new DataGrid();
        $grid->setTemplateFile(__DIR__ . '/../../../Grid/templates/datagrid.latte');
        $grid->setPrimaryKey("url");
        $grid->setDataSource($this->pluginRepository->getExternalLinks($this->userRepository->university));

        $grid->addColumnText('url', 'Url')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('title', 'Link text')
            ->setSortable()
            ->setFilterText();

        $grid->addActionCallback("delete", "", function ($id) {
            $this->pluginRepository->removeExternalLink($id, $this->userRepository->university);
            $this->flashMessage("You just delete url", "green");
            $this->redirect("this");
            die;
        })
            ->setIcon('trash')
            ->setTitle('Delete')
            ->setClass('btn btn-xs btn-danger')
            ->setConfirm('Do you really want to delete this request?');

        $this->addComponent($grid, $name);
    }
}