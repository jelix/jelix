<?php
/**
 * jSmtp, based on SMTP, a  PHP SMTP class by Chris Ryan.
 *
 * Define an SMTP class that can be used to connect
 * and communicate with any SMTP server. It implements
 * all the SMTP functions defined in RFC821 except TURN.
 *
 * @author      Laurent Jouanneau
 * @copyright   2006-2014 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Mailer;

/**
 * SMTP is rfc 821 compliant and implements all the rfc 821 SMTP
 * commands except TURN which will always return a not implemented
 * error. SMTP also provides some utility methods for sending mail
 * to an SMTP server.
 *
 * This class is just a simple wrapper around SMTP.
 *
 * @see SMTP
 */
class Smtp extends \SMTP
{
}
