<?php
/**
 * @package     jelix
 * @subpackage  responsehtml_plugin
 *
 * @author      Laurent Jouanneau
 * @contributor Julien Issler
 *
 * @copyright   2010-2012 Laurent Jouanneau
 * @copyright   2011 Julien Issler
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * interface for plugins for the debugbar.
 *
 * @since 1.3
 */
interface jIDebugbarPlugin
{
    /**
     * @return string CSS styles
     */
    public function getCss();

    /**
     * @return string Javascript code lines
     */
    public function getJavascript();

    /**
     * it should adds content or set some properties on the debugbar
     * to displays some contents.
     *
     * @param debugbarHTMLResponsePlugin $debugbar the debugbar
     */
    public function show($debugbar);
}

require_once __DIR__.'/errors.debugbar.php';

/**
 * information for a component a debug bar.
 *
 * @since 1.3
 */
class debugbarItemInfo
{
    /**
     * an id. required.
     */
    public $id = '';

    /**
     * a simple text label.
     */
    public $label = '';

    /**
     * the HTML label to display in the debug bar.
     */
    public $htmlLabel = '';

    /**
     * the HTML content of the popup if the information needs a popup.
     */
    public $popupContent = '';

    /**
     * indicate if the popup should be opened or not at the startup.
     */
    public $popupOpened = false;

    /**
     * @param string $id           an id
     * @param string $label        a simple text label
     * @param string $htmlLabel    the HTML label to display in the debug bar
     * @param string $popupContent the HTML content of the popup if the information needs a popup
     * @param bool   $isOpened     indicate if the popup should be opened or not at the startup
     */
    public function __construct($id, $label, $htmlLabel = '', $popupContent = '', $isOpened = false)
    {
        $this->id = $id;
        $this->label = $label;
        $this->htmlLabel = $htmlLabel;
        $this->popupContent = $popupContent;
        $this->popupOpened = $isOpened;
    }
}

/**
 * plugin for jResponseHTML, it displays a debugbar.
 *
 * @since 1.3
 */
class debugbarHTMLResponsePlugin implements jIHTMLResponsePlugin
{
    protected $response;

    protected $plugins = array();

    protected $tabs = array();

    // ------------- implementation of the jIHTMLResponsePlugin interface

    public function __construct(jResponse $c)
    {
        $this->response = $c;
        $this->plugins['errors'] = new errorsDebugbarPlugin();
    }

    /**
     * called just before the jResponseBasicHtml::doAfterActions() call.
     */
    public function afterAction()
    {
    }

    /**
     * called just before the final output. This is the opportunity
     * to make changes before the head and body output. At this step
     * the main content (if any) is already generated.
     */
    public function beforeOutput()
    {
        // load plugins
        $plugins = jApp::config()->debugbar['plugins'];
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
    }

