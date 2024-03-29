<?php
/**
 * @package     jelix
 * @subpackage  core_response
 *
 * @author      Laurent Jouanneau
 * @contributor Yann, Dominique Papin
 * @contributor Warren Seine, Alexis Métaireau, Julien Issler, Olivier Demah, Brice Tence
 *
 * @copyright   2005-2023 Laurent Jouanneau, 2006 Yann, 2007 Dominique Papin
 * @copyright   2008 Warren Seine, Alexis Métaireau
 * @copyright   2009 Julien Issler, Olivier Demah
 * @copyright   2010 Brice Tence
 *              few lines of code are copyrighted CopixTeam http://www.copix.org
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
require_once __DIR__.'/jResponseBasicHtml.class.php';

/**
 * HTML5 response.
 *
 * @package  jelix
 * @subpackage core_response
 */
class jResponseHtml extends jResponseBasicHtml
{
    /**
     * jresponse id.
     *
     * @var string
     */
    protected $_type = 'html';

    /**
     * Title of the document.
     *
     * @var string
     */
    public $title = '';

    /**
     * favicon url linked to the document.
     *
     * @var string
     *
     * @since 1.0b2
     */
    public $favicon = '';

    /**
     * The template engine used to generate the body content.
     *
     * @var jTpl
     */
    public $body;

    /**
     * selector of the main template file
     * This template should contains the body content, and is used by the $body template engine.
     *
     * @var string
     */
    public $bodyTpl = '';

    /**
     * Selector of the template used when there are some errors, instead of $bodyTpl.
     *
     * @var string
     */
    public $bodyErrorTpl = '';

    /**
     * body attributes
     * These attributes are written on the body tag.
     *
     * @var array
     */
    public $bodyTagAttributes = array();

    /**
     * html attributes
     * These attributes are written on the html tag.
     *
     * @var array
     */
    public $htmlTagAttributes = array();

    /**
     * @var string indicate the value for the X-UA-Compatible meta element, which
     *             indicate the compatiblity mode of IE. Exemple: "IE=edge"
     *             In future version, default will be "IE=edge".
     *
     * @since 1.6.17
     */
    public $IECompatibilityMode = '';

    /**
     * @var string the content of the viewport meta element
     *
     * @since 1.6.17
     */
    public $metaViewport = '';

    /**
     * list of css stylesheet.
     *
     * @var array[] key = url, value=link attributes
     */
    protected $_CSSLink = array();

    /**
     * list of CSS code.
     *
     * @var string[]
     */
    protected $_Styles = array();

    /**
     * list of js script.
     *
     * @var array[] key = url, value=link attributes
     */
    protected $_JSLink = array();

    /**
     * inline js code to insert before js links.
     *
     * @var string[] list of js source code
     */
    protected $_JSCodeBefore = array();

    /**
     * inline js code to insert after js links.
     *
     * @var string[] list of js source code
     */
    protected $_JSCode = array();

    /**
     * list of keywords to add into a meta keyword tag.
     *
     * @var string[]
     */
    protected $_MetaKeywords = array();

    /**
     * list of descriptions to add into a meta description tag.
     *
     * @var string[]
     */
    protected $_MetaDescription = array();

    /**
     * content of the meta author tag.
     *
     * @var string
     */
    protected $_MetaAuthor = '';

    /**
     * content of the meta generator tag.
     *
     * @var string
     */
    protected $_MetaGenerator = '';

    /**
     * @var bool false if it should be output <meta charset=""/> or true
     *           for the default old behavior : <meta content="text/html; charset=""../>
     *
     * @since 1.6.17
     */
    protected $_MetaOldContentType = false;

    /**
     * @var array[] list of arrays containing attributes for each meta elements
     *
     * @since 1.6.17
     */
    protected $_Meta = array();

    /**
     * list of information to generate link tags.
     *
     * @var array keys are the href value, valu is an array ('rel','type','title')
     */
    protected $_Link = array();

