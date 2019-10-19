<?php

namespace App\Forms;

use App\Model\CountryRepository;
use App\Model\UniversityRepository;
use App\Model\UserRepository;
use Kdyby\Translation\Translator;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;


/**
 * Class SignInFormFactory
 * @package App\Admin\Forms
 */
class ProfileFormFactory
{
    use Nette\SmartObject;

    private $internationalDialRegex = "(\+|00)(297|93|244|1264|358|355|376|971|54|374|1684|1268|61|43|994|257|32|229|226|880|359|973|1242|387|590|375|501|1441|591|55|1246|673|975|267|236|1|61|41|56|86|225|237|243|242|682|57|269|238|506|53|5999|61|1345|357|420|49|253|1767|45|1809|1829|1849|213|593|20|291|212|34|372|251|358|679|500|33|298|691|241|44|995|44|233|350|224|590|220|245|240|30|1473|299|502|594|1671|592|852|504|385|509|36|62|44|91|246|353|98|964|354|972|39|1876|44|962|81|76|77|254|996|855|686|1869|82|383|965|856|961|231|218|1758|423|94|266|370|352|371|853|590|212|377|373|261|960|52|692|389|223|356|95|382|976|1670|258|222|1664|596|230|265|60|262|264|687|227|672|234|505|683|31|47|977|674|64|968|92|507|64|51|63|680|675|48|1787|1939|850|351|595|970|689|974|262|40|7|250|966|249|221|65|500|4779|677|232|503|378|252|508|381|211|239|597|421|386|46|268|1721|248|963|1649|235|228|66|992|690|993|670|676|1868|216|90|688|886|255|256|380|598|1|998|3906698|379|1784|58|1284|1340|84|678|681|685|967|27|260|263)(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{4,20}$";


    private $renderer;
    private $userRepository;
    private $translator;
    private $universityRepository;
    private $countryRepository;


    /**
     * ProfileFormFactory constructor.
     * @param DefaultFormRenderer $renderer
     * @param UserRepository $userRepository
     * @param UniversityRepository $universityRepository
     * @param CountryRepository $countryRepository
     * @param Translator $translator
     */
    public function __construct(DefaultFormRenderer $renderer,
                                UserRepository $userRepository,
                                UniversityRepository $universityRepository,
                                CountryRepository $countryRepository,
                                Translator $translator)
    {
        $this->renderer = $renderer;
        $this->userRepository = $userRepository;
        $this->translator = $translator;
        $this->universityRepository = $universityRepository;
        $this->countryRepository = $countryRepository;
    }

    /**
     * Create component for edit profile - only for logged user
     * @param callable $onSuccess
     *
     * @return Form
     */
    public function createEditMyProfileForm(callable $onSuccess)
    {

        $sex = [
            'm' => 'Male',
            'f' => 'Female',
        ];

        $faculties = $this->universityRepository->getFaculties($this->userRepository->university);
        $countries = $this->countryRepository->getAllCountries();

        $user = $this->userRepository->getData();
        if ($this->userRepository->isInRole("admin") || $this->userRepository->isInRole("globalAdmin")) {
            $faculties["long"][$user["faculty"]["id"]] = $user["faculty"]["faculty"];
            array_unique($faculties["long"]);
        }

        $form = $this->renderer->create();

        if ($this->userRepository->isInRole("admin") || $this->userRepository->isInRole("globalAdmin")) {
            $form->addHidden("user_id")->setDefaultValue($this->userRepository->getId());

            $form->addText("new_email", "Email")
                ->setHtmlAttribute('placeholder', 'Email')
                ->addRule(Form::EMAIL)
                ->setDefaultValue($this->userRepository->getId())
                ->setRequired();
        } else {
            $form->addHidden("user_id")->setDefaultValue($this->userRepository->getId());
        }

        $form->addText("name", "First name")
            ->setHtmlAttribute('placeholder', 'First name')
            ->setDefaultValue($user["name"])
            ->setRequired("What's your name?");

        $form->addText("surname", "Last name")
            ->setHtmlAttribute('placeholder', "Last name")
            ->setDefaultValue($user["surname"])
            ->setRequired("What's your name?");

        $form->addSelect('faculty_id', 'Faculty:', $faculties["long"])
            ->setPrompt("Faculty")
            ->setDefaultValue($user["faculty_id"])
            ->setRequired("What's your faculty?");

        $form->addSelect('gender', 'Gender:', $sex)
            ->setPrompt("Gender")
            ->setDefaultValue($user["gender"])
            ->setRequired();

        $form->addText("phone_number", "Phone (include country code):")
            ->setHtmlAttribute('placeholder', "Phone (include country code)")
            ->setDefaultValue($user["phone_number"])
            ->addRule(Form::PATTERN, 'Please Enter a valid phone number. International numbers start with \'+\' and country code (ex. +33155555555)', $this->internationalDialRegex)
            ->setRequired(false);

        $form->addSelect("country_id", "Country:", $countries)
            ->setHtmlAttribute('placeholder', "Country")
            ->setPrompt("Country")
            ->setDefaultValue($user["country_id"]);

        $form->addText("birthday", "Birthday:")
            ->setHtmlAttribute('placeholder', "Birthday")
            ->setHtmlAttribute("class", 'datetimepicker')
            ->setDefaultValue(date("d. m. Y", strtotime($user["birthday"])));

        if ($this->userRepository->isInRole("editor") || $this->userRepository->isInRole("admin") || $this->userRepository->isInRole("globalAdmin")) {
            $form->addText("esn_card", "ESN card:")
                ->setHtmlAttribute('placeholder', "ESN card")
                ->setDefaultValue($user["esn_card"]);
        }

        if ($this->userRepository->isInRole("international")) {
            $form->addText("home_university", "Home University:")
                ->setHtmlAttribute('placeholder', "Home University")
                ->setDefaultValue($user["home_university"]);
        }

        $form->addText("facebook_url", "Facebook URL:")
            ->setHtmlAttribute('placeholder', "Facebook URL")
            ->setDefaultValue($user["facebook_url"]);

        $form->addText("instagram_url", "Instagram URL:")
            ->setHtmlAttribute('placeholder', "Instagram URL")
            ->setDefaultValue($user["instagram_url"]);

        $form->addText("twitter_url", "Twitter URL:")
            ->setHtmlAttribute('placeholder', "Twitter URL")
            ->setDefaultValue($user["twitter_url"]);

        $form->addTextArea("description", "About me", "30", "10")
            ->setHtmlAttribute("placeholder", "Write some details about yourself")
            ->addRule(Form::MAX_LENGTH, null, 500)
            ->setDefaultValue($user["description"])
            ->setRequired(false);

        $form->addSubmit('send', "Save Changes");

        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $values["birthday"] = DateTime::createFromFormat("j. n. Y", $values["birthday"]);
            $this->userRepository->setUserData($values);

            if (isset($values["new_email"])) {
                $this->userRepository->setUser($values);
            }
            $onSuccess($values);
        };

        return $form;
    }
}
