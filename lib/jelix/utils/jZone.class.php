<?php
/**
 * @package    jelix
 * @subpackage utils
 *
 * @author     Gérald Croes, Laurent Jouanneau
 * @contributor Laurent Jouanneau, Laurent Raufaste, Pulsation
 *
 * @copyright  2001-2005 CopixTeam, 2005-2019 Laurent Jouanneau, 2008 Laurent Raufaste, 2008 Pulsation
 *
 * This class was get originally from the Copix project (CopixZone, Copix 2.3dev20050901, http://www.copix.org)
 * Some lines of code are copyrighted 2001-2005 CopixTeam (LGPL licence).
 * Initial authors of this Copix classes are Gerald Croes and Laurent Jouanneau,
 * and this class was adapted/improved for Jelix by Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

/**
 * jZone is a representation of a zone in an response content, in a html page.
 * A user zone should inherits from jZone. jZone provide a cache mecanism.
 *
 * @package    jelix
 * @subpackage utils
 */
class jZone
{
    /**
     * If we're using cache on this zone
     * You should override it in your class if you want activate the cache.
     *
     * @var bool
     */
    protected $_useCache = false;

    /**
     * cache timeout (seconds).
     * set to 0 if you want to delete cache manually.
     *
     * @var int
     */
    protected $_cacheTimeout = 0;

    /**
     * list of zone parameters.
     *
     * @var array
     */
    protected $_params;

    /**
     * template selector
     * If you want to use a template for your zone, set its name in this property
     * in your zone, and override _prepareTpl. Else, keep it to empty string, and
     * override _createContent.
     *
     * @var string
     */
    protected $_tplname = '';

    /**
     * says the type of the output of the template, in the case of the result
     * of the zone is not used in a response in the same output type.
     * For example, the output type of a ajax response is text, but the template
     * can contains html, so the template should be treated as html content,
     * so you should put 'html' here.
     * If empty, the output type will be the output type of the current response.
     *
     * @var string
     *
     * @see jTpl::fetch
     */
    protected $_tplOutputType = '';

    /**
     * the jtpl object created automatically by jZone if you set up _tplname
     * you can use it in _prepareTpl.
     *
     * @var jTpl
     */
    protected $_tpl;

    /**
     * When the cache system is activated, says if the cache should be generated or not
     * you set it to false in _createContent or _prepareTpl, in specific case.
     *
     * @var bool
     */
    protected $_cancelCache = false;

    /**
     * constructor.
     *
     * @param mixed $params
     */
    public function __construct($params = array())
    {
        $this->_params = $params;
    }

    /**
     * get the content of a zone.
     *
     * @param string $name   zone selector
     * @param array  $params parameters for the zone
     *
     * @return string the generated content of the zone
     *
     * @since 1.0b1
     */
    public static function get($name, $params = array())
    {
        return self::_callZone($name, 'getContent', $params);
    }

    /**
     * clear a specific cache of a zone.
     *
     * @param string $name   zone selector
     * @param array  $params parameters for the zone
     *
     * @since 1.0b1
     */
    public static function clear($name, $params = array())
    {
        self::_callZone($name, 'clearCache', $params);
    }

    /**
     * clear all zone cache or all cache of a specific zone.
     *
     * @param string $name zone selector
     *
     * @since 1.0b1
     */
    public static function clearAll($name = '')
    {
        $dir = jApp::tempPath('zonecache/');
        if (!file_exists($dir)) {
            return;
        }

        if ($name != '') {
            $sel = new jSelectorZone($name);
            $dir .= $sel->module.'/'.strtolower($sel->resource).'zone/';
        }

        jFile::removeDir($dir, false);
    }

    /**
     * gets the value of a parameter, if defined. Returns the default value instead.
     *
     * @param string $paramName    the parameter name
     * @param mixed  $defaultValue the parameter default value
     *
     * @return mixed the param value
     */
    public function param($paramName, $defaultValue = null)
    {
        return array_key_exists($paramName, $this->_params) ? $this->_params[$paramName] : $defaultValue;
    }

    /**
     * get the zone content
     * Return the cache content if it is activated and if it's exists, or call _createContent.
     *
     * @return string zone content
     */
    public function getContent()
    {
        if ($this->_useCache && !jApp::config()->zones['disableCache']) {
            $cacheFiles = $this->_getCacheFiles();
            $f = $cacheFiles['content'];
            if (file_exists($f)) {
                if ($this->_cacheTimeout > 0) {
                    clearstatcache(false, $f);
                    if (time() - filemtime($f) > $this->_cacheTimeout) {
                        // timeout : regenerate the cache
                        unlink($f);
                        $this->_cancelCache = false;
                        list($content, $metaContent) = $this->_generateContentAndCatchMetaCalls();
                        if (!$this->_cancelCache) {
                            jFile::write($f, $content);
                            jFile::write($cacheFiles['meta'], $metaContent);
                        }

                        return $content;
                    }
                }
                //fetch metas from cache :
                if (file_exists($cacheFiles['meta'])) {
                    if (filesize($cacheFiles['meta']) > 0) {
                        //create an anonymous function and then unset it. if jZone cache is cleared within 2 calls in a single
                        //request, this should still work fine
                        $this->_execMetaFunc(jApp::coord()->response, $cacheFiles['meta']);
                    }
                } else {
                    //the cache does not exist yet for this response type. We have to generate it !
                    list(, $metaContent) = $this->_generateContentAndCatchMetaCalls();
                    if (!$this->_cancelCache) {
                        jFile::write($cacheFiles['meta'], $metaContent);
                    }
                }
                //and now fetch content from cache :
                $content = file_get_contents($f);
            } else {
                $this->_cancelCache = false;
                list($content, $metaContent) = $this->_generateContentAndCatchMetaCalls();
                if (!$this->_cancelCache) {
                    jFile::write($f, $content);
                    jFile::write($cacheFiles['meta'], $metaContent);
                }
            }
        } else {
            $content = $this->_createContent();
        }

        return $content;
    }

