<?php
/**
 * Unit tests for external request client
 *
 * @group kohana
 * @group kohana.request
 * @group kohana.request.client
 * @group kohana.request.client.external
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_Request_Client_ExternalTest extends Unittest_TestCase {

	/**
	 * Provider for test_factory()
	 *
	 * @return  array
	 */
	public function provider_factory()
	{
		Request_Client_External::$client = 'Request_Client_Stream';

		$return = array(
			array(
				array(),
				NULL,
				'Request_Client_Stream'
			),
			array(
				array(),
				'Request_Client_Stream',
				'Request_Client_Stream'
			)
		);

		if (extension_loaded('curl'))
		{
			$return[] = array(
				array(),
				'Request_Client_Curl',
				'Request_Client_Curl'
			);
		}

		if (extension_loaded('http'))
		{
			$return[] = array(
				array(),
				'Request_Client_HTTP',
				'Request_Client_HTTP'
			);
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
		return array(
			array(
				NULL,
				NULL,
				array()
			),
			array(
				array('foo' => 'bar', 'stfu' => 'snafu'),
				NULL,
				array('foo' => 'bar', 'stfu' => 'snafu')
			),
			array(
				'foo',
				'bar',
				array('foo' => 'bar')
			),
			array(
				array('foo' => 'bar'),
				'foo',
				array('foo' => 'bar')
			)
		);
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
		$post = array('foo' => 'bar', 'snafu' => 'stfu');

		return array(
			array(
				'application/json',
				$json,
				array(),
				array(
					'content-type' => 'application/json',
					'body'         => $json
				)
			),
			array(
				'application/json',
				$json,
				$post,
				array(
					'content-type' => 'application/x-www-form-urlencoded; charset='.Kohana::$charset,
					'body'         => http_build_query($post, NULL, '&')
				)
			)
		);
	}
}