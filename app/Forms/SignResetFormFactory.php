<?php

namespace App\Forms;

use App\Mailing\MailFactory;
use Kdyby\Translation\Translator;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


/**
 * Class SignInFormFactory
 * @package App\Admin\Forms
 */
class SignResetFormFactory
{
    use Nette\SmartObject;

    const PASSWORD_MIN_LENGTH = 6;
    const MIN_LENGTH = ':minLength';

    private $renderer;
    private $user;
    private $translator;
    private $mailFactory;


    public function __construct(DefaultFormRenderer $renderer, User $user, Translator $translator, MailFactory $mailFactory)
    {
        $this->renderer = $renderer;
        $this->user = $user;
        $this->translator = $translator;
        $this->mailFactory = $mailFactory;
    }

    /**
     * Sign in form
     * @param callable $onSuccess
     *
     * @return Form
     */
    public function createResetPassword(callable $onSuccess)
    {
        $form = $this->renderer->create();
        $form->setTranslator(
            $this->translator->domain(
                "sign.reset"
            )
        );

        $form->addText('email', 'email')
            ->setHtmlAttribute('placeholder', 'email')
            ->addRule(Form::EMAIL, "validation")
            ->setRequired("required");
        $form->addSubmit('send', "find");


        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $this->mailFactory->resetPassword($values["email"]);
            $onSuccess();
        };

        return $form;
    }

    public function createSetNewPassword(callable $onSuccess)
    {
        $form = $this->renderer->create();

        $form->setTranslator(
            $this->translator->domain(
                "sign.setPassword"
            )
        );


        $form->addProtection();

        $form->addPassword('password')
            ->setRequired('requiredPassword')
            ->addRule($form::MIN_LENGTH, "minimumPassword", self::PASSWORD_MIN_LENGTH)
            ->setHtmlAttribute('class', 'input-block-level')
            ->setHtmlAttribute('placeholder', 'setPassword');


        $form->addPassword('confirmation')
            ->setHtmlAttribute('placeholder', 'repeatPassword')
            ->setOmitted(TRUE)
            ->addConditionOn($form['password'], Form::FILLED)
            ->addRule(Form::FILLED,"requiredRepeatPassword")
            ->addRule(Form::EQUAL, "notMatchPassword", $form['password']);

        $form->addSubmit('send', "send");


        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {

            $onSuccess($values);
        };

        return $form;
    }
}
