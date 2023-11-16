<?php
/**
 * @package     jelix
 * @subpackage  core_response
 *
 * @author      Laurent Jouanneau
 *
 * @copyright   2023 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * Response for jForms forms submitted with XHR (ajax).
 *
 * It produces the JSON content of the response, expected by the jFormsJQ
 * javascript object.
 */
class jResponseFormJQJson extends jResponse
{

    /**
     * @var \jFormsBase
     */
    protected $form = null;

    /**
     * @var mixed
     */
    protected $customData = null;

    protected $locationUrl = '';

    protected $errorLocationUrl = '';

    protected $errorMessage = '';

    /**
     * Set the form for the response.
     *
     * Its status and content will help to generate the JSON content
     * for the HTTP response.
     *
     * @param jFormsBase $form
     * @return void
     */
    public function setForm(\jFormsBase $form)
    {
        $this->form = $form;
    }

    /**
     * Arbitrary data that will be sent
     *
     * @param $data
     * @return void
     */
    public function setCustomData($data)
    {
        $this->customData = $data;
    }

    /**
     * Set the url of the page that the browser will load (in Javascript) after
     * receiving the http response, in case of there is no errors into the form.
     *
     * @param string $url
     * @return void
     */
    public function changeLocation($url)
    {
        $this->locationUrl = $url;
    }

    /**
     * The response will be an error.
     *
     * @param string $message the error message to be displayed
     * @param string $locationUrl the url to load into the browser. Will override
     *        the url given to changeLocation, if any.
     */
    public function setError($message = '', $locationUrl = '')
    {
        $this->errorLocationUrl = $locationUrl;
        $this->errorMessage = $message;
    }

    public function output()
    {
        if ($this->_outputOnlyHeaders) {
            $this->sendHttpHeaders();

            return true;
        }

        $this->_httpHeaders['Content-Type'] = 'application/json';

        if ($this->errorMessage || $this->errorLocationUrl) {
            $data = array(
                'success' => false,
                'errorMessage' => $this->errorMessage,
                'locationUrl' => $this->errorLocationUrl,
                'customData' => $this->customData,
            );
        }
        else {
            $data = array(
                'success' => true,
                'customData' => $this->customData,
                'locationUrl' => $this->locationUrl
            );
            $errors = $this->form->getErrors();
            if (count($errors)) {
                $data['success'] = false;
                $data['errors'] = $errors;
            }
        }

        $content = json_encode($data);
        $this->sendHttpHeaders();
        echo $content;

        return true;
    }

    public function outputErrors()
    {
        $message = array();
        $message['errorMessage'] = jApp::coord()->getGenericErrorMessage();
        $e = jApp::coord()->getErrorMessage();
        if ($e) {
            $message['errorCode'] = $e->getCode();
        } else {
            $message['errorCode'] = -1;
        }
        $this->clearHttpHeaders();
        $this->_httpStatusCode = '500';
        $this->_httpStatusMsg = 'Internal Server Error';
        $this->_httpHeaders['Content-Type'] = 'application/json';
        $content = json_encode($message);
        $this->sendHttpHeaders();
        echo $content;
    }
}