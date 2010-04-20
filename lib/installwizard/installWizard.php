<?php

/**
* Installation wizard
*
* @package     InstallWizard
* @author      Laurent Jouanneau
* @copyright   2010 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

require(dirname(__FILE__).'/jtpl/jtpl_standalone_prepend.php');
require(dirname(__FILE__).'/installWizardPage.php');

/**
 * main class of the wizard
 *
 */
class installWizard {

    protected $config;

    protected $configPath;

    protected $lang = 'en';

    protected $pages = array();

    protected $customPath = '';

    protected $tempPath = '';

    protected $stepName = "";

    protected $locales = array();

    /**
     * @param string $config an ini file for the installation
     * should contain this parameter:
     * - 
     */
    function __construct($configFile) {
        $this->configPath = $configFile;
        session_start();
        date_default_timezone_set("Europe/Paris");
    }

    protected function readConfiguration() {
        $conf = parse_ini_file($this->configPath,true);
        if (!$conf)
            throw new Exception('Impossible to read the configuration file');
        $this->config = $conf;
        
        if (isset($this->config['supportedLang'])) {
           $this->config['supportedLang'] = preg_split('/ *, */',$this->config['supportedLang']);
        }
        else
            $this->config['supportedLang'] = array('en');
    }

    protected function initPath() {

        $list = preg_split('/ *, */',$this->config['pagesPath']);
        $basepath = dirname($this->configPath).'/';

        foreach($list as $k=>$path){
            if(trim($path) == '') continue;
            $p = realpath($basepath.$path);
            if ($p== '' || !file_exists($p))
                throw new Exception ('The path, '.$path.' given in the configuration doesn\'t exist !');

            if (substr($p,-1) !='/')
                $p.='/';

            if ($handle = opendir($p)) {
                while (false !== ($f = readdir($handle))) {
                    if ($f[0] != '.' && is_dir($p.$f) && isset($this->config[$f.'.step'])) {
                        $this->pages[$f] = $p.$f.'/';
                    }
                }
                closedir($handle);
            }
        }
        if (isset($this->config['customPath']) && $this->config['customPath'] != '') {
            $this->customPath = realpath($basepath.$this->config['customPath']);
            if ($this->customPath)
                $this->customPath .= '/';
        }

        if (isset($this->config['tempPath']) && $this->config['tempPath'] != '') {
            $this->tempPath = realpath($basepath.$this->config['tempPath']);
            if (!$this->tempPath)
                throw new Exception("no temp directory");
            $this->tempPath .= '/';
        }
        else
            throw new Exception("no temp directory");
    }

    protected function initPrevious($step ='', $previousStep='') {
        if ($step == '') {
            if (isset($this->config['start']))
                $step = $this->config['start'];
            else
                return;
        }
        if (!isset($this->pages[$step]) || !isset($this->config[$step.'.step'])) {
            return;
        }
        if (isset($this->config[$step.'.step']['__previous'])) {
            return;
        }
        
        if (isset($this->config[$step.'.step']['noprevious']) && $this->config[$step.'.step']['noprevious'])
            $this->config[$step.'.step']['__previous'] = '';
        else
            $this->config[$step.'.step']['__previous'] = $previousStep;
        
        if (!isset($this->config[$step.'.step']['next'])) {
            return;
        }
        
        if (is_array($this->config[$step.'.step']['next'])) {
            foreach($this->config[$step.'.step']['next'] as $next)
                $this->initPrevious($next, $step);
        }
        else 
            $this->initPrevious($this->config[$step.'.step']['next'], $step);
    }

    protected function guessLanguage($lang = '') {
        if($lang == '' && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            $languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach($languages as $bl){
                // pour les user-agents qui livrent un code internationnal
                if(preg_match("/^([a-zA-Z]{2})(?:[-_]([a-zA-Z]{2}))?(;q=[0-9]\\.[0-9])?$/",$bl,$match)){
                    $lang = strtolower($match[1]);
                    break;
                }
            }
        }elseif(preg_match("/^([a-zA-Z]{2})(?:[-_]([a-zA-Z]{2}))?$/",$lang,$match)){
            $lang = strtolower($match[1]);
        }
        if($lang == '' || !in_array($lang, $this->config['supportedLang'])){
            $lang = 'en';
        }
        return $lang;
    }

    protected function getStepName() {
        if (isset($_REQUEST['step'])) {
            $stepname = $_REQUEST['step'];
        }
        elseif (isset($this->config['start'])) {
            $stepname = $this->config['start'];
        }
        else {
            throw new Exception('No step start in the configuration');
        }

        if (!isset($this->pages[$stepname])) {
            throw new Exception('Unknow step');
        }

        return $stepname;
    }

