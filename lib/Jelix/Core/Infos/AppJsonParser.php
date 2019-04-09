<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2015 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

class AppJsonParser extends JsonParserAbstract
{
    protected function createInfos()
    {
        return new AppInfos($this->path, false);
    }
}
