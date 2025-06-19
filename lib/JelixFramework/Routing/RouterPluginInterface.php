<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2005-2015 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing;

/**
 * interface that should implement all coordinator plugins.
 */
interface RouterPluginInterface
{
    /**
     * @param array $config content of the config ini file of the plugin
     */
    public function __construct($config);

    /**
     * this method is called before each action.
     *
     * @param array $params plugin parameters for the current action
     */
    public function beforeAction($params);

    /**
     * this method is called after the execution of the action, and before the output of the response.
     */
    public function beforeOutput();

    /**
     * this method is called after the output.
     */
    public function afterProcess();
}
