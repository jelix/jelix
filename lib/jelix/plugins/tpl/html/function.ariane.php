<?php
/**
 * @package    jelix
 * @subpackage jtpl_plugin
 * @author     Lepeltier kévin
 * @copyright  2008 Lepeltier kévin
 * @link       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * Adds the path followed by the user
 * 
 * {ariane 5, '>>'}
 *
 * <ol id="ariane">
 *     <li value="3" class="first"><a href="./?module=main&action=page&page=home">Home</a> >> </li>
 *     <li><a href="./?module=main&action=page&page=product">Product</a> >> </li>
 *     <li><a href="./?module=main&action=page&page=home">Home</a> >> </li>
 *     <li class="end"><a href="./?module=main&action=page&page=contact">Contact</a></li>
 * </ol>
 * 
 * Rendering example :
 * 
 * Home >> Product >> Home >> Contact
 * ¯¯¯¯    ¯¯¯¯¯¯¯    ¯¯¯¯
 */

/**
 * ariane plugin : write the Ariane Wire : Shows the travels of the user
 *
 * @param jTpl $tpl template engine
 * @param array $nb the number of items displayed by the plugin
 * @param string $separator Symbol separating items
 */
function jtpl_function_html_ariane($tpl, $nb=null, $separator = '') {
    
    if( isset($_SESSION['HISTORY']) ) {
    
        echo '<ol class="history">';
        
        $leng = count($_SESSION['HISTORY']);
        $nb = ($nb !== null)? count($_SESSION['HISTORY'])-$nb:0;
        $nb = ($nb < 0)? 0:$nb;
        
        for( $i = $nb; $i < $leng; $i++ ) {
            
            $page = $_SESSION['HISTORY'][$i];
            
            echo '<li value="'.($i+1).'"'.($i==$nb?' class="first"':($i==$leng-1?' class="end"':'')).'>';
            
            if( $i!=$leng-1 )
                echo '<a href="'.jUrl::get($page['action'], $page['params'], jUrl::XMLSTRING).'" '.($page['title']!=''?'title="'.$page['title'].'"':'').'>';
                
            echo $_SESSION['HISTORY'][$i]['label'];
            
            if( $i!=$leng-1 )
                echo '</a>';
                
            echo ($i==$leng-1?'':$separator).'</li>';
        }
        
        echo '</ol>';
    }
}
?>