<?php
/**
 * Unit tests for external request client
 *
 * @group koseven
 * @group koseven.request
 * @group koseven.request.client
 * @group koseven.request.client.external
 *
 * @package    Koseven
 * @category   Tests
 * @copyright  (c) 2007-2014  Kohana Team
 * @copyright  (c) 2014-2018  Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Koseven_Request_Client_ExternalTest extends Unittest_TestCase {

	/**
	 * Provider for test_factory()
	 *
	 * @return  array
	 */
	public function provider_factory()
	{
		Request_Client_External::$client = 'Request_Client_Stream';

		$return = [
			[
				[],
				NULL,
				'Request_Client_Stream'
			],
			[
				[],
				'Request_Client_Stream',
				'Request_Client_Stream'
			]
		];

		if (extension_loaded('curl'))
		{
			$return[] = [
				[],
				'Request_Client_Curl',
				'Request_Client_Curl'
			];
		}

		if (extension_loaded('http'))
		{
			$return[] = [
				[],
				'Request_Client_HTTP',
				'Request_Client_HTTP'
			];
		}

		return $return;
	}

	/**
	 * Tests the [Request_Client_External::factory()] method
	 *
	 * @dataProvider provider_factory
	 *
	 * @param   array   $params  params
	 * @param   string  $client  client
	 * @param   Request_Client_External $expected expected
	 * @return  void
	 */
	public function test_factory($params, $client, $expected)
	{
		$this->assertInstanceOf($expected, Request_Client_External::factory($params, $client));
	}

	/**
	 * Data provider for test_options
	 *
	 * @return  array
	 */
	public function provider_options()
	{
		return [
			[
				NULL,
				NULL,
				[]
			],
			[
				['foo' => 'bar', 'stfu' => 'snafu'],
				NULL,
				['foo' => 'bar', 'stfu' => 'snafu']
			],
			[
				'foo',
				'bar',
				['foo' => 'bar']
			],
			[
				['foo' => 'bar'],
				'foo',
				['foo' => 'bar']
			]
		];
	}

	/**
	 * Tests the [Request_Client_External::options()] method
	 *
	 * @dataProvider provider_options
	 *
	 * @param   mixed  $key  key
	 * @param   mixed  $value  value
	 * @param   array  $expected  expected
	 * @return  void
	 */
	public function test_options($key, $value, $expected)
	{
		// Create a mock external client
		$client = new Request_Client_Stream;

		$client->options($key, $value);
		$this->assertSame($expected, $client->options());
	}

	/**
	 * Data provider for test_execute
	 *
	 * @return  array
	 */
	public function provider_execute()
	{
		$json = '{"foo": "bar", "snafu": "stfu"}';
		$post = ['foo' => 'bar', 'snafu' => 'stfu'];

		return [
			[
				'application/json',
				$json,
				[],
				[
					'content-type' => 'application/json',
					'body'         => $json
				]
			],
			[
				'application/json',
				$json,
				$post,
				[
					'content-type' => 'application/x-www-form-urlencoded; charset='.Koseven::$charset,
					'body'         => http_build_query($post, NULL, '&')
				]
			]
		];
	}
}