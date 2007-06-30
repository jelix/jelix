<?php
/**
* @package    jelix
* @subpackage coord_plugin
* @author     Gerald Croes, Jouanneau Laurent
* @contributor
* @copyright  2001-2005 CopixTeam, 2005-2007 Jouanneau laurent
* Some parts of this file are took from Copix Framework v2.3dev20050901, magicquotes.plugin.php,
* copyrighted by CopixTeam and released under GNU Lesser General Public Licence
* author : Gerald Croes, Laurent Jouanneau
* http://www.copix.org
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*
*/

/**
 * This is a plugin which cancel magic quotes effect. Magic quotes should be off
 * with jelix.
 * @package    jelix
 * @subpackage coord_plugin
 */
class MagicQuotesCoordPlugin implements jICoordPlugin {

    /**
    *
    */
    function __construct($config){
        if(get_magic_quotes_gpc()){
            foreach ($_GET as $key=>$elem){
                $_GET[$key] = $this->_stripSlashes ($elem);
            }
            foreach ($_POST as $key=>$elem){
                $_POST[$key] = $this->_stripSlashes ($elem);
            }
            foreach ($_COOKIE as $key=>$elem){
                $_COOKIE[$key] = $this->_stripSlashes ($elem);
            }
            foreach ($_REQUEST as $key=>$elem){
                $_REQUEST[$key] = $this->_stripSlashes ($elem);
            }
        }
        set_magic_quotes_runtime(0);
    }

    /**
    * enleve tout les slashes d'une chaine ou d'un tableau de chaine
    * @param string/array   $string
    * @return string/array   l'objet transformé
    */
    protected function _stripSlashes ($string){
        if (is_array ($string)){
            $toReturn = array ();
            // c'est un tableau, on traite un à un tout les elements du tableau
            foreach ($string as $key=>$elem){
                $toReturn[$key] = $this->_stripSlashes ($elem);
            }
            return $toReturn;
        }else{
            return stripslashes ($string);
        }
    }

    /**
     * @param    array  $params   plugin parameters for the current action
     * @return null or jSelectorAct  if action should change
     */
    public function beforeAction($params){ return null;}

    /**
    *
    */
    public function beforeOutput() {}

    /**
    *
    */
    public function afterProcess (){}
}
?>
