<?php

require_once(JELIX_LIB_PATH.'plugins/configcompiler/webassets/webassets.configcompiler.php');

require_once(JELIX_LIB_PATH.'core/response/jResponseHtml.class.php');

use \Jelix\WebAssets\WebAssetsCompiler;

class htmlRespAssetsTest extends jResponseHtml {
    function __construct (){
        $this->body = new jTpl();
    }
    protected function sendHttpHeaders(){ $this->_httpHeadersSent=true; }
}


class webassetsTest extends \Jelix\UnitTests\UnitTestCase
{

    public function getWebAssets() {
        return array(
          array(
              '
[webassets]
useCollection=foo

[webassets_foo]
',
              array(
                  'compiled_webassets_common' => array(
                      'dependencies_order' => array()
                  ),
                  'compiled_webassets_foo' => array(
                      'dependencies_order' => array()
                  ))
          ),

          array(
              '
[webassets]
useCollection=foo

[webassets_foo]
a.js = a.js
',
              array(
                  'compiled_webassets_common' => array(
                      'dependencies_order' => array()
                  ),
                  'compiled_webassets_foo' => array(
                      'dependencies_order' => array('a'),
                      'webassets_a.deps' => array(),
                      'webassets_a.js' => array('k>a.js>'),
                      'webassets_a.css' => array(),
                  ))
          ),

          array(
              '
[webassets]
useCollection=foo

[webassets_foo]

b.js = b.js
b.require = a

a.js = a.js
',
              array(
                  'compiled_webassets_common' => array(
                      'dependencies_order' => array()
                  ),
                  'compiled_webassets_foo' => array(
                      'dependencies_order' => array('a', 'b'),
                      'webassets_a.deps' => array(),
                      'webassets_a.js' => array('k>a.js>'),
                      'webassets_a.css' => array(),
                      'webassets_b.deps' => array('a'),
                      'webassets_b.js' => array('k>b.js>'),
                      'webassets_b.css' => array(),
                  ))
          ),

          array(
              '
[webassets]
useCollection=foo

[webassets_foo]

b.js = b.js
b.require = a

a.js = a.js

c.js = c.js
c.require = a
',
              array(
                  'compiled_webassets_common' => array(
                      'dependencies_order' => array()
                  ),
                  'compiled_webassets_foo' => array(
                      'dependencies_order' => array('a', 'b', 'c'),
                      'webassets_a.deps' => array(),
                      'webassets_a.js' => array('k>a.js>'),
                      'webassets_a.css' => array(),
                      'webassets_b.deps' => array('a'),
                      'webassets_b.js' => array('k>b.js>'),
                      'webassets_b.css' => array(),
                      'webassets_c.deps' => array('a'),
                      'webassets_c.js' => array('k>c.js>'),
                      'webassets_c.css' => array(),

                  ))
          ),

          array(
              '
[webassets]
useCollection=foo

[webassets_foo]

b.js = b.js
b.require = a

a.js = "a.js|defer"
a.css = "a.css|media=screen"
a2.js = "mymodule.js|type=module"
a2.css = "a2.css|media=screen and (max-width: 600px)|rel=stylesheet"

c.js = c.js
c.require = a
c.include = b
',
              array(
                  'compiled_webassets_common' => array(
                      'dependencies_order' => array()
                  ),
                  'compiled_webassets_foo' => array(
                      'dependencies_order' => array('a', 'c', 'b', 'a2'),
                      'webassets_a.deps' => array(),
                      'webassets_a.js' => array('k>a.js>defer'),
                      'webassets_a.css' => array('k>a.css>media=screen'),
                      'webassets_a2.deps' => array(),
                      'webassets_a2.js' => array('k>mymodule.js>type=module'),
                      'webassets_a2.css' => array('k>a2.css>media=screen and (max-width: 600px)|rel=stylesheet'),
                      'webassets_b.deps' => array('a'),
                      'webassets_b.js' => array('k>b.js>'),
                      'webassets_b.css' => array(),
                      'webassets_c.deps' => array('a','b'),
                      'webassets_c.js' => array('k>c.js>'),
                      'webassets_c.css' => array(),

                  ))
          ),

          array(
              '
[webassets]
useCollection=foo

[webassets_common]

b.js = b.js
b.require = a

a.js = a.js

d.js = d.js

c.js = c.js
c.require = a,d
c.include = b

[webassets_foo]
c.js = c2.js
c.require = a
',
              array(
                  'compiled_webassets_common' => array(
                      'dependencies_order' => array('a', 'd', 'c', 'b'),
                      'webassets_a.deps' => array(),
                      'webassets_a.js' => array('k>a.js>'),
                      'webassets_a.css' => array(),
                      'webassets_b.deps' => array('a'),
                      'webassets_b.js' => array('k>b.js>'),
                      'webassets_b.css' => array(),
                      'webassets_c.deps' => array('a', 'd', 'b'),
                      'webassets_c.js' => array('k>c.js>'),
                      'webassets_c.css' => array(),
                      'webassets_d.deps' => array(),
                      'webassets_d.js' => array('k>d.js>'),
                      'webassets_d.css' => array(),
                  ),
                  'compiled_webassets_foo' => array(
                      'dependencies_order' => array('a', 'c', 'b', 'd'),
                      'webassets_a.deps' => array(),
                      'webassets_a.js' => array('k>a.js>'),
                      'webassets_a.css' => array(),
                      'webassets_b.deps' => array('a'),
                      'webassets_b.js' => array('k>b.js>'),
                      'webassets_b.css' => array(),
                      'webassets_c.deps' => array('a'),
                      'webassets_c.js' => array('k>c2.js>'),
                      'webassets_c.css' => array(),
                      'webassets_d.deps' => array(),
                      'webassets_d.js' => array('k>d.js>'),
                      'webassets_d.css' => array(),
                  )
              )
          ),
array(
              '
[webassets]
useCollection=foo

[webassets_foo]

a.js = a.js
a.require = b,c
a.include = e

b.js = b.js

c.js = c.js
c.require = k
c.include = r

d.js = d.js

e.js = e.js
e.require = g

f.js = f.js,f2.js
f.require = a
f.include = r

g.js = g.js
k.js = k.js
r.js = r.js

',
              array(
                  'compiled_webassets_common' => array(
                      'dependencies_order' => array()
                  ),
                  'compiled_webassets_foo' => array(
                      'dependencies_order' => array(
                          'b', 'k', 'c', 'a', 'd', 'g', 'e', 'f', 'r'),
                      'webassets_a.deps' => array('b', 'c', 'k', 'r', 'e', 'g'),
                      'webassets_a.js' => array('k>a.js>'),
                      'webassets_a.css' => array(),
                      'webassets_b.deps' => array(),
                      'webassets_b.js' => array('k>b.js>'),
                      'webassets_b.css' => array(),
                      'webassets_c.deps' => array('k', 'r'),
                      'webassets_c.js' => array('k>c.js>'),
                      'webassets_c.css' => array(),
                      'webassets_d.deps' => array(),
                      'webassets_d.js' => array('k>d.js>'),
                      'webassets_d.css' => array(),
                      'webassets_e.deps' => array('g'),
                      'webassets_e.js' => array('k>e.js>'),
                      'webassets_e.css' => array(),
                      'webassets_f.deps' => array('a', 'b', 'c', 'k', 'r', 'e', 'g'),
                      'webassets_f.js' => array('k>f.js>', 'k>f2.js>'),
                      'webassets_f.css' => array(),
                      'webassets_g.deps' => array(),
                      'webassets_g.js' => array('k>g.js>'),
                      'webassets_g.css' => array(),
                      'webassets_k.deps' => array(),
                      'webassets_k.js' => array('k>k.js>'),
                      'webassets_k.css' => array(),
                      'webassets_r.deps' => array(),
                      'webassets_r.js' => array('k>r.js>'),
                      'webassets_r.css' => array(),
                  ),
              )
          ),

        );
    }


