<?php

/**
 * @package     jelix
 * @subpackage  profiles
 *
 * @author      Laurent Jouanneau
 * @copyright   2015 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jdbProfilesCompiler extends jProfilesCompilerPlugin
{
    protected function consolidate($profile)
    {
        $parameters = new jDbParameters($profile);

        return $parameters->getParameters();
    }
}
