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

abstract class jXMLFeedReader {

    /**
    * @var jRSS20Info or jAtom10Info
    */
    protected $infos;
    
    /**
    * @var array of jRSSItem or jAtom10Item
    */
    protected $items;
    
    /**
    * content an url
    * @var SimpleXMLElement
    */
    protected $xml;


    private $_items_analyzed = false;
    private $_infos_analyzed = false;

    /**
    * read an flux with an url parameter
    * @param string $url
    */
    public function __construct($url) {
        $stream = jHttp::quickGet($url);
        $xml = simplexml_load_string($stream);
        $this->xml = $xml;
    }

    /**
    * @return array of jXMLFeedInfo
    */
    public function getInfos() {
        if(!$this->_infos_analyzed){
            $this->analyzeInfo();
            $this->_infos_analyzed = true;
        }
        return $this->infos;
    }

    /**
    * @return array of jXMLFeedItem
    */
    public function getItems() {
        if(!$this->_items_analyzed){
            $this->analyzeItems();
            $this->_items_analyzed = true;
        }
        $this->analyzeItems();
        return $this->items;
    }
    
    public function getXML(){
        return $this->xml;
    }
    
    /**
    * analyze infos of the feed
    */
    protected abstract function analyzeInfo();
    
    /**
    * analyze items of the feed
    */
    protected abstract function analyzeItems();
    
    
}