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
    xmlrpc, rdf, soap.

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
        $type = $this->getOption('-type');
        if(!$type)
            $type='classic';

        if(!in_array($type, array('classic','jsonrpc','xmlrpc','rdf','soap')))
            die("Error: invalid type\n");

        if($type=='classic')
            $type='index';

        $name = $this->getParam('name');
        if (file_exists(JELIX_APP_WWW_PATH.$name)) {
           die("Error: the entry point already exists\n");
        }

        $param = array();
        $param['rp_app']   = jxs_getRelativePath(JELIX_APP_WWW_PATH, JELIX_APP_PATH, true);
        $param['config_file'] = $name.'/config.ini.php';

        $this->createDir(JELIX_APP_CONFIG_PATH.$name);
        $this->createFile(JELIX_APP_CONFIG_PATH.$name.'/config.ini.php','var/config/index/config.ini.php.tpl',$param);
        $this->createFile(JELIX_APP_WWW_PATH.$name.'.php','www/'.$type.'.php.tpl',$param);


        $doc = new DOMDocument();

        if (!$doc->load(JELIX_APP_PATH.'project.xml')){
           die("Error: cannot load project.xml");
        }

        if ($doc->documentElement->namespaceURI != JELIX_NAMESPACE_BASE.'project/1.0'){
           die("Error: bad namespace in project.xml");
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

