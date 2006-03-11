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

function jtpl_function_jlocale($tpl, $locale)
{
     if(func_num_args() == 3 && is_array(func_get_arg(2))){
         echo jLocale::get($locale, func_get_arg(2));
     }elseif(func_num_args() > 2){
         $params = func_get_args();
         unset($params[0]);
         unset($params[0]);
         echo jLocale::get($locale, $params);
     }else{
         echo jLocale::get($locale);
     }
}

?>