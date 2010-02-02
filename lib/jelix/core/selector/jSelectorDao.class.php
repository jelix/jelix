<?php
/**
* see jISelector.iface.php for documentation about selectors. 
* @package     jelix
* @subpackage  core_selector
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @copyright   2005-2007 Laurent Jouanneau, 2007 Loic Mathaud
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * Selector for dao file
 * syntax : "module~daoName".
 * file : daos/daoName.dao.xml
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorDao extends jSelectorModule {
    protected $type = 'dao';
    public $driver;
    protected $_dirname = 'daos/';
    protected $_suffix = '.dao.xml';
    protected $_where;

    function __construct($sel, $driver, $isprofile=true){
        if ($isprofile) {
            $p = jDb::getProfile($driver);
            if ($p['driver'] == 'pdo') {
                $this->driver = substr($p['dsn'], 0, strpos($p['dsn'],':'));
            }
            else {
                $this->driver = $p['driver'];
            }
        }
        else {
            $this->driver = $driver;
        }
        $this->_compiler='jDaoCompiler';
        $this->_compilerPath=JELIX_LIB_PATH.'dao/jDaoCompiler.class.php';
        parent::__construct($sel);
    }

    protected function _createPath(){
        global $gJConfig;
        if(!isset($gJConfig->_modulesPathList[$this->module])){
            throw new jExceptionSelector('jelix~errors.selector.module.unknown', $this->toString());
        }

        // on regarde si le dao a été redéfini
        $overloadedPath = JELIX_APP_VAR_PATH.'overloads/'.$this->module.'/'.$this->_dirname.$this->resource.$this->_suffix;
        if (is_readable ($overloadedPath)){
           $this->_path = $overloadedPath;
           $this->_where = 'overloaded/';
           return;
        }
        // et sinon, on regarde si le dao existe dans le module en question
        $this->_path = $gJConfig->_modulesPathList[$this->module].$this->_dirname.$this->resource.$this->_suffix;

        if (!is_readable ($this->_path)){
            throw new jExceptionSelector('jelix~errors.selector.invalid.target', array($this->toString(), "dao"));
        }
        $this->_where = 'modules/';
    }

    protected function _createCachePath(){
        // on ne partage pas le même cache pour tous les emplacements possibles
        // au cas où un overload était supprimé
        $this->_cachePath = JELIX_APP_TEMP_PATH.'compiled/daos/'.$this->_where.$this->module.'~'.$this->resource.'~'.$this->driver.$this->_cacheSuffix;
    }

    public function getDaoClass(){
        return 'cDao_'.$this->module.'_Jx_'.$this->resource.'_Jx_'.$this->driver;
    }
    public function getDaoRecordClass(){
        return 'cDaoRecord_'.$this->module.'_Jx_'.$this->resource.'_Jx_'.$this->driver;
    }
}
