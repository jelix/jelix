<?php
/**
* @package     jelix
* @subpackage  utils
* @version     $Id$
* @author      Loic Mathaud
* @contributor
* @copyright   2005-2006 Loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
class jRss20Item {
    public $title;
    
    public $link;
    
    public $description;
    
    public $author;
    
    public $category;
    
    public $comments;
    
    public $enclosure;
    
    public $guid;
    
    public $pubDate;
    
    public $source;
    
    
    function __construct($title, $link) {
        $this->title = $title;
        $this->link = $link;
    }
}

?>