    /**
     * the end tag to finish tags. it is different if we are in XHTML mode or not.
     *
     * @var string
     */
    protected $_endTag = "/>\n";

    /**
     * @var \Jelix\WebAssets\WebAssetsSelection
     */
    protected $webAssetsSelection;

    /**
     * constructor;
     * setup the charset, the lang, the template engine.
     */
    public function __construct()
    {
        $this->body = new jTpl();
        $this->webAssetsSelection = new \Jelix\WebAssets\WebAssetsSelection();
        parent::__construct();
    }

    /**
     * output the html content.
     *
     * @return bool true if the generated content is ok
     */
    public function output()
    {
        if ($this->_outputOnlyHeaders) {
            $this->sendHttpHeaders();

            return true;
        }

        foreach ($this->plugins as $name => $plugin) {
            $plugin->afterAction();
        }

        $this->doAfterActions();

        $this->setContentType();
        // let's get the main content for the body
        // we don't output yet <head> and other things, to have the
        // opportunity for any components called during the output,
        // to add things in the <head>
        if ($this->bodyTpl != '') {
            $this->body->meta($this->bodyTpl);
            $content = $this->body->fetch($this->bodyTpl, 'html', true, false);
        } else {
            $content = '';
        }

        // retrieve errors messages and log messages
        jLog::outputLog($this);

        foreach ($this->plugins as $name => $plugin) {
            $plugin->beforeOutput();
        }

        $this->webAssetsSelection->compute(
            jApp::config(),
            jApp::config()->webassets['useCollection'],
            jApp::urlBasePath(),
            array(
                '$lang' => jLocale::getCurrentLang(),
                '$locale' => jLocale::getCurrentLocale(),
                '$theme' => rtrim('themes/'.jApp::config()->theme, '/'),
                '$jelix' => rtrim(jApp::urlJelixWWWPath(), '/'),
            )
        );

        // now let's output the html content
        $this->sendHttpHeaders();
        $this->outputDoctype();
        $this->outputHtmlHeader();
        echo '<body ';
        foreach ($this->bodyTagAttributes as $attr => $value) {
            echo $attr,'="', htmlspecialchars($value, ENT_COMPAT | ENT_SUBSTITUTE),'" ';
        }
        echo ">\n";
        echo implode("\n", $this->_bodyTop);
        echo $content;
        echo implode("\n", $this->_bodyBottom);

        foreach ($this->plugins as $name => $plugin) {
            $plugin->atBottom();
        }

        echo '</body></html>';

        return true;
    }

    /**
     * set the title of the page.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Append the revision parameter to the given url string.
     *
     * @param string $url
     *
     * @return string
     */
    public function appendRevisionToUrl($url)
    {
        $revisionParam = jApp::config()->urlengine['assetsRevQueryUrl'];
        if ($revisionParam != '') {
            $url .= (strpos($url, '?') === false) ? '?' : '&';
            $url .= $revisionParam;
        }

        return $url;
    }

    /**
     * Add the revision parameter to the given list of query parameters.
     *
     * @param array $parameters list of query parameters
     */
    public function appendRevisionToQueryParameters(&$parameters)
    {
        $revision = jApp::config()->urlengine['assetsRevision'];
        if ($revision != '') {
            $p = jApp::config()->urlengine['assetsRevisionParameter'];
            $parameters[$p] = $revision;
        }
    }

    /**
     * add a generic link to the head.
     *
     * @param string $href  url of the link
     * @param string $rel   relation name
     * @param string $type  mime type of the ressource
     * @param string $title
     */
    public function addLink($href, $rel, $type = '', $title = '')
    {
        $this->_Link[$href] = array($rel, $type, $title);
    }

