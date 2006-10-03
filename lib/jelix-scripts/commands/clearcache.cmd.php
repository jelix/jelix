<?php
/**
* @package     jelix-scripts
* @author      Thiriot Christophe
* @contributor
* @copyright   2006 Thiriot Christophe
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class clearcacheCommand extends JelixScriptCommand {

    public  $name = 'clearcache';
    public  $allowed_options=array('-v'=>false);
    public  $allowed_parameters=array();

    public  $syntaxhelp = "[-v]";
    public  $help="
    Vide le cache.

    -v (facultatif) : affiche la liste des fichiers et dossiers supprimés";


    public function run(){
        try {
            $this->recurDel(JELIXS_APPTPL_TEMP_PATH, false);
        }
        catch (Exception $e) {
        	echo "Un ou plusieurs répertoires n'ont pas pu être supprimés.\n" .
                    "Message d'erreur :" . $e->getMessage()."\n";
        }
    }

    /**
     * Recursive function deleting a directory
     *
     * @param string  $path           The path of the directory to remove recursively
     * @param boolean $deleteParent  If the path must be deleted too
     */
    protected function recurDel($path, $deleteParent=true) {
        $verbose = $this->getOption('-v');
        $dir = new DirectoryIterator($path);
        foreach ($dir as $dirContent) {
        	// file deletion
            if ($dirContent->isFile()) {
        		if ($verbose && unlink($dirContent->getPathName())) {
        			echo $dirContent->getPathName()."\n";
        		}
        	} else {
        		// recursive directory deletion
                if (!$dirContent->isDot() && $dirContent->isDir()) {
                        $this->recurDel($dirContent->getPathName());
        		}
        	}
        }
        // removes the parent directory
        if ($deleteParent) {
            if (rmdir($path) && $verbose) {
            	echo $path."\n";
            }
        }
    }
}


?>