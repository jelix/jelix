<?php
/**
* @package     jelix-scripts
* @author      Jouanneau Laurent
* @contributor Bastien Jaillot (bug fix)
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class createdaocrudCommand extends JelixScriptCommand {

    public  $name = 'createdaocrud';
    public  $allowed_options=array('-profil'=>true);
    public  $allowed_parameters=array('module'=>true, 'table'=>true, 'ctrlname'=>false);

    public  $syntaxhelp = "[-profil name] MODULE TABLE [CTRLNAME]";
    public  $help=array(
        'fr'=>"
    Créer un nouveau controleur de type jControllerDaoCrud, reposant sur un jdao et un jform.

    -profil (facultatif) : indique le profil à utiliser pour se connecter à
                           la base et récupérer les informations de la table
    MODULE : le nom du module où stocker le contrôleur
    TABLE : le nom de la table SQL
    CTRLNAME (facultatif) : nom du controlleur (par défaut, celui de la table)",

        'en'=>"
    Create a new controller jControllerDaoCrud

    -profil (optional) : indicate the name of the profil to use for the
                        database connection.

    MODULE: name of the module wher to create the crud
    TABLE : name of the SQL table
    CTRLNAME (optional) : name of the controller."
    );


    public function run(){

        jxs_init_jelix_env();
        $path= $this->getModulePath($this->_parameters['module']);

        $table = $this->getParam('table');
        $ctrlname = $this->getParam('ctrlname', $table);

        if(file_exists($path.'controllers/'.$ctrlname.'.classic.php')){
            die("Error: controller '".$ctrlname."' already exists");
        }

        $agcommand = jxs_load_command('createdao');
        $options = array();
        $profil = '';
        if ($this->getOption('-profil')) {
            $profil = $this->getOption('-profil');
            $options = array('-profil'=>$profil);
        }
        $agcommand->init($options,array('module'=>$this->_parameters['module'], 'name'=>$ctrlname,'table'=>$table));
        $agcommand->run();

        $agcommand = jxs_load_command('createform');
        $agcommand->init(array(),array('module'=>$this->_parameters['module'], 'form'=>$ctrlname,'dao'=>$ctrlname));
        $agcommand->run();

        $this->createDir($path.'controllers/');
        $this->createFile($path.'controllers/'.$ctrlname.'.classic.php','controller.daocrud.tpl',array('name'=>$ctrlname, 
                'module'=>$this->_parameters['module'], 'table'=>$table, 'profil'=>$profil));
    }
}


?>