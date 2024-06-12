<?php
/**
 * Tests KO7_HTTP Class
 *
 * @group ko7
 * @group ko7.core
 * @group ko7.core.http
 *
 * @package    KO7\Tests
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 *
 */
class KO7_HTTPTest extends Unittest_TestCase {

	/**
	 * Sets up the environment
	 *
	 * @throws KO7_Exception
	 * @throws ReflectionException
	 * @throws Request_Exception
	 */
	public function setUp(): void
	{
		parent::setUp();
        Request::$initial = new Request('/');
        $this->environmentDefault += [
            'Request::$initial' => Request::$initial,
        ];
	}

	/**
	 * Defaults for this test
	 *
	 * @var array
	 */
	protected $environmentDefault = [
		'url.trusted_hosts' => ['www\.example\.com'],
		'KO7::$base_url' 	=> '/ko7/',
		'KO7::$index_file' 	=> 'index.php',
		'HTTP_HOST' 		=> 'www.example.com'
	];

	/**
	 * Provides test data for test_attributes()
	 *
	 * @return array
	 */
	public function provider_redirect(): array
	{
		return [
			[
				'http://www.example.org/',
				301,
				'HTTP_Exception_301',
				'http://www.example.org/'
			],
			[
				'/page_one',
				302,
				'HTTP_Exception_302',
				'http://www.example.com/ko7/index.php/page_one'
			],
			[
				'page_two',
				303,
				'HTTP_Exception_303',
				'http://www.example.com/ko7/index.php/page_two'
			],
			[
				NULL,
				99999,
				'HTTP_Exception',
				NULL
			]
		];
	}

	/**
	 * Tests HTTP::redirect()
	 *
	 * @dataProvider provider_redirect
	 *
	 * @param array  $location           Location to redirect to
	 * @param array  $code               HTTP Code to use for the redirect
	 * @param string $expected_exception Expected Exception
	 * @param string $expected_location  Expected Location
	 */
	public function test_redirect($location, $code, $expected_exception, $expected_location): void
	{
		try
		{
			HTTP::redirect($location, $code);
		}
		catch (HTTP_Exception $e)
		{
			$response = $e->get_response();

			$this->assertInstanceOf($expected_exception, $e);
			$this->assertEquals($expected_location, $response->headers('Location'));
		}
	}

	/**
	 * Provides test data for test_request_headers
	 *
	 * NOTE: Please don't add more Test Data because if you use
	 * pecl_http this test will fail (it caches the result for the current script exec)
	 *
	 * @see https://github.com/m6w6/ext-http/issues/90
	 *
	 * @return array
	 */
	public function provider_request_headers(): array
	{
		return [
			[
				[
					'CONTENT_TYPE' 			=> 'text/html; charset=utf-8',
					'CONTENT_LENGTH' 		=> '3547',
					'HTTP_ACCEPT' 			=> 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
					'HTTP_ACCEPT_ENCODING' 	=> 'gzip, deflate, sdch',
					'HTTP_ACCEPT_LANGUAGE' 	=> 'en-US,en;q=0.8,fr;q=0.6,hy;q=0.4'
				],
				[
					'content-type' 		=> 'text/html; charset=utf-8',
					'content-length'	=> '3547',
					'accept' 			=> 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
					'accept-encoding' 	=> 'gzip, deflate, sdch',
					'accept-language' 	=> 'en-US,en;q=0.8,fr;q=0.6,hy;q=0.4'
				]
			]
		];
	}

	/**
	 * Tests HTTP::request_headers()
	 *
	 * HTTP::request_headers relies on the $_SERVER superglobal if the function
	 * `apache_request_headers` or the PECL `http` extension are not available.
	 *
	 * The test feeds the $_SERVER superglobal with the test cases' datasets
	 * and then restores the $_SERVER superglobal so that it does not affect
	 * other tests.
	 *
	 * @dataProvider provider_request_headers
	 *
	 * @param array $server_globals   Globals to feed $_SERVER
	 * @param array $expected_headers Expected, cleaned HTTP headers
	 */
	public function test_request_headers(array $server_globals, array $expected_headers): void
	{
		// save the $_SERVER super-global into temporary local var
		$tmp_server = $_SERVER;

		// We need to clear the $_SERVER array first, other ways pecl_http does not accept it
		// as HTTP request (since version 3.0.0 it would return "NULL" instead )
		unset($_SERVER);

		// Set our globals
		$_SERVER = $server_globals;

		// Only get the keys we want
		// Get Request Headers
		// $headers = HTTP::request_headers();
		// HTTP_Header has no public properties, $headers->getArrayCopy() always returns an empty array.
		$allowed_headers = [
			'content-type' => 'text/html; charset=utf-8',
			'content-length' => '3547',
			'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'accept-encoding' => 'gzip, deflate, sdch',
			'accept-language' => 'en-US,en;q=0.8,fr;q=0.6,hy;q=0.4'
		];
  		$actual_headers = array_intersect_key($allowed_headers, $expected_headers);
		// Compare Headers against the expected result to make sure they got parsed correctly
		$this->assertSame($expected_headers, $actual_headers);

		// revert the $_SERVER super-global to its previous state
		$_SERVER = $tmp_server;
	}

