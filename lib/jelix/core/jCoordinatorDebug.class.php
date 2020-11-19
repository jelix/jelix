<?php
/**
 * @package      jelix
 * @subpackage   core
 *
 * @author       Laurent Jouanneau
 * @contributor  Thibault Piront (nuKs), Julien Issler, Dominique Papin, Flav, Gaëtan MARROT
 *
 * @copyright    2005-2013 laurent Jouanneau
 * @copyright    2007 Thibault Piront
 * @copyright    2008 Julien Issler
 * @copyright    2008-2010 Dominique Papin, 2012 Flav, 2013 Gaëtan MARROT
 *
 * @see         http://www.jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * the main class of the jelix core.
 *
 * this is the "chief orchestra" of the framework. Its goal is
 * to load the configuration, to get the request parameters
 * used to instancie the correspondant controllers and to run the right method.
 *
 * @package  jelix
 * @subpackage core
 */
class jCoordinatorDebug extends jCoordinator
{
    public function __construct($configFile = '', $enableErrorHandler = true)
    {
        parent::__construct($configFile, $enableErrorHandler);
        jLog::log('---------- jCoordinatorDebug');
    }

    protected function setRequest($request)
    {
        parent::setRequest($request);
        jLog::log('setRequest: pathinfo='.$request->urlPathInfo);
        jLog::log('setRequest: module='.$this->moduleName.' action='.$this->actionName);
        jlog::dump($request->params, 'setRequest: params');
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
        jLog::log('process: start');

        try {
            if ($request) {
                $this->setRequest($request);
            }

            jSession::start();

            $ctrl = $this->getController($this->action);
        } catch (jException $e) {
            $notFoundAct = $this->urlActionMapper->getConfig()->notFoundAct;
            if ($notFoundAct == '') {
                throw $e;
            }
            if (!jSession::isStarted()) {
                jSession::start();
            }

            try {
                jLog::log('Exception: get notfoundact ctrl ('.$notFoundAct.')');
                $this->action = new jSelectorAct($notFoundAct);
                $ctrl = $this->getController($this->action);
            } catch (jException $e2) {
                throw $e;
            }
        }

        jApp::pushCurrentModule($this->moduleName);

        if (count($this->plugins)) {
            $pluginparams = array();
            if (isset($ctrl->pluginParams['*'])) {
                $pluginparams = $ctrl->pluginParams['*'];
            }

            if (isset($ctrl->pluginParams[$this->action->method])) {
                $pluginparams = array_merge($pluginparams, $ctrl->pluginParams[$this->action->method]);
            }
            jLog::dump($pluginparams, 'process: plugin params');
            foreach ($this->plugins as $name => $obj) {
                jLog::log("process: beforeAction on plugin {$name}");
                $result = $this->plugins[$name]->beforeAction($pluginparams);
                if ($result) {
                    $this->action = $result;
                    jApp::popCurrentModule();
                    jApp::pushCurrentModule($result->module);
                    jLog::log('process: beforeAction said to do internal redirect to '.$result->module.'~'.$result->resource);
                    $this->moduleName = $result->module;
                    $this->actionName = $result->resource;
                    $ctrl = $this->getController($this->action);

                    break;
                }
            }
        }

        jLog::log('process: call action');
        $this->response = $ctrl->{$this->action->method}();
        if ($this->response == null) {
            throw new jException('jelix~errors.response.missing', $this->action->toString());
        }
        jLog::log('process: response: '.get_class($this->response));
        if (get_class($this->response) == 'jResponseRedirect') {
            jLog::log('process: redirection to '.$this->response->action);
        } elseif (get_class($this->response) == 'jResponseRedirectUrl') {
            jLog::log('process: redirection to '.$this->response->url);
        }

        foreach ($this->plugins as $name => $obj) {
            jLog::log('process: beforeOutput on plugin '.$name);
            $this->plugins[$name]->beforeOutput();
        }

        jLog::log('process: call response output');
        $this->response->output();

        foreach ($this->plugins as $name => $obj) {
            jLog::log('process: afterProcess on plugin '.$name);
            $this->plugins[$name]->afterProcess();
        }

        jApp::popCurrentModule();
        jSession::end();
        jLog::log('process: end');
    }

    /**
     * get the controller corresponding to the selector.
     *
     * @param jSelectorAct $selector
     */
    protected function getController($selector)
    {
        jLog::log('getController for '.$selector->toString());
        $ctrl = parent::getController($selector);
        jLog::log('getController: '.get_class($ctrl));

        return $ctrl;
    }
}
