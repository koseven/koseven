<?php
/**
 * Unit tests for external request client
 *
 * @group ko7
 * @group ko7.request
 * @group ko7.request.client
 * @group ko7.request.client.external
 *
 * @package    KO7\Tests
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */


use Pagely\Component\HttpMock\PHPUnit\HttpMockTrait;

class KO7_Request_Client_ExternalTest extends Unittest_TestCase {

	/**
	 * We need a fake HTTP Server for this one
	 */
	use HttpMockTrait;

	/**
	 * Host we setup our fake server (you probably don't need to change this)
	 *
	 * @var string
	 */
	public static $host = '127.0.0.1';

	/**
	 * Port our fake server will run on (you maybe need to change this to another one)
	 * Default is 7507 because according to Wikipedia it has no use by another software
	 *
	 * @var int
	 */
	public static $port = 7507;

	/**
	 * Setup fake HTTP Server
	 */
	public static function setUpBeforeClass() : void
	{
		static::setUpHttpMockBeforeClass(static::$port, static::$host);
		parent::setUpBeforeClass();
	}

	/**
	 * Shutdown HTTP Server
	 */
	public static function tearDownAfterClass() : void
	{
		static::tearDownHttpMockAfterClass();
		parent::tearDownAfterClass();
	}

	/**
	 * Check if Server works and start it
	 * @throws KO7_Exception
	 * @throws ReflectionException
	 */
	public function setUp() : void
	{
		$this->setUpHttpMock();
		parent::setUp();
	}

	/**
	 * Kill Server
	 * @throws KO7_Exception
	 * @throws ReflectionException
	 */
	public function tearDown() : void
	{
		$this->tearDownHttpMock();
		parent::tearDown();
	}

	/**
	 * Provides test data for test_external_requests
	 *
	 * Note: We test GET and POST requests
	 *
	 * @return array
	 */
	public function provider_external_requests() : array
	{
		// Test Stream Client
		$return = [
			[
				'client'  => 'Request_Client_Stream',
				'method'  => HTTP_Request::GET,
				'options' => [
					'http' => [
						'user_agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:15.0) Gecko/20100101 Firefox/15.0.1'
					]
				]
			],

			// This will fail since our stream is not write-able
			[
				'client'    => 'Request_Client_Stream',
				'method'    => HTTP_Request::POST,
				'options'   => [],
				'status'    => 200,
				'exception' => TRUE
			],
		];

		// Test curl client if php curl extension is loaded
		if (extension_loaded('curl'))
		{
			$return[] =
			[
				'client'  => 'Request_Client_Curl',
				'method'  => HTTP_Request::GET,
				'options' => [
					constant('CURLOPT_TIMEOUT') => 1200000,
					constant('CURLOPT_USERAGENT') => 'spider'
				]
			];
			$return[] =
			[
				'client'  => 'Request_Client_Curl',
				'method'  => HTTP_Request::POST,
				'options' => [
					constant('CURLOPT_TIMEOUT') => 1200000,
					constant('CURLOPT_USERAGENT') => 'spider'
				]
			];

			// Test with invalid port
			$return[] =
			[
				'client'    => 'Request_Client_Curl',
				'method'    => HTTP_Request::GET,
				'options'   => [
					constant('CURLOPT_PORT')	=> 111
				],
				'status'    => 200,
				'exception' => TRUE
			];

			// Test with invalid options
			$return[] =
			[
				'client'  => 'Request_Client_Curl',
				'method'  => HTTP_Request::GET,
				'options' => [
					'invalid option' => 'this is not valid'
				],
				'status'	=> 200,
				'exception' => TRUE
			];
		}

		// Test curl client if pecl_http extension is loaded
		if (extension_loaded('http'))
		{
			$return[] =
			[
				'client'  => 'Request_Client_HTTP',
				'method'  => HTTP_Request::GET,
				'options' => [
					'timeout'	=> 1200000
				]
			];
			$return[] =
			[
				'client'  => 'Request_Client_HTTP',
				'method'  => HTTP_Request::POST,
				'options' => [
					'timeout'	=> 1200000
				]
			];

			// Test with invalid port
			$return[] =
			[
				'client'  => 'Request_Client_HTTP',
				'method'  => HTTP_Request::GET,
				'options' => [
					'port'	=> '11112'
				],
				'status'	=> 200,
				'exception' => TRUE
			];
		}

		return $return;
	}

