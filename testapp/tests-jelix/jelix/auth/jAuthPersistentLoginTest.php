<?php
/**
 * @package     testapp
 * @subpackage  jelix_tests module
 *
 * @author      Laurent Jouanneau
 * @copyright   2026 Laurent Jouanneau
 *
 * @link        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * Subclass that captures cookie operations instead of calling the
 * real PHP setcookie() function, which cannot work in CLI tests.
 */
class testableJAuthPersistentLogin extends jAuthPersistentLogin
{
    public $setCookieCalls = array();
    public $deleteCookieCount = 0;

    protected function setCookie($login, $token, $series, $expiresAt)
    {
        $this->setCookieCalls[] = array(
            'login' => $login,
            'token' => $token,
            'series' => $series,
            'expiresAt' => $expiresAt,
        );
    }

    protected function deleteCookie()
    {
        $this->deleteCookieCount++;
    }

    public function getCookieName()
    {
        return $this->persistentCookieName;
    }

    public function getCookiePath()
    {
        return $this->persistentCookiePath;
    }

    public function getCookieDuration()
    {
        return $this->persistentCookieDuration;
    }
}

class jAuthPersistentLoginTest extends \Jelix\UnitTests\UnitTestCaseDb
{
    protected $dbProfile = '';

    public static function setUpBeforeClass(): void
    {
        self::initJelixConfig();
    }

    public function setUp(): void
    {
        parent::setUp();
        try {
            \Jelix\Core\Profiles::get('jdb', '', true);
        } catch (Exception $e) {
            $this->markTestSkipped(get_class($this).' cannot be run: '.$e->getMessage());
            return;
        }
        try {
            $this->emptyTable('jauthremembertoken');
        } catch (Exception $e) {
            $this->markTestSkipped(get_class($this).' cannot be run: jauthremembertoken table is missing - '.$e->getMessage());
            return;
        }
    }

    public function tearDown(): void
    {
        try {
            $this->emptyTable('jauthremembertoken');
        } catch (Exception $e) {
            // ignore: setUp may have skipped the test
        }
    }

    // ============ Constructor / configuration ============

    public function testDefaultConfiguration()
    {
        $persist = new testableJAuthPersistentLogin(array());
        $this->assertFalse($persist->isPersistencyEnabled());
        $this->assertEquals('jauthPersistentSession', $persist->getCookieName());
        $this->assertEquals('', $persist->getCookiePath());
        $this->assertEquals(172800, $persist->getCookieDuration());
    }

