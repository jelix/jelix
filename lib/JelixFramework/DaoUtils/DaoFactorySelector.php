<?php
/**
 * @package     jelix
 * @subpackage  core_selector
 *
 * @author      Laurent Jouanneau
 * @copyright   2026 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\DaoUtils;

class DaoFactorySelector extends DaoRecordSelector
{
    protected $type = 'daofactory';
    protected $_dirname = 'daos/';
    protected $_suffix = '.daofactory.php';

}