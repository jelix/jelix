<?php

/**
 * @package     jelix
 * @subpackage  profiles
 *
 * @author      Laurent Jouanneau
 * @copyright   2015-2021 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jdbProfilesCompiler extends \Jelix\Profiles\ReaderPlugin
{
    protected function consolidate($profile)
    {
        $options = array(
            'filePathParser' => 'jDb::parseSqlitePath'
        );

        $parameters = new \Jelix\Database\AccessParameters($profile, $options);

        return $parameters->getNormalizedParameters();
    }
}
