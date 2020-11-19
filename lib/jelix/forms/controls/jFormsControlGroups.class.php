<?php
/**
 * @package     jelix
 * @subpackage  forms
 *
 * @author      Laurent Jouanneau
 * @contributor Julien Issler
 *
 * @copyright   2006-2008 Laurent Jouanneau
 * @copyright   2008 Julien Issler
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * abstract classes for controls which contain other controls.
 *
 * @package     jelix
 * @subpackage  forms
 */
abstract class jFormsControlGroups extends jFormsControl
{
    public $type = 'groups';

    /**
     * all child controls of the group.
     */
    protected $childControls = array();

    public function check()
    {
        $rv = null;
        foreach ($this->childControls as $ctrl) {
            if (!$ctrl->isActivated()) {
                continue;
            }
            if (($rv2 = $ctrl->check()) !== null) {
                $rv = $rv2;
            }
        }

        return $rv;
    }

    public function getDisplayValue($value)
    {
        return $value;
    }

    public function setValueFromRequest($request)
    {
        foreach ($this->childControls as $name => $ctrl) {
            if (!$this->form->isActivated($name) || $this->form->isReadOnly($name)) {
                continue;
            }
            $ctrl->setValueFromRequest($request);
        }
        $this->setData($request->getParam($this->ref, ''));
    }

    public function addChildControl($control, $itemName = '')
    {
        $this->childControls[$control->ref] = $control;
    }

    public function getChildControls()
    {
        return $this->childControls;
    }

    public function setReadOnly($r = true)
    {
        $this->container->setReadOnly($this->ref, $r);
        foreach ($this->childControls as $ctrl) {
            $ctrl->setReadOnly($r);
        }
    }

    public function deactivate($deactivation = true)
    {
        $this->container->deactivate($this->ref, $deactivation);
        foreach ($this->childControls as $ctrl) {
            $ctrl->deactivate($deactivation);
        }
    }
}
