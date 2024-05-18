<?php

/**
 * @author     Laurent Jouanneau
 * @copyright  2024 Laurent Jouanneau
 *
 * @see        https://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\WarmUp;


enum FilePlaceEnum
{
    /** the file is into var/overloads and is tied to a module */
    case VarOverloads;

    /** the file is into var/ */
    case Var;

    /** the file is into app/overloads and is tied to a module */
    case AppOverloads;

    /** the file is into app/ */
    case App;

    /** the file is into a module */
    case Module;
}
