<?php
/**
 * @package   jelix
 * @subpackage core
 * @author    Vincent Viaud
 * @contributor 2013 foxmask
 * @copyright 2010 Vincent Viaud 2012 FoxMaSk
 * @link      
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

require_once (dirname(__FILE__). '/jFilexml.class.php');

class projectInfo {
    public $id='';
    public $name='';
    public $createDate='';

    public $version = '';
    public $versionDate = '';
    public $versionStability = '';

    public $label = '';
    public $description = '';

    public $creators = array();
    public $contributors = array();
    public $notes = '';
    public $homepageURL = '';
    public $updateURL = '';
    public $license = '';
    public $licenseURL = '';
    public $copyright = '';

    public $dependencies = array();
    public $directories = array();
    public $entrypoints = array();
}

/**
 * Class to parse the module.xml file of each module
 */

class jProjectxml extends jFilexml {

    public function getInfo() {
        $file = jApp::appPath('project.xml');
        if (!file_exists($file))
            return null;

        $project = new projectinfo();
        $project = self::parse($file, $project);
        jLog::dump($project);
        return $project;
    }

    protected function parseDirectories (XMLReader $xml, $object) {
        if (XMLReader::ELEMENT == $xml->nodeType && 'directories' == $xml->name) {

            $property = $xml->name;

            while ($xml->read()) {

                if ($xml->nodeType == XMLReader::END_ELEMENT && 'directories' == $xml->name) {
                    break;
                }

                if ($xml->nodeType == XMLReader::ELEMENT) {

                    $directry = array();
                    $directory['type'] = $xml->name;
                    $xml->read();
                    $directory['path'] = $xml->value;


                    array_push($object->$property, $directory);
                }
            }
        }

        return $object;
    }

    protected function parseEntrypoints (XMLReader $xml, $object) {
        if (XMLReader::ELEMENT == $xml->nodeType && 'entrypoints' == $xml->name) {

            $property = $xml->name;

            while ($xml->read()) {

                if ($xml->nodeType == XMLReader::END_ELEMENT && 'entrypoints' == $xml->name) {
                    break;
                }

                if ($xml->nodeType == XMLReader::ELEMENT) {

                    $entrypoint = array();

                    while ($xml->moveToNextAttribute()) {
                        $attrName = $xml->name;
                        $entrypoint[$attrName] = $xml->value;
                    }

                    array_push($object->$property, $entrypoint);
                }
            }
        }

        return $object;
    }
}

