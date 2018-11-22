<?php

/**
 * Unit tests for internal request client
 *
 * @group koseven
 * @group koseven.core
 * @group koseven.core.request
 * @group koseven.core.request.client
 * @group koseven.core.request.client.internal
 *
 * @package    Koseven
 * @category   Tests
 * @copyright  (c) 2007-2014  Kohana Team
 * @copyright  (c) 2014-2018  Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Koseven_Request_Client_InternalTest extends Unittest_TestCase
{

	protected $_log_object;

	// @codingStandardsIgnoreStart
	public function setUp()
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();

		// temporarily save $log object
		$this->_log_object = Koseven::$log;
		Koseven::$log = NULL;
	}

	// @codingStandardsIgnoreStart
	public function tearDown()
	// @codingStandardsIgnoreEnd
	{
		// re-assign log object
		Koseven::$log = $this->_log_object;

		parent::tearDown();
	}

	public function provider_response_failure_status()
	{
		return [
			['', 'Welcome', 'missing_action', 'Welcome/missing_action', 404],
			['koseven3', 'missing_controller', 'index', 'koseven3/missing_controller/index', 404],
			['', 'Template', 'missing_action', 'koseven3/Template/missing_action', 500],
		];
	}

	/**
	 * Tests for correct exception messages
	 *
	 * @test
	 * @dataProvider provider_response_failure_status
	 *
	 * @return null
	 */
	public function test_response_failure_status($directory, $controller, $action, $uri, $expected)
	{
		// Mock for request object
		$request = $this->createMock('Request', ['directory', 'controller', 'action', 'uri', 'response', 'method'], [$uri]);

		$request->expects($this->any())
			->method('directory')
			->will($this->returnValue($directory));

		$request->expects($this->any())
			->method('controller')
			->will($this->returnValue($controller));

		$request->expects($this->any())
			->method('action')
			->will($this->returnValue($action));

		$request->expects($this->any())
			->method('uri')
			->will($this->returnValue($uri));

		$request->expects($this->any())
			->method('execute')
			->will($this->returnValue($this->createMock('Response')));

		// mock `method` method to avoid fatals in newer versions of PHPUnit
		$request->expects($this->any())
			->method('method')
			->withAnyParameters();

		$internal_client = new Request_Client_Internal;

		$response = $internal_client->execute($request);

		$this->assertSame($expected, $response->status());
	}
}
