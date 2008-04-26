<?php
/**
* @package    jelix
* @subpackage forms
* @author     Laurent Jouanneau
* @contributor Loic Mathaud, Dominique Papin
* @contributor Uriel Corfa Emotic SARL
* @copyright   2006-2008 Laurent Jouanneau
* @copyright   2007 Loic Mathaud, 2007 Dominique Papin
* @copyright   2007 Emotic SARL
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require(JELIX_LIB_PATH.'forms/jIFormsBuilderCompiler.iface.php');

/**
 * generates form class from an xml file describing the form
 * @package     jelix
 * @subpackage  forms
 */
class jFormsCompiler implements jISimpleCompiler {

    protected $sourceFile;

    public function compile($selector){
        global $gJCoord;
        global $gJConfig;
        $sel = clone $selector;

        $this->sourceFile = $selector->getPath();

        // chargement du fichier XML
        $doc = new DOMDocument();

        if(!$doc->load($this->sourceFile)){
            throw new jException('jelix~formserr.invalid.xml.file',array($this->sourceFile));
        }

        if ($doc->documentElement->namespaceURI == JELIX_NAMESPACE_BASE.'forms/1.0') {
            require_once(JELIX_LIB_PATH.'forms/jFormsCompiler_jf_1_0.class.php');
            $compiler = new jFormsCompiler_jf_1_0($this->sourceFile);
        } elseif ($doc->documentElement->namespaceURI == JELIX_NAMESPACE_BASE.'forms/1.1') {
            require_once(JELIX_LIB_PATH.'forms/jFormsCompiler_jf_1_1.class.php');
            $compiler = new jFormsCompiler_jf_1_1($this->sourceFile);
        } else {
           throw new jException('jelix~formserr.namespace.wrong',array($this->sourceFile));
        }

        $source=array();
        $source[]='<?php ';
        $source[]='class '.$selector->getClass().' extends jFormsBase {';
        $source[]='    protected $_builders = array( ';

        $srcBuilders=array();
        $buildersCompilers = array();
        foreach($gJConfig->_pluginsPathList_jforms as $buildername => $pluginPath) {
            require_once($pluginPath.$buildername.'.jformscompiler.php');
            $classname = $buildername.'JformsCompiler';
            $buildersCompilers[$buildername] = new $classname($compiler);

            $srcBuilders[$buildername]=array();
            $srcBuilders[$buildername][] = '<?php ';
            $srcBuilders[$buildername][] = ' require_once(\''.$pluginPath.$buildername.'.jformsbuilder.php\'); ';
            $srcBuilders[$buildername][] = ' class '.$selector->getClass().'_builder_'.$buildername.' extends '.$buildername.'JformsBuilder'.' {';
            $srcBuilders[$buildername][] = ' public function __construct($form){';
            $srcBuilders[$buildername][] = '          parent::__construct($form); ';
            $srcBuilders[$buildername][] = '  }';
            $srcBuilders[$buildername][] = $buildersCompilers[$buildername]->startCompile();

            $source[]='    \''.$buildername.'\'=>array(\''.$selector->getCompiledBuilderFilePath($buildername).'\',\''.$selector->getClass().'_builder_'.$buildername.'\'), ';
        }

        $source[]='    );';
        $source[]=' public function __construct($sel, &$container, $reset = false){';
        $source[]='          parent::__construct($sel, $container, $reset); ';

        $compiler->compile($doc, $source, $srcBuilders, $buildersCompilers);

        $source[]="  }\n} ?>";
        jFile::write($selector->getCompiledFilePath(), implode("\n", $source));

        foreach($gJConfig->_pluginsPathList_jforms as $buildername => $pluginPath) {
            $srcBuilders[$buildername][]= $buildersCompilers[$buildername]->endCompile();
            $srcBuilders[$buildername][]= '} ?>';
            jFile::write($selector->getCompiledBuilderFilePath($buildername), implode("\n", $srcBuilders[$buildername]));
        }
        return true;
    }
}
