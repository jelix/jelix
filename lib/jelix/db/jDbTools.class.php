<?php
/**
* @package    jelix
* @subpackage db
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau, Gwendal Jouannic
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @copyright  2008 Gwendal Jouannic
*
* This class was get originally from the Copix project (CopixDbTools, CopixDbConnection, Copix 2.3dev20050901, http://www.copix.org)
* Some lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix classes are Gerald Croes and Laurent Jouanneau,
* and this class was adapted/improved for Jelix by Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 *
 * @package  jelix
 * @subpackage db
 */
 class jDbFieldProperties {
    /**
     * type of the field
     * @var string
     */
    public $type;

    /**
     * field name
     * @var string
     */
    public $name;

    /**
     * says if the field can be null or not
     * @var boolean
     */
    public $notNull=true;

    /**
     * says if the field is the primary key
     * @var boolean
     */
    public $primary=false;

    /**
     * says if the field is auto incremented
     * @var boolean
     */
    public $autoIncrement=false;

    /**
     * default value
     * @var string
     */
    public $default='';

    /**
     * says if there is a default value
     * @var boolean
     */
    public $hasDefault = false;

    public $length = 0;
    
     /**
     * if there is a sequence
     * @var string
     */
    public $sequence = false;
}


/**
 * classe d'outils pour gérer une base de données
 * @package  jelix
 * @subpackage db
 */
abstract class jDbTools {

    /**
    * the database connector
    * @var jDbConnection
    */
    protected $_connector;

    /**
    *
    */
    function __construct( $connector){
        $this->_connector = $connector;
    }

    /**
    * returns the table list
    */
    public function getTableList (){
        return $this->_getTableList ();
    }

    /**
    * return the field list of a given table
    */
    public function getFieldList ($tableName){
        return $this->_getFieldList ($tableName);
    }

    abstract protected function _getTableList ();
    abstract protected function _getFieldList ($tableName);

    protected $dbmsStyle = array();

    /**
     * regular expression to detect comments and end of query
     */
    protected $dbmsDefaultStyle = array('/^\s*#/', '/;\s*$/');

    public function execSQLScript ($file) {

        $lines = file($file);
        $cmdSQL = '';
        $nbCmd = 0;

        if(isset($this->dbmsStyle[$this->_connector->dbms])){
            $style=$this->dbmsStyle[$this->_connector->dbms];
        }else{
            $style=$this->dbmsDefaultStyle;
        }

        foreach ((array)$lines as $key=>$line) {
            if ((!preg_match($style[0],$line))&&(strlen(trim($line))>0)) { // la ligne n'est ni vide ni commentaire
               //$line = str_replace("\\'","''",$line);
               //$line = str_replace($this->scriptReplaceFrom, $this->scriptReplaceBy,$line);

                $cmdSQL.=$line;

                if (preg_match($style[1],$line)) {
                    //Si on est à la ligne de fin de la commande on l'execute
                    // On nettoie la commande du ";" de fin et on l'execute
                    $cmdSQL = preg_replace($style[1],'',$cmdSQL);
                    $this->_connector->query ($cmdSQL);
                    $nbCmd++;
                    $cmdSQL = '';
                }
            }
        }
        return $nbCmd;
    }
}
?>