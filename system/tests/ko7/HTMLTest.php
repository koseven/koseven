<?php
/**
 * Tests HTML
 *
 * @group ko7
 * @group ko7.core
 * @group ko7.core.html
 *
 * @package    KO7
 * @category   Tests
 *
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) 2016-2020 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class KO7_HTMLTest extends Unittest_TestCase
{
	/**
	 * Sets up the environment
	 */
	// @codingStandardsIgnoreStart
	public function setUp(): void
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();
		KO7::$config->load('url')->set('trusted_hosts', ['www\.koseven\.dev']);
	}
	
	/**
	 * Defaults for this test
	 * @var array
	 */
	// @codingStandardsIgnoreStart
	protected $environmentDefault = [
		'KO7::$base_url'    => '/ko7/',
		'KO7::$index_file'  => 'index.php',
		'HTML::$strict'     => TRUE,
		'HTTP_HOST'	    => 'www.koseven.dev',
	];
	// @codingStandardsIgnoreStart

	/**
	 * Provides test data for test_attributes()
	 *
	 * @return array
	 */
	public function provider_attributes()
	{
		return [
			[
				['name' => 'field', 'random' => 'not_quite', 'id' => 'unique_field'],
				[],
				' id="unique_field" name="field" random="not_quite"'
			],
			[
				['invalid' => NULL],
				[],
				''
			],
			[
				[],
				[],
				''
			],
			[
				['name' => 'field', 'checked'],
				[],
				' name="field" checked="checked"',
			],
			[
				['id' => 'disabled_field', 'disabled'],
				['HTML::$strict' => FALSE],
				' id="disabled_field" disabled',
			],
		];
	}

	/**
	 * Tests HTML::attributes()
	 *
	 * @test
	 * @dataProvider provider_attributes
	 * @param array  $attributes  Attributes to use
	 * @param array  $options     Environment options to use
	 * @param string $expected    Expected output
	 */
	public function test_attributes(array $attributes, array $options, $expected)
	{
		$this->setEnvironment($options);

		$this->assertSame(
			$expected,
			HTML::attributes($attributes)
		);
	}

	/**
	 * Provides test data for test_script
	 *
	 * @return array Array of test data
	 */
	public function provider_script()
	{
		return [
			[
				'<script type="text/javascript" src="http://google.com/script.js"></script>',
				'http://google.com/script.js',
			],
			[
				'<script type="text/javascript" src="http://www.koseven.dev/ko7/index.php/my/script.js"></script>',
				'my/script.js',
				NULL,
				'http',
				TRUE
			],
			[
				'<script type="text/javascript" src="https://www.koseven.dev/ko7/my/script.js"></script>',
				'my/script.js',
				NULL,
				'https',
				FALSE
			],
			[
				'<script type="text/javascript" src="https://www.koseven.dev/ko7/my/script.js"></script>',
				'/my/script.js', // Test absolute paths
				NULL,
				'https',
				FALSE
			],
			[
				'<script type="text/javascript" src="//google.com/script.js"></script>',
				'//google.com/script.js',
			],

		];
	}

	/**
	 * Tests HTML::script()
	 *
	 * @test
	 * @dataProvider  provider_script
	 * @param string  $expected       Expected output
	 * @param string  $file           URL to script
	 * @param array   $attributes     HTML attributes for the anchor
	 * @param string  $protocol       Protocol to use
	 * @param bool    $index          Should the index file be included in url?
	 */
	public function test_script($expected, $file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		$this->assertSame(
			$expected,
			HTML::script($file, $attributes, $protocol, $index)
		);
	}

	/**
	 * Data provider for the style test
	 *
	 * @return array Array of test data
	 */
	public function provider_style()
	{
		return [
			[
				'<link type="text/css" href="http://google.com/style.css" rel="stylesheet" />',
				'http://google.com/style.css',
				[],
				NULL,
				FALSE
			],
			[
				'<link type="text/css" href="/ko7/my/style.css" rel="stylesheet" />',
				'my/style.css',
				[],
				NULL,
				FALSE
			],
			[
				'<link type="text/css" href="https://www.koseven.dev/ko7/my/style.css" rel="stylesheet" />',
				'my/style.css',
				[],
				'https',
				FALSE
			],
			[
				'<link type="text/css" href="https://www.koseven.dev/ko7/index.php/my/style.css" rel="stylesheet" />',
				'my/style.css',
				[],
				'https',
				TRUE
			],
			[
				'<link type="text/css" href="https://www.koseven.dev/ko7/index.php/my/style.css" rel="stylesheet" />',
				'/my/style.css',
				[],
				'https',
				TRUE
			],
			[
				// #4283: http://koseven.dev/issues/4283
				'<link type="text/css" href="https://www.koseven.dev/ko7/index.php/my/style.css" rel="stylesheet/less" />',
				'my/style.css',
				[
					'rel' => 'stylesheet/less'
				],
				'https',
				TRUE
			],
			[
				'<link type="text/css" href="//google.com/style.css" rel="stylesheet" />',
				'//google.com/style.css',
				[],
				NULL,
				FALSE
			],
		];
	}

	/**
	 * Tests HTML::style()
	 *
	 * @test
	 * @dataProvider  provider_style
	 * @param string  $expected     The expected output
	 * @param string  $file         The file to link to
	 * @param array   $attributes   Any extra attributes for the link
	 * @param string  $protocol     Protocol to use
	 * @param bool    $index        Whether the index file should be added to the link
	 */
	public function test_style($expected, $file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		$this->assertSame(
			$expected,
			HTML::style($file, $attributes, $protocol, $index)
		);
	}

	/**
	 * Provides test data for test_anchor
	 *
	 * @return array Test data
	 */
	public function provider_anchor()
	{
		return [
			// a fragment-only anchor
			[
				'<a href="#go-to-section-ko7">KO7</a>',
				[],
				'#go-to-section-ko7',
				'KO7',
			],
			// a query-only anchor
			[
				'<a href="?cat=a">Category A</a>',
				[],
				'?cat=a',
				'Category A',
			],
			[
				'<a href="http://koseven.dev/">KO7</a>',
				[],
				'http://koseven.dev/',
				'KO7',
			],
			[
				'<a href="http://google.com" target="_blank">GOOGLE</a>',
				[],
				'http://google.com',
				'GOOGLE',
				['target' => '_blank'],
				'http',
			],
			[
				'<a href="//google.com/">GOOGLE</a>',
				[],
				'//google.com/',
				'GOOGLE',
			],
			[
				'<a href="https://www.koseven.dev/ko7/users/example">KO7</a>',
				[],
				'users/example',
				'KO7',
				NULL,
				'https',
				FALSE,
			],
			[
				'<a href="https://www.koseven.dev/ko7/index.php/users/example">KO7</a>',
				[],
				'users/example',
				'KO7',
				NULL,
				'https',
				TRUE,
			],
			[
				'<a href="https://www.koseven.dev/ko7/index.php/users/example">KO7</a>',
				[],
				'users/example',
				'KO7',
				NULL,
				'https',
			],
			[
				'<a href="https://www.koseven.dev/ko7/index.php/users/example">KO7</a>',
				[],
				'users/example',
				'KO7',
				NULL,
				'https',
				TRUE,
			],
			[
				'<a href="https://www.koseven.dev/ko7/users/example">KO7</a>',
				[],
				'users/example',
				'KO7',
				NULL,
				'https',
				FALSE,
			],
			[
				'<a href="https://www.koseven.dev/ko7/users/example">KO7</a>',
				[],
				'/users/example',
				'KO7',
				NULL,
				'https',
				FALSE,
			],
		];
	}

	/**
	 * Tests HTML::anchor
	 *
	 * @test
	 * @dataProvider provider_anchor
	 */
	public function test_anchor($expected, array $options, $uri, $title = NULL, array $attributes = NULL, $protocol = NULL, $index = TRUE)
	{
		// $this->setEnvironment($options);

		$this->assertSame(
			$expected,
			HTML::anchor($uri, $title, $attributes, $protocol, $index)
		);
	}

	/**
	 * Data provider for test_file_anchor
	 *
	 * @return array
	 */
	public function provider_file_anchor()
	{
		return [
			[
				'<a href="/ko7/mypic.png">My picture file</a>',
				[],
				'mypic.png',
				'My picture file',
			],
			[
				'<a href="https://www.koseven.dev/ko7/index.php/mypic.png" attr="value">My picture file</a>',
				['attr' => 'value'],
				'mypic.png',
				'My picture file',
				'https',
				TRUE
			],
			[
				'<a href="ftp://www.koseven.dev/ko7/mypic.png">My picture file</a>',
				[],
				'mypic.png',
				'My picture file',
				'ftp',
				FALSE
			],
			[
				'<a href="ftp://www.koseven.dev/ko7/mypic.png">My picture file</a>',
				[],
				'/mypic.png',
				'My picture file',
				'ftp',
				FALSE
			],
		];
	}

	/**
	 * Test for HTML::file_anchor()
	 *
	 * @test
	 * @covers HTML::file_anchor
	 * @dataProvider provider_file_anchor
	 */
	public function test_file_anchor($expected, array $attributes, $file, $title = NULL, $protocol = NULL, $index = FALSE)
	{
		$this->assertSame(
			$expected,
			HTML::file_anchor($file, $title, $attributes, $protocol, $index)
		);
	}


	/**
	 * Provides test data for test_image
	 *
	 * @return array Array of test data
	 */
	public function provider_image()
	{
		return [
			[
				'<img src="http://google.com/image.png" />',
				'http://google.com/image.png',
			],
			[
				'<img src="//google.com/image.png" />',
				'//google.com/image.png',
			],
			[
				'<img src="/ko7/img/image.png" />',
				'img/image.png',
			],
			[
				'<img src="https://www.koseven.dev/ko7/index.php/img/image.png" alt="..." />',
				'img/image.png',
				['alt' => '...',],
				'https',
				TRUE
			],
		];
	}

	/**
	 * Tests HTML::image()
	 *
	 * @test
	 * @dataProvider  provider_image
	 * @param string  $expected       Expected output
	 * @param string  $file           file name
	 * @param array   $attributes     HTML attributes for the image
	 * @param string  $protocol       Protocol to use
	 * @param bool    $index          Should the index file be included in url?
	 */
	public function test_image($expected, $file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		$this->assertSame(
			$expected,
			HTML::image($file, $attributes, $protocol, $index)
		);
	}

}
