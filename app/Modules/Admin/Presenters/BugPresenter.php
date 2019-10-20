<?php
/**
 * Created by PhpStorm.
 * User: thanh.dolong
 * Date: 19/02/2018
 * Time: 17:08
 */

namespace App\Admin\Presenters;

use App\Forms\DefaultFormRenderer;
use App\Model\Paginator;
use Nette\Database\Context;
use Nette\Forms\Form;

/**
 * Class BugPresenter
 * @package App\Admin\Presenters
 */
class BugPresenter extends BasePresenter
{

    private $renderer;
    private $database;

    /**
     * VotePresenter constructor.
     *
     * @param DefaultFormRenderer $renderer
     * @param Context $database
     */
    public function __construct(DefaultFormRenderer $renderer, Context $database)
    {
        $this->renderer = $renderer;
        $this->database = $database;
    }

    /**
     * Send feature
     * Feature form
     */
    protected function createComponentSendBug()
    {
        $form = $this->renderer->create();

        $form->addTextArea("message", "Message", "30", "10")
            ->setHtmlAttribute("placeholder", "Please try to include as much information as possible. The more accurate information we receive, the more quickly we will be able to resolve the issue.")
            ->addRule(Form::MIN_LENGTH, null, 25)
            ->addRule(Form::MAX_LENGTH, null, 280)
            ->setRequired();


        $form->addSubmit('send', 'REPORT A BUG');

        $form->onSuccess[] = function (Form $form, $values) {
            $this->database->table("bug")->insert([
                'text' => $values["message"],
                "user_id" => $this->user->getId()
            ]);

            $this->flashMessage('Your bug report was send', "green");
            $this->redirect('this');
        };

        return $form;
    }

    /**
     * Render all suggestions
     * @param int $page
     */
    function renderDefault($page = 1)
    {
        $suggestions = $this->database->table("bug")->order("id DESC");

        $lastPage = 0;
        $this->template->suggestions = $suggestions->page($page, 4, $lastPage);
        $pagination = new Paginator($page, $lastPage);

        $this->template->page = $page;
        $this->template->steps = $pagination->getSteps();
        $this->template->lastPage = $lastPage;
    }
}