    /**
     * called when the content is generated, and potentially sent, except
     * the body end tag and the html end tags. This method can output
     * directly some contents.
     */
    public function atBottom()
    {
        $css = file_get_contents(__DIR__.'/debugbar.css');
        $js = '';
        foreach ($this->plugins as $name => $plugin) {
            $css .= $plugin->getCSS();
            $js .= $plugin->getJavascript();
        }
        echo "<style type=\"text/css\">\n";

        $LOGOBULLETPLUS = base64_encode(file_get_contents(__DIR__.'/icons/bullet_toggle_plus.png'));
        $LOGOBULLETMINUS = base64_encode(file_get_contents(__DIR__.'/icons/bullet_toggle_minus.png'));
        $css .=  "ul.jxdb-list li h5 a {background-image: url('data:image/png;base64,".$LOGOBULLETPLUS."');}\n";
        $css .=  "ul.jxdb-list li.jxdb-opened  h5 a {background-image: url('data:image/png;base64,".$LOGOBULLETMINUS."');}\n";
        echo preg_replace("/(\\s+)/", " ", $css)."\n</style>\n";

        echo "<script type=\"text/javascript\">\n//<![CDATA[\n";

        require __DIR__.'/debugbar.js';
        echo $js."\n//]]>\n</script>";

        foreach ($this->plugins as $plugin) {
            $plugin->show($this);
        }

        if (isset($_COOKIE['jxdebugbarpos'])) {
            $class = 'jxdb-position-'.$_COOKIE['jxdebugbarpos'];
        } else {
            $class = 'jxdb-position-'.(jApp::config()->debugbar['defaultPosition'] == 'left' ? 'l' : 'r');
        } ?>
<div id="jxdb" class="<?php echo $class; ?>">
    <div id="jxdb-header">
        <?php

        $LOGOJELIX = base64_encode(file_get_contents(__DIR__.'/jelix-dbg.png'));
        $LOGOSTOP = base64_encode(file_get_contents(__DIR__.'/icons/cancel.png'));
        echo "<a href=\"javascript:jxdb.selectTab('jxdb-panel-jelix');\"><img src=\"data:image/png;base64,".$LOGOJELIX."\" alt=\"Jelix debug toolbar\"/></a>\n";
        foreach ($this->tabs as $item) {
            $label = ($item->htmlLabel ? $item->htmlLabel : htmlspecialchars($item->label));
            if ($item->popupContent) {
                echo '<span><a href="javascript:jxdb.selectTab(\'jxdb-panel-'.$item->id.'\');">'.$label.'</a></span>';
            } else {
                echo '<span>'.$label.'</span>';
            }
        }

        echo '<a href="javascript:jxdb.close();"><img src="data:image/png;base64,'.$LOGOSTOP."\" alt=\"close\" title=\"click to close the debug toolbar\"/></a>\n"; ?>
    </div>
    <div id="jxdb-tabpanels">
        <div id="jxdb-panel-jelix" class="jxdb-tabpanel">
            <ul>
                <li>Jelix version: <?php echo jFramework::version(); ?></li>
                <li>Move the debug bar <a id="jxdb-pjlx-a-right" href="javascript:jxdb.moveTo('r')">to right</a>
                <a href="javascript:jxdb.moveTo('c')" id="jxdb-pjlx-a-center">to center</a>
                <a href="javascript:jxdb.moveTo('l')" id="jxdb-pjlx-a-left">to left</a>
                </li>
                <li>To remove it definitively, deactivate the plugin "debugbar"<br/> into the configuration</li>
            </ul>
        </div>
        <?php
        $alreadyOpen = false;
        foreach ($this->tabs as $item) {
            if (!$item->popupContent) {
                continue;
            }
            echo '<div id="jxdb-panel-'.$item->id.'" class="jxdb-tabpanel"';
            if ($item->popupOpened && !$alreadyOpen) {
                $alreadyOpen = true;
                echo ' class="jxdb-tabpanel-displayed"';
            }
            echo '>', $item->popupContent;
            echo '</div>';
        } ?>
    </div>
</div>
        <?php
    }

    /**
     * called just before the output of an error page.
     */
    public function beforeOutputError()
    {
        $this->beforeOutput();
        ob_start();
        $this->atBottom();
        $this->response->addContent(ob_get_clean(), true);
    }

    // ------------- methods that plugins for the debugbar can call

    /**
     * add an information in the debug bar.
     *
     * @param debugbarItemInfo $info informations
     */
    public function addInfo($info)
    {
        $this->tabs[] = $info;
    }

    /**
     * returns html formated stack trace.
     *
     * @param array $trace
     *
     * @return string
     */
    public function formatTrace($trace)
    {
        $html = '<table>';
        foreach ($trace as $k => $t) {
            if (isset($t['file'])) {
                $file = $t['file'];
            } else {
                $file = '[php]';
            }
            $html .= '<tr><td>'.$k.'</td><td>'.(isset($t['class']) ? $t['class'].$t['type'] : '').$t['function'].'()</td>';
            $html .= '<td>'.($file).'</td><td>'.(isset($t['line']) ? $t['line'] : '').'</td></tr>';
        }
        $html .= '</table>';

        return $html;
    }
}
