<?php
/**
* @package    jelix
* @subpackage jtpl_plugin
* @author     Laurent Jouanneau
* @copyright  2005-2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * function plugin :  write the localized string corresponding to the given locale key
 *
 * example : {jlocale 'myModule~my.locale.key'}
 * @param jTpl $tpl template engine
 * @param string $locale the locale key
 */
function jtpl_function_xml_jlocale($tpl, $locale)
{
     if(func_num_args() == 3 && is_array(func_get_arg(2))){
         echo htmlspecialchars(jLocale::get($locale, func_get_arg(2)));
     }elseif(func_num_args() > 2){
         $params = func_get_args();
         unset($params[0]);
         unset($params[1]);
         echo htmlspecialchars(jLocale::get($locale, $params));
     }else{
         echo htmlspecialchars(jLocale::get($locale));
     }
}