    /**
     * add a link to a javascript script in the document head.
     *
     * $forIe parameter exists since 1.0b2
     *
     * @param string $src    the link
     * @param array  $params additionals attributes for the script tag
     */
    public function addJSLink($src, $params = array())
    {
        if (!preg_match('!^https?://!', $src)) {
            $newSrc = $this->appendRevisionToUrl($src);

            if ($newSrc != $src) {
                // if the resource has already been added without the revision let's remove it
                unset($this->_JSLink[$src]);
                $src = $newSrc;
            }
        }

        if (!isset($this->_JSLink[$src])) {
            $this->_JSLink[$src] = $params;
        }
    }

    /**
     *  add a link to a javascript script stored into modules.
     *
     * @param string $module the module where file is stored
     * @param mixed  $src    the relative path inside the {module}/www/ directory
     * @param array  $params additionnal parameters for the generated tag (a media attribute for stylesheet for example)
     */
    public function addJSLinkModule($module, $src, $params = array())
    {
        $jurlParams = array('targetmodule' => $module, 'file' => $src);
        $this->appendRevisionToQueryParameters($jurlParams);
        $src = jUrl::get('jelix~www:getfile', $jurlParams);
        if (!isset($this->_JSLink[$src])) {
            $this->_JSLink[$src] = $params;
        }
    }

    /**
     * returns all JS links.
     *
     * @return array key = url, value=link attributes
     */
    public function getJSLinks()
    {
        return $this->_JSLink;
    }

    /**
     * set all JS links.
     *
     * @param array $list key = url, value=link attributes
     */
    public function setJSLinks($list)
    {
        $this->_JSLink = $list;
    }

    /**
     * returns all CSS links.
     *
     * @return array key = url, value=link attributes
     */
    public function getCSSLinks()
    {
        return $this->_CSSLink;
    }

    /**
     * set all CSS links.
     *
     * @param array $list key = url, value=link attributes
     */
    public function setCSSLinks($list)
    {
        $this->_CSSLink = $list;
    }

    /**
     * add a link to a css stylesheet in the document head.
     *
     * $forIe parameter exists since 1.0b2
     *
     * @param string $src    the link
     * @param array  $params additionnals attributes for the link tag
     */
    public function addCSSLink($src, $params = array())
    {

        if (!preg_match('!^https?://!', $src)) {
            $newSrc = $this->appendRevisionToUrl($src);

            if ($newSrc != $src) {
                // if the resource has already been added without the revision let's remove it
                unset($this->_CSSLink[$src]);
                $src = $newSrc;
            }
        }

        if (!isset($this->_CSSLink[$src])) {
            $this->_CSSLink[$src] = $params;
        }
    }

    /**
     *  add a link to a css stylesheet  stored into modules.
     *
     * @param string $module the module where file is stored
     * @param mixed  $src    the relative path inside the {module}/www/ directory
     * @params array $params additionnal parameters for the generated tag (a media attribute for stylesheet for example)
     *
     * @param mixed $params
     */
    public function addCSSLinkModule($module, $src, $params = array())
    {
        $jurlParams = array('targetmodule' => $module, 'file' => $src);
        $this->appendRevisionToQueryParameters($jurlParams);
        $src = jUrl::get('jelix~www:getfile', $jurlParams);
        if (!isset($this->_CSSLink[$src])) {
            $this->_CSSLink[$src] = $params;
        }
    }

    /**
     *  add a link to a csstheme stylesheet  stored into modules.
     *
     * @param string $module the module where file is stored
     * @param mixed  $src    the relative path inside the {module}/www/themes/{currenttheme}/ directory
     * @params array $params additionnal parameters for the generated tag (a media attribute for stylesheet for example)
     *
     * @param mixed $params
     */
    public function addCSSThemeLinkModule($module, $src, $params = array())
    {
        $file = 'themes/'.jApp::config()->theme.'/'.$src;
        $jurlParams = array('targetmodule' => $module, 'file' => $file);
        $this->appendRevisionToQueryParameters($jurlParams);
        $src = jUrl::get('jelix~www:getfile', $jurlParams);
        if (!isset($this->_CSSLink[$src])) {
            $this->_CSSLink[$src] = $params;
        }
    }

