<?php
/**
* @package     jelix
* @subpackage  responsehtml_plugin
* @author      Laurent Jouanneau
* @copyright   2010-2011 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * interface for plugins for the debugbar
 * @since 1.3
 */
interface jIDebugbarPlugin {

    /**
     * @return string CSS styles
     */
    function getCss();

    /**
     * @return string Javascript code lines 
     */
    function getJavascript();

    /**
     * it should adds content or set some properties on the debugbar
     * to displays some contents.
     * @param debugbarHTMLResponsePlugin $debugbar the debugbar
     */
    function show($debugbar);

}

#if ENABLE_OPTIMIZED_SOURCE
#includephp errors.debugbar.php
#else
require_once(dirname(__FILE__).'/errors.debugbar.php');
#endif


/**
 * information for a component a debug bar
 * @since 1.3
 */
class debugbarItemInfo {

    /**
     * an id. required
     */
    public $id ='';

    /**
     * a simple text label
     */
    public $label='';

    /**
     * the HTML label to display in the debug bar
     */
    public $htmlLabel = '';

    /**
     * the HTML content of the popup if the information needs a popup
     */
    public $popupContent ='';

    /**
     * indicate if the popup should be opened or not at the startup
     */
    public $popupOpened = false;

    function __construct($id, $label, $htmlLabel='', $popupContent='', $isOpened= false) {
        $this->id = $id;
        $this->label = $label;
        $this->htmlLabel = $htmlLabel;
        $this->popupContent = $popupContent;
        $this->popupOpened = $isOpened;
    }
}

/**
 * plugin for jResponseHTML, it displays a debugbar
 * @since 1.3
 */
class debugbarHTMLResponsePlugin implements jIHTMLResponsePlugin {

    protected $response = null;

    protected $plugins = array();

    protected $tabs = array();

    // ------------- implementation of the jIHTMLResponsePlugin interface

    public function __construct(jResponse $c) {
        $this->response = $c;
        $this->plugins['errors'] = new errorsDebugbarPlugin();
    }

    /**
     * called just before the jResponseBasicHtml::doAfterActions() call
     */
    public function afterAction() {

    }

    /**
     * called just before the final output. This is the opportunity
     * to make changes before the head and body output. At this step
     * the main content (if any) is already generated.
     */
    public function beforeOutput() {
        global $gJConfig;
        $plugins = $gJConfig->debugbar['plugins'];
        $css = '';
        $js = '';
        if ($plugins) {
            $plugins = preg_split('/ *, */', $plugins);
            foreach ($plugins as $name) {
                $plugin = jApp::loadPlugin($name, 'debugbar', '.debugbar.php', $name.'DebugbarPlugin', $this);
                if ($plugin) {
                    $this->plugins[$name] = $plugin;
                }
                /*else
                    throw new jException('');*/
            }
        }

        foreach($this->plugins as $name => $plugin) {
            $css .= $plugin->getCSS();
            $js .= $plugin->getJavascript();
        }

        $this->response->addHeadContent('
<style type="text/css">
#includeraw debugbar.css
'.$css.'
</style>
<script type="text/javascript">
#includeraw debugbar.js|jspacker|escquote
'.$js.'
</script>
');
    }

    /**
     * called when the content is generated, and potentially sent, except
     * the body end tag and the html end tags. This method can output
     * directly some contents.
     */
    public function atBottom() {
        global $gJConfig;

        foreach($this->plugins as $plugin) {
            $plugin->show($this);
        }

#includerawinto LOGOJELIX jelix-dbg.png | base64
#includerawinto LOGOSTOP icons/cancel.png | base64
        ?>
<div id="jxdb">
    <div id="jxdb-header">
#expand    <a href="javascript:jxdb.selectTab('jxdb-panel-jelix');"><img src="data:image/png;base64,__LOGOJELIX__" alt="Jelix debug toolbar"/></a>
<?php foreach ($this->tabs as $item) {
    $label = ($item->htmlLabel ? $item->htmlLabel: htmlspecialchars($item->label));
    if ($item->popupContent) {
        echo '<span><a href="javascript:jxdb.selectTab(\'jxdb-panel-'.$item->id.'\');">'.$label.'</a></span>';
    }
    else
        echo '<span>'.$label.'</span>';
}
?>
#expand    <a href="javascript:jxdb.close();"><img src="data:image/png;base64,__LOGOSTOP__" alt="close" title="click to close the debug toolbar"/></a>
    </div>
    <div id="jxdb-tabpanels">
        <div id="jxdb-panel-jelix" class="jxdb-tabpanel" style="display:none">
            <ul>
                <li>Jelix version: <?php echo JELIX_VERSION?></li>
                <li>Move the debug bar <a id="jxdb-pjlx-a-right" href="javascript:jxdb.moveTo('r')">to right</a>
                <a href="javascript:jxdb.moveTo('l')" id="jxdb-pjlx-a-left">to left</a></li>
            </ul>
        </div>
        <?php
        $alreadyOpen = false;
        foreach ($this->tabs as $item) {
            if (!$item->popupContent)
                continue;
            echo '<div id="jxdb-panel-'.$item->id.'" class="jxdb-tabpanel"';
            if ($item->popupOpened && !$alreadyOpen) {
                $alreadyOpen = true;
                echo ' style="display:block"';
            }
            else
                echo ' style="display:none"';
            echo '>', $item->popupContent;
            echo '</div>';
        }?>
    </div>
</div>
        <?php
    }

    /**
     * called just before the output of an error page
     */
    public function beforeOutputError() {
        $this->beforeOutput();
        ob_start();
        $this->atBottom();
        $this->response->addContent(ob_get_clean(),true);
    }

    // ------------- methods that plugins for the debugbar can call

    /**
     * add an information in the debug bar
     * @param debugbarItemInfo $info  informations
     */
    function addInfo($info) {
        $this->tabs[] = $info;
    }
}

