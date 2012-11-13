<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @copyright   2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

namespace jelix\forms\HtmlWidget;

/**
 * Interface for HTML widgets plugins
 */
interface WidgetInterface {

    public function __construct(\jelix\forms\Builder\HtmlBuilder $mainBuilder, ParentBuilderInterface $parentBuilder);

    /**
     * return the id of the HTML element
     */
    public function getId();

    /**
     * return the name of the HTML element
     */
    public function getName();

    /**
     * return the CSS class of the HTML element
     */
    public function getCSSClass();
    
    /**
     * return the value of the HTML element
     */
    public function getValue();

    public function outputMeta();

    /**
     * displays the help of the form field
     */
    public function outputHelp();

    /**
     * displays the form field label.
     */
    public function outputLabel();

    /**
     * displays the form field itself
     */
    public function outputControl();

}

