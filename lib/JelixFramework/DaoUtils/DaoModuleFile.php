<?php

/**
 * @author      Laurent Jouanneau
 * @copyright   2026 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\DaoUtils;


use Jelix\Dao\DaoFileInterface2;

class DaoModuleFile implements DaoFileInterface2
{
    protected $module;

    protected $daoName;

    protected $daoFile;

    protected $buildPath;

    protected $sqlType;

    protected $escapedModule;

    protected $escapedName;

    /**
     * DaoSimpleFile constructor.
     * @param string $daoName the name as given to JelixDao API.
     * @param string $daoXmlFile the path to the dao file
     * @param string $sqlType type of the sql language (pgsql, sqlite, mysql...)
     * @param string $buildPath directory where to store the compiled file
     */
    public function __construct($module, $daoName, $daoXmlFile, $sqlType, $buildPath)
    {
        $this->module = $module;
        $this->escapedModule = ucfirst($module);
        $this->escapedName = ucfirst($daoName);
        $this->daoName = $daoName;
        $this->daoFile = $daoXmlFile;
        $this->buildPath = $buildPath;
        $this->sqlType = ucfirst($sqlType);
    }

    /**
     * A name allowing to easily identify the dao.
     *
     * @return string a filename, a URI or another identifier
     */
    public function getName()
    {
        return $this->module.'~'.$this->daoName;
    }

    /**
     * @return string path to the Dao file
     */
    public function getPath()
    {
        return $this->daoFile;
    }

    /**
     * @return string path of a file where to store generated classes
     */
    public function getCompiledFilePath()
    {
        return $this->getCompiledFactoryFilePath();
    }

    public function getCompiledRecordFilePath()
    {
        return $this->buildPath.'/Daos/'.$this->escapedModule.'/'.$this->escapedName.$this->sqlType.'Record.php';
    }

    public function getCompiledFactoryFilePath()
    {
        return $this->buildPath.'/Daos/'.$this->escapedModule.'/'.$this->escapedName.$this->sqlType.'Factory.php';
    }

    /**
     * @return string name of the factory class that should be used by the generator
     */
    public function getCompiledFactoryClass()
    {
        return 'Jelix\\BuiltComponents\\Daos\\'.$this->escapedModule.'\\'.$this->escapedName.$this->sqlType.'Factory';
    }

    /**
     * @return string name of the record class that should be used by the generator
     */
    public function getCompiledRecordClass()
    {
        return 'Jelix\\BuiltComponents\\Daos\\'.$this->escapedModule.'\\'.$this->escapedName.$this->sqlType.'Record';
    }
}