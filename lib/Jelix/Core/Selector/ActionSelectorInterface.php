<?php
/**
 *
 * @author      Laurent Jouanneau
 *
 * @copyright   2023 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Selector;

/**
 * interface of selector classes.
 */
interface ActionSelectorInterface extends SelectorInterface
{
    public function getClass();

    public function isEqualTo(ActionSelectorInterface $otherAction);
}
