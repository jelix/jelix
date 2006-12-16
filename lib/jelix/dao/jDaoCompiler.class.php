<?php
/**
* @package    jelix
* @subpackage dao
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
#ifdef ENABLE_OPTIMIZE
* Une partie du code est issue des classes CopixDaoCompiler, CopixDAOGeneratorV1, CopixDAODefinitionV1
* du framework Copix 2.3dev20050901. http://www.copix.org
* il est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

#includephp jDaoParser.class.php
#includephp jDaoGenerator.class.php

#else
* Une partie du code est issue de la classe CopixDaoCompiler
* du framework Copix 2.3dev20050901. http://www.copix.org
* il est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

/**
 *
 */
require_once (JELIX_LIB_DAO_PATH.'jDaoParser.class.php');
require_once (JELIX_LIB_DAO_PATH.'jDaoGenerator.class.php');
#endif

/**
 * The compiler for the DAO xml files. it is used by jIncluder
 * It produces some php classes
 * @package  jelix
 * @subpackage dao
 */
class jDaoCompiler  implements jISimpleCompiler {
    /**
    * the current DAO id.
    * @var string
    */
    static public $daoId = '';

    /**
     * the current DAO file path
     * @var string
     */
    static public $daoPath = '';

    /**
     * The db driver name
     * @var string
     */
    static public $dbDriver='';

    /**
    * compile the given class id.
    */
    public function compile ($selector) {

        jDaoCompiler::$daoId = $selector->toString();
        jDaoCompiler::$daoPath = $selector->getPath();
        jDaoCompiler::$dbDriver = $selector->driver;

        // chargement du fichier XML
        $doc = new DOMDocument();

        if(!$doc->load(jDaoCompiler::$daoPath)){
           throw new jException('jelix~daoxml.file.unknow', jDaoCompiler::$daoPath);
        }

        if($doc->documentElement->namespaceURI != JELIX_NAMESPACE_BASE.'dao/1.0'){
           throw new jException('jelix~daoxml.namespace.wrong',array(jDaoCompiler::$daoPath, $doc->namespaceURI));
        }

        $parser = new jDaoParser ();
        $parser->parse(simplexml_import_dom($doc));

        $generator = new jDaoGenerator($selector->getDaoClass(), $selector->getDaoRecordClass(), $parser);

        // génération des classes PHP correspondant à la définition de la DAO
        $compiled = '<?php '.$generator->buildClasses ()."\n?>";
        jFile::write ($selector->getCompiledFilePath(), $compiled);
        return true;
    }

}

/**
 * Exception for Dao compiler
 * @package  jelix
 * @subpackage dao
 */
class jDaoXmlException extends jException {

    /**
     * @param string $localekey a locale key
     * @param array $localeParams parameters for the message (for sprintf)
     */
    public function __construct($localekey, $localeParams=array()) {
        $localekey= 'jelix~daoxml.'.$localekey;

        $arg=array(jDaoCompiler::$daoId, jDaoCompiler::$daoPath);
        if(is_array($localeParams)){
            $arg=array_merge($arg, $localeParams);
        }else{
            $arg[]=$localeParams;
        }
        parent::__construct($localekey, $arg);
    }
}


?>