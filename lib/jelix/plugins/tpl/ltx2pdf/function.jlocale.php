<?php
/**
* @package    jelix
* @subpackage jtpl_plugin
* @author     Laurent Jouanneau
* @copyright  2005-2007 Laurent Jouanneau
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
function jtpl_function_ltx2pdf_jlocale($tpl, $locale)
{
     if(func_num_args() == 3 && is_array(func_get_arg(2))){
         $param = func_get_arg(2);
         $loc = jLocale::get($locale, $param);
     }elseif(func_num_args() > 2){
         $params = func_get_args();
         unset($params[0]);
         unset($params[1]);
         $loc = jLocale::get($locale, $params);
     }else{
         $loc = jLocale::get($locale);
     }
     echo str_replace(array('#','$','%','^','&','_','{','}','~'), array('\\#','\\$','\\%','\\^','\\&','\\_','\\{','\\}','\\~'), str_replace('\\','\\textbackslash',$loc));
}

