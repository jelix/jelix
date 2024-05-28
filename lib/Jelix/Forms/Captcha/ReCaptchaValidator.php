<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2017 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Forms\Captcha;

use Jelix\Core\App;

class ReCaptchaValidator implements CaptchaValidatorInterface
{
    /**
     * called by the widget to initialize some data when the form is generated.
     *
     * It can returns some data that can be useful for the widget
     *
     * @return mixed
     */
    public function initOnDisplay()
    {
        return null;
    }

    /**
     * Validate the data coming from the submitted form.
     *
     * It should returns null if it is ok, or one of jForms::ERRDATA_* constant
     *
     * @param mixed $value
     * @param mixed $internalData
     *
     * @return null|int
     */
    public function validate($value, $internalData)
    {
        $config = App::config()->recaptcha;
        if (!isset($config['secret']) || $config['secret'] == '') {
            \jLog::log('secret for recaptcha is missing from the configuration', 'warning');

            return \jForms::ERRDATA_INVALID;
        }

        if (!isset($_POST['g-recaptcha-response'])) {
            return \jForms::ERRDATA_REQUIRED;
        }

        $recaptcha = new \ReCaptcha\ReCaptcha($config['secret']);
        $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        if ($resp->isSuccess()) {
            return null;
        }

        foreach ($resp->getErrorCodes() as $code) {
            if ($code == 'missing-input-secret') {
                \jLog::log('secret for recaptcha is missing from the google request', 'warning');
            } elseif ($code == 'invalid-input-secret') {
                \jLog::log('secret for recaptcha is invalid', 'warning');
            }
        }

        return \jForms::ERRDATA_INVALID;
    }
}
