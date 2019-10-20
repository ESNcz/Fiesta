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
use Nette\Application\AbortException;
use Nette\Database\Context;
use Nette\Forms\Form;

/**
 * Class VotePresenter
 * @package App\Admin\Presenters
 */
class VotePresenter extends BasePresenter
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
    protected function createComponentSendFeature()
    {
        $form = $this->renderer->create();

        $form->addTextArea("message", "Message", "30", "10")
            ->setHtmlAttribute("placeholder", "Let your imagination go wild and share with us what you envision. No matter how basic or crazy as it might seem, we are here to reinvent investing together with you!")
            ->addRule(Form::MIN_LENGTH, null, 25)
            ->addRule(Form::MAX_LENGTH, null, 280)
            ->setRequired();


        $form->addSubmit('send', 'SEND');

        $form->onSuccess[] = function ($form, $values) {
            $this->insertSuggestedFeature($values);
            $this->flashMessage('Your message was send', "green");
            $this->redirect('this');
        };

        return $form;
    }

    /**
     * Voting system
     * @param $id
     * @param $votes
     * @throws AbortException
     */
    public function handleVote($id, $votes){
        if($this->changeSuggestionStatus($id, $votes))
            $this->flashMessage('<b>Thank you</b> for vote!', 'info');
        else
            $this->flashMessage("Sorry, you're out of votes!", 'red');

        if ($this->isAjax()) {
            $this->redrawControl('flashes');
        } else {
            $this->redirect('this');
        }
    }

    private function insertSuggestedFeature($values)
    {
        if ($this->user->isInRole("member")) {
            $this->database->table("suggested_feature")->insert([
                'text' => $values["message"],
                "role" => "member",
                'user_id' => $this->user->getId()
            ]);
        } else {
            $this->database->table("suggested_feature")->insert([
                'text' => $values["message"],
                "role" => "international",
                'user_id' => $this->user->getId()
            ]);
        }
    }

    /**
     * Get list of suggestion
     * @return mixed
     */
    private function getSuggestions()
    {
        if ($this->user->isInRole("member")) {
            $result["data"] = $this->database->table("suggested_feature")
                ->select("id,text,status, suggested_feature.user_id")
                ->select("COUNT(:vote(id).feature_id) AS totalVote")
                ->where("role", "member")
                ->group("id")
                ->order("totalVote DESC");
        } else {
            $result["data"] = $this->database->table("suggested_feature")
                ->select("id,text,status, suggested_feature.user_id")
                ->select("COUNT(:vote(id).feature_id) AS totalVote")
                ->where("role", "international")
                ->group("id")
                ->order("totalVote DESC");
        }

        $result["user_vote"] = $this->database->table("vote")->where("user", $this->user->getId())->fetchPairs(null, "feature_id");
        return $result;
    }

    /**
     * Increment user vote in suggestion
     *
     * @param $id
     * @param $votes
     *
     * @return bool
     */
    private function changeSuggestionStatus($id, $votes)
    {
        $checkSuggestion = $this->database->table("vote")
            ->where("feature_id", $id)
            ->where("user", $this->user->getId());

        // User already vote this suggestion
        if ($checkSuggestion->count()) $checkSuggestion->delete();

        // User already vote too much
        else if ($votes > 4) return FALSE;
        else {
            $this->database->table("vote")->insert([
                "feature_id" => $id,
                "user" => $this->user->getId()
            ]);
        }

        return TRUE;
    }

    /**
     * Render all suggestions
     * @param int $page
     */
    function renderDefault($page = 1)
    {
        $suggestions = $this->getSuggestions();

        $lastPage = 0;
        $this->template->suggestions = $suggestions["data"]->page($page, 4, $lastPage);
        $pagination = new Paginator($page, $lastPage);

        $this->template->user_vote = $suggestions["user_vote"];
        $this->template->page = $page;
        $this->template->steps = $pagination->getSteps();
        $this->template->lastPage = $lastPage;
    }

}