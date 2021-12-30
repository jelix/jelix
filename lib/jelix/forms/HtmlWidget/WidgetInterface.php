<?php
/**
 * @package     jelix
 * @subpackage  forms
 *
 * @author      Laurent Jouanneau
 * @copyright   2012 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Forms\HtmlWidget;

/**
 * Interface for HTML widgets plugins.
 */
interface WidgetInterface
{
    /*
     * @param array $args array containing:
     *    jFormsControl,
     *    \jelix\forms\Builder\HtmlBuilder,
     *    \jelix\forms\HtmlWidget\ParentWidgetInterface,
     */
    public function __construct($args);

    /**
     * return the id of the HTML element.
     */
    public function getId();

    /**
     * return the name of the HTML element.
     */
    public function getName();

    /**
     * return the value of the HTML element.
     */
    public function getValue();

    /**
     * add the CSS and javascript link.
     *
     * @param \jResponseHtml $resp The response used
     */
    public function outputMetaContent($resp);

    /**
     * displays the help of the form field.
     */
    public function outputHelp();

    /**
     * displays the form field label.
     *
     * @param mixed $format
     * @param mixed $editMode
     */
    public function outputLabel($format = '', $editMode = true);

    /**
     * displays the form field itself.
     */
    public function outputControl();

    /**
     * displays the value of the form field only.
     */
    public function outputControlValue();

    /**
     * displays the raw value of the form field only.
     */
    public function outputControlRawValue();

    /**
     * set attributes to add on the HTML element.
     *
     * @param array $attributes
     */
    public function setAttributes($attributes);
}