    public function testPersistencyEnabledWithBoolean()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));
        $this->assertTrue($persist->isPersistencyEnabled());
    }

    public function testPersistencyDisabledWithFalse()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => false));
        $this->assertFalse($persist->isPersistencyEnabled());
    }

    public function testPersistencyEnabledWithTruthyValue()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => 1));
        $this->assertTrue($persist->isPersistencyEnabled());
    }

    public function testCustomCookieName()
    {
        $persist = new testableJAuthPersistentLogin(array(
            'persistant_cookie_name' => 'mySession',
        ));
        $this->assertEquals('mySession', $persist->getCookieName());
    }

    public function testCustomCookiePath()
    {
        $persist = new testableJAuthPersistentLogin(array(
            'persistant_cookie_path' => '/myapp',
        ));
        $this->assertEquals('/myapp', $persist->getCookiePath());
    }

    public function testDurationConvertedFromDaysToSeconds()
    {
        $persist = new testableJAuthPersistentLogin(array(
            'persistant_duration' => 3,
        ));
        $this->assertEquals(3 * 86400, $persist->getCookieDuration());
    }

    public function testDurationFromStringIsCastToInt()
    {
        $persist = new testableJAuthPersistentLogin(array(
            'persistant_duration' => '7',
        ));
        $this->assertEquals(7 * 86400, $persist->getCookieDuration());
    }

    // ============ checkTokenFromCookie ============

    public function testCheckTokenWithNoCookieReturnsFalse()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));
        $result = $persist->checkTokenFromCookie(array(), false);
        $this->assertFalse($result);
        $this->assertEquals(0, $persist->deleteCookieCount);
    }

    public function testCheckTokenWithMalformedCookieDeletesCookieAndReturnsFalse()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));
        $result = $persist->checkTokenFromCookie(array(
            'jauthPersistentSession' => 'just-one-part',
        ), false);
        $this->assertFalse($result);
        $this->assertEquals(1, $persist->deleteCookieCount);
    }

    public function testCheckTokenWithEmptyPartsDeletesCookieAndReturnsFalse()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));
        $result = $persist->checkTokenFromCookie(array(
            'jauthPersistentSession' => ':series:token',
        ), false);
        $this->assertFalse($result);
        $this->assertEquals(1, $persist->deleteCookieCount);
    }

    public function testCheckTokenWithNonStringCookieReturnsFalse()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));
        $result = $persist->checkTokenFromCookie(array(
            'jauthPersistentSession' => array('not', 'a', 'string'),
        ), false);
        $this->assertFalse($result);
        $this->assertEquals(0, $persist->deleteCookieCount);
    }

    public function testCheckTokenWhenUserConnectedDeletesExpiredTokens()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));

        $this->insertToken('alice', 'expired_token', 'expired_series', time() - 100);
        $this->insertToken('bob', 'fresh_token', 'fresh_series', time() + 1000);

        $result = $persist->checkTokenFromCookie(array(), true);
        $this->assertFalse($result);

        $this->assertEquals(0, $this->countRecordsForLogin('alice'), 'expired token should be deleted');
        $this->assertEquals(1, $this->countRecordsForLogin('bob'), 'valid token should be kept');
    }

    public function testCheckTokenWithValidCookieRotatesToken()
    {
        $persist = new testableJAuthPersistentLogin(array(
            'persistant_enable' => true,
            'persistant_duration' => 1,
        ));

        $login = 'alice';
        $token = 'mytoken_xyz';
        $series = 'myseries_abc';

        $this->insertToken($login, $token, $series, time() + 1000);

        $cookies = array(
            'jauthPersistentSession' => $login.':'.$series.':'.$token,
        );

        $result = $persist->checkTokenFromCookie($cookies, false);
        $this->assertEquals($login, $result);

        // setCookie was called once with the same series but a NEW token
        $this->assertCount(1, $persist->setCookieCalls);
        $call = $persist->setCookieCalls[0];
        $this->assertEquals($login, $call['login']);
        $this->assertEquals($series, $call['series']);
        $this->assertNotEquals($token, $call['token']);

        // a single record remains for that user (rotated, same series)
        $this->assertEquals(1, $this->countRecordsForLogin($login));

        // and the record is the new one
        $dao = jDao::get('jelix~jauthremembertoken');
        $rec = $dao->getByLoginAndSeries($login, hash('sha256', $series), 0);
        $this->assertNotFalse($rec);
        $this->assertEquals(hash('sha256', $call['token']), $rec->token_hash);
    }

    public function testCheckTokenWithExpiredRecordReturnsFalse()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));

        $login = 'alice';
        $token = 'tok';
        $series = 'ser';

        // expired token in DB
        $this->insertToken($login, $token, $series, time() - 100);

        $cookies = array(
            'jauthPersistentSession' => $login.':'.$series.':'.$token,
        );

        $result = $persist->checkTokenFromCookie($cookies, false);
        $this->assertFalse($result);
        $this->assertCount(0, $persist->setCookieCalls);

        // expired token has been cleaned up
        $this->assertEquals(0, $this->countRecordsForLogin($login));
    }

    public function testCheckTokenTokenTheftDeletesAllUserTokens()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));

        $login = 'alice';
        $series = 'series_abc';

        // current valid token under that series
        $this->insertToken($login, 'current_token', $series, time() + 1000);
        // another active series for the same user
        $this->insertToken($login, 'another_token', 'another_series', time() + 2000);
        // a token belonging to another user (must NOT be touched)
        $this->insertToken('bob', 'bob_token', 'bob_series', time() + 1000);

        // cookie comes in with a stale (stolen) token but matching series
        $cookies = array(
            'jauthPersistentSession' => $login.':'.$series.':stolen_token',
        );

        $result = $persist->checkTokenFromCookie($cookies, false);
        $this->assertFalse($result);
        $this->assertCount(0, $persist->setCookieCalls);

        // all tokens of the user are deleted
        $this->assertEquals(0, $this->countRecordsForLogin($login));
        // bob's token must remain
        $this->assertEquals(1, $this->countRecordsForLogin('bob'));
    }

    public function testCheckTokenNoMatchingRecordCleansExpiredForLogin()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));

        // expired token for the cookie's login
        $this->insertToken('alice', 'tok_old', 'old_series', time() - 100);
        // expired token for someone else (must remain)
        $this->insertToken('bob', 'bob_tok', 'bob_series', time() - 200);

        // cookie with non-existent series
        $cookies = array(
            'jauthPersistentSession' => 'alice:nonexistent:whatever',
        );

        $result = $persist->checkTokenFromCookie($cookies, false);
        $this->assertFalse($result);

        $this->assertEquals(0, $this->countRecordsForLogin('alice'));
        $this->assertEquals(1, $this->countRecordsForLogin('bob'));
    }

    public function testCheckTokenUsesConfiguredCookieName()
    {
        $persist = new testableJAuthPersistentLogin(array(
            'persistant_enable' => true,
            'persistant_cookie_name' => 'customCookie',
        ));

        // Cookie uses default name: should be ignored
        $cookies = array(
            'jauthPersistentSession' => 'alice:s:t',
        );
        $this->assertFalse($persist->checkTokenFromCookie($cookies, false));
        $this->assertEquals(0, $persist->deleteCookieCount);
    }

    // ============ generateCookieWithNewToken ============

    public function testGenerateCookieReturnsZeroWhenPersistencyDisabled()
    {
        $persist = new testableJAuthPersistentLogin(array());
        $result = $persist->generateCookieWithNewToken('alice');
        $this->assertEquals(0, $result);
        $this->assertCount(0, $persist->setCookieCalls);
        $this->assertEquals(0, $this->countRecordsForLogin('alice'));
    }

    public function testGenerateCookieReturnsZeroWhenCookieNameEmpty()
    {
        $persist = new testableJAuthPersistentLogin(array(
            'persistant_enable' => true,
            'persistant_cookie_name' => '',
        ));
        $result = $persist->generateCookieWithNewToken('alice');
        $this->assertEquals(0, $result);
        $this->assertCount(0, $persist->setCookieCalls);
    }

    public function testGenerateCookieCreatesRecordAndCallsSetCookie()
    {
        $persist = new testableJAuthPersistentLogin(array(
            'persistant_enable' => true,
            'persistant_duration' => 2,
        ));

        $before = time();
        $result = $persist->generateCookieWithNewToken('alice');
        $after = time();

        // returned expiration is around now + 2 days
        $this->assertGreaterThanOrEqual($before + 2 * 86400, $result);
        $this->assertLessThanOrEqual($after + 2 * 86400, $result);

        // exactly one record was created for alice
        $this->assertEquals(1, $this->countRecordsForLogin('alice'));

        // setCookie was called with that login and the same expiration
        $this->assertCount(1, $persist->setCookieCalls);
        $call = $persist->setCookieCalls[0];
        $this->assertEquals('alice', $call['login']);
        $this->assertEquals($result, $call['expiresAt']);
        $this->assertNotEmpty($call['token']);
        $this->assertNotEmpty($call['series']);
    }

    public function testGenerateCookieStoresHashedTokenAndSeries()
    {
        $persist = new testableJAuthPersistentLogin(array(
            'persistant_enable' => true,
        ));

        $persist->generateCookieWithNewToken('alice');
        $call = $persist->setCookieCalls[0];

        $dao = jDao::get('jelix~jauthremembertoken');
        $rec = $dao->getByLoginAndSeries('alice', hash('sha256', $call['series']), 0);
        $this->assertNotFalse($rec);
        $this->assertEquals(hash('sha256', $call['token']), $rec->token_hash);
        // raw values must NOT appear in the database
        $this->assertNotEquals($call['token'], $rec->token_hash);
        $this->assertNotEquals($call['series'], $rec->series_hash);
    }

    public function testGenerateCookieGeneratesUniqueTokensOnEachCall()
    {
        $persist = new testableJAuthPersistentLogin(array(
            'persistant_enable' => true,
        ));

        $persist->generateCookieWithNewToken('alice');
        $persist->generateCookieWithNewToken('alice');

        $this->assertCount(2, $persist->setCookieCalls);
        $this->assertNotEquals(
            $persist->setCookieCalls[0]['token'],
            $persist->setCookieCalls[1]['token']
        );
        $this->assertNotEquals(
            $persist->setCookieCalls[0]['series'],
            $persist->setCookieCalls[1]['series']
        );

        // both records must coexist in DB
        $this->assertEquals(2, $this->countRecordsForLogin('alice'));
    }

    // ============ deleteUserToken ============

    public function testDeleteUserTokenWithMatchingLoginDeletesSeries()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));

        $this->insertToken('alice', 'tok1', 'series1', time() + 1000);
        // another series for alice (must NOT be touched)
        $this->insertToken('alice', 'tok2', 'series2', time() + 1000);

        $cookies = array(
            'jauthPersistentSession' => 'alice:series1:tok1',
        );
        $persist->deleteUserToken($cookies, 'alice');

        $this->assertEquals(1, $persist->deleteCookieCount);
        // only the matching series was deleted
        $this->assertEquals(1, $this->countRecordsForLogin('alice'));
        $dao = jDao::get('jelix~jauthremembertoken');
        $rec = $dao->getByLoginAndSeries('alice', hash('sha256', 'series2'), 0);
        $this->assertNotFalse($rec);
    }

    public function testDeleteUserTokenWithMismatchedLoginDeletesByToken()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));

        $this->insertToken('alice', 'tok1', 'series1', time() + 1000);
        $this->insertToken('alice', 'tok2', 'series2', time() + 1000);

        // logout login does not match the cookie's login
        $cookies = array(
            'jauthPersistentSession' => 'alice:series1:tok1',
        );
        $persist->deleteUserToken($cookies, 'someoneelse');

        $this->assertEquals(1, $persist->deleteCookieCount);
        // only the specific token was deleted
        $this->assertEquals(1, $this->countRecordsForLogin('alice'));
    }

    public function testDeleteUserTokenWithMalformedCookieDoesNotTouchDb()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));

        $this->insertToken('alice', 'tok', 'series', time() + 1000);

        $cookies = array(
            'jauthPersistentSession' => 'malformed',
        );
        $persist->deleteUserToken($cookies, 'alice');

        // deleteCookie() is called by getCookiesParts() for malformed cookies
        $this->assertEquals(1, $persist->deleteCookieCount);
        // but the DB is untouched
        $this->assertEquals(1, $this->countRecordsForLogin('alice'));
    }

    public function testDeleteUserTokenWithNoCookieDoesNothing()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));

        $this->insertToken('alice', 'tok', 'series', time() + 1000);

        $persist->deleteUserToken(array(), 'alice');

        $this->assertEquals(0, $persist->deleteCookieCount);
        $this->assertEquals(1, $this->countRecordsForLogin('alice'));
    }

    public function testDeleteUserTokenAlsoDeletesExpiredTokensForLogin()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));

        $this->insertToken('alice', 'tok1', 'series1', time() + 1000);
        $this->insertToken('alice', 'tok_expired', 'series_old', time() - 100);

        $cookies = array(
            'jauthPersistentSession' => 'alice:series1:tok1',
        );
        $persist->deleteUserToken($cookies, 'alice');

        // both the matched series and the expired token must be removed
        $this->assertEquals(0, $this->countRecordsForLogin('alice'));
    }

    // ============ deleteAllUserTokens ============

    public function testDeleteAllUserTokensDeletesOnlyForLogin()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));

        $this->insertToken('alice', 'a1', 's1', time() + 1000);
        $this->insertToken('alice', 'a2', 's2', time() + 2000);
        $this->insertToken('bob', 'b1', 'bs1', time() + 1000);

        $persist->deleteAllUserTokens('alice');

        $this->assertEquals(0, $this->countRecordsForLogin('alice'));
        $this->assertEquals(1, $this->countRecordsForLogin('bob'));
    }

    public function testDeleteAllUserTokensWhenNoTokenExists()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));
        // should not throw
        $persist->deleteAllUserTokens('nobody');
        $this->assertEquals(0, $this->countRecordsForLogin('nobody'));
    }

    // ============ deleteExpiredTokens ============

    public function testDeleteExpiredTokensDeletesOnlyExpired()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));

        $this->insertToken('alice', 'tok_expired', 'series_expired', time() - 100);
        $this->insertToken('bob', 'tok_valid', 'series_valid', time() + 1000);

        $persist->deleteExpiredTokens();

        $this->assertEquals(0, $this->countRecordsForLogin('alice'));
        $this->assertEquals(1, $this->countRecordsForLogin('bob'));
    }

    public function testDeleteExpiredTokensOnEmptyTable()
    {
        $persist = new testableJAuthPersistentLogin(array('persistant_enable' => true));
        // should not throw
        $persist->deleteExpiredTokens();
        $this->assertEquals(0, $this->countAll());
    }

    // ============ helpers ============

    protected function insertToken($login, $token, $series, $expiresAt)
    {
        $dao = jDao::get('jelix~jauthremembertoken');
        $rec = $dao->createRecord();
        $rec->login = $login;
        $rec->token_hash = hash('sha256', $token);
        $rec->series_hash = hash('sha256', $series);
        $rec->expires_at = $expiresAt;
        $dao->insert($rec);
    }

    protected function countRecordsForLogin($login)
    {
        $db = jDb::getConnection($this->dbProfile);
        $sql = 'SELECT COUNT(*) AS c FROM '.$db->encloseName('jauthremembertoken')
            .' WHERE '.$db->encloseName('login').' = '.$db->quote($login);
        $rs = $db->query($sql);
        $r = $rs->fetch();
        return (int) $r->c;
    }

    protected function countAll()
    {
        $dao = jDao::get('jelix~jauthremembertoken');
        return $dao->countAll();
    }
}
