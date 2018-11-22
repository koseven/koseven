<?php

/**
 * Tests Date class
 *
 * @group kohana
 * @group kohana.core
 * @group kohana.core.date
 *
 * @package    Koseven
 * @category   Tests
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_DateTest extends Unittest_TestCase
{
	protected $_original_timezone = NULL;
	protected $default_locale;

	/**
	 * Ensures we have a consistant timezone for testing.
	 */
	// @codingStandardsIgnoreStart
	public function setUp()
	// @codingStandardsIgnoreEnd
	{
		parent::setUp();

		$this->_original_timezone = date_default_timezone_get();
		$this->default_locale = setlocale(LC_ALL, 0);

		date_default_timezone_set('America/Chicago');
		setlocale(LC_ALL, 'en_US.utf8');
	}

	/**
	 * Restores original timezone after testing.
	 */
	// @codingStandardsIgnoreStart
	public function tearDown()
	// @codingStandardsIgnoreEnd
	{
		date_default_timezone_set($this->_original_timezone);
		setlocale(LC_ALL, $this->default_locale);

		parent::tearDown();
	}

	/**
	 * Provides test data for test_offset()
	 *
	 * @return array
	 */
	public function provider_offset()
	{
		return [
			[30600, 'Asia/Calcutta', 'America/Argentina/Buenos_Aires'],
		];
	}

	/**
	 * Tests Date::offset()
	 *
	 * @test
	 * @dataProvider provider_offset
	 * @covers Date::offset
	 * @param integer $expected Expected offset
	 * @param string  $remote   Remote TZ
	 * @param string  $local    Local TZ
	 * @param integer $now      Current timestamp
	 */
	public function test_offset($expected, $remote, $local, $now = NULL)
	{
		$this->assertSame($expected, Date::offset($remote, $local, $now));
	}

	/**
	 * Provides test data for test_date()
	 *
	 * @return array
	 */
	public function provider_am_pm()
	{
		return [
			// All possible values
			[0, 'AM'],
			[1, 'AM'],
			[2, 'AM'],
			[3, 'AM'],
			[4, 'AM'],
			[5, 'AM'],
			[6, 'AM'],
			[7, 'AM'],
			[8, 'AM'],
			[9, 'AM'],
			[10, 'AM'],
			[11, 'AM'],
			[12, 'PM'],
			[13, 'PM'],
			[14, 'PM'],
			[15, 'PM'],
			[16, 'PM'],
			[17, 'PM'],
			[18, 'PM'],
			[19, 'PM'],
			[20, 'PM'],
			[21, 'PM'],
			[22, 'PM'],
			[23, 'PM'],
			[24, 'PM'],
			// ampm doesn't validate the hour, so I don't think we should test it..
			// test strings are converted
			['0', 'AM'],
			['12', 'PM'],
		];
	}

	/**
	 * Tests Date::ampm()
	 *
	 * @test
	 * @covers Date::ampm
	 * @dataProvider provider_am_pm
	 * @param <type> $hour
	 * @param <type> $expected
	 */
	public function test_am_pm($hour, $expected)
	{
		$this->assertSame(
			$expected,
			Date::ampm($hour)
		);
	}

	/**
	 * Provides test data for test_adjust()
	 *
	 * @return array
	 */
	public function provider_adjust()
	{
		return [
			// Might as well test all possibilities
			[1,  'am', '01'],
			[2,  'am', '02'],
			[3,  'am', '03'],
			[4,  'am', '04'],
			[5,  'am', '05'],
			[6,  'am', '06'],
			[7,  'am', '07'],
			[8,  'am', '08'],
			[9,  'am', '09'],
			[10, 'am', '10'],
			[11, 'am', '11'],
			[12, 'am', '00'],
			[1,  'pm', '13'],
			[2,  'pm', '14'],
			[3,  'pm', '15'],
			[4,  'pm', '16'],
			[5,  'pm', '17'],
			[6,  'pm', '18'],
			[7,  'pm', '19'],
			[8,  'pm', '20'],
			[9,  'pm', '21'],
			[10, 'pm', '22'],
			[11, 'pm', '23'],
			[12, 'pm', '12'],
			// It should also work with strings instead of ints
			['10', 'pm', '22'],
			['10', 'am', '10'],
		];
	}

	/**
	 * Tests Date::ampm()
	 *
	 * @test
	 * @dataProvider provider_adjust
	 * @param integer $hour       Hour in 12 hour format
	 * @param string  $ampm       Either am or pm
	 * @param string  $expected   Expected result
	 */
	public function test_adjust($hour, $ampm, $expected)
	{
		$this->assertSame(
			$expected,
			Date::adjust($hour, $ampm)
		);
	}

	/**
	 * Provides test data for test_days()
	 *
	 * @return array
	 */
	public function provider_days()
	{
		return [
			// According to "the rhyme" these should be the same every year
			[9, FALSE, 30],
			[4, FALSE, 30],
			[6, FALSE, 30],
			[11, FALSE, 30],
			[1, FALSE, 31],
			[3, FALSE, 31],
			[5, FALSE, 31],
			[7, FALSE, 31],
			[8, FALSE, 31],
			[10, FALSE, 31],
			// February is such a pain
			[2, 2001, 28],
			[2, 2000, 29],
			[2, 2012, 29],
		];
	}

	/**
	 * Tests Date::days()
	 *
	 * @test
	 * @covers Date::days
	 * @dataProvider provider_days
	 * @param integer $month
	 * @param integer $year
	 * @param integer $expected
	 */
	public function test_days($month, $year, $expected)
	{
		$days = Date::days($month, $year);

		$this->assertSame(
			$expected,
			count($days)
		);

		// This should be a mirrored array, days => days
		for ($i = 1; $i <= $expected; ++$i)
		{
			$this->assertArrayHasKey($i, $days);
			// Combining the type check into this saves about 400-500 assertions!
			$this->assertSame( (string) $i, $days[$i]);
		}
	}

	/**
	 * Provides test data for test_formatted_time()
	 *
	 * @return array
	 */
	public function provider_formatted_time()
	{
		return [
			// Test the default format
			['2010-04-16 17:00:00', '5:00PM 16th April 2010'],
			// Now we use our own format
			// Binary date!
			['01/01/2010 01:00', '1AM 1st January 2010', 'd/m/Y H:i'],
			// Timezones (see #3902)
			['2011-04-01 01:23:45 Antarctica/South_Pole', '2011-04-01 01:23:45', 'Y-m-d H:i:s e', 'Antarctica/South_Pole'],
			['2011-04-01 01:23:45 Antarctica/South_Pole', '2011-03-31 14:23:45 Europe/Paris', 'Y-m-d H:i:s e', 'Antarctica/South_Pole'],
			['2011-04-01 01:23:45 Antarctica/South_Pole', '@1301574225', 'Y-m-d H:i:s e', 'Antarctica/South_Pole'],
		];
	}

	/**
	 * Tests Date::formatted_time()
	 *
	 * @test
	 * @dataProvider provider_formatted_time
	 * @covers Date::formatted_time
	 * @ticket 3035 3902
	 * @param string         $expected         Expected output
	 * @param string|integer $datetime_str     The datetime timestamp / string
	 * @param string|null    $timestamp_format The output format
	 * @param string|null    $timezone         The timezone identifier
	 */
	public function test_formatted_time($expected, $datetime_str, $timestamp_format = NULL, $timezone = NULL)
	{
		$timestamp = Date::formatted_time($datetime_str, $timestamp_format, $timezone);

		$this->assertSame($expected, $timestamp);
	}

	/**
	 * Provider for test_months()
	 *
	 * @return array Test data
	 */
	public function provider_months()
	{
		return [
			[
				[
					1 => "1",
					2 => "2",
					3 => "3",
					4 => "4",
					5 => "5",
					6 => "6",
					7 => "7",
					8 => "8",
					9 => "9",
					10 => "10",
					11 => "11",
					12 => "12"
				],
				NULL
			],
			[
				[
					1 => "1",
					2 => "2",
					3 => "3",
					4 => "4",
					5 => "5",
					6 => "6",
					7 => "7",
					8 => "8",
					9 => "9",
					10 => "10",
					11 => "11",
					12 => "12"
				],
				'Guinness'
			],
			[
				[
					1 => "January",
					2 => "February",
					3 => "March",
					4 => "April",
					5 => "May",
					6 => "June",
					7 => "July",
					8 => "August",
					9 => "September",
					10 => "October",
					11 => "November",
					12 => "December"
				],
				Date::MONTHS_LONG
			],
			[
				[
					1 => "Jan",
					2 => "Feb",
					3 => "Mar",
					4 => "Apr",
					5 => "May",
					6 => "Jun",
					7 => "Jul",
					8 => "Aug",
					9 => "Sep",
					10 => "Oct",
					11 => "Nov",
					12 => "Dec"
				],
				Date::MONTHS_SHORT
			]

		];
	}

	/**
	 * Date::months() should allow the user to specify different format types, defaulting
	 * to a mirrored month number => month number array if format is NULL or unrecognised
	 *
	 * @test
	 * @dataProvider provider_months
	 * @covers Date::months
	 */
	public function test_months($expected, $format)
	{
		$months = Date::months($format);

		$this->assertSame($expected, $months);
	}

	/**
	 * Provides test data for test_span()
	 *
	 * @return array
	 */
	public function provider_span()
	{
		$time = time();
		return [
			// Test that it must specify an output format
			[
				$time,
				$time,
				'',
				FALSE
			],
			// Test that providing only one output just returns that output
			[
				$time - 30,
				$time,
				'seconds',
				30
			],
			// Random tests
			[
				$time - 30,
				$time,
				'years,months,weeks,days,hours,minutes,seconds',
				['years' => 0, 'months' => 0, 'weeks' => 0, 'days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 30],
			],
			[
				$time - (60 * 60 * 24 * 782) + (60 * 25),
				$time,
				'years,months,weeks,days,hours,minutes,seconds',
				['years' => 2, 'months' => 1, 'weeks' => 3, 'days' => 0, 'hours' => 1, 'minutes' => 28, 'seconds' => 24],
			],
			// Should be able to compare with the future & that it only uses formats specified
			[
				$time + (60 * 60 * 24 * 15) + (60 * 5),
				$time,
				'weeks,days,hours,minutes,seconds',
				['weeks' => 2, 'days' => 1, 'hours' => 0, 'minutes' => 5, 'seconds' => 0],
			],
			[
				// Add a bit of extra time to account for phpunit processing
				$time + (14 * 31 * 24* 60 * 60) + (79 * 80),
				NULL,
				'months,years',
				['months' => 2, 'years' => 1],
			],
		];
	}

	/**
	 * Tests Date::span()
	 *
	 * @test
	 * @covers Date::span
	 * @dataProvider provider_span
	 * @param integer $time1     Time in the past
	 * @param integer $time2     Time to compare against
	 * @param string  $output    Units to output
	 * @param array   $expected  Array of $outputs => values
	 */
	public function test_span($time1, $time2, $output, $expected)
	{
		$this->assertSame(
			$expected,
			Date::span($time1, $time2, $output)
		);
	}

	/**
	 * Provides test data to test_fuzzy_span
	 *
	 * This test data is provided on the assumption that it
	 * won't take phpunit more than 30 seconds to get the
	 * data from this provider to the test... ;)
	 *
	 * @return array Test Data
	 */
	public function provider_fuzzy_span()
	{
		$now = time();

		return [
			['moments ago', $now - 30, $now],
			['in moments', $now + 30, $now],

			['a few minutes ago', $now - 10*60, $now],
			['in a few minutes', $now + 10*60, $now],

			['less than an hour ago', $now - 45*60, $now],
			['in less than an hour', $now + 45*60, $now],

			['a couple of hours ago', $now - 2*60*60, $now],
			['in a couple of hours', $now + 2*60*60, $now],

			['less than a day ago', $now - 12*60*60, $now],
			['in less than a day', $now + 12*60*60, $now],

			['about a day ago', $now - 30*60*60, $now],
			['in about a day', $now + 30*60*60, $now],

			['a couple of days ago', $now - 3*24*60*60, $now],
			['in a couple of days', $now + 3*24*60*60, $now],

			['less than a week ago', $now - 5*24*60*60, $now],
			['in less than a week', $now + 5*24*60*60, $now],

			['about a week ago', $now - 9*24*60*60, $now],
			['in about a week', $now + 9*24*60*60, $now],

			['less than a month ago', $now - 20*24*60*60, $now],
			['in less than a month', $now + 20*24*60*60, $now],

			['about a month ago', $now - 40*24*60*60, $now],
			['in about a month', $now + 40*24*60*60, $now],

			['a couple of months ago', $now - 3*30*24*60*60, $now],
			['in a couple of months', $now + 3*30*24*60*60, $now],

			['less than a year ago', $now - 7*31*24*60*60, $now],
			['in less than a year', $now + 7*31*24*60*60, $now],

			['about a year ago', $now - 18*31*24*60*60, $now],
			['in about a year', $now + 18*31*24*60*60, $now],

			['a couple of years ago', $now - 3*12*31*24*60*60, $now],
			['in a couple of years', $now + 3*12*31*24*60*60, $now],

			['a few years ago', $now - 5*12*31*24*60*60, $now],
			['in a few years', $now + 5*12*31*24*60*60, $now],

			['about a decade ago', $now - 11*12*31*24*60*60, $now],
			['in about a decade', $now + 11*12*31*24*60*60, $now],

			['a couple of decades ago', $now - 20*12*31*24*60*60, $now],
			['in a couple of decades', $now + 20*12*31*24*60*60, $now],

			['several decades ago', $now - 50*12*31*24*60*60, $now],
			['in several decades', $now + 50*12*31*24*60*60, $now],

			['a long time ago', $now - pow(10,10), $now],
			['in a long time', $now + pow(10,10), $now],
		];
	}

	/**
	 * Test of Date::fuzy_span()
	 *
	 * @test
	 * @dataProvider provider_fuzzy_span
	 * @param string  $expected        Expected output
	 * @param integer $timestamp       Timestamp to use
	 * @param integer $local_timestamp The local timestamp to use
	 */
	public function test_fuzzy_span($expected, $timestamp, $local_timestamp)
	{
		$this->assertSame(
			$expected,
			Date::fuzzy_span($timestamp, $local_timestamp)
		);
	}

	/**
	 * Provides test data for test_years()
	 *
	 * @return array Test Data
	 */
	public function provider_years()
	{
		return [
			[
				[
					2005 => '2005',
					2006 => '2006',
					2007 => '2007',
				    2008 => '2008',
				    2009 => '2009',
				    2010 => '2010',
				    2011 => '2011',
				    2012 => '2012',
					2013 => '2013',
					2014 => '2014',
					2015 => '2015',
				],
				2005,
				2015
			],
		];
	}

	/**
	 * Tests Data::years()
	 *
	 * @test
	 * @dataProvider provider_years
	 */
	public function test_years($expected, $start = FALSE, $end = FALSE)
	{
		$this->assertSame(
			$expected,
			Date::years($start, $end)
		);
	}

	public function provider_hours()
	{
		return [
			[
				[
					1 => '1',
					2 => '2',
					3 => '3',
					4 => '4',
					5 => '5',
					6 => '6',
					7 => '7',
					8 => '8',
					9 => '9',
					10 => '10',
					11 => '11',
					12 => '12',
				],
			],
		];
	}

	/**
	 * Test for Date::hours
	 *
	 * @test
	 * @dataProvider provider_hours
	 */
	public function test_hours($expected, $step = 1, $long = FALSE, $start = NULL)
	{
		$this->assertSame(
			$expected,
			Date::hours($step, $long, $start)
		);
	}

	/**
	 * Provides test data for test_seconds
	 *
	 * @return array Test data
	 */
	public function provider_seconds()
	{
		return [
			[
				// Thank god for var_export()
				[
					0 => '00', 1 => '01', 2 => '02', 3 => '03', 4 => '04',
					5 => '05', 6 => '06', 7 => '07', 8 => '08', 9 => '09',
					10 => '10', 11 => '11', 12 => '12', 13 => '13', 14 => '14',
					15 => '15', 16 => '16', 17 => '17', 18 => '18', 19 => '19',
					20 => '20', 21 => '21', 22 => '22', 23 => '23', 24 => '24',
					25 => '25', 26 => '26', 27 => '27', 28 => '28', 29 => '29',
					30 => '30', 31 => '31', 32 => '32', 33 => '33', 34 => '34',
					35 => '35', 36 => '36', 37 => '37', 38 => '38', 39 => '39',
					40 => '40', 41 => '41', 42 => '42', 43 => '43', 44 => '44',
					45 => '45', 46 => '46', 47 => '47', 48 => '48', 49 => '49',
					50 => '50', 51 => '51', 52 => '52', 53 => '53', 54 => '54',
					55 => '55', 56 => '56', 57 => '57', 58 => '58', 59 => '59',
				],
				1,
				0,
				60
			],
		];
	}

	/**
	 *
	 * @test
	 * @dataProvider provider_seconds
	 * @covers Date::seconds
	 */
	public function test_seconds($expected, $step = 1, $start = 0, $end = 60)
	{
		$this->assertSame(
			$expected,
			Date::seconds($step, $start, $end)
		);
	}

	/**
	 * Provides test data for test_minutes
	 *
	 * @return array Test data
	 */
	public function provider_minutes()
	{
		return [
			[
				[
					0 => '00', 5 => '05', 10 => '10',
					15 => '15', 20 => '20', 25 => '25',
					30 => '30', 35 => '35', 40 => '40',
					45 => '45', 50 => '50', 55 => '55',
				],
				5,
			],
		];
	}

	/**
	 *
	 * @test
	 * @dataProvider provider_minutes
	 */
	public function test_minutes($expected, $step)
	{
		$this->assertSame(
			$expected,
			Date::minutes($step)
		);
	}

	/**
	 * This tests that the minutes helper defaults to using a $step of 5
	 * and thus returns an array of 5 minute itervals
	 *
	 * @test
	 * @covers Date::minutes
	 */
	public function test_minutes_defaults_to_using_step_of5()
	{
		$minutes = [
			0 => '00', 5 => '05', 10 => '10',
			15 => '15', 20 => '20', 25 => '25',
			30 => '30', 35 => '35', 40 => '40',
			45 => '45', 50 => '50', 55 => '55',
		];

		$this->assertSame(
			$minutes,
			Date::minutes()
		);
	}

	/**
	 * Provids for test_unix2dos
	 *
	 * @return array Test Data
	 */
	public function provider_unix2dos()
	{
		return [
			[
				1024341746,
				1281786936
			],
			[
				2162688,
				315554400
			]
		];
	}

	/**
	 * Test Date::unix2dos()
	 *
	 * You should always pass a timestamp as otherwise the current
	 * date/time would be used and that's oviously variable
	 *
	 * Geert seems to be the only person who knows how unix2dos() works
	 * so we just throw in some random values and see what happens
	 *
	 * @test
	 * @dataProvider provider_unix2dos
	 * @covers Date::unix2dos
	 * @param integer $expected  Expected output
	 * @param integer $timestamp Input timestamp
	 */
	public function test_unix2dos($expected, $timestamp)
	{
		$this->assertSame($expected, Date::unix2dos($timestamp));
	}

	/**
	 * Provides test data for test_dos2unix
	 *
	 * @return array Test data
	 */
	public function provider_dos2unix()
	{
		return [
			[
				1281786936,
				1024341746,
			],
			[
				315554400,
				2162688,
			],
		];
	}

	/**
	 * Tests Date::dos2unix
	 *
	 * @test
	 * @dataProvider provider_dos2unix
	 * @param integer $expected  Expected output
	 * @param integer $timestamp Input timestamp
	 */
	public function test_dos2unix($expected, $timestamp)
	{
		$this->assertEquals($expected, Date::dos2unix($timestamp));
	}
}