    protected function getNextStep($page) {
        if (is_array($page->config['next'])) {
            if (is_numeric($result))
                $nextStep = $page->config['next'][$result];
            else
                $nextStep = $page->config['next'][0];
        }
        else
            $nextStep = $page->config['next'];
        return $nextStep;
    }


    function run () {

        try {

            $this->readConfiguration();

            $this->initPath();

            $this->initPrevious();

            $this->lang = $this->guessLanguage();

            $this->stepName = $this->getStepName();

            jTplConfig::$lang = $this->lang;
            jTplConfig::$localesGetter = array($this, 'getLocale');
            jTplConfig::$cachePath = $this->tempPath;

            $page = $this->loadPage();

            if (isset($_POST['doprocess']) && $_POST['doprocess'] == "1") {
                $result = $page->process();
                if ($result !== false) {
                    header("location: ?step=".$this->getNextStep($page));
                    exit(0);
                }
            }

            $tpl = new jTpl();
            $tpl->assign($page->config);
            $tpl->assign($page->getErrors());
            $tpl->assign('appname', isset($this->config['appname'])?$this->config['appname']:'');
            $continue = $page->show($tpl);
            $content = $tpl->fetch($this->stepName.'.tpl', 'html');

            $this->showMainTemplate($page, $content, $continue);
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            require(dirname(__FILE__).'/error.php');
            exit(1);
        }
    }
    
    
    protected function loadPage() {
        $stepname = $this->stepName;
        // load the class which run the step
        require($this->pages[$stepname].$stepname.'.page.php');
        $class = $stepname.'WizPage';
        if (!class_exists($stepname.'WizPage'))
            throw new Exception ('No class for the given step');

        // load the locales
        $this->loadLocales($stepname, $stepname);

        // load the template
        $tplfile = $this->getRealPath($stepname, $stepname.'.tpl');
        if ($tplfile === false)
            throw new Exception ("No template file for the given step");

        jTplConfig::$templatePath = dirname($tplfile).'/';

        $page = new $class($this->config[$stepname.'.step'], $this->locales);
        return $page;
    }
    
    protected function showMainTemplate($page, $content, $continue) {
        $filename = "wiz_layout.tpl";
        $path = $this->getRealPath('', $filename);
        jTplConfig::$templatePath = dirname($path).'/';

        $this->loadLocales('', 'wiz_layout');

        $conf = $this->config[$this->stepName.'.step'];
        $tpl = new jTpl();
        $tpl->assign('title', $page->getLocale($page->title));
        if (isset($conf['messageHeader']))
            $tpl->assign('messageHeader', $conf['messageHeader']);
        else
            $tpl->assign('messageHeader', '');
        if (isset($conf['messageFooter']))
            $tpl->assign('messageFooter', $conf['messageFooter']);
        else
            $tpl->assign('messageFooter', '');

        $tpl->assign ('MAIN', $content);
        $tpl->assign (array_merge(array('enctype'=>''),$conf));
        $tpl->assign ('stepname', $this->stepName);
        $tpl->assign ('lang', $this->lang);
        $tpl->assign('next', ($continue && isset($conf['next'])));
        $tpl->assign('previous', isset($conf['__previous'])?$conf['__previous']:'');
        $tpl->assign('appname', isset($this->config['appname'])?$this->config['appname']:'');
    
        $tpl->display($filename, 'html');
    }

    protected function getRealPath($stepname, $fileName) {
        if ($this->customPath) {
            if (file_exists($this->customPath.$fileName))
                return $this->customPath.$fileName;
        }

        if ($stepname)
            $path = $this->pages[$stepname];
        else
            $path = dirname(__FILE__)."/";

        if (file_exists($path.$fileName))
            return $path.$fileName;

        return false;
    }

    protected function loadLocales($stepname, $prefix) {
        $localeFile = $this->getRealPath($stepname, $prefix.'.'.$this->lang.'.php');

        if ($localeFile === false && $this->lang != 'en')
            $localeFile = $this->getRealPath($stepname, $prefix.'.en.php');

        if ($localeFile === false)
            throw new Exception ("No lang file for the given step");

        require($localeFile); // load a php array $locales
        $this->locales = $locales;
    }
    
    /**
     * function for the template engine, to retrieve a localized string
     * @param string $name the key of the localized string
     * @return string the localized string or the given key if it doesn't exists
    */
    public function getLocale($name) {
        if (isset($this->locales[$name]))
            return $this->locales[$name];
        else return $name;
    }
    
}