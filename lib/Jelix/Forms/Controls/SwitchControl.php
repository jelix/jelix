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
 * switch.
 *
 * @experimental
 */
class SwitchControl extends ChoiceControl
{
    public $type = 'switch';

    public function setValueFromRequest($request)
    {
        //$this->setData($request->getParam($this->ref,''));
        if (isset($this->items[$this->container->data[$this->ref]])) {
            foreach ($this->items[$this->container->data[$this->ref]] as $name => $ctrl) {
                $ctrl->setValueFromRequest($request);
            }
        }
    }

    public function isModified()
    {
        return false;
    }
}
