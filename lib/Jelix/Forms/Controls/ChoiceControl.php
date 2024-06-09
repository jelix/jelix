<?php
/**
 *
 * @author      Laurent Jouanneau
 * @contributor Julien Issler
 *
 * @copyright   2006-2024 Laurent Jouanneau
 * @copyright   2010 Julien Issler
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Controls;

/**
 * choice control.
 *
 * It has a list of choices, called choice items.
 * Each item has a value and a list of child controls.
 * The value of the choice control is the value of the selected item.
 *
 * $this->container->privateData contain the list of items that are deactivated.
 * A deactivated item is not displayed.
 *
 */
class ChoiceControl extends AbstractGroupsControl
{
    public $type = 'choice';

    /**
     * list of item. Each value is an array which contains corresponding controls of the item
     * an item could not have controls, in this case its value is an empty array.
     */
    public $items = array();

    public $itemsNames = array();

    public function check()
    {
        $val = $this->container->data[$this->ref];

        if (isset($this->container->privateData[$this->ref][$val])) {
            return $this->container->errors[$this->ref] = \Jelix\Forms\Forms::ERRDATA_INVALID;
        }

        if ($val !== '' && isset($this->items[$val])) {
            $rv = null;
            foreach ($this->items[$val] as $ctrl) {
                if (!$ctrl->isActivated()) {
                    continue;
                }
                if (($rv2 = $ctrl->check()) !== null) {
                    $rv = $rv2;
                }
            }

            return $rv;
        }
        if ($this->required) {
            return $this->container->errors[$this->ref] = \Jelix\Forms\Forms::ERRDATA_INVALID;
        }

        return null;
    }

    public function createItem($value, $label)
    {
        $this->items[$value] = array();
        $this->itemsNames[$value] = $label;
    }

    public function deactivateItem($value, $deactivation = true)
    {
        if (!isset($this->items[$value])) {
            return;
        }
        if ($deactivation) {
            $this->container->privateData[$this->ref][$value] = true;
        } elseif (isset($this->container->privateData[$this->ref][$value])) {
            unset($this->container->privateData[$this->ref][$value]);
        }
    }

    public function isItemActivated($value)
    {
        return !(isset($this->container->privateData[$this->ref][$value]));
    }

    public function addChildControl($control, $itemValue = '')
    {
        $this->childControls[$control->ref] = $control;
        $this->items[$itemValue][$control->ref] = $control;
    }

    public function setValueFromRequest($request)
    {
        $value = $request->getParam($this->ref, '');

        if (isset($this->container->privateData[$this->ref][$value])) {
            $this->setData('');

            return;
        }
        $this->setData($value);
        $val = $this->container->data[$this->ref];
        if (isset($this->items[$val])) {
            foreach ($this->items[$val] as $name => $ctrl) {
                $ctrl->setValueFromRequest($request);
            }
        }
    }

    public function getDisplayValue($value)
    {
        if (isset($this->itemsNames[$value]) && $this->isItemActivated($value)) {
            return $this->itemsNames[$value];
        }
        if ($this->emptyValueLabel === null) {
            return $value;
        }

        return $this->emptyValueLabel;
    }
}
