<?php
/**
 *
 * @author      Laurent Jouanneau
 * @copyright   2006-2024 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Controls;

/**
 * menulist/combobox.
 *
 */
class MenulistControl extends RadiobuttonsControl
{
    public $type = 'menulist';
    public $defaultValue = '';
    public $emptyItemLabel;
}
