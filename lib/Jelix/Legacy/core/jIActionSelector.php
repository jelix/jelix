<?php
/**
 * @package     jelix
 * @subpackage  core_selector
 *
 * @author      Laurent Jouanneau
 *
 * @copyright   2023 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * interface of action selector
 *
 * @package    jelix
 * @subpackage core_selector
 * @deprecated
 */
interface jIActionSelector extends jISelector
{
    public function getClass();

    public function isEqualTo(jIActionSelector $otherAction);
}