	/**
	 * Tests Request::factory to a external source (Request_Client_External)
	 *
	 * @dataProvider provider_external_requests
	 *
	 * @param string $client	Client Class to use (has to extend Request_Client_External)
	 * @param string $method	GET/POST/PUT/DELETE
	 * @param array  $options	Additional Options (curl, stream options, etc..) e.g CURLOPT_TIMEOUT
	 * @param int    $status	Status code the request shall return (200, 301, 404, etc..)
	 * @param bool   $exception Expect an exception
	 *
	 * @throws Request_Exception
	 */
	public function test_external_requests(string $client, string $method, array $options, int $status = 200, $exception = false) : void
	{
		// Wee ned to increase our memory size for this test (we will reset this one at the end)
		$initialLimit = ini_get('memory_limit');
		ini_set('memory_limit','1024M');

		// Expose KO7 and send it's UA
		KO7::$expose = TRUE;

		// Setup the Mocking Server
		$server = $this->http->mock;

		// Start creating the response, set method and path
		$server = $server->when()->methodIs($method)->pathIs('/test');

		// Only accept if certain queries are send correctly
		$queries = [
			'test'  => 'value',
			'test2' => 'value2'
		];

		$server = $server->queryParamsAre($queries);

		// Only accept if following headers are sent
		$headers = [
			'X-Sent-Header' => 'Xs Value',
			'Y-Sent-Header' => 'Ys Value'
		];

		foreach ($headers as $header => $value)
		{
			$server = $server->headerIs($header, $value);
		}

		// Start Response Configuration and add Status Code
		$server = $server->then()->statusCode($status);

		// Set Response header - we do this so we can verify that they get parsed correctly in the response
		$responseHeaders = [
			'X-Return-Header' => 'Xr Value',
			'Y-Return-Header' => 'Yr Value'
		];

		foreach ($responseHeaders as $header => $value)
		{
			$server = $server->header($header, $value);
		}

		// Set what the server shall return in case of the above request comes in
		$expectedBody = 'This is a ' . $method . ' request.';

		$server->body($expectedBody)->end();

		// Finish Server Setup
		$this->http->setUp();

		// Set the Request client
		Request_Client_External::$client = $client;

		// Start a request to the server with given client
		try
		{
			$request = Request::factory('http://' . static::$host . ':' . static::$port . '/test', $options);
		}
		catch (Request_Exception $e)
		{
			$this->fail($e->getMessage());
		}

		// Set Request Method, Body, and cookies
		$request->method($method);
		$request->body('Send this body');
		$request->cookie('Test Cookie', 'test');

		// Add a Post Parameter if request is post
		if ($method === HTTP_Request::POST)
		{
			$request->post('post-param1', 'value');
		}

		// Add query to the request
		foreach ($queries as $query => $value)
		{
			$request->query($query, $value);
		}

		// Send headers
		$request->headers($headers);

		// Execute request
		try
		{
			$response = $request->execute();
		}
		catch (HTTP_Exception_404 | Request_Exception $e)
		{
			if ($exception && $e instanceof Request_Exception)
			{
				return;
			}
			$this->fail($e->getMessage());
		}

		// Parse Header
		$expected_header = array_change_key_case($responseHeaders, CASE_LOWER);
		$actual_headers = array_intersect_key($response->headers()->getArrayCopy(), $expected_header);

		// Check if response body is correct
		$this->assertSame($expectedBody, $response->body());

		// Check if response status is correct
		$this->assertSame($status, $response->status());

		// Check if response header are correct
		$this->assertSame($expected_header, $actual_headers);

		// Reset Memory Limit to initial one
		ini_set('memory_limit',$initialLimit);
	}

	public function provider_test_options() : array
	{
		return [
			[
				'key'   => 'Test',
				[
					'Test' => 'Koseven'
				],
				'value' => 'Koseven'
			],
			[
				[
					'Test'  => 'Koseven',
					'Test2' => 'Koseven 2'
				],
				[
					'Test'  => 'Koseven',
					'Test2' => 'Koseven 2'
				]
			]
		];
	}

	/**
	 * Tests Request_Client_External::setOptions
	 *
	 * @dataProvider provider_test_options
	 *
	 * @param  mixed    $key			The option to set/get
	 * @param  array    $expected		Expected Result
	 * @param  string	$value			The options value
	 */
	public function test_options($key, array $expected, $value = NULL) : void
	{
		// Initialize Client
		try
		{
			$client = KO7_Request_Client_External::factory([], 'Request_Client_Stream');
		}
		catch (Request_Exception $e)
		{
			$this->fail('Could not initialize External Client. Error: ' . $e->getMessage());
		}

		// Set Options
		$client->options($key, $value);

		// Get all options and compare arrays
		$this->assertSame($expected, $client->options());

		// Get specific option and check if value is correct
		if (is_array($key))
		{
			foreach ($key as $k => $val)
			{
				$this->assertSame($val, $client->options($k));
			}
		}
		else
		{
			$this->assertSame($value, $client->options($key));
		}
	}

	/**
	 * Test Request_Client_External::factory
	 *
	 * This test checks if Exception is thrown when we pass and Invalid client
	 *
	 */
	public function test_invalid_request_class() : void
	{
		// Expect Exception
		$this->expectException(Request_Exception::class);

		// Try to use it
		Request_Client_External::factory([], 'Arr');
	}

}
