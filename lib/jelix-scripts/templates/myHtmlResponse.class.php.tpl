<?php 
require_once (JELIX_LIB_CORE_PATH.'response/jResponseHtml.class.php');

class myHtmlResponse extends jResponseHtml {

    public $bodyTpl = '%%appname%%~main';
    
    function __construct() {
        parent::__construct();
        
        // Include your common CSS and JS files here
    }

    protected function doAfterActions() {
        $this->body->assignIfNone('MAIN','<p>no content</p>');
    }
}
?>
