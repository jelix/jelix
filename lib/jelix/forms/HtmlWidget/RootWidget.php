<?php
/**
 * @package     jelix
 * @subpackage  forms
 *
 * @author      Laurent Jouanneau
 * @copyright   2006-2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Forms\HtmlWidget;

class RootWidget implements ParentWidgetInterface
{
    //------ ParentWidgetInterface

    protected $js = '';

    public function addJs($js)
    {
        $this->js .= $js;
    }

    protected $finalJs = '';

    public function addFinalJs($js)
    {
        $this->finalJs .= $js;
    }

    public function controlJsChild()
    {
        return false;
    }

    //------ Other methods

    /**
     * @var \jelix\forms\Builder\HtmlBuilder
     *
     * @deprecated
     */
    protected $builder;

    /**
     * @param \jelix\forms\Builder\HtmlBuilder $builder
     */
    public function outputHeader($builder)
    {
        $jsVarName = $builder->getjFormsJsVarName();

        $js = $jsVarName.'.tForm = new jFormsForm(\''.$builder->getName()."');\n";
        $js .= $jsVarName.'.tForm.setErrorDecorator(new '.$builder->getOption('errorDecorator')."())\n";
        if ($builder->getOption('deprecatedDeclareFormBeforeControls')) {
            $js .= $jsVarName.".declareForm(jForms.tForm);\n";
        }
        $this->addJs($js);
        $this->builder = $builder;
    }

    /**
     * @param \jelix\forms\Builder\HtmlBuilder $builder
     */
    public function outputFooter($builder)
    {
        $js = "(function(){var c, c2;\n".$this->js.$this->finalJs;
        if (!$builder->getOption('deprecatedDeclareFormBeforeControls')) {
            $js .= $builder->getjFormsJsVarName().".declareForm(jForms.tForm);\n";
        }
        $js .= '})();';
        $container = $builder->getForm()->getContainer();
        $container->privateData['__jforms_js'] = $js;
        $formId = $container->formId;
        $formName = $builder->getForm()->getSelector();
        echo '<script type="text/javascript" src="'.\jUrl::get(
            'jelix~jforms:js',
            array('__form' => $formName, '__fid' => $formId)
        ).'"></script>';
    }
}
