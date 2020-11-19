<?php

/**
 * check a jelix installation.
 *
 * @package     jelix
 * @subpackage  core
 *
 * @author      Laurent Jouanneau
 * @copyright   2007-2018 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 *
 * @since       1.0b2
 * @deprecated
 */
require 'db/jDbParameters.class.php';
\Jelix\Installer\Checker\CheckerPage::show();
