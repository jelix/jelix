<?php
/**
 * @package     jelix
 * @subpackage  routing
 *
 * @author      Laurent Jouanneau
 * @copyright   2018-2024 laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing;

/**
 * Jelix Exception to generate an HTTP error.
 *
 * @package  jelix
 * @subpackage routing
 */
class HttpErrorException extends \Exception
{
    const HTTP_CODE = array(
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range unsatisfiable',
        417 => 'Expectation failed',
        418 => 'I’m a teapot',
        421 => 'Bad mapping / Misdirected Request',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        451 => 'Unavailable For Legal Reasons',
        456 => 'Unrecoverable Error',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient storage',
        508 => 'Loop detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not extended',
        511 => 'Network authentication required',
    );

    protected $reason = '';

    public function __construct($httpCode, $reason = '', ?\Throwable $previous = null)
    {
        if ($httpCode === 0) {
            $httpCode = 500;
        }
        $codes = self::HTTP_CODE;
        if (isset($codes[$httpCode])) {
            $message = $codes[$httpCode];
        } else {
            $message = 'Unknown code';
        }

        $this->reason = $reason;
        parent::__construct($message, $httpCode, $previous);
    }

    public function getReason()
    {
        return $this->reason;
    }
}
