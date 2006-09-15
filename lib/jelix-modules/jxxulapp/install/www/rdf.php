<?php
/**
* @package  demo
* @subpackage www
* @version  $Id$
* @author
* @contributor
* @copyright
*/

require_once ('../../lib/jelix/init.php');

require_once ('.././application.init.php');

require_once (JELIX_LIB_CORE_PATH.'request/jRdfRequest.class.php');

$config_file = 'config.rdf.ini.php';

$jelix = new jCoordinator($config_file);
$req=new jRdfRequest();
$jelix->process($req);

?>