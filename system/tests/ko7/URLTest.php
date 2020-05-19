<?php

/**
 * Tests URL
 *
 * @group ko7
 * @group ko7.core
 * @group ko7.core.url
 *
 * @package    KO7
 * @category   Tests
 *
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_URLTest extends Unittest_TestCase
{

	/**
	 * Sets up the environment
	 */
	// @codingStandardsIgnoreStart
	public function setUp(): void
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();
		KO7::$config->load('url')->set(
			'trusted_hosts',
			['www\.example\.com', 'sub\.example\.com', 'example\.com', 'example\.org']
		);
	}

	/**
	 * Default values for the environment, see setEnvironment
	 * @var array
	 */
	// @codingStandardsIgnoreStart
	protected $environmentDefault =	[
		'KO7::$base_url'	=> '/ko7/',
		'KO7::$index_file'=> 'index.php',
		'HTTP_HOST' => 'example.com',
		'_GET'		=> [],
	];
	// @codingStandardsIgnoreEnd

	/**
	 * Provides test data for test_base()
	 *
	 * @return array
	 */
	public function provider_base()
	{
		return [
			// $protocol, $index, $subdomain, $expected, $enviroment

			// Test with different combinations of parameters for max code coverage
			[NULL,    FALSE, NULL,  NULL, ['KO7::$base_url' => NULL]],
			[NULL,    FALSE, NULL,  '/', ['KO7::$base_url' => '/']],
			[NULL,    FALSE, NULL,  '/ko7/'],
			['http',  FALSE, NULL,  'http://example.com/ko7/'],
			['http',  FALSE, 'sub', 'http://sub.example.com/ko7/'],
			['http',  FALSE, 'sub', 'http://sub.example.com/ko7/', ['HTTP_HOST' => 'sub.example.com']],
			['http',  FALSE, 'sub', 'http://sub.example.com/ko7/', ['HTTP_HOST' => 'invalid.example.com']],
			[NULL,    TRUE,  NULL,  '/ko7/index.php/'],
			['http',  TRUE,  NULL,  'http://example.com/ko7/index.php/'],
			['http',  TRUE,  'sub', 'http://sub.example.com/ko7/index.php/'],
			['https', TRUE,  NULL,  'https://example.com/ko7/index.php/'],
			['https', TRUE,  'sub', 'https://sub.example.com/ko7/index.php/'],
			['ftp',   TRUE,  NULL,  'ftp://example.com/ko7/index.php/'],
			['ftp',   TRUE,  'sub', 'ftp://sub.example.com/ko7/index.php/'],

			// Test for automatic protocol detection, protocol = TRUE
			[TRUE, TRUE, NULL, 'cli://example.com/ko7/index.php/', ['HTTPS' => FALSE, 'Request::$initial' => Request::factory('/')->protocol('cli')]],

			// Change base url'
			['https', FALSE, NULL, 'https://example.com/ko7/', ['KO7::$base_url' => 'omglol://example.com/ko7/']],

			// Use port in base url, issue #3307
			['http', FALSE, NULL, 'http://example.com:8080/', ['KO7::$base_url' => 'example.com:8080/']],

			// Use protocol from base url if none specified
			[NULL, FALSE, NULL, 'http://www.example.com/', ['KO7::$base_url' => 'http://www.example.com/']],

			[NULL, FALSE, 'sub', 'http://sub.example.com/', ['KO7::$base_url' => 'http://www.example.com/']],

			// Use HTTP_HOST before SERVER_NAME
			['http', FALSE, NULL, 'http://example.com/ko7/', ['HTTP_HOST' => 'example.com', 'SERVER_NAME' => 'example.org']],

			// Use SERVER_NAME if HTTP_HOST DNX
			['http', FALSE, NULL, 'http://example.org/ko7/', ['HTTP_HOST' => NULL, 'SERVER_NAME' => 'example.org']],
		];
	}

	/**
	 * Tests URL::base()
	 *
	 * @test
	 * @dataProvider provider_base
	 * @param mixed   $protocol    Parameter for Url::base()
	 * @param boolean $index       Parameter for Url::base()
	 * @param string  $subdomain   Parameter for Url::base()
	 * @param string  $expected    Expected url
	 * @param array   $enviroment  Array of enviroment vars to change @see KO7_URLTest::setEnvironment()
	 */
	public function test_base($protocol, $index, $subdomain, $expected, array $enviroment = [])
	{
		$this->setEnvironment($enviroment);

		$this->assertSame(
			$expected,
			URL::base($protocol, $index, $subdomain)
		);
	}

	/**
	 * Provides test data for test_site()
	 *
	 * @return array
	 */
	public function provider_site()
	{
		return [
			['', NULL,		'/ko7/index.php/'],
			['', 'http',			'http://example.com/ko7/index.php/'],

			['my/site', NULL, '/ko7/index.php/my/site'],
			['my/site', 'http',  'http://example.com/ko7/index.php/my/site'],

			// @ticket #3110
			['my/site/page:5', NULL, '/ko7/index.php/my/site/page:5'],
			['my/site/page:5', 'http', 'http://example.com/ko7/index.php/my/site/page:5'],

			['my/site?var=asd&ko7=awesome', NULL,  '/ko7/index.php/my/site?var=asd&ko7=awesome'],
			['my/site?var=asd&ko7=awesome', 'http',  'http://example.com/ko7/index.php/my/site?var=asd&ko7=awesome'],

			['?ko7=awesome&life=good', NULL, '/ko7/index.php/?ko7=awesome&life=good'],
			['?ko7=awesome&life=good', 'http', 'http://example.com/ko7/index.php/?ko7=awesome&life=good'],

			['?ko7=awesome&life=good#fact', NULL, '/ko7/index.php/?ko7=awesome&life=good#fact'],
			['?ko7=awesome&life=good#fact', 'http', 'http://example.com/ko7/index.php/?ko7=awesome&life=good#fact'],

			['some/long/route/goes/here?ko7=awesome&life=good#fact', NULL, '/ko7/index.php/some/long/route/goes/here?ko7=awesome&life=good#fact'],
			['some/long/route/goes/here?ko7=awesome&life=good#fact', 'http', 'http://example.com/ko7/index.php/some/long/route/goes/here?ko7=awesome&life=good#fact'],

			['/route/goes/here?ko7=awesome&life=good#fact', 'https', 'https://example.com/ko7/index.php/route/goes/here?ko7=awesome&life=good#fact'],
			['/route/goes/here?ko7=awesome&life=good#fact', 'ftp', 'ftp://example.com/ko7/index.php/route/goes/here?ko7=awesome&life=good#fact'],
		];
	}

	/**
	 * Tests URL::site()
	 *
	 * @test
	 * @dataProvider provider_site
	 * @param string          $uri         URI to use
	 * @param boolean|string  $protocol    Protocol to use
	 * @param string          $expected    Expected result
	 * @param array           $enviroment  Array of enviroment vars to set
	 */
	public function test_site($uri, $protocol, $expected, array $enviroment = [])
	{
		$this->setEnvironment($enviroment);

		$this->assertSame(
			$expected,
			URL::site($uri, $protocol)
		);
	}

	/**
	 * Provides test data for test_site_url_encode_uri()
	 * See issue #2680
	 *
	 * @return array
	 */
	public function provider_site_url_encode_uri()
	{
		$provider = [
			['test', 'encode'],
			['test', 'éñçø∂ë∂'],
			['†éß†', 'encode'],
			['†éß†', 'éñçø∂ë∂', 'µåñ¥'],
		];

		foreach ($provider as $i => $params)
		{
			// Every non-ASCII character except for forward slash should be encoded...
			$expected = implode('/', array_map('rawurlencode', $params));

			// ... from a URI that is not encoded
			$uri = implode('/', $params);

			$provider[$i] = ["/ko7/index.php/{$expected}", $uri];
		}

		return $provider;
	}

	/**
	 * Tests URL::site for proper URL encoding when working with non-ASCII characters.
	 *
	 * @test
	 * @dataProvider provider_site_url_encode_uri
	 */
	public function test_site_url_encode_uri($expected, $uri)
	{
		$this->assertSame($expected, URL::site($uri, FALSE));
	}

	/**
	 * Provides test data for test_title()
	 * @return array
	 */
	public function provider_title()
	{
		return [
			// Tests that..
			// Title is converted to lowercase
			['we-shall-not-be-moved', 'WE SHALL NOT BE MOVED', '-'],
			// Excessive white space is removed and replaced with 1 char
			['thissssss-is-it', 'THISSSSSS         IS       IT  ', '-'],
			// separator is either - (dash) or _ (underscore) & others are converted to underscores
			['some-title', 'some title', '-'],
			['some_title', 'some title', '_'],
			['some!title', 'some title', '!'],
			['some:title', 'some title', ':'],
			// Numbers are preserved
			['99-ways-to-beat-apple', '99 Ways to beat apple', '-'],
			// ... with lots of spaces & caps
			['99_ways_to_beat_apple', '99    ways   TO beat      APPLE', '_'],
			['99-ways-to-beat-apple', '99    ways   TO beat      APPLE', '-'],
			// Invalid characters are removed
			['each-gbp-is-now-worth-32-usd', 'Each GBP(£) is now worth 32 USD($)', '-'],
			// ... inc. separator
			['is-it-reusable-or-re-usable', 'Is it reusable or re-usable?', '-'],
			// Doing some crazy UTF8 tests
			['espana-wins', 'España-wins', '-', TRUE],
		];
	}

	/**
	 * Tests URL::title()
	 *
	 * @test
	 * @dataProvider provider_title
	 * @param string $title        Input to convert
	 * @param string $separator    Seperate to replace invalid characters with
	 * @param string $expected     Expected result
	 */
	public function test_title($expected, $title, $separator, $ascii_only = FALSE)
	{
		$this->assertSame(
			$expected,
			URL::title($title, $separator, $ascii_only)
		);
	}

	/**
	 * Provides test data for URL::query()
	 * @return array
	 */
	public function provider_query()
	{
		return [
			[[], '', NULL],
			[['_GET' => ['test' => 'data']], '?test=data', NULL],
			[[], '?test=data', ['test' => 'data']],
			[['_GET' => ['more' => 'data']], '?more=data&test=data', ['test' => 'data']],
			[['_GET' => ['sort' => 'down']], '?test=data', ['test' => 'data'], FALSE],

			// http://koseven.dev/issues/3362
			[[], '', ['key' => NULL]],
			[[], '?key=0', ['key' => FALSE]],
			[[], '?key=1', ['key' => TRUE]],
			[['_GET' => ['sort' => 'down']], '?sort=down&key=1', ['key' => TRUE]],
			[['_GET' => ['sort' => 'down']], '?sort=down&key=0', ['key' => FALSE]],

			// @issue 4240
			[['_GET' => ['foo' => ['a' => 100]]], '?foo%5Ba%5D=100&foo%5Bb%5D=bar', ['foo' => ['b' => 'bar']]],
			[['_GET' => ['a' => 'a']], '?a=b', ['a' => 'b']],
		];
	}

	/**
	 * Tests URL::query()
	 *
	 * @test
	 * @dataProvider provider_query
	 * @param array $enviroment Set environment
	 * @param string $expected Expected result
	 * @param array $params Query string
	 * @param boolean $use_get Combine with GET parameters
	 */
	public function test_query($enviroment, $expected, $params, $use_get = TRUE)
	{
		$this->setEnvironment($enviroment);

		$this->assertSame(
			$expected,
			URL::query($params, $use_get)
		);
	}

	/**
	 * Provides test data for URL::is_trusted_host()
	 * @return array
	 */
	public function provider_is_trusted_host()
	{
		return [
			// data set #0
			[
				'givenhost',
				[
					'list-of-trusted-hosts',
				],
				FALSE
			],
			// data set #1
			[
				'givenhost',
				[
					'givenhost',
					'example\.com',
				],
				TRUE
			],
			// data set #2
			[
				'www.koseven.dev',
				[
					'.+\.koseven\.dev',
				],
				TRUE
			],
			// data set #3
			[
				'koseven.dev',
				[
					'.+\.koseven\.dev',
				],
				FALSE // because we are requesting a subdomain
			],
		];
	}

	/**
	 * Tests URL::is_trusted_hosts()
	 *
	 * @test
	 * @dataProvider provider_is_trusted_host
	 * @param string $host the given host
	 * @param array $trusted_hosts list of trusted hosts
	 * @param boolean $expected TRUE if host is trusted, FALSE otherwise
	 */
	public function test_is_trusted_host($host, $trusted_hosts, $expected)
	{
		$this->assertSame(
			$expected,
			URL::is_trusted_host($host, $trusted_hosts)
		);
	}

	/**
	 * Tests if invalid host throws "Invalid host" exception
	 */
	public function test_if_invalid_host_throws_exception()
	{
		$this->expectException(KO7_Exception::class);
		$this->expectExceptionMessage('Invalid host <invalid>');

		// set the global HTTP_HOST to <invalid>
		$_SERVER['HTTP_HOST'] = '<invalid>';
		// trigger exception
		URL::base('https');
	}

	/**
	 * Tests if untrusted host throws "Untrusted host" exception
	 */
	public function test_if_untrusted_host_throws_exception()
	{
		$this->expectException(KO7_Exception::class);
		$this->expectExceptionMessage('Untrusted host untrusted.com');

		// set the global HTTP_HOST to a valid but untrusted host
		$_SERVER['HTTP_HOST'] = 'untrusted.com';
		// trigger exception
		URL::base('https');
	}

}
