<?php
/**
 * Tests Kohana i18n class.
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.i18n
 *
 * @package    Kohana
 * @category   Tests
 * @author     Kohana Team
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_I18nTest extends Unittest_TestCase {

	/**
	 * Default values for the environment, see setEnvironment
	 * @var array
	 */
	// @codingStandardsIgnoreStart
	protected $environmentDefault =	[
		'I18n::$lang' => 'en-us',
	];
	// @codingStandardsIgnoreEnd

	/**
	 * Provides test data for test_lang()
	 *
	 * @return array
	 */
	public function provider_lang()
	{
		return [
			// $input, $expected
			[NULL, 'en-us'],
			['es-es', 'es-es'],
			['en_US', 'en-us'],
		];
	}

	/**
	 * Tests I18n::lang()
	 *
	 * @test
	 * @dataProvider provider_lang
	 * @param  boolean  $input     Input for I18n::lang
	 * @param  boolean  $expected  Output for I18n::lang
	 */
	public function test_lang($input, $expected)
	{
		$this->assertSame($expected, I18n::lang($input));
		$this->assertSame($expected, I18n::lang());
	}

	/**
	 * Provides test data for test_get()
	 * 
	 * @return array
	 */
	public function provider_get()
	{
		return [
			// $lang, $input, $expected
			['en-us', 'Hello, world!', 'Hello, world!'],
			['es-es', 'Hello, world!', '¡Hola, mundo!'],
			['fr-fr', 'Hello, world!', 'Bonjour, monde!'],
			['en-us', ['Hello, :name!', [':name' => 'world']], 'Hello, world!'],
			['ru-ru', ['Привет, :name!', [':name' => 'мир']], 'Привет, мир!'],
		];
	}

	/**
	 * Tests i18n::get()
	 *
	 * @test
	 * @dataProvider provider_get
	 * @param boolean $input  Input for I18n::get
	 * @param boolean $expected Output for I18n::get
	 */
	public function test_get($lang, $input, $expected)
	{
		I18n::lang($lang);
		$this->assertSame($expected, I18n::get($input));

		// Test immediate translation, issue #3085
		I18n::lang('en-us');
		$this->assertSame($expected, I18n::get($input, $lang));
	}
}
