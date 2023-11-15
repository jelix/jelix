<?php


/**
 * @author      Laurent Jouanneau
 * @copyright   2023 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\WarmUp;


use Jelix\Installer\GlobalSetup;
use Jelix\Locale\LocaleWarmUp;

class WarmUp
{

    /**
     * @var WarmUpLauncherInterface[]
     */
    protected $warmUpLaunchers = array();

    public function __construct(GlobalSetup $globalSetup, $buildDirectory)
    {

        $this->warmUpLaunchers[] = new LocaleWarmUp($globalSetup, $buildDirectory);
    }

    public function launch()
    {
        foreach($this->warmUpLaunchers as $warmUpLauncher){
            $warmUpLauncher->launch();
        }
    }

}