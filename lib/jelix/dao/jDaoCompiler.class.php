<?php
/**
* @package    jelix
* @subpackage dao
* @version    $Id:$
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Une partie du code est issue de la classe CopixDaoCompiler
* du framework Copix 2.3dev20050901. http://www.copix.org
* il est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

require_once (JELIX_LIB_DAO_PATH.'jDaoParser.class.php');
require_once (JELIX_LIB_DAO_PATH.'jDaoGenerator.class.php');

/**
* The compiler for the DAO classes.
* it is used by jIncluder
*/
class jDaoCompiler  implements jISimpleCompiler {
    /**
    * the current DAO id.
    */
    private $_DaoId ='';

    /**
    * the base name of dao object.
    */
    private $_baseName= '';

    /**
    *
    */
    private $_selector;

    /**
    * compile the given class id.
    */
    public function compile ($selector) {

        // recuperation du chemin et nom de fichier de definition xml de la dao
        $this->_selector = $selector;
        $this->_DaoId = $selector->toString();
        $this->_baseName = $selector->resource;

        // chargement du fichier XML
        $doc = new DOMDocument();

        if(!$doc->load($selector->getPath())){
           throw new jException('jelix~daoxml.file.unknow', $selector->getPath());
        }

        if($doc->documentElement->namespaceURI != JELIX_NAMESPACE_BASE.'dao/1.0'){
           throw new jException('jelix~daoxml.namespace.wrong',array($selector->getPath(), $doc->namespaceURI));
        }

        $parser = new jDaoParser ($this);
        $parser->parse(simplexml_import_dom($doc));

        $generator = new jDaoGenerator($this, $parser);

        // génération des classes PHP correspondant à la définition de la DAO
        $compiled = '<?php '.$generator->buildClasses ()."\n?>";
        jFile::write ($selector->getCompiledFilePath(), $compiled);
        return true;
    }

    public function getDaoId(){ return  $this->_DaoId;}
    public function getSelector(){ return  $this->_selector;}
    public function getDbDriver(){ return  $this->_selector->driver;}

    public function doDefError($message, $arg1=null){
        $arg=array($this->_selector->toString(true),$this->_selector->getPath());
        if(is_array($arg1)){
            $arg=array_merge($arg, $arg1);
        }else{
            $arg[]=$arg1;
        }
        throw new jException('jelix~daoxml.'.$message,$arg);
    }
}
?>
