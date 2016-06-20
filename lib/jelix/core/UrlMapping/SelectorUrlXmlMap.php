<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2005-2016 Laurent Jouanneau
 *
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Routing\UrlMapping;

/**
 * a specific selector for the xml files which contains the configuration of the UrlMapper.
 */
class SelectorUrlXmlMap extends \jSelectorAppCfg
{
    public $type = 'urlxmlmap';

    public function getCompiler()
    {
        return new XmlMapParser();
    }

    public function getCompiledFilePath()
    {
        return \jApp::tempPath('compiled/urlsig/'.$this->file.'.creationinfos_15.php');
    }
}
