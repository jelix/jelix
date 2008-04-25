<?php
/**
* @package    jelix
* @subpackage coord_plugin
* @author     Lepeltier Kévin
* @copyright  2008 Lepeltier Kévin
*
* The plugin History is a plugin coord,
* it records the action / settings made during a session and allows for reuse.
*
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
class historyCoordPlugin implements jICoordPlugin {
    
    public $config;
    
    function __construct( $conf ){
        $this->config = $conf;
        
        if( $this->config['time'] && !isset($_SESSION[$this->config['session_time_name']]) )
            $_SESSION[$this->config['session_time_name']] = microtime(true);
        
    }
    
    public function beforeAction ($params) {
        
        if( !empty($params['history.add']) && $params['history.add'] ) {
            if( !isset($_SESSION[$this->config['session_name']]) )
                $_SESSION[$this->config['session_name']] = array();
            
            global $gJCoord;
            $page['params'] = $gJCoord->request->params;
            unset( $page['params']['module'] );
            unset( $page['params']['action'] );
            
            $page['action'] = $gJCoord->action->toString();
            $page['label'] = ( !empty($params['history.label']) )? $params['history.label']:'';
            $page['title'] = ( !empty($params['history.title']) )? $params['history.title']:'';
            
            if( !count($_SESSION[$this->config['session_name']]) ) {
                $_SESSION[$this->config['session_name']][] = $page;
            } else if( count($_SESSION[$this->config['session_name']]) < $this->config['maxsize'] && ( $this->config['double'] || end($_SESSION[$this->config['session_name']]) != $page ) ) {
                if( $this->config['single'] )
                    foreach( $_SESSION[$this->config['session_name']] as $key=>$valu ) if( $valu == $page )
                        array_splice( $_SESSION[$this->config['session_name']], $key, 1 );
                $_SESSION[$this->config['session_name']][] = $page;
            }
            
        }
        
    }
    
    public function change( $key, $val ) {
        
        $page = array_pop($_SESSION[$this->config['session_name']]);
        $page[$key] = $val;
        
        if( $this->config['double'] || end($_SESSION[$this->config['session_name']]) != $page ) {
            if( $this->config['single'] )
                foreach( $_SESSION[$this->config['session_name']] as $key=>$valu ) if( $valu == $page )
                    array_splice( $_SESSION[$this->config['session_name']], $key, 1 );
            $_SESSION[$this->config['session_name']][] = $page;
        }
        
    }
    
    public function beforeOutput(){}
    
    public function afterProcess (){}
    
    public function reload( $rep ) {
        $last = end($_SESSION[$this->config['session_name']]);
        $rep->action = $last['action'];
        $rep->params = $last['params'];
        return $rep;
    }
    
    public function back( $rep ) {
        array_pop($_SESSION[$this->config['session_name']]);
        $last = end($_SESSION[$this->config['session_name']]);
        $rep->action = $last['action'];
        $rep->params = $last['params'];
        return $rep;
    }
    
    public function time() {
        if( $this->config['time'] && isset($_SESSION[$this->config['session_time_name']]) )
            return microtime(true) - $_SESSION[$this->config['session_time_name']];
        return 0;
    }
    
}

?>