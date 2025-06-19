<?php
/**
 * see Jelix/Core/Selector/SelectorInterface.php for documentation about selectors.
 *
 * @author      Laurent Jouanneau
 * @contributor Christophe Thiriot
 *
 * @copyright   2005-2014 Laurent Jouanneau
 * @copyright   2008 Christophe Thiriot
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Selector;

/**
 * selector for interface.
 *
 * interface is stored in interfacename.iface.php file in the classes/ module directory
 * or one of its subdirectory.
 * syntax : "iface:module~ifacename" or "module~ifacename.
 *
 * @since 1.0.3
 */
class InterfaceSelector extends ClassSelector
{
    protected $type = 'iface';
    protected $_dirname = 'classes/';
    protected $_suffix = '.iface.php';
}
