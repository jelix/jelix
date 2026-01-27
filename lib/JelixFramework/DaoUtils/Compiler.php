<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2026 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\DaoUtils;

use Jelix\Dao\ContextInterface;
use Jelix\Dao\DaoFileInterface;
use Jelix\Dao\Generator\AbstractDaoGenerator;
use Jelix\Dao\Generator\Exception;
use Jelix\FileUtilities\File;

class Compiler extends \Jelix\Dao\Generator\Compiler
{
    public function compile(DaoFileInterface $daoFile, ContextInterface $context)
    {
        $parser = $this->parse($daoFile, $context);

        $dbType = ucfirst($context->getSQLType());
        $class = '\\Jelix\\Dao\\Generator\\Adapter\\'.$dbType.'DaoGenerator';
        if (!class_exists($class)) {
            throw new Exception('Dao adapter for "'.$dbType.'" is not found', 505);
        }
        /** @var AbstractDaoGenerator $generator */
        $generator = new $class($context->getSqlSyntaxHelpers(), $parser);

        // generation of PHP classes corresponding to the DAO definition

        list($factoryNamespace, $factorySources, $recordNamespace, $recordSources) = $generator->buildClasses();

        $factoryHeader = '';
        if ($factoryNamespace) {
            $factoryHeader = "\nnamespace $factoryNamespace;\n";
        }
        File::write($daoFile->getCompiledFactoryFilePath(), '<?php '.$factoryHeader.$factorySources);
        $recordHeader = '';
        if ($recordNamespace) {
            $recordHeader = "\nnamespace $recordNamespace;\n";
        }
        File::write($daoFile->getCompiledRecordFilePath(), '<?php '.$recordHeader.$recordSources);

        return true;
    }

}