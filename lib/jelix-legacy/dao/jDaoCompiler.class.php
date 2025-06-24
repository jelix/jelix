<?php
/**
 * @package    jelix
 * @subpackage dao
 *
 * @author      Laurent Jouanneau
 * @copyright   2005-2025 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * The compiler for the DAO xml files. it is used by jIncluder
 * It produces some php classes.
 *
 * @package  jelix
 * @subpackage dao
 */
class jDaoCompiler implements \Jelix\Core\Includer\SimpleCompilerInterface
{
    /**
     * compile the given class id.
     *
     * @param jSelectorDao $selector
     */
    public function compile($selector)
    {
        $cnt = jDb::getConnection($selector->profile);
        $context = new jDaoContext($selector->profile, $cnt);
        $compiler = new \Jelix\Dao\Generator\Compiler();
        return $compiler->compile($selector, $context);
    }
}
