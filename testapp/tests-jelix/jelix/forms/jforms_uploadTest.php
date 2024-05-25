<?php
/**
 * @package     testapp
 * @subpackage  jelix_tests module
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2019-2024 Laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */


class tesMFormUpload extends jFormsBase {
    function addCtrl($control, $reset=true){
        if($reset){
            $this->controls = array();
            $this->container->data = array();
        }
        $this->addControl($control);
    }
}

class testUploadCtrl extends \Jelix\Forms\Controls\Upload2Control {

    function testProcessUpload($action, $fileInfo)
    {
        $this->processUpload($action, $fileInfo);
    }

    protected $_isUploadedFile = true;
    protected $_moveUploadedFile = true;

    function setUploadResult($isUploadedFile, $moveUploadedFile)
    {
        $this->_isUploadedFile = $isUploadedFile;
        $this->_moveUploadedFile = $moveUploadedFile;
    }

    protected function isUploadedFile($file)
    {
        return $this->_isUploadedFile;
    }

    protected function moveUploadedFile($file, $target)
    {
        return $this->_moveUploadedFile;
    }

    public function getPrivateData()
    {
        return $this->container->privateData[$this->ref];
    }

    public function getTempFile($file) {
        $dir = __DIR__.'/uploads/tmp';
        jFile::createDir($dir, 0775);
        $tmpFile = $dir.'/'.$file;
        return $tmpFile;
    }
}



class jforms_uploadTest extends \Jelix\UnitTests\UnitTestCase {

    protected $uploadCtrl;
    protected $form;
    protected $container;
    function setUp() :void
    {
        $this->container = new \Jelix\Forms\FormDataContainer('', '');
        $this->form = new tesMFormUpload('foo', $this->container);

        $this->uploadCtrl = new testUploadCtrl('uploadctrl');
        $this->uploadCtrl->setForm($this->form);
        $this->form->addCtrl($this->uploadCtrl);
    }

    function testUniqueName() {
        $ctrl = new \Jelix\Forms\Controls\Upload2Control('up');
        $dir = __DIR__.'/';
        $this->assertEquals('foo.test',
            $ctrl->getUniqueFileName(__DIR__, 'foo.test')
        );
        $this->assertEquals('bar/foo.test',
            $ctrl->getUniqueFileName(__DIR__, 'bar/foo.test')
        );
        $this->assertEquals('bar/foo.test',
            $ctrl->getUniqueFileName(realpath(__DIR__.'/../'), 'bar/foo.test')
        );
        $this->assertEquals('jforms_uploadTest1.php',
            $ctrl->getUniqueFileName(__DIR__.'/', 'jforms_uploadTest.php')
        );
        $this->assertEquals('forms/jforms_uploadTest1.php',
            $ctrl->getUniqueFileName(realpath(__DIR__.'/../'), 'forms/jforms_uploadTest.php')
        );
    }



    function testCreationNotRequired()
    {
        $this->uploadCtrl->required = false;

        $this->uploadCtrl->testProcessUpload('new', null);
        $this->assertNull($this->uploadCtrl->check());
        $this->assertFalse($this->uploadCtrl->isModified());
        $this->assertEquals(
            array(
                'newfile' => '',
                'originalfile' => '',
                'action' => 'new'
            ), $this->uploadCtrl->getPrivateData()
        );

        $this->uploadCtrl->testProcessUpload('new', array(
            'name'=>'foo.txt',
            'type'=>'',
            'size'=>0,
            'tmp_name'=>'/tmp/foo123',
            'error'=> 0
        ));
        $this->assertNull($this->uploadCtrl->check());
        $this->assertTrue($this->uploadCtrl->isModified());
        $this->assertEquals(
            array(
                'newfile' => 'foo.txt',
                'originalfile' => '',
                'action' => 'new'
            ), $this->uploadCtrl->getPrivateData()
        );

    }

    function testCreationRequired()
    {
        $this->uploadCtrl->required = true;

        $this->uploadCtrl->testProcessUpload('new', null);
        $this->assertEquals(jForms::ERRDATA_REQUIRED, $this->uploadCtrl->check());
        $this->assertFalse($this->uploadCtrl->isModified());
        $this->assertEquals(
            array(
                'newfile' => '',
                'originalfile' => '',
                'action' => 'new'
            ), $this->uploadCtrl->getPrivateData()
        );

        $this->uploadCtrl->testProcessUpload('new', array(
            'name'=>'foo.txt',
            'type'=>'',
            'size'=>0,
            'tmp_name'=>'/tmp/foo123',
            'error'=> 0
        ));
        $this->assertNull($this->uploadCtrl->check());
        $this->assertTrue($this->uploadCtrl->isModified());
        $this->assertEquals(
            array(
                'newfile' => 'foo.txt',
                'originalfile' => '',
                'action' => 'new'
            ), $this->uploadCtrl->getPrivateData()
        );
    }

