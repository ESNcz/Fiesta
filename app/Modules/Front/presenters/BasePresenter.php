<?php

namespace App\Front\Presenters;

use Kdyby\Translation\Translator;
use Nette;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @persistent */
    public $locale;

    /** @var Translator @inject */
    public $translator;

    function startup()
    {
        parent::startup();

    }

    function beforeRender()
    {
        parent::beforeRender();
    }
}
