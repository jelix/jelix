<?php

/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2008 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class createentrypointCommand extends JelixScriptCommand {

    public  $name = 'createentrypoint';
    public  $allowed_options=array('-type'=>true);
    public  $allowed_parameters=array('name'=>true);

    public  $syntaxhelp = "[-type a_type] NAME";
    public  $help='';

    function __construct(){
        $this->help= array(
            'fr'=>"
    Crée un nouveau point d'entree dans le répertoire www de l'application.

    L'option -type indique le type de point d'entrée : classic, jsonrpc,
    xmlrpc, rdf, soap, cmdline.

    Le nom du point d'entrée peut contenir un sous-repertoire. Il ne doit pas
    contenir le suffixe .php
    ",
            'en'=>"
    Create a new entry point in the www directory of the application.
    
    The -type option indicates the type of the entry point: classic, jsonrpc,
    xmlrpc, rdf, soap.
    
    The name of the entry point can contain a subdirectory. It shouldn't
    contain the .php suffix.
    ",
    );
    }

    public function run(){
        jxs_init_jelix_env();
        $type = $this->getOption('-type');
        if(!$type)
            $type='classic';

        if(!in_array($type, array('classic','jsonrpc','xmlrpc','rdf','soap','cmdline' )))
            throw new Exception("invalid type");

        if($type=='classic')
            $type='index';

        $name = $this->getParam('name');

        $inifile = new jIniMultiFilesModifier(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php',
                                              JELIX_APP_CONFIG_PATH.'index/config.ini.php');

        $param = array();
        $param['modulename'] = $inifile->getValue('startModule');
        
        if ($type == 'cmdline') {
            if (file_exists(JELIX_APP_CMD_PATH.$name.'.php')) {
                throw new Exception("the entry point already exists");
            }

            $this->createDir(JELIX_APP_CONFIG_PATH.'cmdline');
            $this->createDir(JELIX_APP_CMD_PATH);
            $this->createFile(JELIX_APP_CONFIG_PATH.'cmdline/'.$name.'.ini.php','var/config/cmdline/config.ini.php.tpl', $param);
            $param['rp_cmd'] =jxs_getRelativePath(JELIX_APP_PATH, JELIX_APP_CMD_PATH,true);
            $this->createFile(JELIX_APP_CMD_PATH.$name.'.php','scripts/cmdline.php.tpl',$param);
            return;
        }
        
        if (file_exists(JELIX_APP_WWW_PATH.$name.'.php')) {
           throw new Exception("the entry point already exists");
        }

        $param['rp_app']   = jxs_getRelativePath(JELIX_APP_WWW_PATH, JELIX_APP_PATH, true);
        $param['config_file'] = $name.'/config.ini.php';

        $this->createDir(JELIX_APP_CONFIG_PATH.$name);
        $this->createFile(JELIX_APP_CONFIG_PATH.$name.'/config.ini.php','var/config/index/config.ini.php.tpl',$param);
        $this->createFile(JELIX_APP_WWW_PATH.$name.'.php','www/'.$type.'.php.tpl',$param);


        $doc = new DOMDocument();

        if (!$doc->load(JELIX_APP_PATH.'project.xml')){
           throw new Exception("cannot load project.xml");
        }

        if ($doc->documentElement->namespaceURI != JELIX_NAMESPACE_BASE.'project/1.0'){
           throw new Exception("bad namespace in project.xml");
        }

        $elem = $doc->createElementNS(JELIX_NAMESPACE_BASE.'project/1.0', 'entry');
        $elem->setAttribute("file", $name.".php");
        $elem->setAttribute("config", $name."/config.ini.php");
        $ep = $doc->documentElement->getElementsByTagName("entrypoints");
        if(!$ep->length) {
            $ep =  $doc->createElementNS(JELIX_NAMESPACE_BASE.'project/1.0', 'entrypoints');
            $doc->documentElement->appendChild($ep);
            $ep->appendChild($elem);
        }
        else
            $ep->item(0)->appendChild($elem);
        $doc->save(JELIX_APP_PATH.'project.xml');
    }
}

