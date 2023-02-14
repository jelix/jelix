<?php

require_once(JELIX_LIB_PATH.'core/response/jResponseBinary.class.php');


class binRespTest extends jResponseBinary {

    function __construct () {

    }

    protected function sendHttpHeaders(){ $this->_httpHeadersSent=true; }
}


class responseBinaryTest extends \Jelix\UnitTests\UnitTestCase
{
    protected $oldserver;

    function setUp(): void
    {
        $this->oldserver = $_SERVER;
        jApp::saveContext();
        self::initClassicRequest(TESTAPP_URL . 'index.php');
        jApp::pushCurrentModule('jelix_tests');
        parent::setUp();
    }

    function tearDown(): void
    {
        jApp::popCurrentModule();
        jApp::restoreContext();
        $_SERVER = $this->oldserver;
    }

    function testOutputString()
    {
        $resp = new binRespTest();
        $resp->content = 'hello world';

        ob_start();
        $resp->output();
        $output = ob_get_clean();

        $this->assertEquals('hello world', $output);
        $this->assertEquals(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename=""',
            'Content-Description' => 'File Transfert',
            'Content-Transfer-Encoding' => 'binary',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=3600',
            'Content-Length' => 11,
        ), $resp->getHttpHeaders());
    }

    function testOutputFile()
    {
        $file = __DIR__.'/app/project.xml';
        $resp = new binRespTest();
        $resp->fileName = $file;

        ob_start();
        $resp->output();
        $output = ob_get_clean();

        $this->assertEquals(file_get_contents($file), $output);
        $this->assertEquals(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="project.xml"',
            'Content-Description' => 'File Transfert',
            'Content-Transfer-Encoding' => 'binary',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=3600',
            'Content-Length' => filesize($file),

        ), $resp->getHttpHeaders());
    }

    function testOutputFileWithNewName()
    {
        $file = __DIR__.'/app/project.xml';
        $resp = new binRespTest();
        $resp->fileName = $file;
        $resp->outputFileName = 'foo.xml';

        ob_start();
        $resp->output();
        $output = ob_get_clean();

        $this->assertEquals(file_get_contents($file), $output);
        $this->assertEquals(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="foo.xml"',
            'Content-Description' => 'File Transfert',
            'Content-Transfer-Encoding' => 'binary',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=3600',
            'Content-Length' => filesize($file),

        ), $resp->getHttpHeaders());
    }

    function testOutputFileNoDownload()
    {
        $file = __DIR__.'/app/project.xml';
        $resp = new binRespTest();
        $resp->fileName = $file;
        $resp->outputFileName = 'foo.xml';
        $resp->doDownload = false;

        ob_start();
        $resp->output();
        $output = ob_get_clean();

        $this->assertEquals(file_get_contents($file), $output);
        $this->assertEquals(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Length' => filesize($file),
            'Content-Disposition' => 'inline; filename="foo.xml"'
        ), $resp->getHttpHeaders());
    }

    function testOutputFileToDelete()
    {
        $origFile =  __DIR__.'/app/project.xml';
        $file = jApp::tempPath('output_file_to_delete.xml');
        copy($origFile, $file);
        $this->assertTrue(file_exists($file));

        $resp = new binRespTest();
        $resp->fileName = $file;
        $resp->deleteFileAfterSending = true;
        ob_start();
        $resp->output();
        $output = ob_get_clean();

        $this->assertEquals(file_get_contents($origFile), $output);
        $this->assertEquals(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="output_file_to_delete.xml"',
            'Content-Description' => 'File Transfert',
            'Content-Transfer-Encoding' => 'binary',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=3600',
            'Content-Length' => filesize($origFile),

        ), $resp->getHttpHeaders());
        $this->assertFalse(file_exists($file));
    }

    function testOutputSimpleCallback()
    {
        $resp = new binRespTest();
        $resp->setContentCallback(function() {
            echo 'Hello World';
        });

        ob_start();
        $resp->output();
        $output = ob_get_clean();

        $this->assertEquals('Hello World', $output);
        $this->assertEquals(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename=""',
            'Content-Description' => 'File Transfert',
            'Content-Transfer-Encoding' => 'binary',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=3600'

        ), $resp->getHttpHeaders());
    }

    function testOutputIterator()
    {
        $resp = new binRespTest();
        $genFunc = function() {
            $arr = [ 'He', 'llo', ' Wo', 'rld'];
            foreach($arr as $s) {
                yield $s;
            }
        };
        $resp->setContentGenerator($genFunc());

        ob_start();
        $resp->output();
        $output = ob_get_clean();

        $this->assertEquals('Hello World', $output);
        $this->assertEquals(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename=""',
            'Content-Description' => 'File Transfert',
            'Content-Transfer-Encoding' => 'binary',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=3600'
        ), $resp->getHttpHeaders());
    }

    function testOutputCallbackWithFileToDelete()
    {
        $origFile =  __DIR__.'/app/project.xml';
        $file = jApp::tempPath('output_file_to_delete.xml');
        copy($origFile, $file);
        $this->assertTrue(file_exists($file));

        $resp = new binRespTest();
        $resp->fileName = $file;
        $resp->deleteFileAfterSending = true;

        $resp->setContentCallback(function() use($file) {
            $f = fopen($file, 'r');
            while($s = fread($f, 20)) {
                echo $s;
            }
            fclose($f);
        });

        ob_start();
        $resp->output();
        $output = ob_get_clean();

        $this->assertEquals(file_get_contents($origFile), $output);
        $this->assertEquals(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="output_file_to_delete.xml"',
            'Content-Description' => 'File Transfert',
            'Content-Transfer-Encoding' => 'binary',
            'Pragma' => 'public',
            'Cache-Control' => 'maxage=3600',
            'Content-Length' => filesize($origFile),

        ), $resp->getHttpHeaders());
        $this->assertFalse(file_exists($file));
    }
}
