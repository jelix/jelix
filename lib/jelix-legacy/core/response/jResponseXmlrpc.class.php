<?php
/**
 * @package     jelix
 * @subpackage  core_response
 *
 * @author      Laurent Jouanneau
 * @contributor Julien Issler
 *
 * @copyright   2005-2010 Laurent Jouanneau
 * @copyright   2017 Julien Issler
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * xmlrpc response.
 *
 * @package  jelix
 * @subpackage core_response
 *
 * @see jResponse
 */
final class jResponseXmlRpc extends jResponse
{
    /**
     * @var string
     */
    protected $_type = 'xmlrpc';

    /**
     * PHP Data to send into the response.
     */
    public $response;

    public function output()
    {
        if ($this->_outputOnlyHeaders) {
            $this->sendHttpHeaders();

            return true;
        }

        $content = jXmlRpc::encodeResponse($this->response);

        $this->_httpHeaders['Content-Type'] = 'text/xml;charset=UTF-8';
        $this->sendHttpHeaders();
        echo $content;

        return true;
    }

    public function outputErrors()
    {
        $errorMessage = jApp::coord()->getGenericErrorMessage();
        $e = jApp::coord()->getErrorMessage();
        if ($e) {
            $errorCode = $e->getCode();
        } else {
            $errorCode = -1;
        }

        $this->clearHttpHeaders();
        $content = jXmlRpc::encodeFaultResponse($errorCode, $errorMessage);

        header('HTTP/1.0 500 Internal Server Error');
        header('Content-Type: text/xml;charset=UTF-8');
        echo $content;
    }
}
