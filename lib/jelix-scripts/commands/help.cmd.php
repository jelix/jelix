<?php
/**
* @package     jelix-scripts
* @author      Jouanneau Laurent
* @contributor
* @copyright   2005-2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


class helpCommand extends JelixScriptCommand {

    public  $name = 'help';
    public  $allowed_options=array();
    public  $allowed_parameters=array('command'=>false);

    public  $syntaxhelp ="[COMMAND]";
    public  $help=array(
                'fr'=>"      COMMANDE : nom de la commande dont vous voulez l'aide (paramètre facultatif)",
                'en'=>"      COMMAND : command name for which you want help (optional parameter)");

    public  $mainhelp = array(
            'fr'=>"
Utilisation générale :
    %SCRIPT% [--NOMAPP] COMMANDE [OPTIONS] [PARAMETRES]

    NOMAPP   : nom de l'application concernée. Si non présent, le nom de
               l'application doit être dans une variable d'environnement
               JELIX_APP_NAME
    COMMANDE : nom de la commande à executer
    OPTIONS  : une ou plusieurs options. Le nom d'une option commence par un
               tiret et peut être suivi par une valeur.
               exemple d'options pour certaines commandes :
                 -cmdline
                 -profile myprofil
    PARAMETRES : une ou plusieurs valeurs qui se suivent

    Les options et paramètres à indiquer dépendent de la commande. Les options
    sont toujours facultatives, ainsi que certains paramètres.
    Consulter l'aide d'une commande en faisant :
       %SCRIPT% help COMMANDE

Liste des commandes disponibles :\n\t",
            'en'=>"
General use :
    %SCRIPT% [--APPNAME] COMMAND [OPTIONS] [PARAMETERS]

    APPNAME: name of the application on which you want to work. You can omit 
            this parameter if the application name is stored in the 
            JELIX_APP_NAME environment variable.
    COMMAND: name of the command to execute
    OPTIONS: one or more options. An option name begin with a '-' and can be 
            followed by a value. Example with some specific commands:
              -cmdline
              -profil myprofil
    PARAMETERS: one or more values

    Options and parameters depends of the command. Options are always 
    optional. Parameters could be optional or required, depending of the 
    command. To know options and parameters, type:
       %SCRIPT% help COMMAND

List of available commands:\n\t",
            );

    public function run(){
       if(isset($this->_parameters['command'])){
          if($this->_parameters['command'] == 'help'){
             $command=$this;
          }else{
             $command = jxs_load_command($this->_parameters['command']);
          }
          if(MESSAGE_LANG == 'fr'){
              $this->disp("\nUtilisation de la commande ".$this->_parameters['command']." :\n");
              $this->disp("# ".$_SERVER['argv'][0]." [--NOMAPP] ".$this->_parameters['command']." ". $command->syntaxhelp."\n\n");
          }else{
              $this->disp("\nUsage of ".$this->_parameters['command'].":\n");
              $this->disp("# ".$_SERVER['argv'][0]." [--APPNAME] ".$this->_parameters['command']." ". $command->syntaxhelp."\n\n");
          }
          if(is_array($command->help)){
             if(isset($command->help[MESSAGE_LANG])){
                $this->disp($command->help[MESSAGE_LANG]."\n\n");
             }elseif(isset($command->help['en'])){
                $this->disp($command->help['en']."\n\n");
             }else{
                $this->disp(array_shift($command->help)."\n\n");
             }
          }else{
              $this->disp($command->help."\n\n");
          }
       }else{
          if(isset($this->mainhelp[MESSAGE_LANG])){
              $help = $this->mainhelp[MESSAGE_LANG];
          }else{
              $help = $this->mainhelp['en'];
          }
          $help = str_replace('%SCRIPT%', $_SERVER['argv'][0], $help);
          $this->disp($help);

          $list = jxs_commandlist();
          foreach($list as $cmd)
             $this->disp($cmd.' ');
          $this->disp("\n\n");
       }
    }

    protected function disp($str){
       if( ! DISPLAY_HELP_UTF_8){
         echo utf8_decode($str);
       }else{
         echo $str;
       }
    }
}


?>