<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor Thibault Piront (nuKs)
* @copyright   2007-2012 Laurent Jouanneau
* @copyright   2007 Thibault Piront
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jtpl_pluginsTest extends \Jelix\UnitTests\UnitTestCase {

    public function setUp() : void {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        parent::setUp();
    }
    function tearDown() : void {
        jApp::popCurrentModule();
    }
    protected $templates = array(
        0=>array(
            'test_plugin_jurl', // template selector
            '<p><a href="%BASEPATH%index.php/jelix_tests/urlsig/url1">aaa</a></p>', // generated content
        ),
        1=>array(
            'test_plugin_counter',
            '1-2-3-4-5,1-2-3-4-5,6-7-8-9-10,----,11-12-13-14-15',
        ),
        2=>array(
            'test_plugin_counter_reset',
            '1-2-3-4-5,1-2-3-4-5,1-2-3-4-5,1-2-3-4-5',
        ),
        3=>array(
            'test_plugin_counter_reset_all',
            '1-2-3-4-5,1-2-3-4-5,1-2-3-4-5,1-2-3-4-5',
        ),
        4=>array(
            'test_plugin_counter_init_allarg_noexeption',
            '2-0--2--4--6,03-06-09-12-15,g-f-e-d-c,E-J-O-T-Y',
        ),
        5=>array(
            'test_plugin_counter_init_noexeption',
            '1-2-3-4-5,01-02-03-04-05,e-f-g-h-i',
        ),
        6=>array(
            'test_plugin_counter_init_exeption',
            'y-z-1-2-3',
        ),
        7=>array(
            'test_plugin_jrooturl',
            'http://www.junittest.com/',
        )
   );

	protected $truncateHTMLAssigns = array(
		0=>array(
			'33',													//Where do we cut?
			'<p>Lorem &nbsp; ipsum <strong>sit...</strong></p>',	//What are we expecting to see
			false													//Do we add other etcPattern
		),
		1=>array(													//Cutting in the middle of a tag
			'27',
			'<p>Lorem &nbsp; ipsum...</p>',
			false
		),
		2=>array(
			'47',													//Cutting in the middle of an auto closing tag
			'<p>Lorem &nbsp; ipsum <strong>sit dolor...</strong></p>',
			false
		),
		3=>array(
			'53',													//Including auto closing tags and not try to close it
			'<p>Lorem &nbsp; ipsum <strong>sit dolor<br /><br />...</strong></p>',
			false
		),
		4=>array(
			'50',													//Testing cutting in midlle of an image
			'<p>Lorem &nbsp; ipsum <strong>sit dolor<br />...</strong></p>',
			false														
		),
		5=>array(
			'31',													//Testing etc pattern
			'<p>Lorem &nbsp; ipsum<strong>This is not the entire text</strong></p>',			
			'<strong>This is not the entire text</strong>'								
		),
		6=>array(
			'15',													//Trying to break an XML entity
			'<p>Lorem &nbsp;...</p>',
			false
		),
		7=>array(
			'14',													//Trying to break an XML entity
			'<p>Lorem...</p>',
			false
		),
        8=>array(
             '120',													//breaking in the middle of a comment
             '<p>Lorem &nbsp; ipsum <strong>sit dolor<br /><br /> &nbsp; <img src="#longReference" alt="image" title="image" />...</strong></p>',
             false        		
        ),
        9=>array(
		    '0',                                                    //too short cut
		    '',
		    false
        ),															//too long get the same, without comments;
        10=>array(
            '1000',
            '<p>Lorem &nbsp; ipsum <strong>sit dolor<br /><br /> &nbsp; <img src="#longReference" alt="image" title="image" /><div class="emphase">youhou ca marche</div></strong></p>',
            false
        ),
        11=>array(
        	'150',
        	'<p>Lorem &nbsp; ipsum <strong>sit dolor<br /><br /> &nbsp; <img src="#longReference" alt="image" title="image" /><div class="emphase">youhou ca...</div></strong></p>',
        	false        
        )
		
	);
	

    function testPlugin() {

        foreach($this->templates as $k=>$t) {

            // we delete the cache because it won't be updated 
            // if changes are made in jTpl itself or plugins
            $sel = new jSelectorTpl($t[0]); //, $outputtype='', $trusted = true
            $cache = $sel->getCompiledFilePath();
            if(file_exists($cache))
                unlink($cache);

            $tpl = new jTpl();
            $tpl->assign('i', 0); // Pour les boucles for.
            $output = $tpl->fetch ($t[0]); //, $outputtype='', $trusted = true, $callMeta=true
            $expected = $t[1];
            if(strpos($t[1],'%BASEPATH%') !== false){
                $expected = str_replace('%BASEPATH%', jApp::urlBasePath(), $expected);
            }
            $this->assertEquals($expected, $output, 'testplugin['.$k.'], %s');

        }
    }



	function testTruncateHTML(){
		$sentence = '<p>Lorem &nbsp; ipsum <strong>sit dolor<br /><br /> &nbsp; <img src="#longReference" alt="image" title="image" /><!-- This is a comment, it should not be included neither evaluated in the number of word we use for truncate --><div class="emphase">youhou ca marche</div></strong></p>';
		foreach ( $this->truncateHTMLAssigns as $key=>$chars ) {
       		$tpl = new jTpl();
       		$tpl->assign('cut',$chars[0]);
       		$tpl->assign('etc',$chars[2] ? $chars[2] : '...');
       		$tpl->assign('sentence',$sentence);
       		$this->assertEquals('test => '.$key .'(cut '.$chars[0].' ) : '.$chars[1],
                                'test => '.$key .'(cut '.$chars[0].' ) :'.$tpl->fetch('test_truncate_html'),
                                'testplugin['.$key.'], %s');
		}
		
	}


    function testPageLinks() {
        $tpl = new jTpl();
        $output = $tpl->fetch ('test_plugin_pagelinks'); //, $outputtype='', $trusted = true, $callMeta=true
        $basePath = jApp::urlBasePath();
        $expected = 
'1: <ul class="pagelinks"><li class="pagelinks-start pagelinks-disabled"><a href="#">|&lt;</a></li>
<li class="pagelinks-prev pagelinks-disabled"><a href="#">&lt;</a></li>
<li class="pagelinks-current"><a href="#">1</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=10">2</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=20">3</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=30">4</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=40">5</a></li>
<li class="pagelinks-next"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=10">&gt;</a></li>
<li class="pagelinks-end"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=40">&gt;|</a></li>
</ul>
2: <ul class="pagelinks"><li class="pagelinks-start pagelinks-disabled"><a href="#">|&lt;</a></li>
<li class="pagelinks-prev pagelinks-disabled"><a href="#">&lt;</a></li>
<li class="pagelinks-current"><a href="#">1</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=10">2</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=20">3</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=30">4</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=40">5</a></li>
<li class="pagelinks-next"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=10">&gt;</a></li>
<li class="pagelinks-end"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=40">&gt;|</a></li>
</ul>
3: <ul class="pagelinks"><li class="pagelinks-start pagelinks-disabled"><a href="#">|&lt;</a></li>
<li class="pagelinks-prev pagelinks-disabled"><a href="#">&lt;</a></li>
<li class="pagelinks-current"><a href="#">1</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=10">2</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=20">3</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=30">4</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=40">5</a></li>
<li class="pagelinks-next"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=10">&gt;</a></li>
<li class="pagelinks-end"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=40">&gt;|</a></li>
</ul>
4: <ul class="pagelinks"><li class="pagelinks-start"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=0">|&lt;</a></li>
<li class="pagelinks-prev"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=0">&lt;</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=0">1</a></li>
<li class="pagelinks-current"><a href="#">2</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=20">3</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=30">4</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=40">5</a></li>
<li class="pagelinks-next"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=20">&gt;</a></li>
<li class="pagelinks-end"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=40">&gt;|</a></li>
</ul>
5: <ul class="pagelinks"><li class="pagelinks-start pagelinks-disabled"><a href="#">|&lt;</a></li>
<li class="pagelinks-prev pagelinks-disabled"><a href="#">&lt;</a></li>
<li class="pagelinks-current"><a href="#">1</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=5">2</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=10">3</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=15">4</a></li>
<li class="pagelinks-next"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=5">&gt;</a></li>
<li class="pagelinks-end"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=15">&gt;|</a></li>
</ul>
6: <ul class="pagelinks"><li class="pagelinks-start"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=0">|&lt;</a></li>
<li class="pagelinks-prev"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=0">&lt;</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=0">1</a></li>
<li class="pagelinks-current"><a href="#">2</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=10">3</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=15">4</a></li>
<li class="pagelinks-next"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=10">&gt;</a></li>
<li class="pagelinks-end"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=15">&gt;|</a></li>
</ul>
7: <ul class="pagelinks"><li class="pagelinks-start"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=0">|&lt;</a></li>
<li class="pagelinks-prev"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=5">&lt;</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=0">1</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=5">2</a></li>
<li class="pagelinks-current"><a href="#">3</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=15">4</a></li>
<li class="pagelinks-next"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=15">&gt;</a></li>
<li class="pagelinks-end"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=15">&gt;|</a></li>
</ul>
8: <ul class="pagelinks"><li class="pagelinks-start"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=0">|&lt;</a></li>
<li class="pagelinks-prev"><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=10">&lt;</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=0">1</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=5">2</a></li>
<li class=""><a href="'.$basePath.'index.php/jelix_tests/urlsig/url1?offset=10">3</a></li>
<li class="pagelinks-current"><a href="#">4</a></li>
<li class="pagelinks-next pagelinks-disabled"><a href="#">&gt;</a></li>
<li class="pagelinks-end pagelinks-disabled"><a href="#">&gt;|</a></li>
</ul>';
        $this->assertEquals($expected, $output);
    }

	function testInclude() {

		$tpl = new jTpl();
		$tpl->assign ('aaa', 'first');
		$tpl->assign ('bbb', 'second');

		$meta = $tpl->meta('test_include');
		$content = $tpl->fetch('test_include');

		$this->assertEquals(array('main'=>'main template','subtpl'=>'sub template'), $meta);
		$this->assertEquals("
<h1>Main template</h1>
<p>first</p>

<p>Hello, here the sub template</p>
<p>first and second</p>
<p>end of template</p>

<p>End</p>

", $content);
	}

	function testIncludeRecursive() {
		// when a template includes itself, meta should be retrieved only one time
		// to avoid infinite loop
		$tpl = new jTpl();
	 	$tpl->assign('items', array(1,2));
		$meta = $tpl->meta('test_include_recursive');
		$content = $tpl->fetch('test_include_recursive');
		$this->assertEquals(array('main'=>'2', 'counter'=>1), $meta);
		$this->assertEquals("c=2\nx=2\nc=1\nx=1\n" , $content);

		// if a template includes an other template more than one time,
		// meta should be retrieved only one time
		$tpl = new jTpl();
	 	$tpl->assign('items', array());
		$meta = $tpl->meta('test_include_recursive2');
		$content = $tpl->fetch('test_include_recursive2');
		$this->assertEquals(array('main'=>'0', 'counter'=>1), $meta);
		$this->assertEquals("c=2\nx=2\nc=1\nx=1\nc=2\nx=4\nc=1\nx=3\n\n", $content);
	}
}

