<?php
/**
 * @author       Laurent Jouanneau
 * @contributor  Thibault Piront (nuKs), Julien Issler, Dominique Papin, Flav, Gaëtan MARROT
 *
 * @copyright    2005-2015 laurent Jouanneau
 * @copyright    2007 Thibault Piront
 * @copyright    2008 Julien Issler
 * @copyright    2008-2010 Dominique Papin, 2012 Flav, 2013 Gaëtan MARROT
 *
 * @see         http://www.jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing;

use Jelix\Core\App;
use Jelix\Logger\Log;

/**
 * the main class of the jelix core, in debug mode.
 *
 * this is the "chief orchestra" of the framework. Its goal is
 * to load the configuration, to get the request parameters
 * used to instancie the correspondant controllers and to run the right method.
 */
class RouterDebug extends Router
{
    /**
     * @inherit
     *
     * @param mixed $configFile
     * @param mixed $enableErrorHandler
     */
    public function __construct($configFile = '', $enableErrorHandler = true)
    {
        parent::__construct($configFile, $enableErrorHandler);
        Log::log('---------- jCoordinatorDebug');
    }

    /**
     * @inherit
     */
    protected function setRequest(ClientRequest $request)
    {
        parent::setRequest($request);
        Log::log('setRequest: pathinfo='.$request->urlPathInfo);
        Log::log('setRequest: module='.$this->moduleName.' action='.$this->actionName);
        Log::dump($request->params, 'setRequest: params');
    }

    /**
     * main method : launch the execution of the action.
     *
     * This method should be called in a entry point.
     *
     * @param jRequest $request the request object. It is required if a descendant of jCoordinator did not called setRequest before
     */
    public function process($request = null)
    {
        Log::log('process: start');

        try {
            if ($request) {
                $this->setRequest($request);
            }

            \jSession::start();

            $ctrl = $this->getController($this->action);
        } catch (\jException $e) {
            $notFoundAct = $this->urlActionMapper->getConfig()->notFoundAct;
            if ($notFoundAct == '') {
                throw $e;
            }
            if (!\jSession::isStarted()) {
                \jSession::start();
            }

            try {
                Log::log('Exception: get notfoundact ctrl ('.$notFoundAct.')');
                $this->action = new \jSelectorAct($notFoundAct);
                $ctrl = $this->getController($this->action);
            } catch (\jException $e2) {
                throw $e;
            }
        }

        App::pushCurrentModule($this->moduleName);

        if (count($this->plugins)) {
            $pluginparams = array();
            if (isset($ctrl->pluginParams['*'])) {
                $pluginparams = $ctrl->pluginParams['*'];
            }

            if (isset($ctrl->pluginParams[$this->action->method])) {
                $pluginparams = array_merge($pluginparams, $ctrl->pluginParams[$this->action->method]);
            }

            Log::dump($pluginparams, 'process: plugin params');

            foreach ($this->plugins as $name => $obj) {
                Log::log("process: beforeAction on plugin {$name}");
                $result = $this->plugins[$name]->beforeAction($pluginparams);
                if ($result) {
                    $this->action = $result;
                    App::popCurrentModule();
                    App::pushCurrentModule($result->module);
                    Log::log('process: beforeAction said to do internal redirect to '.$result->module.'~'.$result->resource);
                    $this->moduleName = $result->module;
                    $this->actionName = $result->resource;
                    $ctrl = $this->getController($this->action);

                    break;
                }
            }
        }

        Log::log('process: call action');
        $this->response = $ctrl->{$this->action->method}();
        if ($this->response == null) {
            throw new \jException('jelix~errors.response.missing', $this->action->toString());
        }

        Log::log('process: response: '.get_class($this->response));
        if (get_class($this->response) == 'jResponseRedirect') {
            Log::log('process: redirection to '.$this->response->action);
        } elseif (get_class($this->response) == 'jResponseRedirectUrl') {
            Log::log('process: redirection to '.$this->response->url);
        }

        foreach ($this->plugins as $name => $obj) {
            Log::log('process: beforeOutput on plugin '.$name);
            $this->plugins[$name]->beforeOutput();
        }

        Log::log('process: call response output');
        $this->response->output();

        foreach ($this->plugins as $name => $obj) {
            Log::log('process: afterProcess on plugin '.$name);
            $this->plugins[$name]->afterProcess();
        }

        App::popCurrentModule();
        \jSession::end();
        Log::log('process: end');
    }

    /**
     * get the controller corresponding to the selector.
     *
     * @param jSelectorAct $selector
     */
    protected function getController(\jSelectorAct $selector)
    {
        Log::log('getController for '.$selector->toString());
        $ctrl = parent::getController($selector);
        Log::log('getController: '.get_class($ctrl));

        return $ctrl;
    }
}