    /**
     * @dataProvider getWebAssets
     */
    function testCompileWebAssets($ini, $expectedResult) {
        $compiler = new WebAssetsCompiler();

        $config = (object)parse_ini_string($ini, true);
        $result = $compiler->compile($config, false);

        $this->assertEquals($expectedResult, get_object_vars($result));
    }


    function getLinks() {
        return array(
            array(
                array(),
                array(),
                array(),
            ),
            array(
                array('a'),
                array(['/srv/b.js', []], ['/srv/k.fr.js', []], ['/srv/c.js', ["type"=>"module"]],
                    ['/srv/a.js', []], ['/srv/g.js', []], ['/srv/e.js', []], ['/srv/r.fr_FR.js', []]),
                array(['/srv/b.css', []]),
            ),
            array(
                array('b'),
                array(['/srv/b.js', []]),
                array(['/srv/b.css', []]),
            ),
            array(
                array('c'),
                array(['/srv/k.fr.js', []], ['/srv/c.js', ["type"=>"module"]], ['/srv/r.fr_FR.js', []]),
                array(),
            ),
            array(
                array('d'),
                array(['/srv/d.js', []]),
                array(),
            ),
            array(
                array('e'),
                array(['/srv/g.js', []], ['/srv/e.js', []]),
                array(),
            ),
            array(
                array('f'),
                array(['/srv/b.js', []], ['/srv/k.fr.js', []], ['/srv/c.js', ["type"=>"module"]],
                    ['/srv/a.js', []], ['/srv/g.js', []], ['/srv/e.js', []],
                    ['/srv/f.js', []], ['/srv/jelix/f2.js', []], ['/srv/r.fr_FR.js', []]),
                array(['/srv/b.css', []], ['/srv/f.css', []], ['/srv/themes/sun/f2.css', []]),
            ),
            array(
                array('g'),
                array(['/srv/g.js', []]),
                array(),
            ),
            array(
                array('k'),
                array(['/srv/k.fr.js', []]),
                array(),
            ),
            array(
                array('r'),
                array(['/srv/r.fr_FR.js', []]),
                array(),
            ),
            array(
                array('a', 'e'),
                array(['/srv/b.js', []], ['/srv/k.fr.js', []], ['/srv/c.js', ["type"=>"module"]],
                    ['/srv/a.js', []], ['/srv/g.js', []], ['/srv/e.js', []], ['/srv/r.fr_FR.js', []]),
                array(['/srv/b.css', []]),
            ),
            array(
                array('a', 'f'),
                array(['/srv/b.js', []], ['/srv/k.fr.js', []], ['/srv/c.js', ["type"=>"module"]],
                    ['/srv/a.js', []], ['/srv/g.js', []], ['/srv/e.js', []], ['/srv/f.js', []],
                    ['/srv/jelix/f2.js', []], ['/srv/r.fr_FR.js', []]),
                array(['/srv/b.css', []], ['/srv/f.css', []], ['/srv/themes/sun/f2.css', []]),
            ),
            array(
                array('g', 'd', 'e'),
                array(['/srv/d.js', []], ['/srv/g.js', []], ['/srv/e.js', []] ),
                array(),
            ),
        );
    }


