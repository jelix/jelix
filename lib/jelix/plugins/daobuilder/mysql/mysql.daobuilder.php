<?php
/**
 * @package    jelix
 * @subpackage dao_builderdriver
 *
 * @author     Laurent Jouanneau
 * @copyright  2007-2009 Laurent Jouanneau
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * driver for jDaoCompiler.
 */
class mysqlDaoBuilder extends jDaoGenerator
{
    protected $propertiesListForInsert = 'PrimaryFieldsExcludeAutoIncrement';
}
