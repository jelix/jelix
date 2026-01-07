<?php
use Jelix\Locale\Locale;

class jLocaleLangCodeTest extends \Jelix\UnitTests\UnitTestCase {

    public static function setUpBeforeClass() : void  {
        self::initJelixConfig();
    }

    protected $backupAvailableLocale;
    protected $backupAcceptedLanguage;
    protected $backupLangToLocale;

    function setUp() : void  {
        $this->backupLangToLocale = jApp::config()->langToLocale ;
        $this->backupAcceptedLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:'';
        $this->backupAvailableLocale = jApp::config()->availableLocales ;
        parent::setUp();
    }

    public function tearDown() : void  {
        jApp::config()->langToLocale = $this->backupLangToLocale;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $this->backupAcceptedLanguage;
        jApp::config()->availableLocales = $this->backupAvailableLocale;
        parent::tearDown();
    }

    function testLangToLocale() {
        jApp::config()->langToLocale = array();
        
        $this->assertEquals('en_US', Locale::langToLocale('en'));
        $this->assertEquals('', Locale::langToLocale('en_GB'));
        $this->assertEquals('fr_FR', Locale::langToLocale('fr'));
        $this->assertEquals('be_BY', Locale::langToLocale('be'));
        
        jApp::config()->langToLocale = array('locale'=>array('fr'=>'fr_CA'));
        $this->assertEquals('fr_CA', Locale::langToLocale('fr'));
    }

    function testGetCorrespondingLocale() {
        jApp::config()->availableLocales = array('en_US');

        $this->assertEquals(array('locale'=>array('en'=>'en_US', 'fr' => 'fr_FR')), jApp::config()->langToLocale);
        $this->assertEquals('en_US', Locale::getCorrespondingLocale('en'));

        jApp::config()->langToLocale = array('locale'=>array('en'=>'en_EN'));
        $this->assertEquals(array('locale'=>array('en'=>'en_EN')), jApp::config()->langToLocale);
        $this->assertEquals('', Locale::getCorrespondingLocale('en'));

        jApp::config()->langToLocale = array('locale'=>array('en'=>'en_US'));
        $this->assertEquals('en_US', Locale::getCorrespondingLocale('en'));
        jApp::config()->langToLocale = array();
        $this->assertEquals('en_US', Locale::getCorrespondingLocale('en'));
        $this->assertEquals('en_US', Locale::getCorrespondingLocale('en_US'));
        $this->assertEquals('en_US', Locale::getCorrespondingLocale('en_GB'));
        jApp::config()->availableLocales = array('en_US', 'fr_CA');
        jApp::config()->langToLocale = array('locale'=>array('fr'=>'fr_CA')); // simulate Config\Compiler
        $this->assertEquals('en_US', Locale::getCorrespondingLocale('en'));
        $this->assertEquals('fr_CA', Locale::getCorrespondingLocale('fr'));
        $this->assertEquals('fr_CA', Locale::getCorrespondingLocale('fr_FR'));
    }

    function testGetPreferedLocaleFromRequest() {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en_US,fr_CA,en_GB';
        jApp::config()->availableLocales = array('en_US');
        $this->assertEquals('en_US', Locale::getPreferedLocaleFromRequest());
        
        jApp::config()->availableLocales = array('en_US', 'fr_CA');
        jApp::config()->langToLocale = array('locale'=>array('fr'=>'fr_CA')); // simulate Config\Compiler
        $this->assertEquals('en_US', Locale::getPreferedLocaleFromRequest());

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr_CA,en_GB';
        jApp::config()->availableLocales = array('en_US', 'fr_CA');
        $this->assertEquals('fr_CA', Locale::getPreferedLocaleFromRequest());

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr_FR,en_US';
        jApp::config()->availableLocales = array('en_US', 'fr_CA');
        $this->assertEquals('fr_CA', Locale::getPreferedLocaleFromRequest());
    }

    function testLocaleName() {
        $this->assertEquals('فارسی', Locale::getLangName('fa'));
        $this->assertEquals('Persan', Locale::getLangName('fa', 'fr'));
        $this->assertEquals('Persian', Locale::getLangName('fa', 'en'));
        $this->assertEquals('Persian', Locale::getLangName('fa', 'pgoidfgip'));
    }
}
