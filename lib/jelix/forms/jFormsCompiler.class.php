<?php
/**
* @package    jelix
* @subpackage jforms
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 *
 */
require_once(JELIX_LIB_FORMS_PATH.'jFormsControl.class.php');

/**
 * generates form class from an xml file describing the form
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
class jFormsCompiler implements jISimpleCompiler {

   public function compile($selector){
      global $gJCoord;
      $sel = clone $selector;

      $sourceFile = $selector->getPath();
      $cachefile = $selector->getCompiledFilePath();

      // compilation du fichier xml
      $xml = simplexml_load_file ( $sourceFile);
      if(!$xml){
         return false;
      }

      /*if(!isset($xml->model)){
         trigger_error(jLocale::get('jelix~formserr.missing.tag',array('model',$sourceFile)), E_USER_ERROR);
         return false;
      }
      */

      $source=array();
      $source[]='<?php class '.$selector->getClass().' extends jFormsBase {';
      $source[]=' public function __construct(&$container, $reset = false){';
      $source[]='          parent::__construct($container, $reset); ';
      foreach($xml->children() as $controltype=>$control){

         $class = 'jFormsControl'.$controltype;

         if(!class_exists($class,false)){
            throw new jException('jelix~formserr.unknow.tag',array($controltype,$sourceFile));
         }

         if(!isset($control['ref'])){
            throw new jException('jelix~formserr.attribute.missing',array('ref',$controltype,$sourceFile));
         }
         $source[]='$ctrl= new '.$class.'(\''.(string)$control['ref'].'\');';
         if(isset($control['type'])){
            $dt = (string)$control['type'];
            if(!in_array(strtolower($dt), array('string','boolean','decimal','integer','hexadecimal','datetime','date','time','localedatetime','localedate','localetime', 'url','email','ipv4','ipv6'))){
               throw new jException('jelix~formserr.datatype.unknow',array($dt,$controltype,$sourceFile));
            }
            $source[]='$ctrl->datatype= new jDatatype'.$dt.'();';
         }else{
            $source[]='$ctrl->datatype= new jDatatypeString();';
         }

         if(isset($control['readonly'])){
            $readonly=(string)$control['readonly'];

            $source[]='$ctrl->readonly='.($readonly=='true'?'true':'false').';';
         }
         if(isset($control['required'])){
            $required=(string)$control['required'];
            $source[]='$ctrl->required='.($required=='true'?'true':'false').';';
         }

         if(!isset($control->label)){
            throw new jException('jelix~formserr.tag.missing',array('label',$controltype,$sourceFile));
         }

         if(isset($control->label['locale'])){
             $source[]='$ctrl->labellocale=\''.(string)$control->label['locale'].'\';';
         }else{
             $source[]='$ctrl->label=\''.str_replace("'","\\'",(string)$control->label).'\';';
         }
         switch($controltype){
            case 'input':
               break;
            case 'textarea':
               break;
            case 'secret':
               break;
            case 'output':
                //attr value
               break;
            case 'upload': 
                // attr mediatype
               break;
            case 'select1':
            case 'select': 
                // recuperer les <items> attr label|labellocale value
                if(isset($control['dao'])){
                    $daoselector = (string)$control['dao'];
                    $daomethod = (string)$control['daomethod'];
                    $daolabel = (string)$control['daolabelproperty'];
                    $daovalue = (string)$control['daovalueproperty'];
                    $source[]='$ctrl->datasource= new jFormDaoDatasource(\''.$daoselector.'\',\''.
                        $daomethod.'\',\''.$daolabel.'\',\''.$daovalue.'\',);';

                }else{
                    $source[]='$ctrl->datasource= new jFormStaticDatasource();';
                    $source[]='$ctrl->datasource->array(';

                    foreach($control->item as $item){
                        $value ="'".str_replace("'","\\'",(string)$item['value'])."'=>";
                        if(isset($item['label'])){
                            $source[] = $value."'".str_replace("'","\\'",(string)$item['label'])."',";
                        }elseif(isset($item['labellocale'])){
                            $source[] = $value."jLocale::get('".(string)$item['labellocale']."'),";
                        }else{
                            $source[] = $value."'".str_replace("'","\\'",(string)$item['value'])."',";
                        }
                    }
                    $source[]=");";
                }
               break;
            case 'submit':
                // attr value
               break;
         }
         $source[]='$this->addControl($ctrl);';
      }

      $source[]='  }';

      //$source[]=' public function save(){ } ';

      $source[]='} ?>';

      jFile::write($cachefile, implode("\n", $source));
      return true;
   }

}


/**
 *
 * @package     jelix
 * @subpackage  forms
 * @experimental
 */
interface jIFormGenerator {

   // on indique un objet form
   // il renvoi dans un tableau le code généré correspondant
   /*
   startform : code généré pour le debut du formulaire (balise <form> en html) Peut contenir %ATTR%
   head : code généré à ajouter dans l'en-tête de page
   controls : tableau assoc de chaque contrôle généré. Peuvent contenir %ATTR%
   endform : code généré pour la fin du formulaire


   %ATTR% : remplacés par les attributs supplémentaires indiqués par l'utilisateur dans le template
   */
   function buildForm($formObject);

}


?>