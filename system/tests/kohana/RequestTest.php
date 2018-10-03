<?php

/**
 * Unit tests for request class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.request
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_RequestTest extends Unittest_TestCase
{
	protected $_inital_request;

	// @codingStandardsIgnoreStart
	public function setUp()
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();
		Kohana::$config->load('url')->set('trusted_hosts', ['localhost']);
		$this->_initial_request = Request::$initial;
		Request::$initial = new Request('/');
	}

	// @codingStandardsIgnoreStart
	public function tearDown()
	// @codingStandardsIgnoreEnd
	{
		Request::$initial = $this->_initial_request;
		parent::tearDown();
	}

	public function test_initial()
	{
		$this->setEnvironment([
			'Request::$initial' => NULL,
			'Request::$client_ip' => NULL,
			'Request::$user_agent' => NULL,
			'_SERVER' => [
				'HTTPS' => NULL,
				'PATH_INFO' => '/',
				'HTTP_REFERER' => 'http://example.com/',
				'HTTP_USER_AGENT' => 'whatever (Mozilla 5.0/compatible)',
				'REMOTE_ADDR' => '127.0.0.1',
				'REQUEST_METHOD' => 'GET',
				'HTTP_X_REQUESTED_WITH' => 'ajax-or-something',
			],
			'_GET' => [],
			'_POST' => [],
		]);

		$request = Request::factory();

		$this->assertEquals(Request::$initial, $request);

		$this->assertEquals(Request::$client_ip, '127.0.0.1');

		$this->assertEquals(Request::$user_agent, 'whatever (Mozilla 5.0/compatible)');

		$this->assertEquals($request->protocol(), 'HTTP/1.1');

		$this->assertEquals($request->referrer(), 'http://example.com/');

		$this->assertEquals($request->requested_with(), 'ajax-or-something');

		$this->assertEquals($request->query(), []);

		$this->assertEquals($request->post(), []);
	}

	/**
	 * Tests client IP detection
	 *
	 * @return null
	 */
	public function test_client_ips()
	{
		// testing X-Forwarded-For
		$server = [
			'HTTPS' => NULL,
			'PATH_INFO' => '/',
			'REMOTE_ADDR' => '255.255.255.1',
			'HTTP_X_FORWARDED_FOR' => '127.0.0.2, 255.255.255.1, 255.255.255.2',
		];

		$this->setEnvironment([
			'Request::$initial' => NULL,
			'Request::$client_ip' => NULL,
			'Request::$trusted_proxies' => ['255.255.255.1'],
			'_SERVER' => $server,
		]);

		$request = Request::factory();

		$this->assertEquals(Request::$client_ip, '127.0.0.2', 'Header "HTTP_X_FORWARDED_FOR" handled incorrectly');

		// testing Client-IP
		$server['HTTP_CLIENT_IP'] = '127.0.0.3';
		unset($server['HTTP_X_FORWARDED_FOR']);

		$this->setEnvironment([
			'Request::$initial' => NULL,
			'Request::$client_ip' => NULL,
			'_SERVER' => $server,
		]);

		$request = Request::factory();

		$this->assertEquals(Request::$client_ip, '127.0.0.3', 'Header "HTTP_CLIENT_IP" handled incorrectly');

		// testing Cloudflare
		$server['HTTP_CF_CONNECTING_IP'] = '127.0.0.4';

		$this->setEnvironment([
			'Request::$initial' => NULL,
			'Request::$client_ip' => NULL,
			'_SERVER' => $server,
		]);

		$request = Request::factory();

		$this->assertEquals(Request::$client_ip, '127.0.0.4', 'Cloudflare header "HTTP_CF_CONNECTING_IP" handled incorrectly');

	}

	/**
	 * Tests that the allow_external flag prevents an external request.
	 *
	 * @return null
	 */
	public function test_disable_external_tests()
	{
		$this->setEnvironment(
			[
				'Request::$initial' => NULL,
			]
		);

		$request = new Request('http://www.google.com/', [], FALSE);

		$this->assertEquals(FALSE, $request->is_external());
	}

	/**
	 * Provides the data for test_create()
	 * @return  array
	 */
	public function provider_create()
	{
		return [
			['foo/bar', 'Request_Client_Internal'],
			['http://google.com', 'Request_Client_External'],
		];
	}

	/**
	 * Ensures the create class is created with the correct client
	 *
	 * @test
	 * @dataProvider provider_create
	 */
	public function test_create($uri, $client_class)
	{
		$request = Request::factory($uri);

		$this->assertInstanceOf($client_class, $request->client());
	}

	/**
	 * Ensure that parameters can be read
	 *
	 * @test
	 */
	public function test_param()
	{
		$route = new Route('(<controller>(/<action>(/<id>)))');

		$uri = 'kohana_requesttest_dummy/foobar/some_id';
		$request = Request::factory($uri, NULL, TRUE, [$route]);

		// We need to execute the request before it has matched a route
		$response = $request->execute();
		$controller = new Controller_Kohana_RequestTest_Dummy($request, $response);

		$this->assertSame(200, $response->status());
		$this->assertSame($controller->get_expected_response(), $response->body());
		$this->assertArrayHasKey('id', $request->param());
		$this->assertArrayNotHasKey('foo', $request->param());
		$this->assertEquals($request->uri(), $uri);

		// Ensure the params do not contain contamination from controller, action, route, uri etc etc
		$params = $request->param();

		// Test for illegal components
		$this->assertArrayNotHasKey('controller', $params);
		$this->assertArrayNotHasKey('action', $params);
		$this->assertArrayNotHasKey('directory', $params);
		$this->assertArrayNotHasKey('uri', $params);
		$this->assertArrayNotHasKey('route', $params);

		$route = new Route('(<uri>)', ['uri' => '.+']);
		$route->defaults(['controller' => 'kohana_requesttest_dummy', 'action' => 'foobar']);
		$request = Request::factory('kohana_requesttest_dummy', NULL, TRUE, [$route]);

		// We need to execute the request before it has matched a route
		$response = $request->execute();
		$controller = new Controller_Kohana_RequestTest_Dummy($request, $response);

		$this->assertSame(200, $response->status());
		$this->assertSame($controller->get_expected_response(), $response->body());
		$this->assertSame('kohana_requesttest_dummy', $request->param('uri'));
	}

	/**
	 * Tests Request::method()
	 *
	 * @test
	 */
	public function test_method()
	{
		$request = Request::factory('foo/bar');

		$this->assertEquals($request->method(), 'GET');
		$this->assertEquals(($request->method('post') === $request), TRUE);
		$this->assertEquals(($request->method() === 'POST'), TRUE);
	}

	/**
	 * Tests Request::route()
	 *
	 * @test
	 */
	public function test_route()
	{
		$request = Request::factory(''); // This should always match something, no matter what changes people make

		// We need to execute the request before it has matched a route
		try
		{
			$request->execute();
		}
		catch (Exception $e) {}

		$this->assertInstanceOf('Route', $request->route());
	}

	/**
	 * Tests Request::route()
	 *
	 * @test
	 */
	public function test_route_is_not_set_before_execute()
	{
		$request = Request::factory(''); // This should always match something, no matter what changes people make

		// The route should be NULL since the request has not been executed yet
		$this->assertEquals($request->route(), NULL);
	}

	/**
	 * Tests Request::accept_type()
	 *
	 * @test
	 * @covers Request::accept_type
	 */
	public function test_accept_type()
	{
		$this->assertEquals(['*/*' => 1], Request::accept_type());
	}

	/**
	 * Provides test data for Request::accept_lang()
	 * @return array
	 */
	public function provider_accept_lang()
	{
		return [
			['en-us', 1, ['_SERVER' => ['HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5']]],
			['en-us', 1, ['_SERVER' => ['HTTP_ACCEPT_LANGUAGE' => 'en-gb']]],
			['en-us', 1, ['_SERVER' => ['HTTP_ACCEPT_LANGUAGE' => 'sp-sp;q=0.5']]]
		];
	}

	/**
	 * Tests Request::accept_lang()
	 *
	 * @test
	 * @covers Request::accept_lang
	 * @dataProvider provider_accept_lang
	 * @param array $params Query string
	 * @param string $expected Expected result
	 * @param array $enviroment Set environment
	 */
	public function test_accept_lang($params, $expected, $enviroment)
	{
		$this->setEnvironment($enviroment);

		$this->assertEquals(
			$expected,
			Request::accept_lang($params)
		);
	}

	/**
	 * Provides test data for Request::url()
	 * @return array
	 */
	public function provider_url()
	{
		return [
			[
				'foo/bar',
				'http',
				'http://localhost/kohana/foo/bar'
			],
			[
				'foo',
				'http',
				'http://localhost/kohana/foo'
			],
			[
				'http://www.google.com',
				'http',
				'http://www.google.com'
			],
			[
				'0',
				'http',
				'http://localhost/kohana/0'
			]
		];
	}

	/**
	 * Tests Request::url()
	 *
	 * @test
	 * @dataProvider provider_url
	 * @covers Request::url
	 * @param string $uri the uri to use
	 * @param string $protocol the protocol to use
	 * @param array $expected The string we expect
	 */
	public function test_url($uri, $protocol, $expected)
	{
		if ( ! isset($_SERVER['argc']))
		{
			$_SERVER['argc'] = 1;
		}

		$this->setEnvironment([
			'Kohana::$base_url'  => '/kohana/',
			'_SERVER'            => ['HTTP_HOST' => 'localhost', 'argc' => $_SERVER['argc']],
			'Kohana::$index_file' => FALSE,
		]);

		// issue #3967: inject the route so that we don't conflict with the application's default route
		$route = new Route('(<controller>(/<action>))');
		$route->defaults([
			'controller' => 'welcome',
			'action'     => 'index',
		]);

		$this->assertEquals(Request::factory($uri, [], TRUE, [$route])->url($protocol), $expected);
	}

	/**
	 * Data provider for test_set_protocol() test
	 *
	 * @return array
	 */
	public function provider_set_protocol()
	{
		return [
			[
				'http/1.1',
				'HTTP/1.1',
			],
			[
				'ftp',
				'FTP',
			],
			[
				'hTTp/1.0',
				'HTTP/1.0',
			],
		];
	}

	/**
	 * Tests the protocol() method
	 *
	 * @dataProvider provider_set_protocol
	 *
	 * @return null
	 */
	public function test_set_protocol($protocol, $expected)
	{
		$request = Request::factory();

		// Set the supplied protocol
		$result = $request->protocol($protocol);

		// Test the set value
		$this->assertSame($expected, $request->protocol());

		// Test the return value
		$this->assertTrue($request instanceof $result);
	}

	/**
	 * Provides data for test_post_max_size_exceeded()
	 * 
	 * @return  array
	 */
	public function provider_post_max_size_exceeded()
	{
		// Get the post max size
		$post_max_size = Num::bytes(ini_get('post_max_size'));

		return [
			[
				$post_max_size+200000,
				TRUE
			],
			[
				$post_max_size-20,
				FALSE
			],
			[
				$post_max_size,
				FALSE
			]
		];
	}

	/**
	 * Tests the post_max_size_exceeded() method
	 * 
	 * @dataProvider provider_post_max_size_exceeded
	 *
	 * @param   int      content_length 
	 * @param   bool     expected 
	 * @return  void
	 */
	public function test_post_max_size_exceeded($content_length, $expected)
	{
		// Ensure the request method is set to POST
		Request::$initial->method(HTTP_Request::POST);

		// Set the content length
		$_SERVER['CONTENT_LENGTH'] = $content_length;

		// Test the post_max_size_exceeded() method
		$this->assertSame(Request::post_max_size_exceeded(), $expected);
	}

	/**
	 * Provides data for test_uri_only_trimed_on_internal()
	 *
	 * @return  array
	 */
	public function provider_uri_only_trimed_on_internal()
	{
		// issue #3967: inject the route so that we don't conflict with the application's default route
		$route = new Route('(<controller>(/<action>))');
		$route->defaults([
			'controller' => 'welcome',
			'action'     => 'index',
		]);

		$old_request = Request::$initial;
		Request::$initial = new Request(TRUE, [], TRUE, [$route]);

		$result = [
			[
				new Request('http://www.google.com'),
				'http://www.google.com'
			],
			[
				new Request('http://www.google.com/'),
				'http://www.google.com/'
			],
			[
				new Request('foo/bar/'),
				'foo/bar'
			],
			[
				new Request('foo/bar'),
				'foo/bar'
			],
			[
				new Request('/0'),
				'0'
			],
			[
				new Request('0'),
				'0'
			],
			[
				new Request('/'),
				'/'
			],
			[
				new Request(''),
				'/'
			]
		];

		Request::$initial = $old_request;
		return $result;
	}

	/**
	 * Tests that the uri supplied to Request is only trimed
	 * for internal requests.
	 * 
	 * @dataProvider provider_uri_only_trimed_on_internal
	 *
	 * @return void
	 */
	public function test_uri_only_trimed_on_internal(Request $request, $expected)
	{
		$this->assertSame($request->uri(), $expected);
	}

	/**
	 * Data provider for test_options_set_to_external_client()
	 *
	 * @return  array
	 */
	public function provider_options_set_to_external_client()
	{
		$provider = [
			[
				[
					CURLOPT_PROXYPORT   => 8080,
					CURLOPT_PROXYTYPE   => CURLPROXY_HTTP,
					CURLOPT_VERBOSE     => TRUE
				],
				[
					CURLOPT_PROXYPORT   => 8080,
					CURLOPT_PROXYTYPE   => CURLPROXY_HTTP,
					CURLOPT_VERBOSE     => TRUE
				]
			]
		];

		return $provider;
	}

	/**
	 * Test for Request_Client_External::options() to ensure options
	 * can be set to the external client (for cURL and PECL_HTTP)
	 *
	 * @dataProvider provider_options_set_to_external_client
	 * 
	 * @param   array    settings 
	 * @param   array    expected 
	 * @return void
	 */
	public function test_options_set_to_external_client($settings, $expected)
	{
		$request_client = Request_Client_External::factory([], 'Request_Client_Curl');

		// Test for empty array
		$this->assertSame([], $request_client->options());

		// Test that set works as expected
		$this->assertSame($request_client->options($settings), $request_client);

		// Test that each setting is present and returned
		foreach ($expected as $key => $value)
		{
			$this->assertSame($request_client->options($key), $value);
		}
	}

	/**
	 * Provides data for test_headers_get()
	 *
	 * @return  array
	 */
	public function provider_headers_get()
	{
		$x_powered_by = 'Kohana Unit Test';
		$content_type = 'application/x-www-form-urlencoded';
		$request = new Request('foo/bar', [], TRUE, []);

		return [
			[
				$request->headers([
						'x-powered-by' => $x_powered_by,
						'content-type' => $content_type
					]
				),
				[
					'x-powered-by' => $x_powered_by,
					'content-type' => $content_type
				]
			]
		];
	}

	/**
	 * Tests getting headers from the Request object
	 * 
	 * @dataProvider provider_headers_get
	 *
	 * @param   Request  request to test
	 * @param   array    headers to test against
	 * @return  void
	 */
	public function test_headers_get($request, $headers)
	{
		foreach ($headers as $key => $expected_value)
		{
			$this->assertSame( (string) $request->headers($key), $expected_value);
		}
	}

	/**
	 * Provides data for test_headers_set
	 *
	 * @return  array
	 */
	public function provider_headers_set()
	{
		return [
			[
				[
					'content-type'  => 'application/x-www-form-urlencoded',
					'x-test-header' => 'foo'
				],
				"Content-Type: application/x-www-form-urlencoded\r\nX-Test-Header: foo\r\n\r\n"
			],
			[
				[
					'content-type'  => 'application/json',
					'x-powered-by'  => 'kohana'
				],
				"Content-Type: application/json\r\nX-Powered-By: kohana\r\n\r\n"
			]
		];
	}

	/**
	 * Tests the setting of headers to the request object
	 * 
	 * @dataProvider provider_headers_set
	 *
	 * @param   array      header(s) to set to the request object
	 * @param   string     expected http header
	 * @return  void
	 */
	public function test_headers_set($headers, $expected)
	{
		$request = new Request(TRUE, [], TRUE, []);
		$request->headers($headers);
		$this->assertSame($expected, (string) $request->headers());
	}

	/**
	 * Provides test data for test_query_parameter_parsing()
	 *
	 * @return  array
	 */
	public function provider_query_parameter_parsing()
	{
		return [
			[
				'foo/bar',
				[
					'foo'   => 'bar',
					'sna'   => 'fu'
				],
				[
					'foo'   => 'bar',
					'sna'   => 'fu'
				],
			],
			[
				'foo/bar?john=wayne&peggy=sue',
				[
					'foo'   => 'bar',
					'sna'   => 'fu'
				],
				[
					'john'  => 'wayne',
					'peggy' => 'sue',
					'foo'   => 'bar',
					'sna'   => 'fu'
				],
			],
			[
				'http://host.tld/foo/bar?john=wayne&peggy=sue',
				[
					'foo'   => 'bar',
					'sna'   => 'fu'
				],
				[
					'john'  => 'wayne',
					'peggy' => 'sue',
					'foo'   => 'bar',
					'sna'   => 'fu'
				],
			],
		];
	}

	/**
	 * Tests that query parameters are parsed correctly
	 * 
	 * @dataProvider provider_query_parameter_parsing
	 *
	 * @param   string    url
	 * @param   array     query 
	 * @param   array    expected 
	 * @return  void
	 */
	public function test_query_parameter_parsing($url, $query, $expected)
	{
		Request::$initial = NULL;

		$request = new Request($url);

		foreach ($query as $key => $value)
		{
			$request->query($key, $value);
		}

		$this->assertSame($expected, $request->query());
	}

	/**
	 * Tests that query parameters are parsed correctly
	 *
	 * @dataProvider provider_query_parameter_parsing
	 *
	 * @param   string    url
	 * @param   array     query
	 * @param   array    expected
	 * @return  void
	 */
	public function test_query_parameter_parsing_in_subrequest($url, $query, $expected)
	{
		Request::$initial = new Request(TRUE);

		$request = new Request($url);

		foreach ($query as $key => $value)
		{
			$request->query($key, $value);
		}

		$this->assertSame($expected, $request->query());
	}

	/**
	 * Provides data for test_client
	 *
	 * @return  array
	 */
	public function provider_client()
	{
		$internal_client = new Request_Client_Internal;
		$external_client = new Request_Client_Stream;

		return [
			[
				new Request('http://kohanaframework.org'),
				$internal_client,
				$internal_client
			],
			[
				new Request('foo/bar'),
				$external_client,
				$external_client
			]
		];
	}

	/**
	 * Tests the getter/setter for request client
	 * 
	 * @dataProvider provider_client
	 *
	 * @param   Request $request 
	 * @param   Request_Client $client 
	 * @param   Request_Client $expected 
	 * @return  void
	 */
	public function test_client(Request $request, Request_Client $client, Request_Client $expected)
	{
		$request->client($client);
		$this->assertSame($expected, $request->client());
	}

	/**
	 * Tests that the Request constructor passes client params on to the
	 * Request_Client once created.
	 */
	public function test_passes_client_params()
	{
		$request = Request::factory('http://example.com/', [
			'follow' => TRUE,
			'strict_redirect' => FALSE
		]);

		$client = $request->client();

		$this->assertEquals($client->follow(), TRUE);
		$this->assertEquals($client->strict_redirect(), FALSE);
	}

	/**
	 * Tests correctness request content-length header after calling render
	 */
	public function test_content_length_after_render()
	{
		$request = Request::factory('https://example.org/post')
			->client(new Kohana_RequestTest_Header_Spying_Request_Client_External)
			->method(Request::POST)
			->post(['aaa' => 'bbb']);

		$request->render();

		$request->execute();

		$headers = $request->client()->get_received_request_headers();

		$this->assertEquals(strlen($request->body()), $headers['content-length']);
	}

	/**
	 * Tests correctness request content-length header after calling render
	 * and changing post
	 */
	public function test_content_length_after_changing_post()
	{
		$request = Request::factory('https://example.org/post')
			->client(new Kohana_RequestTest_Header_Spying_Request_Client_External)
			->method(Request::POST)
			->post(['aaa' => 'bbb']);

		$request->render();

		$request->post(['one' => 'one', 'two' => 'two', 'three' => 'three']);

		$request->execute();

		$headers = $request->client()->get_received_request_headers();

		$this->assertEquals(strlen($request->body()), $headers['content-length']);
	}

} // End Kohana_RequestTest

/**
 * A dummy Request_Client_External implementation, that spies on the headers
 * of the request
 */
class Kohana_RequestTest_Header_Spying_Request_Client_External extends Request_Client_External
{
	private $headers;

	protected function _send_message(\Request $request, \Response $response)
	{
		$this->headers = $request->headers();

		return $response;
	}

	public function get_received_request_headers()
	{
		return $this->headers;
	}
}

class Controller_Kohana_RequestTest_Dummy extends Controller
{
	// hard coded dummy response
	protected $dummy_response = "this is a dummy response";

	public function action_foobar()
	{
		$this->response->body($this->dummy_response);
	}

	public function get_expected_response()
	{
		return $this->dummy_response;
	}

} // End Kohana_RequestTest
