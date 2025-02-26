<?php
/**
 * @package     jelix
 * @subpackage  routing
 *
 * @author      Laurent Jouanneau
 * @copyright   2018-2019 laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing;



/**
 * Jelix Exception to generate an HTTP 401 error.
 *
 * Unauthorized. The user must be authenticated to access to the resource.
 *
 * @package  jelix
 * @subpackage routing
 */
class Http401UnauthorizedException extends HttpErrorException
{
    public function __construct($reason = '', ?\Throwable $previous = null)
    {
        parent::__construct(401, $reason, $previous);
    }
}
