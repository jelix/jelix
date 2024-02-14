<?php
/**
 * @package    jelix
 * @subpackage dao_builderdriver
 *
 * @author     Yann Lecommandoux
 * @copyright  2008 Yann Lecommandoux
 *
 * @see     http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * driver for jDaoCompiler.
 *
 * @deprecated
 */
class mssqlDaoBuilder extends jDaoGenerator
{
    protected $propertiesListForInsert = 'PrimaryFieldsExcludeAutoIncrement';

    protected function genUpdateAutoIncrementPK($pkai, $pTableRealName)
    {
        return '$record->'.$pkai->name.'= $this->_conn->lastInsertId();';
    }

    protected function _encloseName($name)
    {
        return '['.$name.']';
    }
}
