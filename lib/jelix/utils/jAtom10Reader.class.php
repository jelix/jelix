<?php
/**
* @package     jelix
* @subpackage  utils
* @author      Sebastien Romieu
* @author   Florian Lonqueu-Brochard
* @copyright   2010 Sébastien Romieu
* @copyright   2012 Florian Lonqueu-Brochard
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'utils/jXMLFeedReader.class.php');

class jAtom10Reader extends jXMLFeedReader{

    /**
    * construct an flux with an url parameter
    */
    public function __construct($url) {
        parent::__construct($url);
    }
    
    /**
    * analyze the informations to the flux Atom
    */
    protected function analyzeInfo() {	
        $this->infos = new jAtom10Info();
        $this->infos->setFromXML($this->xml);
    }

    /**
    * analyze the items to the flux RSS
    */
    protected function analyzeItems() {
        $this->items = array();
        
        foreach($this->xml->entry as $i) {
            $item = new jAtom10Item();
            $item->setFromXML($i);
            array_push($this->items, $item);
        }
    }

}