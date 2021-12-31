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
 * Interface for widgets that can have children widget: main builder, choice etc.
 */
interface ParentWidgetInterface
{
    /**
     * Add javascript content into the generated form.
     *
     * @param mixed $js
     */
    public function addJs($js);

    /**
     * Add javascript content into the generated form
     * to be insert at the end of the whole JS generated script.
     *
     * @param mixed $js
     */
    public function addFinalJs($js);

    /**
     * indicate if the parent widget generate itself some of js child.
     *
     * @return bool
     */
    public function controlJsChild();
}
