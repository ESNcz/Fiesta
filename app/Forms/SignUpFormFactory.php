<?php

namespace App\Forms;

use App\Model\AuthenticatorFactory;
use App\Model\DuplicateNameException;
use App\Model\UniversityRepository;
use App\Model\UserRepository;
use Kdyby\Translation\Translator;
use Nette;
use Nette\Application\UI\Form;
use Nette\Http\Session;


class SignUpFormFactory
{

    use Nette\SmartObject;

    const PASSWORD_MIN_LENGTH = 6;

    private $renderer;
    private $session;
    private $sessionSection;
    private $university;
    private $translator;
    private $authenticatorFactory;
    private $userRepository;

    /**
     * SignUpFormFactory constructor.
     * @param DefaultFormRenderer $renderer
     * @param UniversityRepository $university
     * @param AuthenticatorFactory $authenticatorFactory
     * @param UserRepository $userRepository
     * @param Session $session
     * @param Translator $translator
     */
    public function __construct(DefaultFormRenderer $renderer,
                                UniversityRepository $university,
                                AuthenticatorFactory $authenticatorFactory,
                                UserRepository $userRepository,
                                Session $session,
                                Translator $translator)
    {
        $this->renderer = $renderer;
        $this->session = $session;
        $this->sessionSection = $session->getSection('SignSetup');
        $this->university = $university;
        $this->translator = $translator;
        $this->authenticatorFactory = $authenticatorFactory;
        $this->userRepository = $userRepository;
    }


    /**
     * Registration process - step 1
     *
     * @param callable $onSuccess
     *
     * @return Form
     */
    public function createSignSetup(callable $onSuccess)
    {
        $role = [
            'member' => 'Member',
            'international' => 'International',
        ];

        $university = $this->university->getAllUniversities();

        $form = $this->renderer->create();

        $form->addProtection();

        $form->addSelect('section', $this->translator->translate("sign.setup.selectUniversity"), $university)
            ->setPrompt($this->translator->translate("sign.setup.promptUniversity"))
            ->setRequired($this->translator->translate("sign.setup.requiredUniversity"));

        $form->addRadioList('role', $this->translator->translate("sign.setup.role"), $role)
            ->setRequired($this->translator->translate("sign.setup.requiredRole"));


        $form->addSubmit('send', $this->translator->translate("sign.setup.continue"));

        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $onSuccess($values);
        };

        return $form;
    }

    /**
     * @param callable $onSuccess
     * By signing up, you agree to our Terms of Use and Privacy Policy.
     * By signing up to create an account I accept Fiesta's Terms of Use and Privacy Policy,
     * @return Form
     */
    public function createSignUp(callable $onSuccess)
    {
        $form = $this->renderer->create();

        $form->setTranslator(
            $this->translator->domain(
                "sign.up"
            )
        );

        $form->addEmail('email')
            ->addRule(Form::EMAIL, "validation")
            ->setRequired('requiredEmail')
            ->setHtmlAttribute('class', 'input-block-level')
            ->setHtmlAttribute('placeholder', 'email');

        $form->addPassword('password')
            ->setRequired('requiredPassword')
            ->addRule($form::MIN_LENGTH, "minCharacter", self::PASSWORD_MIN_LENGTH)
            ->setHtmlAttribute('class', 'input-block-level')
            ->setHtmlAttribute('placeholder', 'password');

        $form->addSubmit('send', 'continue');

        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            try {
                $this->authenticatorFactory->setNewUser($values->email, $values->password, $this->sessionSection);
                $this->userRepository->login($values->email, $values->password);
            } catch (DuplicateNameException $e) {
                $form['email']->addError('taken');
                return;
            }
            $onSuccess();
        };

        return $form;
    }

    public function createContinueRegistration(callable $onSuccess)
    {

        $sex = [
            'm' => $this->translator->translate("sign.setup.male"),
            'f' => $this->translator->translate("sign.setup.female"),
        ];

        $faculties = $this->university->getAllFaculties($this->userRepository->university);

        $form = $this->renderer->create();

        $form->addProtection();

        $form->addText("name", $this->translator->translate("sign.setup.firstName"))
            ->setHtmlAttribute('placeholder', $this->translator->translate("sign.setup.firstName"))
            ->setRequired($this->translator->translate("sign.setup.requiredFirstName"));

        $form->addText("surname", $this->translator->translate("sign.setup.lastName"))
            ->setHtmlAttribute('placeholder', $this->translator->translate("sign.setup.lastName"))
            ->setRequired($this->translator->translate("sign.setup.requiredLastName"));

        /*$form->addText("phone_number", "Phone (include country code):")
            ->setHtmlAttribute('placeholder', "Phone (include country code):")
            ->addRule(Form::PATTERN, 'Please Enter a valid phone number. International numbers start with \'+\' and country code (ex. +33155555555)', $this->internationalDialRegex)
            ->setRequired("What's your phone number?");*/

        $form->addSelect('faculty_id', $this->translator->translate("sign.setup.faculty"), $faculties["long"])
            ->setPrompt($this->translator->translate("sign.setup.faculty"))
            ->setRequired($this->translator->translate("sign.setup.requiredFaculty"));

        $form->addRadioList('gender', $this->translator->translate("sign.setup.gender"), $sex)
            ->addRule(Form::REQUIRED, $this->translator->translate("sign.setup.requiredGender"));


        $form->addSubmit('send', $this->translator->translate("sign.setup.createAccount"));

        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $this->userRepository->setUserData($values);

            if ($this->userRepository->isInRole("member")) {
                $this->userRepository->setStatus("pending", $this->userRepository->getId());
            }

            if ($this->userRepository->isInRole("international")) {
                $this->userRepository->setStatus("active", $this->userRepository->getId());
            }

            $onSuccess();
        };

        return $form;
    }

}