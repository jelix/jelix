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
use Jelix\Core\Session;

/**
 * the main class of the routing core.
 *
 * this is the "chief orchestra" of the framework. Its goal is
 * to load the configuration, to get the request parameters
 * used to instancie the correspondant controllers and to run the right method.
 */
class Router
{
    /**
     * plugin list.
     *
     * @var array
     */
    public $plugins = array();

    /**
     * current response object.
     *
     * @var \Jelix\Routing\ServerResponse
     */
    public $response;

    /**
     * current request object.
     *
     * @var \Jelix\Routing\ClientRequest
     */
    public $request;

    /**
     * the selector of the current action.
     *
     * @var FastActionSelector
     */
    public $action;

    /**
     * the original action when there is an internal redirection to an action
     * different from the one corresponding to the request.
     *
     * @var ActionSelector
     */
    public $originalAction;

    /**
     * the current module name.
     *
     * @var string
     */
    public $moduleName;

    /**
     * the current action name.
     *
     * @var string
     */
    public $actionName;

    /**
     * the current error message.
     *
     * @var \Jelix\Logger\Message\Error
     */
    protected $errorMessage;

    /**
     * @var \Jelix\Routing\UrlMapping\UrlActionMapper
     */
    protected $urlActionMapper;

    /**
     * @param object|string $config             filename of the ini file to configure the framework, or the config object itself
     *                                          this parameter is optional if App::loadConfig has been already called
     * @param bool          $enableErrorHandler enable the error handler of jelix.
     *                                          keep it to true, unless you have something to debug
     *                                          and really have to use the default handler or an other handler
     * @param mixed         $configFile
     */
    public function __construct($configFile = '', $enableErrorHandler = true)
    {
        if ($configFile) {
            App::loadConfig($configFile, $enableErrorHandler);
        }

        $mapperConfig = new \Jelix\Routing\UrlMapping\MapperConfig(App::config()->urlengine);
        $this->urlActionMapper = new \Jelix\Routing\UrlMapping\UrlActionMapper($mapperConfig);
        $this->_loadPlugins();
    }

    public function __clone()
    {
        $this->urlActionMapper = clone $this->urlActionMapper;
    }

    /**
     * load the plugins and their configuration file.
     */
    private function _loadPlugins()
    {
        $config = App::config();
        foreach ($config->coordplugins as $name => $conf) {
            if (strpos($name, '.') !== false) {
                continue;
            }
            $conf = self::getPluginConf($name);
            $className = $name.'CoordPlugin';
            if (isset($config->coordplugins[$name.'.class'])) {
                $className = $config->coordplugins[$name.'.class'];
            }

            if (preg_match('/(.+)CoordPlugin$/', $className, $m)) {
                $name2 = $m[1];

                include_once $config->_pluginsPathList_coord[$name2].$name2.'.coord.php';
            }

            // if the plugin is registered as a replacement of an other plugin
            // we can set the name of the other plugin in a '*.name' option
            if (isset($config->coordplugins[$name.'.name'])) {
                $name = $config->coordplugins[$name.'.name'];
            }
            if ($className[0] !== '\\') {
                $className = '\\'.$className;
            }
            $this->plugins[strtolower($name)] = new $className($conf);
        }
    }

    public function getUrlActionMapper()
    {
        return $this->urlActionMapper;
    }

    public function setUrlActionMapper(UrlMapping\UrlActionMapper $urlActionMapper)
    {
        $this->urlActionMapper = $urlActionMapper;
    }

