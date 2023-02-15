<?php

use PHPUnit\Framework\TestResult;

/**
 * @package     jelix
 * @subpackage  jelix-tests
 * @author      Laurent Jouanneau
 * @copyright   2011-2023 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 *
 * @deprecated
 */
class JelixTestSuite extends PHPUnit\Framework\TestSuite {
    protected $module = null;

    public function __construct($module) {
        $this->module = $module;
        parent::__construct();
    }

    public function run(TestResult $result = null): TestResult
    {
        jApp::pushCurrentModule($this->module);
        $result = parent::run($result);
        jApp::popCurrentModule();
        return $result;
    }
}