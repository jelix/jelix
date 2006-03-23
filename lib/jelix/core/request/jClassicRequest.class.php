<?php
/**
* @package     jelix
* @subpackage  core
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jClassicRequest extends jRequest {

    public $type = 'classic';

    public $defaultResponseType = 'html';

    protected function _initParams(){

        $this->url  = jUrl::parse($_SERVER['SCRIPT_NAME'], $this->url_path_info, $_GET);
        $this->params = array_merge($this->url->params, $_POST);
    }



}
?>
