<?php
/**
 * @package     testapp
 * @subpackage  jelix_tests module
 * @author      Laurent Jouanneau
 * @copyright   2023 laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

use PHPUnit\Framework\TestCase;

class passwordTest extends TestCase
{

    public function testPasswordStrength() {

        $this->assertEquals(jAuthPassword::checkPasswordStrength(''), jAuthPassword::STRENGTH_NONE);
        $this->assertEquals(jAuthPassword::checkPasswordStrength('qsdq qwerty qsdoi'), jAuthPassword::STRENGTH_BAD_PASS);
        $this->assertEquals(jAuthPassword::checkPasswordStrength('qsdq f**k qsdoi'), jAuthPassword::STRENGTH_BAD_PASS);
        $this->assertEquals(jAuthPassword::checkPasswordStrength('qsdq fp*k qsdoi'), jAuthPassword::STRENGTH_GOOD);
        $this->assertTrue(jAuthPassword::checkPasswordStrength('abcdfg') <= jAuthPassword::STRENGTH_POOR);
        $this->assertTrue(jAuthPassword::checkPasswordStrength('abcd2!fg') <= jAuthPassword::STRENGTH_POOR);
        $this->assertTrue(jAuthPassword::checkPasswordStrength('abcdgqfgfpo') <= jAuthPassword::STRENGTH_WEAK);
        $this->assertTrue(jAuthPassword::checkPasswordStrength('Hefv6OdJiag/ag4oj<') > jAuthPassword::STRENGTH_GOOD);

    }
}
