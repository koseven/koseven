<?php

/**
 * Description of RouteTest
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.route
 *
 * @package    Koseven
 * @category   Tests
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */

include Koseven::find_file('tests', 'test_data/callback_routes');

class Kohana_RouteTest extends Unittest_TestCase
{
	/**
	 * Remove all caches
	 */
	// @codingStandardsIgnoreStart
	public function setUp()
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();

		Koseven::$config->load('url')->set('trusted_hosts', ['kohanaframework\.org']);

		$this->cleanCacheDir();
	}

	/**
	 * Removes cache files created during tests
	 */
	// @codingStandardsIgnoreStart
	public function tearDown()
	// @codingStandardsIgnoreEnd
	{
		parent::tearDown();

		$this->cleanCacheDir();
	}

	/**
	 * If Route::get() is asked for a route that does not exist then
	 * it should throw a Kohana_Exception
	 *
	 * Note use of @expectedException
	 *
	 * @test
	 * @covers Route::get
	 * @expectedException Koseven_Exception
	 */
	public function test_get_throws_exception_if_route_dnx()
	{
		Route::get('HAHAHAHAHAHAHAHAHA');
	}

	/**
	 * Route::all() should return all routes defined via Route::set()
	 * and not through new Route()
	 *
	 * @test
	 * @covers Route::all
	 */
	public function test_all_returns_all_defined_routes()
	{
		$defined_routes = self::readAttribute('Route', '_routes');

		$this->assertSame($defined_routes, Route::all());
	}

	/**
	 * Route::name() should fetch the name of a passed route
	 * If route is not found then it should return FALSE
	 *
	 * @TODO: This test needs to segregate the Route::$_routes singleton
	 * @test
	 * @covers Route::name
	 */
	public function test_name_returns_routes_name_or_false_if_dnx()
	{
		$route = Route::set('flamingo_people', 'flamingo/dance');

		$this->assertSame('flamingo_people', Route::name($route));

		$route = new Route('dance/dance');

		$this->assertFalse(Route::name($route));
	}

	/**
	 * If Route::cache() was able to restore routes from the cache then
	 * it should return TRUE and load the cached routes
	 *
	 * @test
	 * @covers Route::cache
	 */
	public function test_cache_stores_route_objects()
	{
		$routes = Route::all();

		// First we create the cache
		Route::cache(TRUE);

		// Now lets modify the "current" routes
		Route::set('nonsensical_route', 'flabbadaga/ding_dong');

		// Then try and load said cache
		$this->assertTrue(Route::cache());

		// Check the route cache flag
		$this->assertTrue(Route::$cache);

		// And if all went ok the nonsensical route should be gone...
		$this->assertEquals($routes, Route::all());
	}

	/**
	 * Check appending cached routes. See http://dev.kohanaframework.org/issues/4347
	 *
	 * @test
	 * @covers Route::cache
	 */
	public function test_cache_append_routes()
	{
		$cached = Route::all();

		// First we create the cache
		Route::cache(TRUE);

		// Now lets modify the "current" routes
		Route::set('nonsensical_route', 'flabbadaga/ding_dong');

		$modified = Route::all();

		// Then try and load said cache
		$this->assertTrue(Route::cache(NULL, TRUE));

		// Check the route cache flag
		$this->assertTrue(Route::$cache);

		// And if all went ok the nonsensical route should exist with the other routes...
		$this->assertEquals(Route::all(), $cached + $modified);
	}

	/**
	 * Route::cache() should return FALSE if cached routes could not be found
	 *
	 * The cache is cleared before and after each test in setUp tearDown
	 * by cleanCacheDir()
	 *
	 * @test
	 * @covers Route::cache
	 */
	public function test_cache_returns_false_if_cache_dnx()
	{
		$this->assertSame(FALSE, Route::cache(), 'Route cache was not empty');

		// Check the route cache flag
		$this->assertFalse(Route::$cache);
	}

	/**
	 * If the constructor is passed a NULL uri then it should assume it's
	 * being loaded from the cache & therefore shouldn't override the cached attributes
	 *
	 * @test
	 * @covers Route::__construct
	 */
	public function test_constructor_returns_if_uri_is_null()
	{
		// We use a mock object to make sure that the route wasn't recompiled
		$route = $this->createMock('Route', ['_compile'], [], '', FALSE);

		$route
			->expects($this->never())
			->method('compile');

		$route->__construct(NULL,NULL);

		$this->assertAttributeSame('', '_uri', $route);
		$this->assertAttributeSame([], '_regex', $route);
		$this->assertAttributeSame(['action' => 'index', 'host' => FALSE], '_defaults', $route);
		$this->assertAttributeSame(NULL, '_route_regex', $route);
	}

	/**
	 * Provider for test_constructor_only_changes_custom_regex_if_passed
	 *
	 * @return array
	 */
	public function provider_constructor_only_changes_custom_regex_if_passed()
	{
		return [
			['<controller>/<action>', '<controller>/<action>'],
		];
	}

	/**
	 * The constructor should only use custom regex if passed a non-empty array
	 *
	 * Technically we can't "test" this as the default regex is an empty array, this
	 * is purely for improving test coverage
	 *
	 * @dataProvider provider_constructor_only_changes_custom_regex_if_passed
	 *
	 * @test
	 * @covers Route::__construct
	 */
	public function test_constructor_only_changes_custom_regex_if_passed($uri, $uri2)
	{
		$route = new Route($uri, []);

		$this->assertAttributeSame([], '_regex', $route);

		$route = new Route($uri2, NULL);

		$this->assertAttributeSame([], '_regex', $route);
	}

	/**
	 * When we pass custom regex to the route's constructor it should it
	 * in leu of the default. This does not apply to callback/lambda routes
	 *
	 * @test
	 * @covers Route::__construct
	 * @covers Route::compile
	 */
	public function test_route_uses_custom_regex_passed_to_constructor()
	{
		$regex = ['id' => '[0-9]{1,2}'];

		$route = new Route('<controller>(/<action>(/<id>))', $regex);

		$this->assertAttributeSame($regex, '_regex', $route);
		$this->assertAttributeContains(
			$regex['id'],
			'_route_regex',
			$route
		);
	}

	/**
	 * Provider for test_matches_returns_false_on_failure
	 *
	 * @return array
	 */
	public function provider_matches_returns_false_on_failure()
	{
		return [
			['projects/(<project_id>/(<controller>(/<action>(/<id>))))', 'apple/pie'],
		];
	}

	/**
	 * Route::matches() should return false if the route doesn't match against a uri
	 *
	 * @dataProvider provider_matches_returns_false_on_failure
	 *
	 * @test
	 * @covers Route::matches
	 */
	public function test_matches_returns_false_on_failure($uri, $match)
	{
		$route = new Route($uri);

		// Mock a request class with the $match uri
		$stub = $this->get_request_mock($match);

		$this->assertSame(FALSE, $route->matches($stub));
	}

	/**
	 * Provider for test_matches_returns_array_of_parameters_on_successful_match
	 *
	 * @return array
	 */
	public function provider_matches_returns_array_of_parameters_on_successful_match()
	{
		return [
			[
				'(<controller>(/<action>(/<id>)))',
				'welcome/index',
				'Welcome',
				'index',
			],
		];
	}

	/**
	 * Route::matches() should return an array of parameters when a match is made
	 * An parameters that are not matched should not be present in the array of matches
	 *
	 * @dataProvider provider_matches_returns_array_of_parameters_on_successful_match
	 *
	 * @test
	 * @covers Route::matches
	 */
	public function test_matches_returns_array_of_parameters_on_successful_match($uri, $m, $c, $a)
	{
		$route = new Route($uri);

		// Mock a request class with the $m uri
		$request = $this->get_request_mock($m);

		$matches = $route->matches($request);

		$this->assertInternalType('array', $matches);
		$this->assertArrayHasKey('controller', $matches);
		$this->assertArrayHasKey('action', $matches);
		$this->assertArrayNotHasKey('id', $matches);
		// $this->assertSame(5, count($matches));
		$this->assertSame($c, $matches['controller']);
		$this->assertSame($a, $matches['action']);
	}

	/**
	 * Provider for test_matches_returns_array_of_parameters_on_successful_match
	 *
	 * @return array
	 */
	public function provider_defaults_are_used_if_params_arent_specified()
	{
		return [
			[
				'<controller>(/<action>(/<id>))',
				NULL,
				['controller' => 'Welcome', 'action' => 'index'],
				'Welcome',
				'index',
				'unit/test/1',
				[
					'controller' => 'unit',
					'action' => 'test',
					'id' => '1'
				],
				'Welcome',
			],
			[
				'(<controller>(/<action>(/<id>)))',
				NULL,
				['controller' => 'welcome', 'action' => 'index'],
				'Welcome',
				'index',
				'unit/test/1',
				[
					'controller' => 'unit',
					'action' => 'test',
					'id' => '1'
				],
				'',
			],
		];
	}

	/**
	 * Defaults specified with defaults() should be used if their values aren't
	 * present in the uri
	 *
	 * @dataProvider provider_defaults_are_used_if_params_arent_specified
	 *
	 * @test
	 * @covers Route::matches
	 */
	public function test_defaults_are_used_if_params_arent_specified($uri, $regex, $defaults, $c, $a, $test_uri, $test_uri_array, $default_uri)
	{
		$route = new Route($uri, $regex);
		$route->defaults($defaults);

		$this->assertSame($defaults, $route->defaults());

		// Mock a request class
		$request = $this->get_request_mock($default_uri);

		$matches = $route->matches($request);

		$this->assertInternalType('array', $matches);
		$this->assertArrayHasKey('controller', $matches);
		$this->assertArrayHasKey('action', $matches);
		$this->assertArrayNotHasKey('id', $matches);
		// $this->assertSame(4, count($matches));
		$this->assertSame($c, $matches['controller']);
		$this->assertSame($a, $matches['action']);
		$this->assertSame($test_uri, $route->uri($test_uri_array));
		$this->assertSame($default_uri, $route->uri());
	}

	/**
	 * Provider for test_optional_groups_containing_specified_params
	 *
	 * @return array
	 */
	public function provider_optional_groups_containing_specified_params()
	{
		return [
			/**
			 * Specifying this should cause controller and action to show up
			 * refs #4113
			 */
			[
				'(<controller>(/<action>(/<id>)))',
				['controller' => 'welcome', 'action' => 'index'],
				['id' => '1'],
				'welcome/index/1',
			],
			[
				'<controller>(/<action>(/<id>))',
				['controller' => 'welcome', 'action' => 'index'],
				['action' => 'foo'],
				'welcome/foo',
			],
			[
				'<controller>(/<action>(/<id>))',
				['controller' => 'welcome', 'action' => 'index'],
				['action' => 'index'],
				'welcome',
			],
			/**
			 * refs #4630
			 */
			[
				'api(/<version>)/const(/<id>)(/<custom>)',
				['version' => 1],
				NULL,
				'api/const',
			],
			[
				'api(/<version>)/const(/<id>)(/<custom>)',
				['version' => 1],
				['version' => 9],
				'api/9/const',
			],
			[
				'api(/<version>)/const(/<id>)(/<custom>)',
				['version' => 1],
				['id' => 2],
				'api/const/2',
			],
			[
				'api(/<version>)/const(/<id>)(/<custom>)',
				['version' => 1],
				['custom' => 'x'],
				'api/const/x',
			],
			[
				'(<controller>(/<action>(/<id>)(/<type>)))',
				['controller' => 'test', 'action' => 'index', 'type' => 'html'],
				['type' => 'json'],
				'test/index/json',
			],
			[
				'(<controller>(/<action>(/<id>)(/<type>)))',
				['controller' => 'test', 'action' => 'index', 'type' => 'html'],
				['id' => 123],
				'test/index/123',
			],
			[
				'(<controller>(/<action>(/<id>)(/<type>)))',
				['controller' => 'test', 'action' => 'index', 'type' => 'html'],
				['id' => 123, 'type' => 'html'],
				'test/index/123',
			],
			[
				'(<controller>(/<action>(/<id>)(/<type>)))',
				['controller' => 'test', 'action' => 'index', 'type' => 'html'],
				['id' => 123, 'type' => 'json'],
				'test/index/123/json',
			],
		];
	}

	/**
	 * When an optional param is specified, the optional params leading up to it
	 * must be in the URI.
	 *
	 * @dataProvider provider_optional_groups_containing_specified_params
	 *
	 * @ticket 4113
	 * @ticket 4630
	 */
	public function test_optional_groups_containing_specified_params($uri, $defaults, $params, $expected)
	{
		$route = new Route($uri, NULL);
		$route->defaults($defaults);

		$this->assertSame($expected, $route->uri($params));
	}

	/**
	 * Optional params should not be used if what is passed in is identical
	 * to the default.
	 *
	 * refs #4116
	 *
	 * @test
	 * @covers Route::uri
	 */
	public function test_defaults_are_not_used_if_param_is_identical()
	{
		$route = new Route('(<controller>(/<action>(/<id>)))');
		$route->defaults([
			'controller' => 'welcome',
			'action'     => 'index'
		]);

		$this->assertSame('', $route->uri(['controller' => 'welcome']));
		$this->assertSame('welcome2', $route->uri(['controller' => 'welcome2']));
	}

	/**
	 * Provider for test_required_parameters_are_needed
	 *
	 * @return array
	 */
	public function provider_required_parameters_are_needed()
	{
		return [
			[
				'admin(/<controller>(/<action>(/<id>)))',
				'admin',
				'admin/users/add',
			],
		];
	}

	/**
	 * This tests that routes with required parameters will not match uris without them present
	 *
	 * @dataProvider provider_required_parameters_are_needed
	 *
	 * @test
	 * @covers Route::matches
	 */
	public function test_required_parameters_are_needed($uri, $matches_route1, $matches_route2)
	{
		$route = new Route($uri);

		// Mock a request class that will return empty uri
		$request = $this->get_request_mock('');

		$this->assertFalse($route->matches($request));

		// Mock a request class that will return route1
		$request = $this->get_request_mock($matches_route1);

		$matches = $route->matches($request);

		$this->assertInternalType('array', $matches);

		// Mock a request class that will return route2 uri
		$request = $this->get_request_mock($matches_route2);

		$matches = $route->matches($request);

		$this->assertInternalType('array', $matches);
		// $this->assertSame(5, count($matches));
		$this->assertArrayHasKey('controller', $matches);
		$this->assertArrayHasKey('action', $matches);
	}

	/**
	 * Provider for test_required_parameters_are_needed
	 *
	 * @return array
	 */
	public function provider_reverse_routing_returns_routes_uri_if_route_is_static()
	{
		return [
			[
				'info/about_us',
				NULL,
				'info/about_us',
				['some' => 'random', 'params' => 'to confuse'],
			],
		];
	}

	/**
	 * This tests the reverse routing returns the uri specified in the route
	 * if it's a static route
	 *
	 * A static route is a route without any parameters
	 *
	 * @dataProvider provider_reverse_routing_returns_routes_uri_if_route_is_static
	 *
	 * @test
	 * @covers Route::uri
	 */
	public function test_reverse_routing_returns_routes_uri_if_route_is_static($uri, $regex, $target_uri, $uri_params)
	{
		$route = new Route($uri, $regex);

		$this->assertSame($target_uri, $route->uri($uri_params));
	}

	/**
	 * Provider for test_uri_throws_exception_if_required_params_are_missing
	 *
	 * @return array
	 */
	public function provider_uri_throws_exception_if_required_params_are_missing()
	{
		return [
			[
				'<controller>(/<action)',
				NULL,
				['action' => 'awesome-action'],
			],
			/**
			 * Optional params are required when they lead to a specified param
			 * refs #4113
			 */
			[
				'(<controller>(/<action>))',
				NULL,
				['action' => 'awesome-action'],
			],
		];
	}

	/**
	 * When Route::uri is working on a uri that requires certain parameters to be present
	 * (i.e. <controller> in '<controller(/<action)') then it should throw an exception
	 * if the param was not provided
	 *
	 * @dataProvider provider_uri_throws_exception_if_required_params_are_missing
	 *
	 * @test
	 * @covers Route::uri
	 */
	public function test_uri_throws_exception_if_required_params_are_missing($uri, $regex, $uri_array)
	{
		$route = new Route($uri, $regex);

        $this->expectException('Koseven_Exception');
		$route->uri($uri_array);
	}

	/**
	 * Provider for test_uri_fills_required_uri_segments_from_params
	 *
	 * @return array
	 */
	public function provider_uri_fills_required_uri_segments_from_params()
	{
		return [
			[
				'<controller>/<action>(/<id>)',
				NULL,
				'users/edit',
				[
					'controller' => 'users',
					'action'     => 'edit',
				],
				'users/edit/god',
				[
					'controller' => 'users',
					'action'     => 'edit',
					'id'         => 'god',
				],
			],
		];
	}

	/**
	 * The logic for replacing required segments is separate (but similar) to that for
	 * replacing optional segments.
	 *
	 * This test asserts that Route::uri will replace required segments with provided
	 * params
	 *
	 * @dataProvider provider_uri_fills_required_uri_segments_from_params
	 *
	 * @test
	 * @covers Route::uri
	 */
	public function test_uri_fills_required_uri_segments_from_params($uri, $regex, $uri_string1, $uri_array1, $uri_string2, $uri_array2)
	{
		$route = new Route($uri, $regex);

		$this->assertSame(
			$uri_string1,
			$route->uri($uri_array1)
		);

		$this->assertSame(
			$uri_string2,
			$route->uri($uri_array2)
		);
	}

	/**
	 * Provides test data for test_composing_url_from_route()
	 * @return array
	 */
	public function provider_composing_url_from_route()
	{
		return [
			['/'],
			['/news/view/42', ['controller' => 'news', 'action' => 'view', 'id' => 42]],
			['http://kohanaframework.org/news', ['controller' => 'news'], 'http']
		];
	}

	/**
	 * Tests Route::url()
	 *
	 * Checks the url composing from specific route via Route::url() shortcut
	 *
	 * @test
	 * @dataProvider provider_composing_url_from_route
	 * @param string $expected
	 * @param array $params
	 * @param boolean $protocol
	 */
	public function test_composing_url_from_route($expected, $params = NULL, $protocol = NULL)
	{
		Route::set('foobar', '(<controller>(/<action>(/<id>)))')
			->defaults([
				'controller' => 'welcome',
			]
		);

		$this->setEnvironment([
			'_SERVER' => ['HTTP_HOST' => 'kohanaframework.org'],
			'Kohana::$base_url' => '/',
			'Kohana::$index_file' => '',
		]);

		$this->assertSame($expected, Route::url('foobar', $params, $protocol));
	}

	/**
	 * Tests Route::compile()
	 *
	 * Makes sure that compile will use custom regex if specified
	 *
	 * @test
	 * @covers Route::compile
	 */
	public function test_compile_uses_custom_regex_if_specificed()
	{
		$compiled = Route::compile(
			'<controller>(/<action>(/<id>))',
			[
				'controller' => '[a-z]+',
				'id' => '\d+',
			]
		);

		$this->assertSame('#^(?P<controller>[a-z]+)(?:/(?P<action>[^/.,;?\n]++)(?:/(?P<id>\d+))?)?$#uD', $compiled);
	}

	/**
	 * Tests Route::is_external(), ensuring the host can return
	 * whether internal or external host
	 */
	public function test_is_external_route_from_host()
	{
		// Setup local route
		Route::set('internal', 'local/test/route')
			->defaults([
				'controller' => 'foo',
				'action'     => 'bar'
				]
			);

		// Setup external route
		Route::set('external', 'local/test/route')
			->defaults([
				'controller' => 'foo',
				'action'     => 'bar',
				'host'       => 'http://kohanaframework.org'
				]
			);

		// Test internal route
		$this->assertFalse(Route::get('internal')->is_external());

		// Test external route
		$this->assertTrue(Route::get('external')->is_external());
	}

	/**
	 * Provider for test_external_route_includes_params_in_uri
	 *
	 * @return array
	 */
	public function provider_external_route_includes_params_in_uri()
	{
		return [
			[
				'<controller>/<action>',
				[
					'controller'  => 'foo',
					'action'      => 'bar',
					'host'        => 'kohanaframework.org'
				],
				'http://kohanaframework.org/foo/bar'
			],
			[
				'<controller>/<action>',
				[
					'controller'  => 'foo',
					'action'      => 'bar',
					'host'        => 'http://kohanaframework.org'
				],
				'http://kohanaframework.org/foo/bar'
			],
			[
				'foo/bar',
				[
					'controller'  => 'foo',
					'host'        => 'http://kohanaframework.org'
				],
				'http://kohanaframework.org/foo/bar'
			],
		];
	}

	/**
	 * Tests the external route include route parameters
	 *
	 * @dataProvider provider_external_route_includes_params_in_uri
	 */
	public function test_external_route_includes_params_in_uri($route, $defaults, $expected_uri)
	{
		Route::set('test', $route)
			->defaults($defaults);

		$this->assertSame($expected_uri, Route::get('test')->uri());
	}

	/**
	 * Provider for test_route_filter_modify_params
	 *
	 * @return array
	 */
	public function provider_route_filter_modify_params()
	{
		return [
			[
				'<controller>/<action>',
				[
					'controller'  => 'Test',
					'action'      => 'same',
				],
				['Route_Holder', 'route_filter_modify_params_array'],
				'test/different',
				[
					'controller'  => 'Test',
					'action'      => 'modified',
				],
			],
			[
				'<controller>/<action>',
				[
					'controller'  => 'test',
					'action'      => 'same',
				],
				['Route_Holder', 'route_filter_modify_params_false'],
				'test/fail',
				FALSE,
			],
		];
	}

	/**
	 * Tests that route filters can modify parameters
	 *
	 * @covers Route::filter
	 * @dataProvider provider_route_filter_modify_params
	 */
	public function test_route_filter_modify_params($route, $defaults, $filter, $uri, $expected_params)
	{
		$route = new Route($route);

		// Mock a request class
		$request = $this->get_request_mock($uri);

		$params = $route->defaults($defaults)->filter($filter)->matches($request);

		$this->assertSame($expected_params, $params);
	}

	/**
	 * Provides test data for test_route_uri_encode_parameters
	 *
	 * @return array
	 */
	public function provider_route_uri_encode_parameters()
	{
		return [
			[
				'article',
				'blog/article/<article_name>',
				[
					'controller' => 'home',
					'action' => 'index'
				],
				'article_name',
				'Article name with special chars \\ ##',
				'blog/article/Article%20name%20with%20special%20chars%20\\%20%23%23'
			]
		];
	}

	/**
	 * http://dev.kohanaframework.org/issues/4079
	 *
	 * @test
	 * @covers Route::get
	 * @ticket 4079
	 * @dataProvider provider_route_uri_encode_parameters
	 */
	public function test_route_uri_encode_parameters($name, $uri_callback, $defaults, $uri_key, $uri_value, $expected)
	{
		Route::set($name, $uri_callback)->defaults($defaults);

		$get_route_uri = Route::get($name)->uri([$uri_key => $uri_value]);

		$this->assertSame($expected, $get_route_uri);
	}

	/**
	 * Get a mock of the Request class with a mocked `uri` method
	 *
	 * We are also mocking `method` method as it conflicts with newer PHPUnit,
	 * in order to avoid the fatal errors
	 *
	 * @param string $uri
	 * @return type
	 */
	public function get_request_mock($uri)
	{
		// Mock a request class with the $uri uri
		$request = $this->createMock('Request', ['uri', 'method'], [$uri]);

		// mock `uri` method
		$request->expects($this->any())
			->method('uri')
		  	// Request::uri() called by Route::matches() in the tests will return $uri
			->will($this->returnValue($uri));

		// also mock `method` method
		$request->expects($this->any())
			->method('method')
			->withAnyParameters();

		return $request;
	}

}
