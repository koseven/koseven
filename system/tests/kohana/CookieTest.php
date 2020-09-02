<?php

/**
 * Tests the cookie class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.cookie
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_CookieTest extends Unittest_TestCase
{
	const UNIX_TIMESTAMP      = 1411040141;
	const COOKIE_EXPIRATION   = 60;

	/**
	 * Sets up the environment
	 */
	// @codingStandardsIgnoreStart
	public function setUp()
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();
		Kohana_CookieTest_TestableCookie::$_mock_cookies_set = [];

		$this->setEnvironment([
			'Cookie::$salt'   => 'some-random-salt',
			'HTTP_USER_AGENT' => 'cli'
		]);
	}

	/**
	 * Tests that cookies are set with the global path, domain, etc options.
	 *
	 * @covers Cookie::set
	 */
	public function test_set_creates_cookie_with_configured_cookie_options()
	{
		$this->setEnvironment([
			'Cookie::$path'     => '/path',
			'Cookie::$domain'   => 'my.domain',
			'Cookie::$secure'   => TRUE,
			'Cookie::$httponly' => FALSE,
			'Cookie::$samesite' => Kohana_Cookie_Samesite::LAX
		]);

		Kohana_CookieTest_TestableCookie::set('cookie', 'value');

		$this->assertSetCookieWith([
			Kohana_Cookie_Properties::PATH       => '/path',
			Kohana_Cookie_Properties::DOMAIN     => 'my.domain',
			Kohana_Cookie_Properties::SECURE     => TRUE,
			Kohana_Cookie_Properties::HTTP_ONLY  => FALSE,
			Kohana_Cookie_Properties::SAME_SITE  => Kohana_Cookie_Samesite::LAX
		]);
	}

	/**
	 * Provider for test_set_calculates_expiry_from_lifetime
	 *
	 * @return array of $lifetime, $expect_expiry
	 */
	public function provider_set_calculates_expiry_from_lifetime()
	{
		return [
			[NULL, self::COOKIE_EXPIRATION + self::UNIX_TIMESTAMP],
			[0,    0],
			[10,   10 + self::UNIX_TIMESTAMP],
		];
	}

    /**
     * @param int $expiration
     * @param int $expect_expiry
     *
     * @dataProvider provider_set_calculates_expiry_from_lifetime
     * @covers       Cookie::set
     * @throws Kohana_Exception
     */
	public function test_set_calculates_expiry_from_lifetime($expiration, $expect_expiry)
	{
		$this->setEnvironment(['Cookie::$expiration' => self::COOKIE_EXPIRATION]);
		Kohana_CookieTest_TestableCookie::set('foo', 'bar', $expiration);
		$this->assertSetCookieWith([Kohana_Cookie_Properties::EXPIRES => $expect_expiry]);
	}

	/**
	 * @covers Cookie::get
	 */
	public function test_get_returns_default_if_cookie_missing()
	{
		unset($_COOKIE['missing_cookie']);
		$this->assertEquals('default', Cookie::get('missing_cookie', 'default'));
	}

	/**
	 * @covers Cookie::get
	 */
	public function test_get_returns_value_if_cookie_present_and_signed()
	{
		Kohana_CookieTest_TestableCookie::set('cookie', 'value');
		$cookie = Kohana_CookieTest_TestableCookie::$_mock_cookies_set[0];
		$_COOKIE[$cookie['name']] = $cookie['value'];
		$this->assertEquals('value', Cookie::get('cookie', 'default'));
	}

	/**
	 * Provider for test_get_returns_default_without_deleting_if_cookie_unsigned
	 *
	 * @return array
	 */
	public function provider_get_returns_default_without_deleting_if_cookie_unsigned()
	{
		return [
			['unsalted'],
			['un~salted'],
		];
	}

    /**
     * Verifies that unsigned cookies are not available to the kohana application, but are not affected for other
     * consumers.
     *
     * @param string $unsigned_value
     *
     * @dataProvider provider_get_returns_default_without_deleting_if_cookie_unsigned
     * @covers       Cookie::get
     * @throws Kohana_Exception
     */
	public function test_get_returns_default_without_deleting_if_cookie_unsigned($unsigned_value)
	{
		$_COOKIE['cookie'] = $unsigned_value;
		$this->assertEquals('default', Kohana_CookieTest_TestableCookie::get('cookie', 'default'));
		$this->assertEquals($unsigned_value, $_COOKIE['cookie'], '$_COOKIE not affected');
		$this->assertEmpty(Kohana_CookieTest_TestableCookie::$_mock_cookies_set, 'No cookies set or changed');
	}

	/**
	 * If a cookie looks like a signed cookie but the signature no longer matches, it should be deleted.
	 *
	 * @covers Cookie::get
	 */
	public function test_get_returns_default_and_deletes_tampered_signed_cookie()
	{
		$_COOKIE['cookie'] = Cookie::salt('cookie', 'value').'~tampered';
		$this->assertEquals('default', Kohana_CookieTest_TestableCookie::get('cookie', 'default'));
		$this->assertDeletedCookie('cookie');
	}

	/**
	 * @covers Cookie::delete
	 */
	public function test_delete_removes_cookie_from_globals_and_expires_cookie()
	{
		$_COOKIE['cookie'] = Cookie::salt('cookie', 'value').'~tampered';
		$this->assertTrue(Kohana_CookieTest_TestableCookie::delete('cookie'));
		$this->assertDeletedCookie('cookie');
	}

	/**
	 * @covers Cookie::delete
	 * @link    http://dev.kohanaframework.org/issues/3501
	 * @link    http://dev.kohanaframework.org/issues/3020
	 */
	public function test_delete_does_not_require_configured_salt()
	{
		Cookie::$salt = NULL;
		$this->assertTrue(Kohana_CookieTest_TestableCookie::delete('cookie'));
		$this->assertDeletedCookie('cookie');
	}

	/**
	 * @covers Cookie::salt
	 * @expectedException Kohana_Exception
	 */
	public function test_salt_throws_with_no_configured_salt()
	{
		Cookie::$salt = NULL;
		Cookie::salt('key', 'value');
	}

	/**
	 * @covers Cookie::salt
	 */
	public function test_salt_creates_same_hash_for_same_values_and_state()
	{
		$name  = 'cookie';
		$value = 'value';
		$this->assertEquals(Cookie::salt($name, $value), Cookie::salt($name, $value));
	}

	/**
	 * Provider for test_salt_creates_different_hash_for_different_data
	 *
	 * @return array
	 */
	public function provider_salt_creates_different_hash_for_different_data()
	{
		return [
			[['name' => 'foo', 'value' => 'bar', 'salt' => 'our-salt', 'user-agent' => 'Chrome'], ['name' => 'changed']],
			[['name' => 'foo', 'value' => 'bar', 'salt' => 'our-salt', 'user-agent' => 'Chrome'], ['value' => 'changed']],
			[['name' => 'foo', 'value' => 'bar', 'salt' => 'our-salt', 'user-agent' => 'Chrome'], ['salt' => 'changed-salt']],
			[['name' => 'foo', 'value' => 'bar', 'salt' => 'our-salt', 'user-agent' => 'Chrome'], ['user-agent' => 'Firefox']],
			[['name' => 'foo', 'value' => 'bar', 'salt' => 'our-salt', 'user-agent' => 'Chrome'], ['user-agent' => NULL]],
		];
	}

    /**
     * @param array $first_args
     * @param array $changed_args
     *
     * @dataProvider provider_salt_creates_different_hash_for_different_data
     * @covers       Cookie::salt
     * @throws Kohana_Exception
     */
	public function test_salt_creates_different_hash_for_different_data($first_args, $changed_args)
	{
		$second_args = array_merge($first_args, $changed_args);
		$hashes = [];
		foreach ([$first_args, $second_args] as $args)
		{
			Cookie::$salt = $args['salt'];
			$this->set_or_remove_http_user_agent($args['user-agent']);

			$hashes[] = Cookie::salt($args['name'], $args['value']);
		}

		$this->assertNotEquals($hashes[0], $hashes[1]);
	}

	/**
	 * Verify that a cookie was deleted from the global $_COOKIE array, and that a setcookie call was made to remove it
	 * from the client.
	 *
	 * @param string $name
	 */
	// @codingStandardsIgnoreStart
	protected function assertDeletedCookie($name)
	// @codingStandardsIgnoreEnd
	{
		$this->assertArrayNotHasKey($name, $_COOKIE);
		// To delete the client-side cookie, Cookie::delete should send a new cookie with value NULL and expiry in the past
		$this->assertSetCookieWith([
			Kohana_Cookie_Properties::NAME      => $name,
			Kohana_Cookie_Properties::VALUE     => NULL,
			Kohana_Cookie_Properties::EXPIRES   => -86400,
			Kohana_Cookie_Properties::PATH      => Cookie::$path,
			Kohana_Cookie_Properties::DOMAIN    => Cookie::$domain,
			Kohana_Cookie_Properties::SECURE    => Cookie::$secure,
			Kohana_Cookie_Properties::HTTP_ONLY => Cookie::$httponly,
			Kohana_Cookie_Properties::SAME_SITE => Cookie::$samesite,
		]);
	}

	/**
	 * Verify that there was a single call to setcookie including the provided named arguments
	 *
	 * @param array $expected
	 */
	// @codingStandardsIgnoreStart
	protected function assertSetCookieWith($expected)
	// @codingStandardsIgnoreEnd
	{
		$this->assertCount(1, Kohana_CookieTest_TestableCookie::$_mock_cookies_set);
		$relevant_values = array_intersect_key(Kohana_CookieTest_TestableCookie::$_mock_cookies_set[0], $expected);
		$this->assertEquals($expected, $relevant_values);
	}

	/**
	 * Configure the $_SERVER[HTTP_USER_AGENT] environment variable for the test
	 *
	 * @param string $user_agent
	 */
	protected function set_or_remove_http_user_agent($user_agent)
	{
		if ($user_agent === NULL)
		{
			unset($_SERVER['HTTP_USER_AGENT']);
		}
		else
		{
			$_SERVER['HTTP_USER_AGENT'] = $user_agent;
		}
	}
}

/**
 * Class Kohana_CookieTest_TestableCookie wraps the cookie class to mock out the actual setcookie and time calls for
 * unit testing.
 */
class Kohana_CookieTest_TestableCookie extends Cookie {

	/**
	 * @var array setcookie calls that were made
	 */
	public static $_mock_cookies_set = [];

	/**
	 * {@inheritdoc}
	 */
	protected static function _setcookie($name, $value, $expires, $path, $domain, $secure, $httponly, $samesite)
	{
		self::$_mock_cookies_set[] = [
			Kohana_Cookie_Properties::NAME      => $name,
			Kohana_Cookie_Properties::VALUE     => $value,
			Kohana_Cookie_Properties::EXPIRES   => $expires,
			Kohana_Cookie_Properties::PATH      => $path,
			Kohana_Cookie_Properties::DOMAIN    => $domain,
			Kohana_Cookie_Properties::SECURE    => $secure,
			Kohana_Cookie_Properties::HTTP_ONLY => $httponly,
			Kohana_Cookie_Properties::SAME_SITE => $samesite
		];

		return TRUE;
	}

	/**
	 * @return int
	 */
	protected static function _time()
	{
		return Kohana_CookieTest::UNIX_TIMESTAMP;
	}

}