    protected function _execMetaFunc($resp, $_file)
    {
        include $_file;
    }

    /**
     * When the zone generated content is in cache, the template content is not
     * executed, so zones called in the template (via the `{zone}` plugin) are
     * not called. In this case, meta plugin of their templates are not processed.
     *
     * In order to process meta of children zone, we catch all calls of methods
     * of the global response object (did by the meta plugins), to generate a
     * function that will do same calls when getting the cache content of the
     * zone.
     *
     * FIXME: see if a better solution could be the creating of a jZone::meta()
     * that will do a jTpl::meta() of its template, and jZone::meta() would be
     * added into the meta content function of the generated template by the
     * '{zone}' plugin.
     *
     * @return array
     */
    protected function _generateContentAndCatchMetaCalls()
    {
        $response = jApp::coord()->response;
        $sniffer = new jMethodSniffer($response, '$resp', array('getType', 'getFormatType'));
        jApp::coord()->response = $sniffer;
        $content = $this->_createContent();
        jApp::coord()->response = $response;

        return array($content, '<?'."php\n".(string) $sniffer);
    }

    /**
     * Delete the cache of the current zone.
     */
    public function clearCache()
    {
        if ($this->_useCache) {
            foreach ($this->_getCacheFiles(false) as $f) {
                if (file_exists($f)) {
                    unlink($f);
                }
            }
        }
    }

    /**
     * create the content of the zone
     * by default, it uses a template, and so prepare a jTpl object to use in _prepareTpl.
     * zone parameters are automatically assigned in the template
     * If you don't want a template, override it in your class.
     *
     * @return string generated content
     */
    protected function _createContent()
    {
        $this->_tpl = new jTpl();
        $this->_tpl->assign($this->_params);
        $this->_prepareTpl();
        if ($this->_tplname == '') {
            return '';
        }

        return $this->_tpl->fetch($this->_tplname, $this->_tplOutputType);
    }

    /**
     * override this method if you want do additionnal thing on the template object
     * Example : do access to a dao object.. Note : the template object
     * is in the _tpl property.
     */
    protected function _prepareTpl()
    {
    }

    /**
     * Get the list of cache filenames.
     *
     * @param mixed $forCurrentResponse
     *
     * @return array list of filenames
     */
    private function _getCacheFiles($forCurrentResponse = true)
    {
        $module = jApp::getCurrentModule();

        $id = md5(serialize($this->getCacheId()));

        $path = $id[0].'/'.$id[1].$id[2].'/'.$id.'.php';
        $rootPath = 'zonecache/'.$module.'/'.str_replace('\\', '__', strtolower(get_class($this)));
        $cacheFiles = array(
            'content' => jApp::tempPath($rootPath.'/'.$path),
        );
        if ($forCurrentResponse) {
            //make distinct a cache files for metas according to response type as meta handling is often different for different responses
            $respType = jApp::coord()->response->getType();
            $cacheFiles['meta'] = jApp::tempPath($rootPath.'/meta~'.$respType.'/'.$path);
        } else {
            foreach (jApp::config()->responses as $respType) {
                //list all response types
                if (substr($respType, -5) != '.path') {
                    $cacheFiles['meta.'.$respType] = jApp::tempPath($rootPath.'/meta~'.$respType.'/'.$path);
                }
            }
        }

        return $cacheFiles;
    }

    /**
     * It should returns a list of values that are used for the cache Id.
     *
     * By default, it returns all zone parameters. But some parameters may have
     * values (like some object properties) that are not used for the zone
     * and unfortunately which changed often. The Cache id is then not
     * 'stable' and the cache system may generate a cache at each call.
     *
     * So you can redefine this method to return only values that should be used
     * as cache ID (I.e. which determines the uniqueness of the zone content)
     *
     * @return array list of values that are used for the cache Id
     */
    protected function getCacheId()
    {
        $ar = $this->_params;
        ksort($ar);

        return $ar;
    }

    /**
     * instancy a zone object, and call one of its methods.
     *
     * @param string $name   zone selector
     * @param string $method method name
     * @param array  $params arguments for the method
     *
     * @return mixed the result returned by the method
     */
    private static function _callZone($name, $method, &$params)
    {
        $sel = new jSelectorZone($name);
        jApp::pushCurrentModule($sel->module);

        $fileName = $sel->getPath();

        require_once $fileName;
        $className = $sel->resource.'Zone';
        $zone = new $className($params);
        $toReturn = $zone->{$method}();

        jApp::popCurrentModule();

        return $toReturn;
    }
}
