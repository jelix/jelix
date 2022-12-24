<?php
/**
 * @package    jelix
 * @subpackage jtpl_plugin
 *
 * @author     Laurent Jouanneau
 * @copyright  2007-2022 Laurent Jouanneau
 * @contributor Christian Tritten (christian.tritten@laposte.net)
 *
 * @copyright  2007 Christian Tritten
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 *
 * @param mixed $tpl
 * @param mixed $action
 * @param mixed $actionParams
 * @param mixed $itemsTotal
 * @param mixed $offset
 * @param mixed $pageSize
 * @param mixed $paramName
 * @param mixed $displayProperties
 */

/**
 * displays page links.
 *
 * @param jTpl   $tpl               template engine
 * @param string $action            selector of the action
 * @param array  $actionParams      parameters for the action
 * @param int    $itemsTotal        number of items
 * @param int    $offset            index of the first item to display
 * @param int    $pageSize          items number in a page
 * @param string $paramName         name of the parameter in the actionParams which will content a page offset
 * @param array  $displayProperties properties for the links display
 *  */
function jtpl_function_html_pagelinks(
    $tpl,
    $action,
    $actionParams,
    $itemsTotal,
    $offset,
    $pageSize = 15,
    $paramName = 'offset',
    $displayProperties = array()
) {
    $offset = intval($offset);
    if ($offset <= 0) {
        $offset = 0;
    }

    $itemsTotal = intval($itemsTotal);

    $pageSize = intval($pageSize);
    if ($pageSize < 1) {
        $pageSize = 1;
    }

    $defaultDisplayProperties = array(
        'start-label' => '|&lt;',
        'start-class' => 'pagelinks-start',
        'prev-label' => '&lt;',
        'prev-class' => 'pagelinks-prev',
        'next-label' => '&gt;',
        'next-class' => 'pagelinks-next',
        'end-label' => '&gt;|',
        'end-class' => 'pagelinks-end',
        'area-size' => 0,
        'list-class' => 'pagelinks',
        'current-page-class' => 'pagelinks-current',
        'page-class' => '',
        'disabled-class' => 'pagelinks-disabled',
    );

    if (is_array($displayProperties) && count($displayProperties) > 0) {
        $displayProperties = array_merge($defaultDisplayProperties, $displayProperties);
    } else {
        $displayProperties = $defaultDisplayProperties;
    }

    // If there are at least two pages of results
    if ($itemsTotal > $pageSize) {
        $jUrlEngine = jApp::coord()->getUrlActionMapper();

        $urlaction = jUrl::get($action, $actionParams, jUrl::JURLACTION);

        $pages = array();

        $currentPage = 1;

        $numpage = 1;

        $prevBound = 0;

        $nextBound = 0;

        // Generates list of page offsets
        for ($curidx = 0; $curidx < $itemsTotal; $curidx += $pageSize) {
            if ($offset >= $curidx && $offset < $curidx + $pageSize) {
                $pages[$numpage] = '<li class="'.$displayProperties['current-page-class'].'"><a href="#">'.$numpage.'</a></li>';
                $prevBound = $curidx - $pageSize;
                $nextBound = $curidx + $pageSize;
                $currentPage = $numpage;
            } else {
                $urlaction->params[$paramName] = $curidx;
                $url = $jUrlEngine->create($urlaction);
                $pages[$numpage] = '<li class="'.$displayProperties['page-class'].'"><a href="'.$url->toString(true).'">'.$numpage.'</a></li>';
            }
            ++$numpage;
        }

        // Calculate start page url
        $urlaction->params[$paramName] = 0;
        $urlStartPage = $jUrlEngine->create($urlaction);

        // Calculate previous page url
        $urlaction->params[$paramName] = $prevBound;
        $urlPrevPage = $jUrlEngine->create($urlaction);

        // Calculate next page url
        $urlaction->params[$paramName] = $nextBound;
        $urlNextPage = $jUrlEngine->create($urlaction);

        // Calculate end page url
        $urlaction->params[$paramName] = (count($pages) - 1) * $pageSize;
        $urlEndPage = $jUrlEngine->create($urlaction);

        // Links display
        echo '<ul class="'.$displayProperties['list-class'].'">';

        // Start link
        if (!empty($displayProperties['start-label'])) {
            echo '<li class="'.$displayProperties['start-class'];
            if ($prevBound >= 0) {
                echo '"><a href="', $urlStartPage->toString(true), '">', $displayProperties['start-label'], '</a>';
            } else {
                echo ' '.$displayProperties['disabled-class'].'"><a href="#">',$displayProperties['start-label'], '</a>';
            }
            echo '</li>', "\n";
        }

        // Previous link
        if (!empty($displayProperties['prev-label'])) {
            echo '<li class="'.$displayProperties['prev-class'];
            if ($prevBound >= 0) {
                echo '"><a href="', $urlPrevPage->toString(true), '">', $displayProperties['prev-label'], '</a>';
            } else {
                echo ' '.$displayProperties['disabled-class'].'"><a href="#">',$displayProperties['prev-label'], '</a>';
            }
            echo '</li>', "\n";
        }

        // Pages links
        $areaSize = $displayProperties['area-size'];
        $nbPages = count($pages);
        if ($areaSize > 0 && $nbPages > $areaSize) {
            $minpage = $currentPage - floor($areaSize / 2);
            if ($minpage < 1) {
                $minpage = 1;
            }
            $maxpage = ($minpage - 1) + $areaSize;

            if ($maxpage >= $nbPages) {
                $minpage = $nbPages - $areaSize + 1;
            }
        } else {
            $minpage = 1;
            $maxpage = count($pages);
        }

        foreach ($pages as $key => $page) {
            if ($minpage <= $key && $maxpage >= $key) {
                echo $page, "\n";
            }
        }

        // Next link
        if (!empty($displayProperties['next-label'])) {
            echo '<li class="'.$displayProperties['next-class'];
            if ($nextBound < $itemsTotal) {
                echo '"><a href="', $urlNextPage->toString(true), '">', $displayProperties['next-label'], '</a>';
            } else {
                echo ' '.$displayProperties['disabled-class'].'"><a href="#">',$displayProperties['next-label'], '</a>';
            }
            echo '</li>', "\n";
        }

        // End link
        if (!empty($displayProperties['end-label'])) {
            echo '<li class="'.$displayProperties['end-class'];
            if ($nextBound < $itemsTotal) {
                echo '"><a href="', $urlEndPage->toString(true), '">', $displayProperties['end-label'], '</a>';
            } else {
                echo ' '.$displayProperties['disabled-class'].'"><a href="#">',$displayProperties['end-label'], '</a>';
            }
            echo '</li>', "\n";
        }

        echo '</ul>';
    } else {
        echo '<ul class="'.$displayProperties['list-class'].'"><li class="'.$displayProperties['current-page-class'].'">1</li></ul>';
    }
}
