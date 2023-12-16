<?php


/**
 * @author      Laurent Jouanneau
 * @copyright   2023 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\WarmUp;


use Jelix\Core\AppInstance;
use Jelix\Locale\LocaleWarmUp;

/**
 * @internal
 */
class WarmUp
{

    /**
     * @var WarmUpLauncherInterface[]
     */
    protected $warmUpLaunchers = array();

    /**
     * @param AppInstance $app
     * @param $buildDirectory
     */
    public function __construct(AppInstance $app)
    {
        $this->warmUpLaunchers[] = new LocaleWarmUp($app);
    }

    public function launch()
    {
        foreach($this->warmUpLaunchers as $warmUpLauncher){
            $warmUpLauncher->launch();
        }
    }

}