<?php
/**
* @package    jelix
* @subpackage template plugins
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 *
 * @param $tpl jtpl
 * @param $selector string     the action selector
 * @param $params   array      paramètres url et leurs valeurs
 * @param $jsparam  array      paramètres url dynamique et le nom de leur variable js correspondante
 */

function jtpl_function_urljsstring($tpl, $selector, $params=array(), $jsparams=array())
{
    $search = array();
    $repl =array();
    foreach($jsparams as $par=>$var){
        $params[$par] = '__@@'.$var.'@@__';
        $search[] = $params[$par];
        $repl[] = '"+encodeURIComponent('.$var.')+"';
    }
    $url = jUrl::get($selector, $params, false);

    echo '"'.str_replace($search, $repl, $url).'"';
}

?>