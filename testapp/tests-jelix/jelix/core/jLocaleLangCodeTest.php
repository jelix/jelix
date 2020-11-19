<?php

class jLocaleLangCodeTest extends jUnitTestCase {

    public static function setUpBeforeClass() {
        self::initJelixConfig();
    }

    protected $backupAvailableLocale;
    protected $backupAcceptedLanguage;
    protected $backupLangToLocale;

    function setUp() {
        $this->backupLangToLocale = jApp::config()->langToLocale ;
        $this->backupAcceptedLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:'';
        $this->backupAvailableLocale = jApp::config()->availableLocales ;
        parent::setUp();
    }

    public function tearDown() {
        jApp::config()->langToLocale = $this->backupLangToLocale;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $this->backupAcceptedLanguage;
        jApp::config()->availableLocales = $this->backupAvailableLocale;
        parent::tearDown();
    }

    function testLangToLocale() {
        jApp::config()->langToLocale = array();
        
        $this->assertEquals('en_US', jLocale::langToLocale('en'));
        $this->assertEquals('', jLocale::langToLocale('en_GB'));
        $this->assertEquals('fr_FR', jLocale::langToLocale('fr'));
        $this->assertEquals('be_BY', jLocale::langToLocale('be'));
        
        jApp::config()->langToLocale = array('locale'=>array('fr'=>'fr_CA'));
        $this->assertEquals('fr_CA', jLocale::langToLocale('fr'));
    }

    function testGetCorrespondingLocale() {
        jApp::config()->availableLocales = array('en_US');

        $this->assertEquals(array('locale'=>array('en'=>'en_US')), jApp::config()->langToLocale);
        $this->assertEquals('en_US', jLocale::getCorrespondingLocale('en'));

        jApp::config()->langToLocale = array('locale'=>array('en'=>'en_EN'));
        $this->assertEquals(array('locale'=>array('en'=>'en_EN')), jApp::config()->langToLocale);
        $this->assertEquals('', jLocale::getCorrespondingLocale('en'));

        jApp::config()->langToLocale = array('locale'=>array('en'=>'en_US'));
        $this->assertEquals('en_US', jLocale::getCorrespondingLocale('en'));
        jApp::config()->langToLocale = array();
        $this->assertEquals('en_US', jLocale::getCorrespondingLocale('en'));
        $this->assertEquals('en_US', jLocale::getCorrespondingLocale('en_US'));
        $this->assertEquals('en_US', jLocale::getCorrespondingLocale('en_GB'));
        jApp::config()->availableLocales = array('en_US', 'fr_CA');
        jApp::config()->langToLocale = array('locale'=>array('fr'=>'fr_CA')); // simulate jConfigCompiler
        $this->assertEquals('en_US', jLocale::getCorrespondingLocale('en'));
        $this->assertEquals('fr_CA', jLocale::getCorrespondingLocale('fr'));
        $this->assertEquals('fr_CA', jLocale::getCorrespondingLocale('fr_FR'));
    }

    function testGetPreferedLocaleFromRequest() {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en_US,fr_CA,en_GB';
        jApp::config()->availableLocales = array('en_US');
        $this->assertEquals('en_US', jLocale::getPreferedLocaleFromRequest());
        
        jApp::config()->availableLocales = array('en_US', 'fr_CA');
        jApp::config()->langToLocale = array('locale'=>array('fr'=>'fr_CA')); // simulate jConfigCompiler
        $this->assertEquals('en_US', jLocale::getPreferedLocaleFromRequest());

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr_CA,en_GB';
        jApp::config()->availableLocales = array('en_US', 'fr_CA');
        $this->assertEquals('fr_CA', jLocale::getPreferedLocaleFromRequest());

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr_FR,en_US';
        jApp::config()->availableLocales = array('en_US', 'fr_CA');
        $this->assertEquals('fr_CA', jLocale::getPreferedLocaleFromRequest());
    }

    function testLocaleName() {
        $this->assertEquals('فارسی', jLocale::getLangName('fa'));
        $this->assertEquals('Persan', jLocale::getLangName('fa', 'fr'));
        $this->assertEquals('Persian', jLocale::getLangName('fa', 'en'));
        $this->assertEquals('Persian', jLocale::getLangName('fa', 'pgoidfgip'));
    }
}
