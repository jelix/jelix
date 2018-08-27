<?php
/**
* @author     Laurent Jouanneau
* @copyright  2014-2018 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
namespace Jelix\Core\Infos;

abstract class InfosAbstract {

    /** @var string the path to the module/app information file */
    protected $path = '';
    protected $isXml = false;
    protected $_exists = false;

    /** @var string unique id (e.g. 'name@company') */
    public $id = '';

    /** @var string the name of the module, used as identifier in jelix selectors or other part of the code */
    public $name = '';

    /** @var string the birth date of the module/app. optional */
    public $createDate = '';

    /** @var string version of the module/app. required for modules */
    public $version = '';
    /** @var string the release date of the module/app. required for modules */
    public $versionDate = '';
    
    public $versionStability = '';

    /**
     * @var string[]   key is the locale code
     */
    public $label = array();

    /**
     * @var string[]   key is the locale code
     */
    public $description = array();

    /**
     * @var Author[]
     */
    public $author = array();

    public $notes = '';
    public $homepageURL = '';
    public $updateURL = '';
    public $license = '';
    public $licenseURL = '';
    public $copyright = '';

    function __construct($filePath, $isXml)
    {
        $this->path = $filePath;
        $this->isXml = $isXml;
        $this->_exists = file_exists($filePath);
    }

    /**
     * @return string the path of the component, with trailing slash
     */
    public function getFilePath() {
        return $this->path;
    }

    public function getItemPath() {
        return dirname($this->path).'/';
    }

    /**
     * @return bool
     */
    public function isXmlFile() {
        return $this->isXml;
    }

    /**
     * @return bool
     */
    public function exists() {
        return $this->_exists;
    }

    public function getLabel($locale = '') {
        $locale = $this->getLocale($locale);
        if (isset($this->labels[$locale])) {
            return $this->label[$locale];
        }
        reset($this->label);
        return current($this->label);
    }

    public function getDescription($locale = '') {
        $locale = $this->getLocale($locale);
        if (isset($this->description[$locale])) {
            return $this->description[$locale];
        }
        reset($this->description);
        return current($this->description);
    }

    protected function getLocale($locale) {
        if ($locale) {
            return substr($locale, 0, 2);
        }
        $config = \jApp::config();
        if ($config) {
            return $config->locale;
        }
        return 'en';
    }

    /**
     * save the informations into the original file
     */
    abstract public function save();


    /**
     * @param \DOMDocument $doc
     */
    protected function saveInfo($doc) {
        $info = $doc->createElement('info');
        if ($this->id) {
            $info->setAttribute('id', $this->id);
        }
        if ($this->name) {
            $info->setAttribute('name', $this->name);
        }
        if ($this->createDate) {
            $info->setAttribute('createdate', $this->createDate);
        }
        $doc->documentElement->appendChild($info);

        if ($this->version) {
            $version = $doc->createElement('version');
            $version->textContent = $this->version;
            if ($this->versionDate) {
                $version->setAttribute('date', $this->versionDate);
            }
            if ($this->versionStability) {
                $version->setAttribute('stability', $this->versionStability);
            }
            $info->appendChild($version);
        }

        foreach($this->label as $lang => $label) {
            $lab = $doc->createElement('label');
            $lab->textContent = $label;
            $lab->setAttribute('lang', $lang);
            $info->appendChild($lab);
        }

        foreach($this->description as $lang => $description) {
            $desc = $doc->createElement('description');
            $desc->textContent = $description;
            $desc->setAttribute('lang', $lang);
            $info->appendChild($desc);
        }
        if ($this->license) {
            $licence = $doc->createElement('licence');
            $licence->textContent = $this->license;
            if ($this->licenseURL) {
                $licence->setAttribute('URL', $this->licenseURL);
            }
            $info->appendChild($licence);
        }
        if ($this->copyright) {
            $elem = $doc->createElement('copyright');
            $elem->textContent = $this->copyright;
            $info->appendChild($elem);
        }
        foreach($this->author as $author) {
            $lab = $doc->createElement('author');
            $lab->setAttribute('name', $author->name);
            if ($author->email) {
                $lab->setAttribute('email', $author->email);
            }
            if ($author->role) {
                $lab->setAttribute('role', $author->role);
            }
            $info->appendChild($lab);
        }
        if ($this->homepageURL) {
            $elem = $doc->createElement('homepageURL');
            $elem->textContent = $this->homepageURL;
            $info->appendChild($elem);
        }
        if ($this->updateURL) {
            $elem = $doc->createElement('updateURL');
            $elem->textContent = $this->updateURL;
            $info->appendChild($elem);
        }
    }
}