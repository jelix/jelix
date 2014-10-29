<?php
/**
* @author     Laurent Jouanneau
* @copyright  2014 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
namespace Jelix\Core\Infos;

abstract class InfosAbstract {

    protected $path = '';
    protected $xmlFile = false;
    protected $_exists = false;

    /** @var string the package name, foo/bar for composer packages or baz@something for legacy jelix modules */
    public $packageName='';

    /** @var the name of the module, used as identifier in jelix selectors or other part of the code */
    public $name='';

    public $createDate='';

    public $version = '';
    public $versionDate = '';
    public $versionStability = '';

    public $label = '';
    public $description = '';

    public $keywords = array();
    
    /**
     * @var array of array('name'=>'', 'email'=>'', 'role'=>'', 'homepage"=>'', 'nickname'=>'')
     */
    public $authors = array();
    public $notes = '';
    public $homepageURL = '';
    public $updateURL = '';
    public $license = '';
    public $licenseURL = '';
    public $copyright = '';
    /**
     * @var array of array('type'=>'module/plugin','version'=>'','id'=>'','name'=>'')
     */
    public $dependencies = array();

    /**
     * @return string the path of the component, with trailing slash
     */
    public function getPath() {
        return $this->path;
    }
    
    public function isXmlFile() {
        return $this->xmlFile;
    }

    public function exists() {
        return $this->_exists;
    }
}