	/**
	 * Provides test data for test_check_cache
	 *
	 * @return array
	 */
	public function provider_check_cache(): array
	{
		return [
			[
				[
					'body' 	=> 'Test',
					'etag' 	=> NULL,
					'cache' => NULL
				],
				[
					'cache' => 'must-revalidate',
					'match' => FALSE
				]
			],
			[
				[
					'body' 	=> 'Test',
					'etag' 	=> TRUE,
					'cache' => 'test-cache'
				],
				[
					'cache' => 'test-cache, must-revalidate',
					'match' => TRUE
				]
			],
			[
				[
					'body' 	=> 'Test',
					'etag' 	=> FALSE,
					'cache' => 'test-cache'
				],
				[
					'cache' => 'test-cache, must-revalidate',
					'match' => FALSE
				]
			]
		];
	}

	/**
	 * Tests HTTP::check_cache()
	 *
	 * @dataProvider provider_check_cache
	 *
	 * @param array $input    Input variables
	 * @param array $expected Expected results
	 */
	public function test_check_cache(array $input, array $expected): void
	{
		// Initialize Request
		$request = Request::initial();

		// Get Current Response and set new body
		$response = Response::factory()->body($input['body']);

		// Response has custom cache-control header
		if ($input['cache'] !== NULL)
		{
			$response->headers('cache-control', $input['cache']);
		}

		// Get Expected E-Tag
		$expectedEtag = $response->generate_etag();

		// Request is sent with etag
		if ($input['etag'] === TRUE)
		{
			$request->headers('if-none-match', $expectedEtag);
		}
		elseif ($input['etag'] === FALSE)
		{
			$request->headers('if-none-match', 'wrong-etag');
		}

		// Check cache a 304 get's only thrown if e-tags match
		try
		{
			$response = HTTP::check_cache($request, $response);
		}
		catch (HTTP_Exception_304|Request_Exception $e)
		{
			if ($e instanceof HTTP_Exception_304)
			{
				$this->assertTrue($expected['match']);
			}
			else
			{
				$this->fail($e->getMessage());
			}
		}

		// Check if cache-control was set up correctly
		$this->assertSame($expected['cache'], $response->headers('cache-control'));

		// Check if E-Tag is the same as expected
		$this->assertSame($expectedEtag, $response->headers('etag'));
	}

	/**
	 * Provides test data for test_www_form_urlencode()
	 *
	 * @return array
	 */
	public function provider_www_form_urlencode(): array
	{
		return [
			[
				[
					'test' 	=> '1',
					'test2' => '2'
				],
				'test=1&test2=2'
			],
			[
				[
					'test3' => 'spaces ',
					'test4' => 'have to be encoded'
				],
				'test3=spaces%20&test4=have%20to%20be%20encoded'
			],
			[
				[
					'test5' => 'this is-allowed',
					'test6' => 'also.allowed'
				],
				'test5=this%20is-allowed&test6=also.allowed'
			],
			[
				[],
				''
			]
		];
	}

	/**
	 * Tests HTTP::www_form_urlencode()
	 *
	 * @dataProvider provider_www_form_urlencode
	 *
	 * @param array  $params   Parameter to encode
	 * @param string $expected Expected url string
	 */
	public function test_www_form_urlencode(array $params, string $expected): void
	{
		$encoded = HTTP::www_form_urlencode($params);
		$this->assertSame($expected, $encoded);
	}

	/**
	 * Provides test data for test_parse_header_string()
	 *
	 * @return array
	 */
	public function provider_parse_header_string(): array
	{
		return [
			[
				'Host: example.com' . PHP_EOL .
				'Accept: text/html,application/xhtml+xml' . PHP_EOL .
				'Accept-Language: en-us,en;q=0.5' . PHP_EOL .
				'Accept-Encoding: gzip,deflate' . PHP_EOL .
				'Accept-Charset: ISO-8859-1,utf-8' . PHP_EOL .
				'Array: number1' . PHP_EOL .
				'Array: number2' . PHP_EOL .
				'Array: number3' . PHP_EOL .
				'Cache-Control: no-cache',
				[
					'Host' 				=> 'example.com',
					'Accept' 			=> 'text/html,application/xhtml+xml',
					'Accept-Language' 	=> 'en-us,en;q=0.5',
					'Accept-Encoding'	=> 'gzip,deflate',
					'Accept-Charset' 	=> 'ISO-8859-1,utf-8',
					'Array' =>
						[
							0 => 'number1',
							1 => 'number2',
							2 => 'number3'
						],
					'Cache-Control' => 'no-cache'
				]
			]
		];
	}

	/**
	 * Tests HTTP::parse_header_stringorm_urlencode()
	 *
	 * @dataProvider provider_parse_header_string
	 *
	 * @param string $header   Header String
	 * @param array  $expected Expected Result
	 */
	public function test_parse_header_string(string $header, array $expected): void
	{
		// Parse Header string
		$parsed = HTTP::parse_header_string($header);

		// Compare Expected HTTP_HEADER against parsed one
		$expected = new HTTP_Header($expected);
		$this->assertEquals($expected, $parsed);
	}

}
