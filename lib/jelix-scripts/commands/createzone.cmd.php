<?php
/**
* @package     jelix-scripts
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class createzoneCommand extends JelixScriptCommand {

    public  $name = 'createzone';
    public  $allowed_options=array('-notpl'=>false);
    public  $allowed_parameters=array('module'=>true,'name'=>true, 'template'=>false);

    public  $syntaxhelp = "[-notpl] MODULE ZONE [TEMPLATE]";
    public  $help=array(
        'fr'=>"
    Permet de créer un nouveau fichier de zone

    -notpl : indique qu'il ne faut pas créer de template associé
    MODULE : le nom du module concerné.
    ZONE   : nom de la zone à créer.
    TEMPLATE (facultatif) : nom du template associé à créer (par defaut, il a
                            le nom de la zone).",
        'en'=>"
    Create a new zone.

    -notpl : doesn't create a template file with the zone.
    MODULE : module name where the zone is created.
    ZONE   : zone name.
    TEMPLATE (optional) : name of the template created with the zone
            (by default, the template name is the zone name).",

    );


    public function run(){
       $path= $this->getModulePath($this->_parameters['module']);

       $filename= $path.'zones/';
       $this->createDir($filename);

       $filename.=strtolower($this->_parameters['name']).'.zone.php';

       $param= array('name'=>$this->_parameters['name'] , 'module'=>$this->_parameters['module']);

       if(!$this->getOption('-notpl')){
          if($tpl = $this->getParam('template')){
             $param['template'] = $tpl;
          }else{
             $param['template'] = strtolower($this->_parameters['name']);
          }
          $this->createFile($path.'templates/'.$param['template'].'.tpl','template.tpl');
       }else{
          $param['template'] = '';
       }

       $this->createFile($filename,'zone.tpl',$param);
    }
}


?>
