<?php

namespace App\Forms;

use App\Model\UserRepository;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Form;
use Nette\Utils\Image;

class UploadImageFactory
{
    private $renderer;
    private $user;
    private $translator;

    /**
     * SignUpFormFactory constructor.
     * @param DefaultFormRenderer $renderer
     * @param UserRepository      $user
     * @param Translator          $translator
     */
    public function __construct(DefaultFormRenderer $renderer,
                                UserRepository $user,
                                Translator $translator)
    {
        $this->renderer = $renderer;
        $this->user = $user;
        $this->translator = $translator;
    }

    /**
     * Upload Image to server (logged user)
     *
     * @param callable $onSuccess
     *
     * @return Form
     */
    public function uploadUserImage(callable $onSuccess)
    {
        return $this->createBaseUploadForm(
            $this->user->identity->signature,
            $onSuccess
        );
    }

    /**
     * Upload Image to server (anyone)
     * @param          $signature
     * @param callable $onSuccess
     * @return Form
     */
    public function uploadImage($signature, callable $onSuccess)
    {
        return $this->createBaseUploadForm(
            $signature,
            $onSuccess
        );
    }

    /**
     * Creates base for upload form (file input & submit button)
     * @param          $userSignature
     * @param callable $onSuccess
     * @return Form
     */
    protected function createBaseUploadForm($userSignature, callable $onSuccess)
    {
        $form = $this->renderer->create();
        $form->getElementPrototype()->class('ajax');
        $form->addUpload('upload', '', FALSE)
            ->setRequired(false)
            ->addRule(Form::IMAGE, "The uploaded file must be image in format JPEG or PNG.", array('image/jpeg', 'image/png'))
            ->addRule(Form::MAX_FILE_SIZE, "The size of the uploaded image cannot be up to 2 MB", 2000000 /* v bytech */)
            ->setHtmlAttribute('class', "hidden");

        $form->addSubmit('send', 'Send')
            ->setHtmlAttribute('id', "sendUpload")
            ->setHtmlAttribute("class", "hidden");

        $form->addHidden("signature")->setDefaultValue($userSignature);

        /**
         * @param Form $form
         * @param      $values
         */
        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            if ($values->upload->isImage() and $values->upload->isOk()) {
                $image = Image::fromFile($values->upload);
                $image->resize(300, 300, Image::EXACT);
                $image->save("images/avatar/{$values->signature}.jpg", 100, Image::JPEG);
            }
            $onSuccess();
        };

        return $form;
    }
}