<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2006-2014 Laurent Jouanneau
 *
 * @see       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Logger\Message;

/**
 * class that handles a dump of a php value, for a logger.
 */
class Dump extends Text
{
    /**
     * @var string the additionnal label
     */
    protected $label;

    public function __construct($obj, $label = '', $category = 'default')
    {
        $this->message = var_export($obj, true);
        $this->category = $category;
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getFormatedMessage()
    {
        if ($this->label) {
            return $this->label.': '.$this->message;
        }

        return $this->message;
    }
}
