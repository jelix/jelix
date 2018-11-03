<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Installer;


/**
 * entry points properties for configurators
 *
 * @method getType()
 * @method getScriptName()
 * @method getFileName()
 * @method isCliScript()
 * @method getEpId()
 * @method getConfigFileName()
 * @method getUrlMap()
 * @method getModulesList()
 * @method getConfigIni()
 * @method getSingleConfigIni()
 * @method getConfigObj()
 *
 * @since 1.7
 */
class EntryPointPreConfigurator
{
    /**
     * @var EntryPoint
     */
    protected $entryPoint;

    protected $readOnlyConfig = false;

    /**
     * @var GlobalSetup
     */
    protected $globalSetup;

    function __construct(EntryPoint $entryPoint,
                         GlobalSetup $globalSetup,
                         $readOnlyConfig
    )
    {
        $this->entryPoint = $entryPoint;
        $this->globalSetup = $globalSetup;
        $this->readOnlyConfig = $readOnlyConfig;
    }

    public function __call ( $functionName , $arguments) {
        if ($functionName != 'setConfigObj') {
            return call_user_func_array([$this->entryPoint, $functionName], $arguments);
        }
        throw new \ErrorException("Unknown method $functionName on ".__CLASS__);
    }

}
