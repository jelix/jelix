<?php
/**
 * @author    Vincent Viaud
 * @contributor Laurent Jouanneau
 *
 * @copyright 2010 Vincent Viaud, 2014-2024 Laurent Jouanneau
 *
 * @see      http://havefnubb.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

abstract class XmlParserAbstract
{
    /**
     * @var string the path of the xml file to read
     */
    protected $path;

    /**
     * @param string $path the path of the xml file to read
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return InfosAbstract
     */
    abstract protected function createInfos();

    /**
     * @return InfosAbstract
     */
    public function parse()
    {
        $object = $this->createInfos();
        $xml = new \XMLreader();
        $xml->open($this->path, null, LIBXML_COMPACT);

        while ($xml->read()) {
            if ($xml->nodeType == \XMLReader::ELEMENT) {
                $method = 'parse'.ucfirst($xml->name);
                if (method_exists($this, $method)) {
                    $this->{$method}($xml, $object);
                }
            }
        }
        $xml->close();

        return $object;
    }

    public function parseFromString($xmlContent)
    {
        $object = $this->createInfos();
        $xml = new \XMLreader();
        $xml->xml($xmlContent, 'utf-8', LIBXML_COMPACT);

        while ($xml->read()) {
            if ($xml->nodeType == \XMLReader::ELEMENT) {
                $method = 'parse'.ucfirst($xml->name);
                if (method_exists($this, $method)) {
                    $this->{$method}($xml, $object);
                }
            }
        }
        $xml->close();

        return $object;
    }

    protected function parseInfo(\XMLReader $xml, InfosAbstract $object)
    {
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
        $object->name = (string) $xml->getAttribute('name');
        if ($object->name == '') {
            $object->name = basename(dirname($object->getFilePath()));
        }
        $object->id = (string) $xml->getAttribute('id');

        $object->createDate = (string) $xml->getAttribute('createdate');

        while ($xml->read()) {
            if ($xml->nodeType == \XMLReader::END_ELEMENT && $xml->name == 'info') {
                break;
            }

            if ($xml->nodeType == \XMLReader::ELEMENT) {
                $property = $xml->name;
                $attributes = array();
                $textContent = '';
                if ($xml->hasAttributes) {
                    while ($xml->moveToNextAttribute()) {
                        $attributes[$xml->name] = $xml->value;
                    }
                    $xml->moveToElement();
                }
                if (!$xml->isEmptyElement) {
                    $xml->read();
                    $textContent = $xml->value;
                }

                if ($property == 'label' || $property == 'description') {
                    $lang = isset($attributes['lang']) ? substr($attributes['lang'], 0, 2) : 'en';
                    $object->{$property}[$lang] = trim($textContent);
                } elseif ($property == 'author' || $property == 'creator' || $property == 'contributor') {
                    $name = isset($attributes['name']) ? $attributes['name'] : '';
                    $email = isset($attributes['email']) ? $attributes['email'] : '';
                    $role = isset($attributes['role']) ? $attributes['role'] : '';
                    if ($name != '') {
                        if ($role == '' && $property != 'author') {
                            $role = $property;
                        }
                        $object->author[] = new Author($name, $email, $role);
                    }
                } elseif ($property == 'licence') { // we support licence and license, but store always as license
                    foreach ($attributes as $attr => $val) {
                        $attrProperty = 'license'.ucfirst($attr);
                        $object->{$attrProperty} = $val;
                    }
                    $object->license = $textContent;
                } else { // <version> <license> <copyright> <homepageURL> <updateURL>
                    // read attributes 'date', 'stability' etc ... and store them into versionDate, versionStability
                    foreach ($attributes as $attr => $val) {
                        $attrProperty = $property.ucfirst($attr);
                        if ($attrProperty == 'versionDate') {
                            if ($val == '__TODAY__') { // for non-packages modules
                                $val = date('Y-m-d');
                            }
                            $object->versionDate = $val;
                        } else {
                            $object->{$attrProperty} = $val;
                        }
                    }

                    if ($property == 'version') {
                        $object->{$property} = $this->fixVersion($xml->value);
                    } else {
                        $object->{$property} = trim($xml->value);
                    }
                }
            }
        }

        return $object;
    }

    /**
     * Fix version for non built lib.
     *
     * @param mixed $version
     */
    protected function fixVersion($version)
    {
        $v = str_replace('__LIB_VERSION_MAX__', \jFramework::versionMax(), $version);
        $v = str_replace('__LIB_VERSION__', \jFramework::version(), $v);

        return str_replace('__VERSION__', \jApp::version(), $v);
    }
}
