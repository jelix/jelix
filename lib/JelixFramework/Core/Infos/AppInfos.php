<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2014-2018 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

class AppInfos extends InfosAbstract
{
    public function save()
    {
        if ($this->isXmlFile()) {
            $writer = new ProjectXmlWriter($this->getFilePath());

            return $writer->write($this);
        }

        return false;
    }

    /**
     * create a new AppInfos object, loaded from a file that is into the
     * given directory.
     *
     * @param string $directoryPath the path to the application directory
     * @param mixed  $fileName
     *
     * @return AppInfos
     */
    public static function load($directoryPath = '', $fileName = '')
    {
        if ($directoryPath == '') {
            $directoryPath = \Jelix\Core\App::appPath();
        }

        if ($fileName == '') {
            if (file_exists($directoryPath.'/jelix-app.json')) {
                $parser = new AppJsonParser($directoryPath.'/jelix-app.json');

                return $parser->parse();
            }
            if (file_exists($directoryPath.'/project.xml')) {
                $parser = new ProjectXmlParser($directoryPath.'/project.xml');

                return $parser->parse();
            }
        } elseif (file_exists($directoryPath.'/'.$fileName)) {
            if (substr($fileName, -4) == '.xml') {
                $parser = new ProjectXmlParser($directoryPath.'/project.xml');

                return $parser->parse();
            }

            $parser = new AppJsonParser($directoryPath.'/jelix-app.json');

            return $parser->parse();
        }

        throw new \Exception('No project.xml or jelix-app.json file into '.$directoryPath);
    }
}
