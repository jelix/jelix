<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2018 Laurent Jouanneau
 * @link       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Core\Infos;

class Application {

    /** @var string the path to the application */
    protected $path = '';


    /**
     * @var AppInfos
     */
    protected $appInfos;

    /**
     * @param string $path path to the app directory. If not given
     *              call \Jelix\Core\App to retrieve it.
     */
    function __construct($path) {

        $this->path = rtrim($path, '/').'/';
    }

    /**
     * @return AppInfos
     */
    public function getInfos() {
        if (!$this->appInfos) {
            $parser = new ProjectXmlParser($this->path.'project.xml');
            $this->appInfos = $parser->parse();
        }
        return $this->appInfos;
    }
}