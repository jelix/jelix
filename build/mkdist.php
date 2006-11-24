<?php

/**
* @package     jBuildTools
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

// arguments :  fichier.lf   chemin_source chemin_dist

require_once(dirname(__FILE__).'/lib/jManifest.class.php');

if($_SERVER['argc'] < 4){
   exit(1);
}
array_shift($_SERVER['argv']); // shift the script name

$options = array('verbose'=>false, 'stripcomment'=>false);

if(substr($_SERVER['argv'][0],0,1) == '-'){
  $sw = substr(array_shift($_SERVER['argv']),1);
  $options['verbose'] = (strpos('v', $sw) !== false);
  $options['stripcomment'] = (strpos('c', $sw) !== false);
}

try {
    list($ficlist, $sourcedir, $distdir) = $_SERVER['argv'];
    jManifest::process($ficlist, $sourcedir, $distdir, $_SERVER, $options['stripcomment'], $options['verbose']);
    exit(0);
}catch(Exception $e){
    echo $e->getMessage();
    exit(1);
}

?>