    /**
     * @dataProvider getLinks
     */
    function testWebAssetsSelection($selection, $jsLinks, $cssLinks) {
        $compiler = new WebAssetsCompiler();
        $ini = '
[urlengine]
jelixWWWPath=/srv/jelix/

[webassets]
useCollection=foo

[webassets_foo]

a.js = a.js
a.require = b,c
a.include = e

b.js = b.js
b.css = b.css

c.js = "c.js|type=module"
c.require = k
c.include = r

d.js = d.js

e.js = e.js
e.require = g

f.js = f.js,$jelix/f2.js
f.css = f.css,$theme/f2.css
f.require = a
f.include = r

g.js = g.js
k.js = k.$lang.js
r.js = r.$locale.js

';
        $config = (object)parse_ini_string($ini, true);
        $compiler->compile($config);
        // order is 'b', 'k', 'c', 'a', 'd', 'g', 'e', 'f', 'r'


        $select = new \Jelix\WebAssets\WebAssetsSelection();
        foreach($selection as $group) {
            $select->addAssetsGroup($group);
        }
        $select->compute($config,'foo', '/srv/', array(
            '$lang' =>  'fr',
            '$locale' =>  'fr_FR',
            '$theme' => 'themes/sun',
        ));

        $this->assertEquals($jsLinks, $select->getJsLinks());
        $this->assertEquals($cssLinks, $select->getCssLinks());
    }


}