    /**
     * add inline css style into the document (inside a <style> tag).
     *
     * @param string $selector css selector
     * @param string $def      css properties for the given selector
     */
    public function addStyle($selector, $def = null)
    {
        if (!isset($this->_Styles[$selector])) {
            $this->_Styles[$selector] = $def;
        }
    }

    /**
     * set attributes on the html tag.
     *
     * @param array $attrArray an associative array of attributes and their values
     */
    public function setHtmlAttributes($attrArray)
    {
        if (is_array($attrArray)) {
            foreach ($attrArray as $attr => $value) {
                if (!is_numeric($attr)) {
                    $this->htmlTagAttributes[$attr] = $value;
                }
            }
        }
    }

    /**
     * set attributes on the body tag.
     *
     * @param array $attrArray an associative array of attributes and their values
     */
    public function setBodyAttributes($attrArray)
    {
        if (is_array($attrArray)) {
            foreach ($attrArray as $attr => $value) {
                if (!is_numeric($attr)) {
                    $this->bodyTagAttributes[$attr] = $value;
                }
            }
        }
    }

    /**
     * add inline javascript code (inside a <script> tag).
     *
     * @param string $code   javascript source code
     * @param bool   $before will insert the code before js links if true
     */
    public function addJSCode($code, $before = false)
    {
        if ($before) {
            $this->_JSCodeBefore[] = $code;
        } else {
            $this->_JSCode[] = $code;
        }
    }

    /**
     * adds a web assets group.
     *
     * @param string $assetGroup
     */
    public function addAssets($assetGroup)
    {
        $this->webAssetsSelection->addAssetsGroup($assetGroup);
    }

    /**
     * add some keywords in a keywords meta tag.
     *
     * @author Yann
     *
     * @param string $content keywords
     *
     * @since 1.0b1
     */
    public function addMetaKeywords($content)
    {
        $this->_MetaKeywords[] = $content;
    }

    /**
     * add a description in a description meta tag.
     *
     * @author Yann
     *
     * @param string $content a description
     *
     * @since 1.0b1
     */
    public function addMetaDescription($content)
    {
        $this->_MetaDescription[] = $content;
    }

    /**
     * add author(s) in a author meta tag.
     *
     * @author Olivier Demah
     *
     * @param string $content author(s)
     *
     * @since 1.2
     */
    public function addMetaAuthor($content)
    {
        $this->_MetaAuthor = $content;
    }

    /**
     * add generator a generator meta tag.
     *
     * @author Olivier Demah
     *
     * @param string $content generator
     *
     * @since 1.2
     */
    public function addMetaGenerator($content)
    {
        $this->_MetaGenerator = $content;
    }

    /**
     * add a meta element.
     *
     * @param array  list of attribute and their values to set on a new meta element
     * @param mixed $params
     */
    public function addMeta($params)
    {
        $this->_Meta[] = $params;
    }

    /**
     * generate the doctype. You can override it if you want to have your own doctype, like XHTML+MATHML.
     *
     * @since 1.1
     */
    protected function outputDoctype()
    {
        echo '<!DOCTYPE HTML>', "\n";
        $locale = str_replace('_', '-', $this->_locale);
        $this->htmlTagAttributes['lang'] = $locale;
        if ($this->_isXhtml) {
            echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="',$locale,'" ';
        } else {
            echo '<html ';
        }

        foreach ($this->htmlTagAttributes as $attr => $value) {
            echo $attr,'="', htmlspecialchars($value, ENT_COMPAT | ENT_SUBSTITUTE),'" ';
        }

        echo ">\n";
    }

