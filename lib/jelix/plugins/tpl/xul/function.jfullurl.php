<?php
/**
* @package    jelix
* @subpackage jtpl_plugin
* @author     Mickael Fradin aka kewix
* @contributor Laurent Jouanneau
* @copyright  2009 Mickael Fradin
* @link       http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * function plugin :  write the full url (with domain name) corresponding to the given jelix action
 *
 * @param jTpl $tpl template engine
 * @param string $selector selector action
 * @param array $params parameters for the url
 * @param string domain name, false if you want to use the config domain name or the server name
 * @param boolean $escape if true, then escape the string for html
 */
function jtpl_function_xul_jfullurl($tpl, $selector, $params=array(), $domain=false, $escape=true) {
    global $gJConfig;

    if (!$domain) {
        $domain = $gJConfig->domainName;
    }

    // Add the http or https if not given
    if (!preg_match('/^http/', $domain)) {
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] && $_SERVER['HTTPS']!='off'))
            $domain = 'https://'.$domain;
        else
            $domain = 'http://'.$domain;
    }

    // echo the full Url
    echo $domain.jUrl::get($selector, $params, ($escape?1:0));
}
