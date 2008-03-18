<?php 
require_once (JELIX_LIB_CORE_PATH.'response/jResponseHtml.class.php');

class myHtmlResponse extends jResponseHtml {

	protected function _commonProcess() {
		$this->bodyTpl = '%%appname%%~main';
		$this->body->assignIfNone('MAIN','<p>no content</p>');
		
	}
}
?>