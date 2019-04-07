<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2018 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

class Author
{
    public $name;

    public $email;

    public $role;

    public function __construct($name, $email, $role = '')
    {
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
    }
}
