<?php
/**
* @package     jelix-scripts
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class createzoneCommand extends JelixScriptCommand {

    public  $name = 'createzone';
    public  $allowed_options=array('-notpl'=>false);
    public  $allowed_parameters=array('module'=>true,'name'=>true, 'template'=>false);

    public  $syntaxhelp = "[-notpl] MODULE ZONE [TEMPLATE]";
    public  $help="
    Permet de créer un nouveau fichier de zone

    -notpl : indique qu'il ne faut pas créer de template associé
    MODULE : le nom du module concerné.
    ZONE : nom de la zone à créer.
    TEMPLATE (facultatif) : nom du template associé à créer (par defaut, à le nom de la zone).";


    public function run(){
       $path= $this->getModulePath($this->_parameters['module']);

       $filename= $path.'zones/';
       $this->createDir($filename);

       $filename.=strtolower($this->_parameters['name']).'.zone.php';

       $param= array('name'=>$this->_parameters['name'] , 'module'=>$this->_parameters['module']);

       if(!$this->getOption('-notpl')){
          if($tpl = $this->getParam('template')){
             $param['template'] = $tpl.'.tpl';
          }else{
             $param['template'] = strtolower($this->_parameters['name']).'.tpl';
          }
          $this->createFile($path.'templates/'.$param['template'],'template.tpl');
       }else{
          $param['template'] ='.tpl';
       }

       $this->createFile($filename,'zone.tpl',$param);
    }
}


?>