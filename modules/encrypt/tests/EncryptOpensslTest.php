<?php
/**
 * Unit Tests and KAT Tests for OpenSSL - which is considered as default encryption driver
 *
 * @group      ko7
 * @group      ko7.encrypt
 *
 * @package    KO7/Encrypt
 * @category   Test
 *
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2016-2018 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class EncryptOpensslTest extends EncryptTestBase {

	/**
	 * Setup class (should be created within every test)
	 *
	 * @return void
	 * @throws KO7_Exception
	 */
	public function setUp(): void
	{
		if (!extension_loaded('openssl')) {
			$this->markTestSkipped('The OpenSSL extension is not available.');
		}

		$this->set_config([
			'type' => 'openssl',
			'key' => EncryptTestBase::KEY32,
			'cipher' => Encrypt_Engine_Openssl::AES_256_CBC
		]);

		parent::setUp();
	}

	/**
	 * Testing against KAT Vectors
	 *
	 * @dataProvider provider_kat
	 *
	 * @param array $vectors
	 *
	 * @return void
	 * @throws KO7_Exception
	 */
	public function test_kat(array $vectors)
	{
		// Init
		extract($vectors);

		$this->set_config([
			'type' => 'openssl',
			'key' => $key,
			'cipher' => $cipher
		]);

		// Test encryption with known answers
		$encrypt = Encrypt::instance();
		$encrypted = $encrypt->encode($plaintext, $iv);

		$this->assertEquals($ciphertext, $encrypted);
		$decrypted = $encrypt->decode($encrypted);
		$this->assertEquals($decrypted, $plaintext);
	}

	/**
	 * Test decryption with invalid message
	 *
	 * @throws KO7_Exception
	 */
	public function test_decrypt_invalid()
	{
		$encrypt = Encrypt::instance();
		$this->assertNull($encrypt->decode(base64_encode(json_encode([
            'iv' => 'invalid',
            'value' => 'invalid'
        ]))));
	}

	/**
	 * Data source for kat
	 *
	 * @return array
	 */
	public function provider_kat(): array
	{
		return [
			[
				[
					'iv' => '0000000000000000',
					'ciphertext' => 'eyJpdiI6Ik1EQXdNREF3TURBd01EQXdNREF3TUE9PSIsInZhbHVlIjoiTCtBemVKNXdKQ2FVVFNJNlwvdjdcL0VRPT0iLCJtYWMiOiJjNTQ5MWJiMWI5OTY2NWY2ZDNiYWZkMTllNjlkYzViZDFmZjU2NmI1ZGRmZWNlZjJlMWMxZDg3ODUxOTUzYmYzIn0=',
					'plaintext' => 'test',
					'cipher' => Encrypt_Engine_Openssl::AES_256_CBC,
					'key' => EncryptTestBase::KEY32
				]
			],
			[
				[
					'iv' => '1111111111111111',
					'ciphertext' => 'eyJpdiI6Ik1URXhNVEV4TVRFeE1URXhNVEV4TVE9PSIsInZhbHVlIjoibUQzdFVadld5OG1mY2F1XC9hcTlaMnc9PSIsIm1hYyI6IjIzYzllM2NmZTdkMzAzYWUzNzE1OWNmZGQ4ZWM3OTgyMzZiOTk0NjI4YjBkMTIxYTdlMDQ5MTI2ODk1OTEwNGEifQ==',
					'plaintext' => 'test2',
					'cipher' => NULL,
					'key' => EncryptTestBase::KEY32
				]
			],
			[
				[
					'iv' => '2222222222222222',
					'ciphertext' => 'eyJpdiI6Ik1qSXlNakl5TWpJeU1qSXlNakl5TWc9PSIsInZhbHVlIjoicmF6WE1Va0dKYkNJUCs5YlV4RzRZUT09IiwibWFjIjoiNDNhNTE1YWVhODA3OTMyMGZlMTBiZGZhNzA2NDRlOGQ0YmQyNjE1ZDFmMTVmYmFiMDk2ZDIyYzI0ZTVkN2FmNCJ9',
					'plaintext' => 'test3',
					'cipher' => Encrypt_Engine_Openssl::AES_128_CBC,
					'key' => EncryptTestBase::KEY16
				]
			],
			[
				[
					'iv' => '3333333333333333',
					'ciphertext' => 'eyJpdiI6Ik16TXpNek16TXpNek16TXpNek16TXc9PSIsInZhbHVlIjoiUkRxemZGNnB5Z2JyTGZBd3NCS1N2QT09IiwibWFjIjoiZDJiMzI1NDY1YjU4YjVjYjA5ZWUyOGE2ZGY1NDgxZjcwNjc3ODg1YTZlNmJmOWY1NjFjYWYxOTZlNGNkY2QxMSJ9',
					'plaintext' => 'test4',
					'cipher' => Encrypt_Engine_Openssl::AES_128_CBC,
					'key' => EncryptTestBase::KEY16
				]
			]
		];
	}
}