    protected function outputJsScriptTag($fileUrl, $scriptParams)
    {
        $params = '';
        foreach ($scriptParams as $param_name => $param_value) {
            if (is_bool($param_value)) {
                if ($param_value === true) {
                    $params .= $param_name.' ';
                }
            } else {
                $params .= $param_name.'="'.htmlspecialchars($param_value).'" ';
            }
        }
        if (!isset($scriptParams['type'])) {
            $params = 'type="text/javascript" '.$params;
        }
        echo '<script src="',htmlspecialchars($fileUrl),'" ',$params,'></script>',"\n";
    }

    protected function outputCssLinkTag($fileUrl, $cssParams)
    {
        $params = '';
        foreach ($cssParams as $param_name => $param_value) {
            if (is_bool($param_value)) {
                if ($param_value === true) {
                    $params .= $param_name.' ';
                }
            } else {
                $params .= $param_name.'="'.htmlspecialchars($param_value).'" ';
            }
        }

        if (!isset($cssParams['rel'])) {
            $params .= 'rel="stylesheet" ';
        }
        echo '<link type="text/css" href="',htmlspecialchars($fileUrl),'" ',$params,$this->_endTag,"\n";
    }

    protected function outputIconLinkTag($fileUrl, $iconParams)
    {
        $params = '';
        foreach ($iconParams as $param_name => $param_value) {
            if (is_bool($param_value)) {
                if ($param_value === true) {
                    $params .= $param_name.' ';
                }
            } else {
                $params .= $param_name.'="'.htmlspecialchars($param_value).'" ';
            }
        }

        if (!isset($iconParams['rel'])) {
            $params .= 'rel="icon" ';
        }
        echo '<link href="',htmlspecialchars($fileUrl),'" ',$params,$this->_endTag,"\n";
    }

    /**
     * @param string[] $params list of attributes to add to a meta element
     *
     * @since 1.6.17
     */
    protected function outputMetaTag($params)
    {
        $html = '';
        foreach ($params as $param_name => $param_value) {
            $html .= $param_name.'="'.htmlspecialchars($param_value).'" ';
        }

        echo '<meta ', $html, $this->_endTag;
    }

