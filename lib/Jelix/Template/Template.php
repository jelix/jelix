<?php
/**
 * @author      Laurent Jouanneau
 * @contributor Dominique Papin
 *
 * @copyright   2005-2023 Laurent Jouanneau, 2007 Dominique Papin
 *
 * @see        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Template;

use Jelix\Core\App;
use Jelix\Core\Includer\Includer;
use Jelix\Locale\Locale;

/**
 * template engine.
 */
class Template extends \Jelix\Castor\CastorCore
{
    public function __construct()
    {
        $config = App::config();
        $basePath = App::urlBasePath();
        $this->_vars['j_basepath'] = $basePath;
        $this->_vars['j_jelixwww'] = $config->urlengine['jelixWWWPath'];
        $this->_vars['j_themepath'] = $basePath.'themes/'.$config->theme.'/';
        $this->_vars['j_locale'] = $config->locale;
        $this->_vars['j_lang'] = Locale::getCurrentLang();
        $this->_vars['j_country'] = Locale::getCurrentCountry();
        $this->_vars['j_assetsRevision'] = $config->urlengine['assetsRevision'];
        $this->_vars['j_assetsRevQueryUrl'] = $config->urlengine['assetsRevQueryUrl'];
        $this->_vars['j_assetsRevisionParameter'] = $config->urlengine['assetsRevisionParameter'];
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
        $this->_vars[$name] = \jZone::get($zoneName, $params);
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
            $this->_vars[$name] .= \jZone::get($zoneName, $params);
        } else {
            $this->_vars[$name] = \jZone::get($zoneName, $params);
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
            $this->_vars[$name] = \jZone::get($zoneName, $params);
        }
    }

    /**
     * process all meta instruction of a template.
     *
     * @param string $tpl        template selector
     * @param string $outputType the type of output (html, text etc..)
     * @param bool   $trusted    says if the template file is trusted or not
     *
     * @return array
     */
    public function meta($tpl, $outputType = '', $trusted = true)
    {
        $sel = new TemplateSelector($tpl, $outputType, $trusted);
        $tpl = $sel->toString();

        if (in_array($tpl, $this->processedMeta)) {
            // we want to process meta only one time, when a template is included
            // several time in an other template, or, more important, when a template
            // is included in a recursive manner (in this case, it did cause infinite loop, see #1396).
            return $this->_meta;
        }

        $this->processedMeta[] = $tpl;
        $md = $this->getTemplate($sel, $outputType, $trusted);

        $fct = 'template_meta_'.$md;
        $fct($this);

        return $this->_meta;
    }

    /**
     * display the generated content from the given template.
     *
     * @param string $tpl        template selector
     * @param string $outputType the type of output (html, text etc..)
     * @param bool   $trusted    says if the template file is trusted or not
     */
    public function display($tpl, $outputType = '', $trusted = true)
    {
        $sel = new TemplateSelector($tpl, $outputType, $trusted);
        $tpl = $sel->toString();

        $previousTpl = $this->_templateName;
        $this->_templateName = $tpl;
        $this->recursiveTpl[] = $tpl;

        $md = $this->getTemplate($sel, $outputType, $trusted);

        $fct = 'template_'.$md;
        $fct($this);
        array_pop($this->recursiveTpl);
        $this->_templateName = $previousTpl;
    }

    /**
     * include the compiled template file and call one of the generated function.
     *
     * @param TemplateSelector $tpl        template selector
     * @param string           $outputType the type of output (html, text etc..)
     * @param bool             $trusted    says if the template file is trusted or not
     *
     * @throws \Exception
     *
     * @return string the suffix name of the function to call
     */
    protected function getTemplate($tpl, $outputType = '', $trusted = true)
    {
        $tpl->userModifiers = $this->userModifiers;
        $tpl->userFunctions = $this->userFunctions;
        Includer::inc($tpl);

        return md5($tpl->module.'_'.$tpl->resource.'_'.$tpl->outputType.($trusted ? '_t' : ''));
    }

    /**
     * return the generated content from the given template.
     *
     * @param string $tpl        template selector
     * @param string $outputType the type of output (html, text etc..)
     * @param bool   $trusted    says if the template file is trusted or not
     * @param bool   $callMeta   false if meta should not be called
     *
     * @throws \Exception
     *
     * @return string the generated content
     */
    public function fetch($tpl, $outputType = '', $trusted = true, $callMeta = true)
    {
        $sel = new TemplateSelector($tpl, $outputType, $trusted);
        $tpl = $sel->toString();

        return $this->_fetch($tpl, $sel, $outputType, $trusted, $callMeta);
    }

    protected function getCachePath()
    {
        return App::tempPath('compiled/templates/');
    }

    protected function getCompiler()
    {
        return new TemplateCompiler();
    }

    protected function compilationNeeded($cacheFile)
    {
        return App::config()->compilation['force'] || !file_exists($cacheFile);
    }

    /**
     * return the current encoding.
     *
     * Always UTF-8 into Jelix 2+
     *
     * @return string the charset string
     */
    public function getEncoding()
    {
        return 'UTF-8';
    }
}
