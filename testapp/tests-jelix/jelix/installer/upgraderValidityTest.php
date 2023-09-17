<?php
/**
 * @package     testapp
 * @subpackage  jelix_tests module
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2023 Laurent Jouanneau
 * @link        https://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

use Jelix\Core\Infos\ModuleInfos;
use Jelix\Installer\ModuleStatus;
require_once(__DIR__.'/installer.lib.php');

class testUpgraderComponentModule3 extends \Jelix\Installer\ModuleInstallerLauncher {

    public function __construct(ModuleStatus $moduleStatus, ModuleInfos $moduleInfos)
    {
        $this->moduleStatus = $moduleStatus;
        $this->name = $moduleStatus->getName();
        $this->moduleInfos = $moduleInfos;
    }

    /**
     * @param string $newVersion version into module.xml
     * @param string $newVersionDate date into module.xml
     * @param string $currentVersion  version into the installer.ini
     * @param string $currentVersionDate date into the installer.ini
     * @param string[] $targetVersions target versions of the upgrader
     * @param string $upgraderDate     date of the upgrader
     * @param string $firstVersion  first version from the installer.ini
     * @param string $firstVersionDate  first version date from the installer.ini
     * @return false|mixed|string
     */
    function testCheckUpgraderValidity
    (
        $currentVersion,
        $currentVersionDate,
        $newVersion,
        $newVersionDate,
        array $targetVersions,
        $upgraderDate
    )
    {
        return $this->checkUpgraderValidity(
            $currentVersion,
            $currentVersionDate,
            $newVersion,
            $newVersionDate,
            $targetVersions,
            $upgraderDate
        );
    }
}



class upgraderValidityTest extends \Jelix\UnitTests\UnitTestCase
{
    public $globalSetup;

    function setUp() : void {
        self::initJelixConfig();
        $this->globalSetup = new testInstallerGlobalSetup();
        jApp::saveContext();
    }

    function tearDown() : void {
        jApp::restoreContext();
    }

