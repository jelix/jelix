<?php
/**
* @package     jelix-scripts
* @author      Jouanneau Laurent
* @contributor Nicolas Jeudy (patch ticket #99)
* @contributor Gwendal Jouannic (patch ticket #615)
* @copyright   2005-2007 Jouanneau laurent
* @copyright   2007 Nicolas Jeudy, 2008 Gwendal Jouannic
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

class createdaoCommand extends JelixScriptCommand {

    public  $name = 'createdao';
    public  $allowed_options=array('-profil'=>true, '-empty'=>false);
    public  $allowed_parameters=array('module'=>true,'name'=>true, 'table'=>false);

    public  $syntaxhelp = "[-profil name] [-empty] MODULE DAO TABLE";
    public  $help=array(
        'fr'=>"
    Créer un nouveau fichier de dao

    -profil (facultatif) : indique le profil à utiliser pour se connecter à
                           la base et récupérer les informations de la table
    -empty (facultatif) : ne se connecte pas à la base et génère un fichier
                          dao vide

    MODULE: nom du module concerné.
    DAO   : nom du dao à créer.
    TABLE : nom de la table principale sur laquelle s'appuie le dao
            (cette commande ne permet pas de générer un dao  s'appuyant sur
             de multiple table)",
        'en'=>"
    Create a new dao file.

    -profil (optional) : indicate the name of the profil to use for the
                        database connection.
    -empty (optional) : juste create an empty dao file (it doesn't connect to
                        the database

    MODULE : module name where to create the dao
    DAO    : dao name
    TABLE  : name of the main table on which the dao is mapped. You cannot indicates
             multiple table, sorry..",
    );


    public function run(){

       jxs_init_jelix_env();

       $path= $this->getModulePath($this->_parameters['module']);

       $filename= $path.'daos/';
       $this->createDir($filename);

       $filename.=strtolower($this->_parameters['name']).'.dao.xml';

       $profil= $this->getOption('-profil');

       $param = array('name'=>($this->_parameters['name']),
              'table'=>($this->_parameters['table']));

       if($this->getOption('-empty')){
          $this->createFile($filename,'dao_empty.xml.tpl',$param);
       }else{

         $tools = jDb::getTools($profil);
         $fields = $tools->getFieldList($this->_parameters['table']);

         $properties='';
         $primarykeys='';
         foreach($fields as $fieldname=>$prop){

            switch($prop->type){

               case 'clob': 
               case 'text':
               case 'mediumtext':
               case 'longtext':
               case 'tinytext':
                  $type='text';
                  break;
               case 'varchar2':
               case 'varchar':
               case 'char':
               case 'enum':
               case 'bpchar':
               case 'set':
                  $type='string';
                  break;
               case 'number':
               case 'tinyint':
               case 'int':
               case 'integer':
               case 'smallint':
               case 'year':
                  if($prop->autoIncrement ){
                     $type='autoincrement';
                  }else{
                     $type='int';
                  }
                  break;

               case 'mediumint':
               case 'bigint':
                  if($prop->autoIncrement ){
                     $type='bigautoincrement';
                  }else{
                     $type='numeric';
                  }
                  break;
               case 'float':
               case 'double':
               case 'decimal':
                  $type='float';
                  break;

               case 'date':
                  $type='date';
                  break;
               case 'timestamp':
               case 'datetime':
                  $type='datetime';
                  break;
               case 'time':
                  $type='time';
                  break;
               case 'bool':
               case 'boolean':
                  $type='boolean';
                  break;
               default:
                  $type='';
            }

            if($type!=''){
               $properties.="\n    <property name=\"$fieldname\" fieldname=\"$fieldname\"";
               $properties.=' datatype="'.$type.'"';
               if($prop->primary){
                  if($primarykeys != '')
                     $primarykeys.=','.$fieldname;
                  else
                     $primarykeys.=$fieldname;
               }
               if($prop->notNull && !$prop->autoIncrement)
                  $properties.=' required="true"';

               if($prop->hasDefault) {
                   $properties.=' default="'.htmlspecialchars($prop->default).'"';
               }
               if ($prop->length) {
                    $properties.=' maxlength="'.$prop->length.'"';
               }
               if ($prop->sequence) {
                    $properties.=' sequence="'.$prop->sequence.'"';
               }
               $properties.='/>';
            }

         }

         if($primarykeys == '') {
            throw new Exception("The table has no primary keys. A dao needs a primary key on the table to be defined.");
         }

         $param['properties']=$properties;
         $param['primarykeys']=$primarykeys;
         $this->createFile($filename,'dao.xml.tpl',$param);
       }
    }
}

