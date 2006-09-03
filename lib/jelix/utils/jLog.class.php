<?php
/**
* @package    jelix
* @subpackage utils
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2006 Jouanneau laurent
* @link       http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 *
 * @package    jelix
 * @subpackage utils
 */
class jLog {

   private function __construct(){}

   public static function dump($obj, $label='', $type='default'){
      if($label!=''){
         $message = $label.': '.var_export($obj,true);
      }else{
         $message = var_export($obj,true);
      }
      self::log($message, $type);
   }

   public static function log($message, $type='default'){
      global $gJConfig;
      $f = $gJConfig->logfiles[$type];
      $f = str_replace('%ip%', $_SERVER['REMOTE_ADDR'], $f);
      $sel = new jSelectorLog($f);

      $str = date ("Y-m-d H:i:s")."\t".$_SERVER['REMOTE_ADDR']."\t$type\t$message\n";

      error_log($str,3, $sel->getPath());
   }
}
?>