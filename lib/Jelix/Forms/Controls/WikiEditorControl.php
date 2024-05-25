<?php
/**
 *
 * @author      Olivier Demah
 * @contributor Laurent Jouanneau
 *
 * @copyright   2009 Olivier Demah, 2010-2024 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Forms\Controls;

use Jelix\Core\App;

/**
 *
 * @since 1.2
 */
class WikiEditorControl extends AbstractControl
{
    public $type = 'wikieditor';
    public $rows = 5;
    public $cols = 40;
    public $config = 'default';

    public function __construct($ref)
    {
        parent::__construct($ref);
        $this->datatype = new \jDatatypeString();
    }

    public function isHtmlContent()
    {
        return true;
    }

    public function getDisplayValue($value)
    {
        $engine = App::config()->wikieditors[$this->config.'.wiki.rules'];
        $wiki = new \jWiki($engine);

        return $wiki->render($value);
    }
}