    /**
     * initialize the given request and some properties of the router.
     *
     * It extracts information for the request to set the module name and the
     * action name. It doesn't verify if the corresponding controller does
     * exist or not.
     * It enables also the error handler of Jelix, if needed.
     * Does not call this method directly in entry points. Prefer to call
     * process() instead (that will call setRequest).
     * setRequest is mostly used for tests or specific contexts.
     *
     * @param ClientRequest $request the request object
     * @throw \jException if the module is unknown or the action name format is not valid
     *
     * @see Router::process()
     */
    protected function setRequest(ClientRequest $request)
    {
        $config = App::config();
        $this->request = $request;

        if ($config->enableErrorHandler) {
            set_error_handler(array($this, 'errorHandler'));
            set_exception_handler(array($this, 'exceptionHandler'));

            // let's log messages appeared during init
            foreach (\jBasicErrorHandler::$initErrorMessages as $msg) {
                \Jelix\Logger\Log::log($msg, $msg->getCategory());
            }
        }

        $this->request->init($this->urlActionMapper);

        list($this->moduleName, $this->actionName) = $request->getModuleAction();
        App::pushCurrentModule($this->moduleName);

        $this->action =
        $this->originalAction = new FastActionSelector($this->request->type, $this->moduleName, $this->actionName);

        if (!$config->modules[$this->moduleName.'.enabled']) {
            throw new \jException('jelix~errors.module.untrusted', $this->moduleName);
        }
    }

    /**
     * main method : launch the execution of the action.
     *
     * This method should be called in a entry point.
     *
     * @param ClientRequest $request the request object. It is required if a descendant of Router did not called setRequest before
     *
     * @throws \jException
     */
    public function process($request = null)
    {
        try {
            if ($request) {
                $this->setRequest($request);
            }

            Session::start();

            $ctrl = $this->getController($this->action);
        } catch (\jException $e) {
            $notFoundAct = $this->urlActionMapper->getConfig()->notFoundAct;
            if ($notFoundAct == '') {
                throw $e;
            }
            if (!Session::isStarted()) {
                Session::start();
            }

            try {
                $this->action = new ActionSelector($notFoundAct);
                $ctrl = $this->getController($this->action);
            } catch (\jException $e2) {
                throw $e;
            }
        }

        App::pushCurrentModule($this->moduleName);

        try {
            if (count($this->plugins)) {
                $pluginparams = array();
                if (isset($ctrl->pluginParams['*'])) {
                    $pluginparams = $ctrl->pluginParams['*'];
                }

                if (isset($ctrl->pluginParams[$this->action->method])) {
                    $pluginparams = array_merge($pluginparams, $ctrl->pluginParams[$this->action->method]);
                }

                foreach ($this->plugins as $name => $obj) {
                    $result = $this->plugins[$name]->beforeAction($pluginparams);
                    if ($result) {
                        $this->action = $result;
                        App::popCurrentModule();
                        App::pushCurrentModule($result->module);
                        $this->moduleName = $result->module;
                        $this->actionName = $result->resource;
                        $ctrl = $this->getController($this->action);

                        break;
                    }
                }
            }

            $this->response = $ctrl->{$this->action->method}();
        } catch (HttpErrorException $httpError) {
            $this->response = $this->getHttpErrorResponse($httpError->getCode(), $httpError->getMessage(), $httpError->getReason());
        }

        if ($this->response == null) {
            throw new \jException('jelix~errors.response.missing', $this->action->toString());
        }

        foreach ($this->plugins as $name => $obj) {
            $this->plugins[$name]->beforeOutput();
        }

        $this->response->output();

        foreach ($this->plugins as $name => $obj) {
            $this->plugins[$name]->afterProcess();
        }

        App::popCurrentModule();
        Session::end();
    }

    /**
     * get the controller corresponding to the selector.
     *
     * @param FastActionSelector $selector
     *
     * @throws \jException
     *
     * @return \jController the controller corresponding to the selector
     */
    protected function getController(FastActionSelector $selector)
    {
        $ctrlpath = $selector->getPath();
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = ' REFERER:'.$_SERVER['HTTP_REFERER'];
        } else {
            $referer = '';
        }
        if (!file_exists($ctrlpath)) {
            throw new \jException('jelix~errors.ad.controller.file.unknown', array($this->actionName, $ctrlpath.$referer));
        }

        require_once $ctrlpath;
        $class = $selector->getClass();
        if (!class_exists($class, false)) {
            throw new \jException('jelix~errors.ad.controller.class.unknown', array($this->actionName, $class, $ctrlpath.$referer));
        }
        $ctrl = new $class($this->request);
        if ($ctrl instanceof \jIRestController) {
            $selector->method = strtolower($_SERVER['REQUEST_METHOD']);
        } elseif (!is_callable(array($ctrl, $selector->method))) {
            throw new \jException('jelix~errors.ad.controller.method.unknown', array($this->actionName, $selector->method, $class, $ctrlpath.$referer));
        }
        if (property_exists($ctrl, 'sensitiveParameters')) {
            $config = App::config();
            $config->error_handling['sensitiveParameters'] = array_merge($config->error_handling['sensitiveParameters'], $ctrl->sensitiveParameters);
        }

