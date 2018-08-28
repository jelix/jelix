<?php
/**
 * @author    Vincent Viaud
 * @contributor Laurent Jouanneau
 * @copyright 2010 Vincent Viaud, 2014-2018 Laurent Jouanneau
 * @link      http://havefnubb.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Core\Infos;

abstract class XmlParserAbstract {

    /**
     * @var string the path of the xml file to read
     */
    protected $path;

    /**
     * @param string $path the path of the xml file to read
     */
    public function __construct($path) {
        $this->path = $path;
    }

    /**
     * @return InfosAbstract
     */
    abstract protected function createInfos();

    /**
     *
     */
    public function parse(){
        $object = $this->createInfos();
        $xml = new \XMLreader();
        $xml->open($this->path, '', LIBXML_COMPACT );

        while ($xml->read()) {
            if($xml->nodeType == \XMLReader::ELEMENT) {
                $method = 'parse' . ucfirst($xml->name);
                if (method_exists ($this, $method)) {
                    $this->$method($xml, $object);
                }
            }
        }
        $xml->close();
        return $object;
    }

    protected function parseInfo (\XMLReader $xml, InfosAbstract $object) {
        /*
        <info id="jelix@modules.jelix.org" name="jelix" createdate="">
            <version stability="stable" date="">1.0</version>
            <label lang="en_US" locale="">Jelix Main Module</label>
            <description lang="en_US" locale="" type="text/xhtml">Main module of jelix which contains some ressources needed by jelix classes</description>
            <license URL="http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html">LGPL 2.1</license>
            <copyright>2005-2008 Laurent Jouanneau and other contributors</copyright>
            <creator name="Laurent Jouanneau" nickname="" email=""/>
            <contributor name="hisname" email="hisemail@yoursite.undefined" since="" role=""/>
            <homepageURL>http://jelix.org</homepageURL>
            <updateURL>http://jelix.org</updateURL>
        </info>
       */

        // we don't read the name attribute for the module name as in previous
        // jelix version, it has always to be the directory name
        $object->name = (string)$xml->getAttribute('name');
        if ($object->name == '') {
            $object->name = basename(dirname($object->getFilePath()));
        }
        $object->id = (string)$xml->getAttribute('id');

        $object->createDate = (string)$xml->getAttribute('createdate');

        while ($xml->read()) {

            if (\XMLReader::END_ELEMENT == $xml->nodeType && 'info' == $xml->name) {
                break;
            }

            if($xml->nodeType == \XMLReader::ELEMENT) {

                $property = $xml->name;

                if ('label' == $property || 'description' == $property) {
                    $lang = 'en';
                    while ($xml->moveToNextAttribute()) {
                        if ($xml->name == 'lang') {
                            $lang = substr($xml->value, 0, 2);
                        }
                    }
                    $xml->read();
                    $object->{$property}[$lang] = trim($xml->value);
                }
                elseif ('author' == $property || 'creator' == $property || 'contributor' == $property) {
                    $name = $email = $role = '';
                    while ($xml->moveToNextAttribute()) {
                        $attrName = $xml->name;
                        switch($attrName) {
                            case 'name':
                                $name = $xml->value;
                                break;
                            case 'email':
                                $email = $xml->value;
                                break;
                            case 'role':
                                $role = $xml->value;
                                break;
                        }
                    }
                    if ($name != '') {
                        if ($role == '' && $property != 'author') {
                            $role = $property;
                        }
                        $object->author[] = new Author($name, $email, $role);
                    }
                }
                elseif ('licence' == $property) { // we support licence and license, but store always as license
                    while ($xml->moveToNextAttribute()) {
                        $attrProperty = 'license' . ucfirst($xml->name);
                        $object->$attrProperty = $xml->value;
                    }
                    $xml->read();
                    $object->license = trim($xml->value);
                }
                else { // <version> <license> <copyright> <homepageURL> <updateURL>
                    // read attributes 'date', 'stability' etc ... and store them into versionDate, versionStability
                    while ($xml->moveToNextAttribute()) {
                        $attrProperty = $property . ucfirst($xml->name);
                        if ($attrProperty == 'versionDate') {
                            $d = $xml->value;
                            if ($d == '__TODAY__') { // for non-packages modules
                                $d = date('Y-m-d');
                            }
                            $object->versionDate = $d;
                        }
                        else {
                            $object->$attrProperty = $xml->value;
                        }
                    }
                    $xml->read();
                    if ($property == 'version') {
                        $object->$property = $this->fixVersion($xml->value);
                    }
                    else {
                        $object->$property = trim($xml->value);
                    }
                }
            }
        }
        return $object;
    }


    /**
     * Fix version for non built lib
     */
    protected function fixVersion($version) {
        $v = str_replace('__LIB_VERSION_MAX__', \jFramework::versionMax(), $version);
        $v = str_replace('__LIB_VERSION__', \jFramework::version(), $v);
        $v = str_replace('__VERSION__', \jApp::version(), $v);
        return $v;
    }
}

