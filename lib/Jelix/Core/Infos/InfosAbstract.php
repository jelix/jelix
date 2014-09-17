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

    public $id='';
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
}