    /**
     * generate the content of the <head> content.
     */
    protected function outputHtmlHeader()
    {
        echo "<head>\n";
        echo implode("\n", $this->_headTop);
        if ($this->_isXhtml && $this->xhtmlContentType && strstr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml')) {
            echo '<meta content="application/xhtml+xml; charset=UTF-8" http-equiv="content-type"'.$this->_endTag;
        } elseif (!$this->_MetaOldContentType) {
            echo '<meta charset="UTF-8" '.$this->_endTag;
        } else {
            echo '<meta content="text/html; charset=UTF-8" http-equiv="content-type"'.$this->_endTag;
        }

        if ($this->IECompatibilityMode) {
            echo '<meta http-equiv="X-UA-Compatible" content="'.$this->IECompatibilityMode.'"'.$this->_endTag;
        }

        if ($this->metaViewport) {
            echo '<meta name="viewport" content="'.$this->metaViewport.'"'.$this->_endTag;
        }

        // Meta link
        foreach ($this->_Meta as $params) {
            $this->outputMetaTag($params);
        }

        echo '<title>'.htmlspecialchars($this->title)."</title>\n";

        if (!empty($this->_MetaDescription)) {
            // meta description
            $description = implode(' ', $this->_MetaDescription);
            echo '<meta name="description" content="'.htmlspecialchars($description).'" '.$this->_endTag;
        }

        if (!empty($this->_MetaKeywords)) {
            // meta description
            $keywords = implode(',', $this->_MetaKeywords);
            $this->outputMetaTag(array('name' => 'keywords', 'content' => $keywords));
        }
        if (!empty($this->_MetaGenerator)) {
            $this->outputMetaTag(array('name' => 'generator', 'content' => $this->_MetaGenerator));
        }
        if (!empty($this->_MetaAuthor)) {
            $this->outputMetaTag(array('name' => 'author', 'content' => $this->_MetaAuthor));
        }

        // css link
        foreach ($this->webAssetsSelection->getCssLinks() as $src) {
            $this->outputCssLinkTag($src[0], $src[1]);
            if (isset($this->_CSSLink[$src[0]])) {
                unset($this->_CSSLink[$src[0]]);
            }
        }
        foreach ($this->_CSSLink as $src => $params) {
            $this->outputCssLinkTag($src, $params);
        }

        if ($this->favicon != '') {
            $fav = htmlspecialchars($this->favicon);
            echo '<link rel="icon" type="image/x-icon" href="',$fav,'" ',$this->_endTag;
            echo '<link rel="shortcut icon" type="image/x-icon" href="',$fav,'" ',$this->_endTag;
        }

        foreach ( $this->webAssetsSelection->getIconLinks() as $src => $params) {
            $this->outputIconLinkTag($src, $params);
        }

        // others links
        foreach ($this->_Link as $href => $params) {
            $more = array();
            if (!empty($params[1])) {
                $more[] = 'type="'.$params[1].'"';
            }
            if (!empty($params[2])) {
                $more[] = 'title = "'.htmlspecialchars($params[2]).'"';
            }
            echo '<link rel="',$params[0],'" href="',htmlspecialchars($href),'" ',implode(' ', $more),$this->_endTag;
        }

        // js code
        if (count($this->_JSCodeBefore)) {
            echo '<script type="text/javascript">
// <![CDATA[
 '.implode("\n", $this->_JSCodeBefore).'
// ]]>
</script>';
        }

        // js link
        foreach ($this->webAssetsSelection->getJsLinks() as $jsUrl) {
            $this->outputJsScriptTag($jsUrl[0], $jsUrl[1]);
            if (isset($this->_JSLink[$jsUrl[0]])) {
                unset($this->_JSLink[$jsUrl[0]]);
            }
        }
        foreach ($this->_JSLink as $src => $params) {
            $this->outputJsScriptTag($src, $params);
        }

        // styles
        if (count($this->_Styles)) {
            echo "<style type=\"text/css\">\n";
            foreach ($this->_Styles as $selector => $value) {
                if (strlen($value)) {
                    // there is a key/value
                    echo $selector.' {'.$value."}\n";
                } else {
                    // no value, it could be simply a command
                    //for example @import something, ...
                    echo $selector, "\n";
                }
            }
            echo "\n </style>\n";
        }
        // js code
        if (count($this->_JSCode)) {
            echo '<script type="text/javascript">
// <![CDATA[
 '.implode("\n", $this->_JSCode).'
// ]]>
</script>';
        }
        echo implode("\n", $this->_headBottom), '</head>';
    }

    /**
     * used to erase some head properties.
     *
     * @param array $what list of one or many of this strings : 'CSSLink', 'CSSIELink', 'Styles', 'JSLink', 'JSIELink', 'JSCode', 'Others','MetaKeywords','MetaDescription'. If null, it cleans all values.
     */
    public function clearHtmlHeader($what = null)
    {
        $cleanable = array('CSSLink', 'CSSIELink', 'Styles', 'JSLink', 'JSIELink', 'JSCode',
            'Others', 'MetaKeywords', 'MetaDescription', 'Meta', 'MetaAuthor', 'MetaGenerator', );
        if ($what == null) {
            $what = $cleanable;
        }
        foreach ($what as $elem) {
            if (in_array($elem, $cleanable)) {
                $name = '_'.$elem;
                $this->{$name} = array();
            }
        }
    }

    /**
     * change the type of html for the output.
     *
     * @param bool $xhtml true if you want xhtml, false if you want html
     */
    public function setXhtmlOutput($xhtml = true)
    {
        $this->_isXhtml = $xhtml;
        if ($xhtml) {
            $this->_endTag = "/>\n";
        } else {
            $this->_endTag = ">\n";
        }
    }

    /**
     * return the end of a html tag : "/>" or ">", depending if it will generate xhtml or html.
     *
     * @return string
     */
    public function endTag()
    {
        return $this->_endTag;
    }
}
