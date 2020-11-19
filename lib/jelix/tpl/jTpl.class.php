<?php
/**
 * @package     jelix
 * @subpackage  jtpl
 *
 * @author      Laurent Jouanneau
 * @contributor Dominique Papin
 *
 * @copyright   2005-2015 Laurent Jouanneau, 2007 Dominique Papin
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * template engine.
 *
 * @package     jelix
 * @subpackage  jtpl
 */
class jTpl extends \Jelix\Castor\CastorCore
{
    public function __construct()
    {
        $config = jApp::config();
        $basePath = jApp::urlBasePath();
        $this->_vars['j_basepath'] = $basePath;
        $this->_vars['j_jelixwww'] = $config->urlengine['jelixWWWPath'];
        // @deprecated
        $this->_vars['j_jquerypath'] = $config->urlengine['jqueryPath'];
        $this->_vars['j_themepath'] = $basePath.'themes/'.$config->theme.'/';
        $this->_vars['j_locale'] = $config->locale;
        $this->_vars['j_lang'] = jLocale::getCurrentLang();
        $this->_vars['j_country'] = jLocale::getCurrentCountry();
        parent::__construct();
    }

    /**
     * assign a zone content to a template variable.
     *
     * @param string $name     the variable name
     * @param string $zoneName a zone selector
     * @param array  $params   parameters for the zone
     *
     * @see jZone
     */
    public function assignZone($name, $zoneName, $params = array())
    {
        $this->_vars[$name] = jZone::get($zoneName, $params);
    }

    /**
     * append a zone content to a template variable.
     *
     * @param string $name     the variable name
     * @param string $zoneName a zone selector
     * @param array  $params   parameters for the zone
     *
     * @see jZone
     * @since 1.0
     */
    public function appendZone($name, $zoneName, $params = array())
    {
        if (isset($this->_vars[$name])) {
            $this->_vars[$name] .= jZone::get($zoneName, $params);
        } else {
            $this->_vars[$name] = jZone::get($zoneName, $params);
        }
    }

    /**
     * assign a zone content to a template variable only if this variable doesn't exist.
     *
     * @param string $name     the variable name
     * @param string $zoneName a zone selector
     * @param array  $params   parameters for the zone
     *
     * @see jZone
     */
    public function assignZoneIfNone($name, $zoneName, $params = array())
    {
        if (!isset($this->_vars[$name])) {
            $this->_vars[$name] = jZone::get($zoneName, $params);
        }
    }

    /**
     * process all meta instruction of a template.
     *
     * @param string $tpl        template selector
     * @param string $outputtype the type of output (html, text etc..)
     * @param bool   $trusted    says if the template file is trusted or not
     *
     * @return array
     */
    public function meta($tpl, $outputtype = '', $trusted = true)
    {
        $sel = new jSelectorTpl($tpl, $outputtype, $trusted);
        $tpl = $sel->toString();

        if (in_array($tpl, $this->processedMeta)) {
            // we want to process meta only one time, when a template is included
            // several time in an other template, or, more important, when a template
            // is included in a recursive manner (in this case, it did cause infinite loop, see #1396).
            return $this->_meta;
        }

        $this->processedMeta[] = $tpl;
        $md = $this->getTemplate($sel, $outputtype, $trusted);

        $fct = 'template_meta_'.$md;
        $fct($this);

        return $this->_meta;
    }

    /**
     * display the generated content from the given template.
     *
     * @param string $tpl        template selector
     * @param string $outputtype the type of output (html, text etc..)
     * @param bool   $trusted    says if the template file is trusted or not
     */
    public function display($tpl, $outputtype = '', $trusted = true)
    {
        $sel = new jSelectorTpl($tpl, $outputtype, $trusted);
        $tpl = $sel->toString();

        $previousTpl = $this->_templateName;
        $this->_templateName = $tpl;
        $this->recursiveTpl[] = $tpl;

        $md = $this->getTemplate($sel, $outputtype, $trusted);

        $fct = 'template_'.$md;
        $fct($this);
        array_pop($this->recursiveTpl);
        $this->_templateName = $previousTpl;
    }

    /**
     * include the compiled template file and call one of the generated function.
     *
     * @param jSelectorTpl|string $tpl        template selector
     * @param string              $outputtype the type of output (html, text etc..)
     * @param bool                $trusted    says if the template file is trusted or not
     *
     * @throws Exception
     *
     * @return string the suffix name of the function to call
     */
    protected function getTemplate($tpl, $outputtype = '', $trusted = true)
    {
        $tpl->userModifiers = $this->userModifiers;
        $tpl->userFunctions = $this->userFunctions;
        jIncluder::inc($tpl);

        return md5($tpl->module.'_'.$tpl->resource.'_'.$tpl->outputType.($trusted ? '_t' : ''));
    }

    /**
     * return the generated content from the given template.
     *
     * @param string $tpl        template selector
     * @param string $outputtype the type of output (html, text etc..)
     * @param bool   $trusted    says if the template file is trusted or not
     * @param bool   $callMeta   false if meta should not be called
     *
     * @throws Exception
     *
     * @return string the generated content
     */
    public function fetch($tpl, $outputtype = '', $trusted = true, $callMeta = true)
    {
        $sel = new jSelectorTpl($tpl, $outputtype, $trusted);
        $tpl = $sel->toString();

        return $this->_fetch($tpl, $sel, $outputtype, $trusted, $callMeta);
    }

    protected function getCachePath()
    {
        return  jApp::tempPath('compiled/templates/');
    }

    protected function getCompiler()
    {
        require_once JELIX_LIB_PATH.'tpl/jTplCompiler.class.php';

        return new jTplCompiler();
    }

    protected function compilationNeeded($cacheFile)
    {
        return jApp::config()->compilation['force'] || !file_exists($cacheFile);
    }

    /**
     * return the current encoding.
     *
     * @return string the charset string
     */
    public function getEncoding()
    {
        return jApp::config()->charset;
    }
}
