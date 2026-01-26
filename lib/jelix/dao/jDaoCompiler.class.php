<?php
/**
 * @package    jelix
 * @subpackage dao
 *
 * @author      Laurent Jouanneau
 * @copyright   2005-2026 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use Jelix\DaoUtils\DaoContext;
use Jelix\DaoUtils\DaoSelector;

/**
 * The compiler for the DAO xml files. it is used by jIncluder
 * It produces some php classes.
 *
 * @package  jelix
 * @subpackage dao
 */
class jDaoCompiler implements jISimpleCompiler
{
    /**
     * compile the given class id.
     *
     * @param DaoSelector $selector
     */
    public function compile($selector)
    {
        $profile = \jProfiles::get('jdb', $selector->profile);
        $context = new DaoContext($selector->profile, $profile['dbtype']);
        $compiler = new \Jelix\Dao\Generator\Compiler();
        return $compiler->compile($selector, $context);
    }
}
