<?php
/**
 * @package    jelix
 * @subpackage jtpl_plugin
 *
 * @author     Mickael Fradin
 * @contributor Laurent Jouanneau
 *
 * @copyright  2009 Mickael Fradin
 *
 * @see       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 *
 * @param mixed      $tpl
 * @param mixed      $selector
 * @param mixed      $params
 * @param null|mixed $domain
 * @param mixed      $escape
 */

/**
 * function plugin :  write the full url (with domain name) corresponding to the given jelix action.
 *
 * @param jTpl   $tpl      template engine
 * @param string $selector selector action
 * @param array  $params   parameters for the url
 * @param string $domain   domain name, false if you want to use the config domain name or the server name
 * @param bool   $escape   if true, then escape the string for html
 */
function jtpl_function_html_jfullurl($tpl, $selector, $params = array(), $domain = null, $escape = true)
{
    echo jUrl::getFull($selector, $params, ($escape ? 1 : 0), $domain);
}
