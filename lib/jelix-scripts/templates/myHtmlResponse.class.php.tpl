<?php 
require_once (JELIX_LIB_CORE_PATH.'response/jResponseHtml.class.php');

class myHtmlResponse extends jResponseHtml {

    public $bodyTpl = '%%appname%%~main';

    protected function _commonProcess() {
        $this->body->assignIfNone('MAIN','<p>no content</p>');
    }
}
?>
