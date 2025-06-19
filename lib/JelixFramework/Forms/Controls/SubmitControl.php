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
 */
class SubmitControl extends AbstractDatasourceControl
{
    public $type = 'submit';
    public $standalone = true;

    public function check()
    {
        return null;
    }

    public function setValueFromRequest($request)
    {
        $value = $request->getParam($this->ref, '');

        if ($value && !$this->standalone) {
            // because IE send the <button> content as value instead of the content of the
            // "value" attribute, we should verify it and get the real value
            // or when using <input type="submit">, we have only the label as value (in all browsers...)
            $data = $this->datasource->getData($this->form);
            if (!isset($data[$value])) {
                $data = array_flip($data);
                if (isset($data[$value])) {
                    $value = $data[$value];
                }
            }
        }
        $this->setData($value);
    }

    public function isModified()
    {
        // it does not make sens to compare old and new value, it has never an old value.
        return false;
    }

}
