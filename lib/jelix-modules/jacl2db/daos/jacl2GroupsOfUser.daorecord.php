<?php
/**
 * @author      Laurent Jouanneau
 *
 * @copyright   2023 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     https://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */


abstract class jacl2GroupsOfUserDaoRecord extends jDaoRecordBase
{
    public $type = '';

    /**
     * @var string[]
     */
    public $groups = [];

}