<?php
/**
 *
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 *
 * @copyright   2006-2024 Laurent Jouanneau
 * @copyright   2007 Loic Mathaud
 *
 * @see        https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Controls;

/**
 */
class TextareaControl extends AbstractControl
{
    public $type = 'textarea';
    public $rows = 5;
    public $cols = 40;

    /**
     * @since 1.2
     */
    public function isHtmlContent()
    {
        return $this->datatype instanceof \jDatatypeHtml;
    }
}
