<?php

namespace App\Admin\Presenters;

use App\Forms\SignInFormFactory;
use App\Forms\SignResetFormFactory;
use App\Forms\SignUpFormFactory;
use App\Forms\UploadImageFactory;
use App\Mailing\MailFactory;
use App\Model\TokenRepository;
use App\Model\UniversityRepository;
use Kdyby\Translation\Translator;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;


/**
 * Class SignPresenter
 * @package App\Admin\Presenters
 */
class SignPresenter extends BasePresenter
{
    private $signInFactory;
    private $signUpFactory;
    private $mailFactory;
    private $university;
    private $translator;
    private $uploadImageFactory;
    private $signResetFormFactory;


    /**
     * SignPresenter constructor.
     * @param SignInFormFactory $signInFactory
     * @param SignUpFormFactory $signUpFactory
     * @param UploadImageFactory $uploadImageFactory
     * @param SignResetFormFactory $signResetFormFactory
     * @param UniversityRepository $university
     * @param MailFactory $mailFactory
     * @param Translator $translator
     */
    public function __construct(SignInFormFactory $signInFactory,
                                SignUpFormFactory $signUpFactory,
                                UploadImageFactory $uploadImageFactory,
                                SignResetFormFactory $signResetFormFactory,
                                UniversityRepository $university,
                                MailFactory $mailFactory,
                                Translator $translator
    )
    {
        $this->signInFactory = $signInFactory;
        $this->signUpFactory = $signUpFactory;
        $this->university = $university;
        $this->mailFactory = $mailFactory;
        $this->translator = $translator;
        $this->uploadImageFactory = $uploadImageFactory;
        $this->signResetFormFactory = $signResetFormFactory;
    }

    function actionIn()
    {
        $this->getSession('SignSetup')->remove();
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Homepage:default');
        }
    }

    /**
     * Registration process - step 1
     *
     * @param $university
     * @param $role
     *
     * @return void
     * @throws AbortException
     */

    function actionSetup($university, $role)
    {
        $this->session->start();

        $this->getSession('SignSetup')->remove();

        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Homepage:default');
        } else if (isset($university)) {
            if ($this->university->isUniversityValid($university)) {
                if ($this->userRepository->isRoleValid($role)) {
                    $setup = $this->getSession('SignSetup');
                    $setup["section"] = $university;
                    $setup["role"] = $role;

                    $this->redirect('Sign:up');
                }
            }
        }

    }

    function actionUp()
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('Homepage:default');
        }

        $setup = $this->getSession('SignSetup');

        if (is_null($setup["section"]) || is_null($setup["role"])) {
            $this->redirect('Sign:setup');
        }
    }

    public function actionContinue()
    {
        $profileStatus = $this->getUser()->getIdentity()->status;
        if ($this->userRepository->isLoggedIn()) {
            if ($profileStatus === "active") {
                $this->redirect('Homepage:default');
            }
        } else {
            $this->redirect('Sign:in');
        }
    }

    function actionReset($id, $token)
    {
        $tokenRepository = new TokenRepository();


        if ($tokenRepository->isTokenValid($token)) {
            $data = $tokenRepository->getData();

            $setup = $this->getSession('ResetPassword');
            $setup["email"] = $data->email;
        } else {
            $this->flashMessage("<b>Ops,</b> you do not have permission to view this page", "red");
            $this->redirect('Sign:in');
        }
    }

    public function actionOut()
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->getUser()->logout(true);
            $this->getSession('SignSetup')->remove();
            $this->flashMessage("<b>You've been logged out.</b>
            <br>Don't worry, you can log back in below", "green");
            $this->redirect('Sign:in');
        }
    }

    /**
     * Is user logged?
     * @return bool
     */
    protected function isLogged()
    {
        return true;
    }

    /**
     * User is on Sign page
     * @return bool
     */
    protected function onSignPage()
    {
        return true;
    }

    /**
     * Check if Registration is complete
     * @return bool
     */
    protected function onRegistrationComplete()
    {
        return false;
    }

    /**
     * Sign-in form factory.
     * @return Form
     */
    protected function createComponentSignInForm()
    {
        return $this->signInFactory->createSignIn(function () {
            if ($this->userRepository->getIdentity()->name == null) {
                $this->redirect('Sign:continue');
            } else {
                $this->redirect('Homepage:default');
            }
        });
    }

    protected function createComponentSignSetup()
    {
        return $this->signUpFactory->createSignSetup(function ($values) {
            $setup = $this->getSession('SignSetup');
            $setup["section"] = $values["section"];
            $setup["role"] = $values["role"];

            if (!$this->isAjax()) {
                $this->redirect('Sign:up');
            }
        });
    }

    /**
     * Registration process - step 2
     * @return Form
     */
    protected function createComponentSignUpForm()
    {
        return $this->signUpFactory->createSignUp(function () {
            $this->flashMessage("You have been registered.", "green");
            $this->redirect('Sign:continue');
        });
    }

    /**
     * Registration process - step 3
     * @return Form
     */
    protected function createComponentSignContinueForm()
    {
        return $this->signUpFactory->createContinueRegistration(function () {
            $this->flashMessage("All set.", "green");
            $this->redirect('Homepage:default');
        });
    }

    /**
     * Component for upload avatar
     * @return Form
     */
    protected function createComponentUploadImage()
    {
        return $this->uploadImageFactory->uploadUserImage(function () {
            if (!$this->isAjax()) {
                $this->redirect('this');
            }
        });
    }

    protected function createComponentResetPassword()
    {
        return $this->signResetFormFactory->createResetPassword(function () {
            $this->flashMessage("Please click on the link in the email we've just sent you.", "green");
            $this->redirect("Sign:in");
        });
    }

    protected function createComponentSetNewPassword()

    {
        return $this->signResetFormFactory->createSetNewPassword(function ($values) {
            $email = $this->getSession('ResetPassword');
            $this->userRepository->setNewPassword($email["email"], $values["password"]);
            $email->remove();
            $this->flashMessage('<strong>Success!</strong><br> Your password has been updated.', "green");
            $this->redirect("Sign:in");
        });
    }
}
