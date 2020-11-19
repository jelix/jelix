<?php
/**
 * @package     jelix
 * @subpackage  core_response
 *
 * @author      Laurent Jouanneau
 * @contributor Julien Issler
 *
 * @copyright   2005-2017 Laurent Jouanneau
 * @copyright   2017 Julien Issler
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * plain Text response.
 *
 * @package  jelix
 * @subpackage core_response
 */
class jResponseText extends jResponse
{
    /**
     * @var string
     */
    protected $_type = 'text';

    /**
     * text content.
     *
     * @var string
     */
    public $content = '';

    /**
     * The mimeType of the content.
     * It will be sent in the header "Content-Type".
     *
     * @var string
     *
     * @since 1.7
     */
    public $mimeType = 'text/plain';

    /**
     * output the content.
     *
     * @return bool true si it's ok
     */
    public function output()
    {
        if ($this->_outputOnlyHeaders) {
            $this->sendHttpHeaders();

            return true;
        }

        $this->addHttpHeader('Content-Type', $this->mimeType.';charset='.jApp::config()->charset, false);
        $this->sendHttpHeaders();
        echo $this->content;

        return true;
    }

    /**
     * output errors.
     */
    public function outputErrors()
    {
        header('HTTP/1.0 500 Internal Jelix Error');
        header('Content-Type: text/plain;charset='.jApp::config()->charset);
        echo jApp::coord()->getGenericErrorMessage();
    }
}
