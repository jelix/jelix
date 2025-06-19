<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2017-2024 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Forms\Captcha;

use Jelix\Locale\Locale;

class SimpleCaptchaValidator implements CaptchaValidatorInterface
{
    /**
     * called by the widget to initialize some data when the form is generated.
     *
     * It can returns some data that can be useful for the widget, and which will
     * be passed to validate() method ($internalData)
     *
     * @return mixed
     */
    public function initOnDisplay()
    {
        $numbers = Locale::get('jelix~captcha.number');
        $id = rand(1, intval($numbers));

        return array(
            'question' => Locale::get('jelix~captcha.question.'.$id),
            'expectedresponse' => Locale::get('jelix~captcha.response.'.$id),
        );
    }

    /**
     * Validate the data coming from the submitted form.
     *
     * It should returns null if it is ok, or one of jForms::ERRDATA_* constant
     *
     * @param string $value the value of the control if it exists
     * @param mixed
     * @param mixed $internalData
     *
     * @return null|int
     */
    public function validate($value, $internalData)
    {
        if (trim($value) == '') {
            return \Jelix\Forms\Forms::ERRDATA_REQUIRED;
        }
        if (!$internalData
                || !is_array($internalData)
                || !isset($internalData['expectedresponse'])
                || $value != $internalData['expectedresponse']) {
            return \Jelix\Forms\Forms::ERRDATA_INVALID;
        }

        return null;
    }
}
