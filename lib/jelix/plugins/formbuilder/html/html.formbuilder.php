<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @contributor Julien Issler, Dominique Papin, Olivier Demah
* @copyright   2006-2012 Laurent Jouanneau
* @copyright   2008 Julien Issler, 2008 Dominique Papin
* @copyright   2009 Olivier Demah
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * HTML form builder
 * @package     jelix
 * @subpackage  jelix-plugins
 */
class htmlFormsBuilder extends \jelix\forms\Builder\HtmlBuilder {

    protected $jFormsJsVarName = 'jFormsJQ';

    public function outputMetaContent($t) {

        $resp= jApp::coord()->response;
        if($resp === null || $resp->getType() !='html'){
            return;
        }
        $confUrlEngine = &jApp::config()->urlengine;
        $confHtmlEditor = &jApp::config()->htmleditors;
        $confDate = &jApp::config()->datepickers;
        $confWikiEditor = &jApp::config()->wikieditors;
        $www = $confUrlEngine['jelixWWWPath'];
        $jq = $confUrlEngine['jqueryPath'];
        $bp = $confUrlEngine['basePath'];
        $resp->addJSLink($jq.'jquery.js');
        $resp->addJSLink($jq.'include/jquery.include.js');
        $resp->addJSLink($www.'js/jforms_jquery.js');
        $resp->addCSSLink($www.'design/jform.css');
        foreach($t->_vars as $k=>$v){
            if(!$v instanceof jFormsBase)
                continue;
            foreach($v->getHtmlEditors() as $ed) {
                if(isset($confHtmlEditor[$ed->config.'.engine.file'])){
                    if(is_array($confHtmlEditor[$ed->config.'.engine.file'])){
                        foreach($confHtmlEditor[$ed->config.'.engine.file'] as $url) {
                            $resp->addJSLink($bp.$url);
                        }
                    }else
                        $resp->addJSLink($bp.$confHtmlEditor[$ed->config.'.engine.file']);
                }
                
                if(isset($confHtmlEditor[$ed->config.'.config']))
                    $resp->addJSLink($bp.$confHtmlEditor[$ed->config.'.config']);

                $skin = $ed->config.'.skin.'.$ed->skin;

                if(isset($confHtmlEditor[$skin]) && $confHtmlEditor[$skin] != '')
                    $resp->addCSSLink($bp.$confHtmlEditor[$skin]);
            }

            $datepicker_default_config = jApp::config()->forms['datepicker'];
            
            foreach($v->getControls() as $ctrl){
                if($ctrl instanceof jFormsControlDate || get_class($ctrl->datatype) == 'jDatatypeDate' || get_class($ctrl->datatype) == 'jDatatypeLocaleDate'){
                    $config = isset($ctrl->datepickerConfig)?$ctrl->datepickerConfig:$datepicker_default_config;
                    $resp->addJSLink($bp.$confDate[$config]);
                }
            }

            foreach($v->getWikiEditors() as $ed) {
                if(isset($confWikiEditor[$ed->config.'.engine.file']))
                    $resp->addJSLink($bp.$confWikiEditor[$ed->config.'.engine.file']);
                if(isset($confWikiEditor[$ed->config.'.config.path'])) {
                    $p = $bp.$confWikiEditor[$ed->config.'.config.path'];
                    $resp->addJSLink($p.jApp::config()->locale.'.js');
                    $resp->addCSSLink($p.'style.css');
                }
                if(isset($confWikiEditor[$ed->config.'.skin']))
                    $resp->addCSSLink($bp.$confWikiEditor[$ed->config.'.skin']);
            }
        }
    }


}
