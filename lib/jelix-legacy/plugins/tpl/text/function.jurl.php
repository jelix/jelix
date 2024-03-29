<?php
/**
 * @package    jelix
 * @subpackage jtpl_plugin
 *
 * @author     Laurent Jouanneau
 * @copyright  2005-2006 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 *
 * @param mixed $tpl
 * @param mixed $selector
 * @param mixed $params
 */

/**
 * function plugin :  write the url corresponding to the given jelix action.
 *
 * @param jTpl   $tpl      template engine
 * @param string $selector selector action
 * @param array  $params   parameters for the url
 */
function jtpl_function_text_jurl($tpl, $selector, $params = array())
{
    echo jUrl::get($selector, $params, 0);
}
