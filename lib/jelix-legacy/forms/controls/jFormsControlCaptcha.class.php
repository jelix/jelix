<?php
/**
 * @package     jelix
 * @subpackage  forms
 *
 * @author      Laurent Jouanneau
 * @copyright   2006-2023 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * captcha control.
 *
 * @package     jelix
 * @subpackage  forms
 *
 * @since 1.1
 */
class jFormsControlCaptcha extends jFormsControl
{
    public $type = 'captcha';

    public $required = true;

    protected $validatorName = 'simple';

    public function __construct($ref)
    {
        parent::__construct($ref);
        $this->validatorName = jApp::config()->forms['captcha'];
    }

    public function setValidator($validatorName)
    {
        $this->validatorName = $validatorName;
    }

    public function getWidgetType()
    {
        if (isset(jApp::config()->forms['captcha.'.$this->validatorName.'.widgettype'])) {
            return jApp::config()->forms['captcha.'.$this->validatorName.'.widgettype'];
        }

        return $this->type;
    }

    /**
     * @throws Exception
     *
     * @return \Jelix\Forms\Captcha\CaptchaValidatorInterface
     */
    protected function getCaptcha()
    {
        $className = '';
        if (isset(jApp::config()->forms['captcha.'.$this->validatorName.'.validator'])) {
            $className = jApp::config()->forms['captcha.'.$this->validatorName.'.validator'];
        }
        if (!$className) {
            throw new \Exception("Captcha validator not set in the configuration for '".$this->validatorName."'");
        }

        return new $className();
    }

    public function check()
    {
        $value = $this->container->data[$this->ref];
        if (isset($this->container->privateData[$this->ref])) {
            $internalData = $this->container->privateData[$this->ref];
        } else {
            $internalData = null;
        }
        $result = $this->getCaptcha()->validate($value, $internalData);
        if ($result) {
            $this->container->errors[$this->ref] = $result;
        }

        return $result;
    }

    /**
     * @return mixed data returns by the captcha validator
     */
    public function initCaptcha()
    {
        $data = $this->getCaptcha()->initOnDisplay();
        $this->container->privateData[$this->ref] = $data;

        return $data;
    }
}
