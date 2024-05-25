<?php
/**
 *
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 *
 * @copyright   2006-2024 Laurent Jouanneau
 * @copyright   2007 Loic Mathaud
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Controls;

/**
 */
class SecretConfirmControl extends AbstractControl
{
    public $type = 'secretconfirm';
    public $size = 0;
    /**
     * ref value of the associated secret control.
     */
    public $primarySecret = '';

    public function check()
    {
        if ($this->container->data[$this->ref] != $this->form->getData($this->primarySecret)) {
            return $this->container->errors[$this->ref] = \jForms::ERRDATA_INVALID;
        }

        return null;
    }
}
