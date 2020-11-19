<?php
/**
 * @package     jelix
 * @subpackage  core_response
 *
 * @author      Laurent Jouanneau
 * @copyright   2019 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
require_once __DIR__.'/jResponseHtml.class.php';

/**
 * HTML5 response to display HTTP errors.
 *
 * @package  jelix
 * @subpackage core_response
 */
class jResponseHtmlError extends jResponseHtml
{
    public $bodyTpl = 'jelix~http_error.html';

    protected function doAfterActions()
    {
        $httpCode = $this->_httpStatusCode;
        if ($httpCode == 404) {
            $this->bodyTpl = 'jelix~404.html';
        } elseif ($httpCode == 403) {
            $this->bodyTpl = 'jelix~403.html';
        }

        $this->body->assign('httpCode', $httpCode);
        $this->body->assign('httpMessage', $this->_httpStatusMsg);
        $this->body->assignIfNone('httpErrorDetails', '');
    }
}
