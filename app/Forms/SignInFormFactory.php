<?php

namespace App\Forms;

use Kdyby\Translation\Translator;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


/**
 * Class SignInFormFactory
 * @package App\Admin\Forms
 */
class SignInFormFactory
{
    use Nette\SmartObject;

    private $renderer;
    private $user;
    private $translator;


    public function __construct(DefaultFormRenderer $renderer, User $user, Translator $translator)
    {
        $this->renderer = $renderer;
        $this->user = $user;
        $this->translator = $translator;
    }

    /**
     * Sign in form
     * @param callable $onSuccess
     *
     * @return Form
     */
    public function createSignIn(callable $onSuccess)
    {

        $form = $this->renderer->create();
        $form->setTranslator(
            $this->translator->domain(
                "sign.in"
            )
        );

        $form->addText('email')
            ->setHtmlAttribute('placeholder', 'email');

        $form->addPassword('password')
            ->setHtmlAttribute('placeholder', 'password');

        $form->addCheckbox('remember', 'keepMeSignedIn');

        $form->addSubmit('send', 'login');


        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            try {
                ;
                $this->user->setExpiration($values->remember ? '14 days' : '10 minutes');
                $this->user->login($values->email, $values->password);
            } catch (Nette\Security\AuthenticationException $e) {
                $form->addError('wrongLogin');
                return;
            }
            $onSuccess();
        };

        return $form;
    }
}