        return $ctrl;
    }

    /**
     * says if the currently executed action is the original one.
     *
     * @return bool true if yes
     */
    public function execOriginalAction()
    {
        if (!$this->originalAction) {
            return false;
        }

        return $this->originalAction->isEqualTo($this->action);
    }

    /**
     * Create a response object to show an HTTP error (404, 403...).
     *
     * @param int    $httpCode     the http code
     * @param string $httpMessage  the http message
     * @param string $errorDetails reason or details of the error
     *
     * @return \jResponseHtml|\jResponseText
     */
    public function getHttpErrorResponse($httpCode, $httpMessage, $errorDetails)
    {
        $htmlOk = (isset($_SERVER['HTTP_ACCEPT']) && strstr($_SERVER['HTTP_ACCEPT'], 'text/html'));
        if (!$this->request->isAjax() && $htmlOk) {
            try {
                $response = $this->request->getResponse('htmlerror');
            } catch (\Exception $e) {
                $response = null;
            }
            if ($response && $response instanceof \jResponseHtml) {
                $response->body->assign('httpCode', $httpCode);
                $response->body->assign('httpMessage', $httpMessage);
                $response->body->assign('httpErrorDetails', $errorDetails);
                $response->setHttpStatus($httpCode, $httpMessage);

                return $response;
            }
        }

        require_once __DIR__.'/response/jResponseText.class.php';
        $response = new \jResponseText();
        $response->content = $httpMessage."\n".$errorDetails;
        $response->setHttpStatus($httpCode, $httpMessage);

        return $response;
    }

    /**
     * Error handler using a response object to return the error.
     * Replace the default PHP error handler.
     *
     * @param int    $errno    error code
     * @param string $errmsg   error message
     * @param string $filename filename where the error appears
     * @param int    $linenum  line number where the error appears
     *
     * @since 1.4
     */
    public function errorHandler($errno, $errmsg, $filename, $linenum)
    {
        if (error_reporting() == 0) {
            return;
        }

        if (preg_match('/^\s*\((\d+)\)(.+)$/', $errmsg, $m)) {
            $code = $m[1];
            $errmsg = $m[2];
        } else {
            $code = 1;
        }

        if (!isset(\jBasicErrorHandler::$errorCode[$errno])) {
            $errno = E_ERROR;
        }
        $codestr = \jBasicErrorHandler::$errorCode[$errno];

        $trace = debug_backtrace();
        array_shift($trace);
        $this->handleError($codestr, $errno, $errmsg, $filename, $linenum, $trace);
    }

    /**
     * Exception handler using a response object to return the error
     * Replace the default PHP Exception handler.
     *
     * @param \Throwable $e the exception object
     *
     * @since 1.4
     */
    public function exceptionHandler(\Throwable $e)
    {
        $this->handleError(
            'error',
            $e->getCode(),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTrace()
        );
    }

    /**
     * Handle an error event. Called by error handler and exception handler.
     *
     * @param string $type    error type : 'error', 'warning', 'notice'
     * @param int    $code    error code
     * @param string $message error message
     * @param string $file    the file name where the error appear
     * @param int    $line    the line number where the error appear
     * @param array  $trace   the stack trace
     *
     * @since 1.1
     */
    public function handleError($type, $code, $message, $file, $line, $trace)
    {
        $errorLog = new \Jelix\Logger\Message\Error($type, $code, $message, $file, $line, $trace);

        $errorLog->setFormat(App::config()->error_handling['messageLogFormat']);
        \Jelix\Logger\Log::log($errorLog, $type);

        // if non fatal error, it is finished, continue the execution of the action
        if ($type != 'error') {
            return;
        }

        $this->errorMessage = $errorLog;

        while (ob_get_level() && @ob_end_clean());

        $resp = $this->request->getErrorResponse($this->response);
        $resp->outputErrors();
        Session::end();

        exit(1);
    }

    /**
     * return the generic error message (errorMessage in the configuration).
     * Replaced the %code% pattern in the message by the current error code.
     *
     * @return string
     */
    public function getGenericErrorMessage()
    {
        $msg = App::config()->error_handling['errorMessage'];
        if ($this->errorMessage) {
            $code = $this->errorMessage->getCode();
        } else {
            $code = '';
        }

        return str_replace('%code%', $code, $msg);
    }

    /**
     * @return \Jelix\Logger\Message\Error the current error
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * gets a given router plugin if registered.
     *
     * @param string $pluginName the name of the plugin
     * @param bool   $required   says if the plugin is required or not. If true, will generate an exception if the plugin is not registered.
     *
     * @throws \jException
     *
     * @return \Jelix\Routing\RouterPluginInterface
     */
    public function getPlugin($pluginName, $required = true)
    {
        $pluginName = strtolower($pluginName);
        if (isset($this->plugins[$pluginName])) {
            $plugin = $this->plugins[$pluginName];
        } else {
            if ($required) {
                throw new \jException('jelix~errors.plugin.unregister', $pluginName);
            }
            $plugin = null;
        }

        return $plugin;
    }

    /**
     * Says if the given router plugin $name is enabled.
     *
     * @param string $pluginName
     *
     * @return bool true : plugin is ok
     */
    public function isPluginEnabled($pluginName)
    {
        return isset($this->plugins[strtolower($pluginName)]);
    }

    /**
     * return the configuration of a plugin for the coordinator.
     *
     * This is the content of the configuration file of the plugin if it is
     * given,
     * or the content of the section `<pluginname>` from the main configuration
     * or the content of the section `coordplugin_<pluginname>` from the main configuration
     *
     * The plugin configuration file can be merged with the section `<pluginname>`
     * or `coordplugin_<pluginname>` if there is a `<pluginname>.mergeconfig`
     * parameter in the `coordplugins` section.
     *
     * @param string $pluginName
     *
     * @throws Exception when the configuration filename is not found
     *
     * @return array the configuration. May be empty if the plugin is unknown
     */
    public static function getPluginConf($pluginName)
    {
        $config = App::config();
        if (!isset($config->coordplugins[$pluginName])) {
            foreach ($config->coordplugins as $key => $value) {
                if (preg_match('/(.+)\\.name$/', $key, $m) && $value == $pluginName) {
                    return self::getPluginConf($m[1]);
                }
            }

            return array();
        }

        if (isset($config->{$pluginName}) && is_array($config->{$pluginName})) {
            $pluginConf = $config->{$pluginName};
        } else {
            // old section naming. deprecated
            $confname = 'coordplugin_'.$pluginName;
            if (isset($config->{$confname}) && is_array($config->{$confname})) {
                $pluginConf = $config->{$confname};
            } else {
                $pluginConf = array();
            }
        }

        // the config compiler has removed all deactivated plugins
        // so we don't have to check if the value $conf is empty or not
        $conf = $config->coordplugins[$pluginName];
        if ($conf !== 1 && $conf !== true && !is_numeric($conf)) {
            // the path to the coordplugin conf has already been processed
            // by the config compiler, and is now a relative path to the app
            $pluginConfFile = App::appPath($conf);
            if (($pluginConf2 = parse_ini_file($pluginConfFile, true, INI_SCANNER_TYPED)) === false) {
                throw new Exception("Error in a plugin configuration file -- plugin: {$pluginName}  file: {$pluginConfFile}", 13);
            }

            if (isset($config->coordplugins[$pluginName.'.mergeconfig'])
                && $config->coordplugins[$pluginName.'.mergeconfig']
                && count($pluginConf)
            ) {
                // we merge config content from the dedicated file with
                // the content of the coordplugin_ section from the application
                // configuration. coordplugin_ section has priority to allow
                // to setup some configuration parameter in localconfig.ini
                // or liveconfig.ini.
                $pluginConf = array_merge($pluginConf2, $pluginConf);
            } else {
                $pluginConf = $pluginConf2;
            }
        }

        return $pluginConf;
    }
}
