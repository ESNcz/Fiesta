<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;


/**
 * Class DefaultFormRenderer
 * @package App\Model
 */
class DefaultFormRenderer
{
    use Nette\SmartObject;

    /**
     * Create form responsive with twitter bootstrap
     * @return Form
     */
    public function create()
    {

        $form = new form;
        $this->makeBootstrap3($form);
        //$form->setTranslator($this->translator);

        Nette\Forms\Container::extensionMethod('addDate', function ($form, $name, $label = null) {
            return $form->addText($name, $label)
                ->setHtmlAttribute("class", "form-control");
        });

        return $form;
    }

    /**
     * Create form responsive with twitter bootstrap
     * @param Form $form
     */
    function makeBootstrap3(Form $form)
    {
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['pair']['container'] = 'div class=form-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = '';
        $renderer->wrappers['label']['container'] = 'control-label"';
        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
        $form->onRender[] = function ($form) {
            foreach ($form->getControls() as $control) {
                $type = $control->getOption('type');
                if ($type === 'button') {
                    $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn p-v-xl form-btn btn-primary' : 'btn p-v-xl form-btn btn-default');
                    $usedPrimary = true;
                } elseif (in_array($type, ['text', 'textarea', 'select'], true)) {
                    $control->getControlPrototype()->addClass('form-control');
                } elseif (in_array($type, ['checkbox', 'radio'], true)) {
                    $control->getSeparatorPrototype()->setName('div')->addClass($type);
                }

                if ($type === 'select') {
                    $control->getControlPrototype()->addClass("default-select2");
                }
            }
        };
    }

}