<?php
/**
 *
 * @author      Laurent Jouanneau
 * @copyright   2006-2024 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Controls;

/**
 *
 */
class HtmlEditorControl extends AbstractControl
{
    public $type = 'htmleditor';
    public $rows = 5;
    public $cols = 40;
    public $config = 'default';
    public $skin = 'default';

    public function __construct($ref)
    {
        parent::__construct($ref);
        $this->datatype = new \jDatatypeHtml(true, true);
    }

    /**
     * @since 1.2
     */
    public function isHtmlContent()
    {
        return true;
    }
}
