<?php
/**
 * @author   Laurent Jouanneau
 * @contributor
 *
 * @copyright 2014 Laurent Jouanneau
 *
 * @see     http://www.jelix.org
 * @licence  MIT
 */

namespace Jelix\Legacy;

Autoloader::init();
spl_autoload_register(__NAMESPACE__.'\\Autoloader::loadClass');
