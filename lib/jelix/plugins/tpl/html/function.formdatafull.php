<?php
/**
 * @package      jelix
 * @subpackage   jtpl_plugin
 *
 * @author       Laurent Jouanneau
 * @contributor  Dominique Papin, Julien Issler, Brunto, DSDenes
 *
 * @copyright    2007-2022 Laurent Jouanneau, 2007 Dominique Papin
 * @copyright    2008-2010 Julien Issler, 2010 Brunto, 2009 DSDenes
 *
 * @see         http://www.jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * Display all data of a form without the use of other plugins.
 *
 * @param jTpl       $tpl     template engine
 * @param jFormsBase $form    the form to display
 * @param string     $builder the builder type to use
 * @param array      $options options for the builder
 */
function jtpl_function_html_formdatafull($tpl, $form, $builder = '', $options = array())
{
    if ($builder == '') {
        $builder = jApp::config()->tplplugins['defaultJformsBuilder'];
    }

    $formTplController = new \Jelix\Forms\HtmlWidget\TemplateController($form, $builder, $options);
    $formTplController->startForm();
    $formTplController->outputAllControlsValues();
    $formTplController->endForm();
}