    function testModificationKeep()
    {
        $this->uploadCtrl->setDataFromDao('foo.txt', 'string');

        $this->uploadCtrl->testProcessUpload('keep', null);
        $this->assertNull($this->uploadCtrl->check());
        $this->assertFalse($this->uploadCtrl->isModified());
        $this->assertEquals(
            array(
                'newfile' => '',
                'originalfile' => 'foo.txt',
                'action' => 'keep'
            ), $this->uploadCtrl->getPrivateData()
        );


    }

    function testModificationKeepNew()
    {
        $this->uploadCtrl->setDataFromDao('foo.txt', 'string');

        $this->uploadCtrl->testProcessUpload('new', array(
            'name'=>'bar.txt',
            'type'=>'',
            'size'=>0,
            'tmp_name'=>'/tmp/bar123',
            'error'=> 0
        ));
        $this->assertNull($this->uploadCtrl->check());
        $this->assertTrue($this->uploadCtrl->isModified());
        $this->assertEquals(
            array(
                'newfile' => 'bar.txt',
                'originalfile' => 'foo.txt',
                'action' => 'new'
            ), $this->uploadCtrl->getPrivateData()
        );

        $this->uploadCtrl->testProcessUpload('keepnew', null);
        $this->assertNull($this->uploadCtrl->check());
        $this->assertTrue($this->uploadCtrl->isModified());
        $this->assertEquals(
            array(
                'newfile' => 'bar.txt',
                'originalfile' => 'foo.txt',
                'action' => 'keepnew'
            ), $this->uploadCtrl->getPrivateData()
        );
    }

    function testModificationNewTwice()
    {
        $this->uploadCtrl->setDataFromDao('foo.txt', 'string');

        $this->uploadCtrl->testProcessUpload('new', array(
            'name'=>'bar.txt',
            'type'=>'',
            'size'=>0,
            'tmp_name'=>'/tmp/bar123',
            'error'=> 0
        ));
        $this->assertNull($this->uploadCtrl->check());
        $this->assertTrue($this->uploadCtrl->isModified());
        $this->assertEquals(
            array(
                'newfile' => 'bar.txt',
                'originalfile' => 'foo.txt',
                'action' => 'new'
            ), $this->uploadCtrl->getPrivateData()
        );

        $this->uploadCtrl->testProcessUpload('new',  array(
            'name'=>'baz.txt',
            'type'=>'',
            'size'=>0,
            'tmp_name'=>'/tmp/baz123',
            'error'=> 0
        ));
        $this->assertNull($this->uploadCtrl->check());
        $this->assertTrue($this->uploadCtrl->isModified());
        $this->assertEquals(
            array(
                'newfile' => 'baz.txt',
                'originalfile' => 'foo.txt',
                'action' => 'new'
            ), $this->uploadCtrl->getPrivateData()
        );
    }

    function testDeletion()
    {
        $this->uploadCtrl->setDataFromDao('foo.txt', 'string');

        $this->uploadCtrl->testProcessUpload('del', null);
        $this->assertNull($this->uploadCtrl->check());
        $this->assertTrue($this->uploadCtrl->isModified());
        $this->assertEquals(
            array(
                'newfile' => '',
                'originalfile' => 'foo.txt',
                'action' => 'del'
            ), $this->uploadCtrl->getPrivateData()
        );

        // the user cancel the deletion
        $this->uploadCtrl->testProcessUpload('keep', null);
        $this->assertNull($this->uploadCtrl->check());
        $this->assertFalse($this->uploadCtrl->isModified());
        $this->assertEquals(
            array(
                'newfile' => '',
                'originalfile' => 'foo.txt',
                'action' => 'keep'
            ), $this->uploadCtrl->getPrivateData()
        );
    }


    function testDeletionWithRequired()
    {
        $this->uploadCtrl->required = true;
        $this->uploadCtrl->setDataFromDao('foo.txt', 'string');

        $this->uploadCtrl->testProcessUpload('del', null);
        $this->assertEquals(jForms::ERRDATA_REQUIRED, $this->uploadCtrl->check());
        $this->assertFalse($this->uploadCtrl->isModified());
        $this->assertEquals(
            array(
                'newfile' => '',
                'originalfile' => 'foo.txt',
                'action' => 'del'
            ), $this->uploadCtrl->getPrivateData()
        );

        // the user cancel the deletion
        $this->uploadCtrl->testProcessUpload('keep', null);
        $this->assertNull($this->uploadCtrl->check());
        $this->assertFalse($this->uploadCtrl->isModified());
        $this->assertEquals(
            array(
                'newfile' => '',
                'originalfile' => 'foo.txt',
                'action' => 'keep'
            ), $this->uploadCtrl->getPrivateData()
        );
    }

}
