<?php

require('diff/difflib.php');
require('diff/diffhtml.php');

if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', 'simpletest/');
}

require_once(SIMPLE_TEST . 'unit_tester.php');
require_once(SIMPLE_TEST . 'reporter.php');
require_once(SIMPLE_TEST . 'myhtmlreporter.class.php');
require_once(SIMPLE_TEST . 'junittestcase.class.php');
require_once('../jtpl_standalone_prepend.php');
require_once('compiler.php');
require_once('expressions_parsing.php');


jTplConfig::$lang = 'fr';

$test = new GroupTest('All tests');
$test->addTestCase(new UTjtplcontent());
$test->addTestCase(new UTjtplexpr());
$test->run(new myHtmlReporter());