    public function getUpgraderInfos()
    {
        return array(
            // v,    v date,       new v,  new v date,  targets,  upgrader date, expected result
            // 0
            // upgrade from 1.0 to 1.1, and have an upgrader for 1.1 -> to execute
            ['1.0', '2021-01-01', '1.1', '2021-02-02', ['1.1'], '2021-01-30', '1.1' ],

            // upgrade from 1.1 to 1.3, and have an upgrader for 1.2 -> to execute
            ['1.1', '2021-02-02', '1.3', '2021-03-03', ['1.2'], '2021-02-25', '1.2' ],

            // upgrade from 1.1 to 1.3, and have an upgrader for 1.1 -> to ignore (already executed)
            ['1.1', '2021-02-02', '1.3', '2021-03-03', ['1.1'], '2021-01-30', false ],

            // upgrade from 1.3 to 2.0, and have an upgrader for 1.1 -> to ignore
            ['1.3', '2021-03-03', '2.0', '2022-01-01', ['1.1'], '2021-01-30', false ],

            // upgrade from 1.4 to 2.0, and have an upgrader for 1.3 and 2.0 -> to ignore
            // because already applied in 1.4
            ['1.4', '2021-04-04', '2.0', '2022-01-01', ['1.3', '2.0'], '2021-03-30', false ],

            // upgrade from 1.4 to 2.0, and have an upgrader for 1.3 and 2.0 -> to execute
            // because upgrader date is higher that 1.4 date
            ['1.4', '2021-04-04', '2.0', '2022-01-01', ['1.3', '2.0'], '2021-10-30', '2.0' ],

            // upgrade from 2.0 to 2.1, and have an upgrader for 1.3 and 2.0 -> to ignore
            ['2.0', '2022-01-01', '2.1', '2022-01-01', ['1.3', '2.0'], '2021-10-30', false ],

            // upgrade from 1.4 to 2.5, and have an upgrader for 1.2 and 2.3 -> to ignore
            // because already applied in 1.4
            ['1.4', '2021-04-04', '2.5', '2023-02-01', ['1.2', '2.3'], '2021-02-02', false ],

            // upgrade from 1.4 to 2.5, and have an upgrader for 1.5 and 2.3 -> to execute
            ['1.4', '2021-04-04', '2.5', '2023-02-01', ['1.5', '2.3'], '2022-10-30', '1.5' ],
            // 9
            // upgrade from 3.5.10 to 3.6.2 and upgrade for 3.6.1 : it should be executed, even if its date
            // is before the release of 3.5.10
            [ '3.5.10', '2023-01-25', '3.6.2', '2023-02-17', ['3.6.1-beta.1'], '2022-11-30', '3.6.1-beta.1' ],
            [ '3.5.10', '2023-01-25', '3.6.2', '2023-02-17', ['3.6.1-beta.1'], '', '3.6.1-beta.1' ],

            ['1.2.3', '2022-02-02', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1.2', '1.2.4'], '2022-02-20', '1.2.4'],

            // 12 testGetUpgradersWithOneValidUpgrader
            ['1.2.3', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1.2', '1.2.4'], '', '1.2.4'], // testinstall2ModuleUpgrader_newupgraderfilename
            ['1.2.3', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1.3', '1.2.2'], '2011-01-13', false], //testinstall2ModuleUpgrader_newupgraderfilenamedate
            ['1.2.3', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1.5'], '', false], //testinstall2ModuleUpgrader_second
            ['1.2.3', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1'], '', false], // testinstall2ModuleUpgrader_first

            ['1.2.3', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1.3', '1.2.2'], '', false], //testinstall2ModuleUpgrader_newupgraderfilenamedate

            // 17 testGetUpgradersWithTwoValidUpgrader
            ['1.1.2', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1.2', '1.2.4'], '', false], // testinstall2ModuleUpgrader_newupgraderfilename
            ['1.1.2', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1.3', '1.2.2'], '2011-01-13', '1.1.3' ], //testinstall2ModuleUpgrader_newupgraderfilenamedate
            ['1.1.2', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1.5'], '', '1.1.5'], //testinstall2ModuleUpgrader_second
            ['1.1.2', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1'], '', false], // testinstall2ModuleUpgrader_first

            // 21  testGetUpgradersWithTwoValidUpgrader2
            ['1.1.1', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1.2', '1.2.4'], '', '1.1.2'],
            ['1.1.1', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1.3', '1.2.2'], '2011-01-13', '1.1.3'],
            ['1.1.1', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1.5'], '', '1.1.5'],
            ['1.1.1', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1'], '', false],

            // 25 testGetUpgradersWithTwoValidUpgraderWithDate
            ['1.1', '2011-01-10', '1.1.5','2011-01-15', ['1.1.2', '1.2.4'], '', '1.1.2'], // testinstall2ModuleUpgrader_newupgraderfilename
            ['1.1', '2011-01-10', '1.1.5','2011-01-15', ['1.1.3', '1.2.2'], '2011-01-13', '1.1.3'], //testinstall2ModuleUpgrader_newupgraderfilenamedate
            ['1.1', '2011-01-10', '1.1.5','2011-01-15', ['1.1.5'], '', '1.1.5'], //testinstall2ModuleUpgrader_second
            ['1.1', '2011-01-10', '1.1.5','2011-01-15', ['1.1'], '', false], // testinstall2ModuleUpgrader_first

            // 29
            ['1.1.5', '2011-01-15', '1.2.5', '2011-01-25', ['1.1.2', '1.2.4'], '', '1.2.4'],  // testinstall2ModuleUpgrader_newupgraderfilename
            ['1.1.5', '2011-01-15', '1.2.5', '2011-01-25', ['1.1.3', '1.2.2'], '2011-01-13', false ],//testinstall2ModuleUpgrader_newupgraderfilenamedate
            ['1.1.5', '2011-01-15', '1.2.5', '2011-01-25', ['1.1.5'], '', false], //testinstall2ModuleUpgrader_second
            ['1.1.5', '2011-01-15', '1.2.5', '2011-01-25', ['1.1'], '', false],// testinstall2ModuleUpgrader_first

            // 33 testGetUpgradersWithAllUpgraders
            ['0.9', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1.2', '1.2.4'], '', '1.1.2'],  // testinstall2ModuleUpgrader_newupgraderfilename
            ['0.9', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1.3', '1.2.2'], '2011-01-13', '1.1.3'],//testinstall2ModuleUpgrader_newupgraderfilenamedate
            ['0.9', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1.5'], '', '1.1.5'], //testinstall2ModuleUpgrader_second
            ['0.9', '', '1.8.0-rc.4', '2023-01-23 12:53', ['1.1'], '', '1.1'],// testinstall2ModuleUpgrader_first
        );
    }

    /**
     * @dataProvider getUpgraderInfos
     * @return void
     */
    public function testUpgraderValidity(
        $currentVersion,
        $currentVersionDate,
        $newVersion,
        $newVersionDate,
        array $targetVersions,
        $upgraderDate, $expectedResult)
    {

        $moduleConfigInfo = array(
            'test.enabled'=>true,
            'test.installed'=>true,
            'test.version'=> '',
            'test.dbprofile'=> '',
        );

        $path = jApp::appPath().'modules/test/';
        $moduleStatus = new \Jelix\Installer\ModuleStatus('test',
            $path, $moduleConfigInfo, true);

        $moduleInfos = new ModuleInfos($path, false);
        $moduleInfos->name = 'test';
        $moduleInfos->version = '';
        $moduleInfos->versionDate = '';

        $modComp = new testUpgraderComponentModule3($moduleStatus, $moduleInfos);

        $result = $modComp->testCheckUpgraderValidity(
            $currentVersion,
            $currentVersionDate,
            $newVersion,
            $newVersionDate,
            $targetVersions,
            $upgraderDate
        );

        $this->assertEquals($expectedResult, $result);

    }

}