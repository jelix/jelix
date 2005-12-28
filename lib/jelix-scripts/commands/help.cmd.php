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


class helpCommand extends JelixScriptCommand {

    public  $name = 'help';
    public  $allowed_options=array();
    public  $allowed_parameters=array('command'=>false);

    public  $syntaxhelp ="[COMMANDE]";
    public  $help="      COMMANDE est le nom de la commande dont vous voulez l'aide (paramètre facultatif)";


    public function run(){
       if(isset($this->_parameters['command'])){
          if($this->_parameters['command'] == 'help'){
             $command=$this;
          }else{
             $command = jxs_load_command($this->_parameters['command']);
          }
          echo "\nUtilisation de la commande ".$this->_parameters['command']." :\n";
          echo "# php jelix.php ".$this->_parameters['command']." ". $command->syntaxhelp,"\n\n";
          echo $command->help,"\n\n";
       }else{
          echo "\nUtilisation générale : php jelix.php COMMANDE [OPTIONS] [PARAMETRES]

    COMMANDE : nom de la commande à executer
    OPTIONS : une ou plusieurs options. Le nom d'une option commence par un tiret
              et peut être suivi par une valeur.
              exemple :
                 -override
                 -project-path /foo/bar
    PARAMETRES : une ou plusieurs valeurs qui se suivent

    Les options et paramètres à indiquer dépendent de la commande. Les options
    sont toujours facultatives, ainsi que certains paramètres.
    Consulter l'aide d'une commande en faisant :
       php jelix help COMMANDE

Liste des commandes disponibles :\n\t";

          $list = jxs_commandlist();
          foreach($list as $cmd)
             echo $cmd,' ';
          echo "\n\n";

       }
    }
}


?>