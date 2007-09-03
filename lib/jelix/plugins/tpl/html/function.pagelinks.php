<?php
/**
* @package    jelix
* @subpackage jtpl_plugin
* @author     Jouanneau Laurent
* @copyright  2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * displays page links
 *
 * @param jTpl $tpl template engine
 * @param string $action selector of the action 
 * @param array $actionParams parameters for the action
 * @param integer $pageSize  items number in a page
 * @param integer $itemsTotal number of items
 * @param integer $offset  index of the first item to display
 * @param string $paramName name of the parameter in the actionParams which will content a page offset
 */
function jtpl_function_html_pagelinks($tpl, $action, $actionParams, $itemsTotal, $offset, $pageSize=15, $paramName='offset' )
{
    $offset=intval($offset);
    if($offset<=0)
        $offset=0;

    $itemsTotal=intval($itemsTotal);

    $pageSize=intval($pageSize);
    if ($pageSize<1)
        $pageSize=1;

    $urlaction = jUrl::get($action, $actionParams, jUrl::JURLACTION);

    $pages = array(); // tableau des indices de toutes les pages

    $current_page=1;
    $pages[1]=0;

    $numpage=1;
    //$nextpage=0;
    //$prevpage=0;

    // generates list of page offsets
    for($curidx = 0; $curidx < $itemsTotal; $curidx += $pageSize){
        if( $offset >= $curidx && $offset < $curidx + $pageSize){
            //$current_page=$numpage;
            //$nextpage=$numpage+1;
            //$prevpage=$numpage-1;
            echo ' <strong>'.$numpage.'</strong>';
        }else{
            $urlaction->params[$paramName] = $curidx;
            $url = jUrl::getEngine()->create($urlaction);
            echo ' <a href="'.$url->toString(true).'">'.$numpage.'</a>';
            //$pages[$numpage++]=$curidx;
        }
        $numpage++;
    }

    /*
    if(isset($pages[$nextpage]))
        $next_page_idx = $pages[$nextpage];
    else
        $next_page_idx = -1;

    if($current_page > 1)
        $prev_page_idx = $offset - $page_size;
    else
        $prev_page_idx = -1;
    */
}

?>