<?php
/**
* @package     jelix
* @subpackage  dao
* @author      Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright   2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* This class was get originally from the Copix project (CopixDAODefinitionV1, Copix 2.3dev20050901, http://www.copix.org)
* Few lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix class are Gerald Croes and Laurent Jouanneau,
* and this class was adapted/improved for Jelix by Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * Container for properties of a dao property
 * @package  jelix
 * @subpackage dao
 */

class jDaoProperty {
    /**
    * the name of the property of the object
    */
    public $name = '';

    /**
    * the name of the field in table
    */
    public $fieldName = '';

    /**
    * give the regular expression that needs to be matched against.
    * @var string
    */
    public $regExp = null;

    /**
    * says if the field is required when doing a check
    * @var boolean
    */
    public $required = false;

    /**
    * says if the value of the field is required when construct SQL conditions
    * @var boolean
    */
    public $requiredInConditions = false;

    /**
    * Says if it's a primary key.
    * @var boolean
    */
    public $isPK = false;

    /**
    * Says if it's a foreign key
    * @var boolean
    */
    public $isFK = false;

    public $datatype;

    public $table=null;
    public $updatePattern='%s';
    public $insertPattern='%s';
    public $selectPattern='%s';
    public $sequenceName='';

    /**
    * the maxlength of the key if given
    * @var int
    */
    public $maxlength = null;
    public $minlength = null;

    public $ofPrimaryTable = true;

    public $defaultValue = null;
    /**
    * constructor.
    */
    function __construct ($aParams, $def){
        $needed = array('name', 'fieldname', 'table', 'datatype', 'required',
                        'minlength', 'maxlength', 'regexp', 'sequence', 'default');

        $params = $def->getAttr($aParams, $needed);

        if ($params['name']===null){
            throw new jDaoXmlException ('missing.attr', array('name', 'property'));
        }
        $this->name       = $params['name'];

        if(!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $this->name)){
            throw new jDaoXmlException ('property.invalid.name', $this->name);
        }


        $this->fieldName  = $params['fieldname'] !==null ? $params['fieldname'] : $this->name;
        $this->table      = $params['table'] !==null ? $params['table'] : $def->getPrimaryTable();

        $tables = $def->getTables();

        if(!isset( $tables[$this->table])){
            throw new jDaoXmlException ('property.unknow.table', $this->name);
        }

        $this->required   = $this->requiredInConditions = $def->getBool ($params['required']);
        $this->maxlength  = $params['maxlength'] !== null ? intval($params['maxlength']) : null;
        $this->minlength  = $params['minlength'] !== null ? intval($params['minlength']) : null;
        $this->regExp     = $params['regexp'];

        if ($params['datatype']===null){
            throw new jDaoXmlException ('missing.attr', array('datatype', 'property'));
        }
        $params['datatype']=trim(strtolower($params['datatype']));

        if (!in_array ($params['datatype'],
                       array ('autoincrement', 'bigautoincrement', 'int',
                              'datetime', 'time', 'integer', 'varchar', 'string',
                              'text', 'varchardate', 'date', 'numeric', 'double',
                              'float', 'boolean'))){
           throw new jDaoXmlException ('wrong.attr', array($params['datatype'],
                                                           $this->fieldName,
                                                           'property'));
        }
        $this->datatype = strtolower($params['datatype']);
        $this->needsQuotes = in_array ($params['datatype'],
                array ('string', 'varchar', 'text', 'date', 'datetime', 'time'));

        $this->isPK = in_array($this->fieldName, $tables[$this->table]['pk']);
        if(!$this->isPK){
            $this->isFK = isset($tables[$this->table]['fk'][$this->fieldName]);
        } else {
            $this->required = true;
            $this->requiredInConditions = true;
        }

        if($this->datatype == 'autoincrement' || $this->datatype == 'bigautoincrement') {
            if($params['sequence'] !==null){
                $this->sequenceName = $params['sequence'];
            }
            $this->required = false;
            $this->requiredInConditions = true;
        }

        if($params['default'] !== null) {
            switch($this->datatype) {
              case 'autoincrement':
              case 'int':
              case 'integer':
                $this->defaultValue = intval($params['default']);
                break;
              case 'double':
              case 'float':
                $this->defaultValue = doubleval($params['default']);
                break;
              case 'boolean':
                $v = $params['default'];
                $this->defaultValue = ($v =='1'|| $v=='t'|| strtolower($v) =='true');
                break;
              default:
                $this->defaultValue = $params['default'];
            }
        }

        // on ignore les attributs *pattern sur les champs PK et FK
        if(!$this->isPK && !$this->isFK){
            if(isset($aParams['updatepattern'])) {
                $this->updatePattern=(string)$aParams['updatepattern'];
            }

            if(isset($aParams['insertpattern'])) {
                $this->insertPattern=(string)$aParams['insertpattern'];
            }

            if(isset($aParams['selectpattern'])) {
                $this->selectPattern=(string)$aParams['selectpattern'];
            }
        }

        // no update and insert patterns for field of external tables
        if($this->table != $def->getPrimaryTable()){
            $this->updatePattern = '';
            $this->insertPattern = '';
            $this->required = false;
            $this->requiredInConditions = false;
            $this->ofPrimaryTable = false;
        }else{
            $this->ofPrimaryTable=true;
        }
    }
}

?>