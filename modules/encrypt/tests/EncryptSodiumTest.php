<?php
/**
 * Unit Tests and KAT Tests for Sodium class. (Introduced in PHP7: libsodium
 *
 * @group          ko7
 * @group          ko7.encrypt
 *
 * @package        KO7/Encrypt
 * @category       Test
 * even Team
 * @copyright  (c) Koseven Team
 * @license        https://koseven.dev/LICENSE
 */
class EncryptSodiumTest extends EncryptTestBase {

	/**
	 * Setup class (should be created within every test)
	 *
	 * @return void
	 * @throws KO7_Exception
	 */
	public function setUp(): void
	{
		if (!extension_loaded('sodium')) {
			$this->markTestSkipped('The Sodium extension is not available.');
		}

		if (!sodium_crypto_aead_aes256gcm_is_available()) {
			$this->markTestSkipped('Sodium AEAD AES 256 GCM is not available.');
		}

		$this->set_config([
			'type' => 'sodium',
			'key' => EncryptTestBase::KEY32,
			'cipher' => Encrypt_Engine_Sodium::AES_256_GCM
		]);

		parent::setUp();
	}

	/**
	 * Tests against known answers
	 *
	 * @dataProvider provider_kat
	 * @covers       KO7_Encrypt_Engine_Sodium::encrypt
	 *
	 * @param  array $vectors Known Answers
	 *
	 * @throws KO7_Exception
	 */
	public function test_kat(array $vectors)
	{
		// Init
		extract($vectors);

		$this->set_config([
			'type' => 'sodium',
			'key' => EncryptTestBase::KEY32,
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
	 * Tests decrypt function with invalid ciphertext
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
	 * Test with invalid Key Length and expect exception
	 *
	 * @throws KO7_Exception
	 */
	public function test_invalid_key_length()
	{
		$this->expectException(KO7_Exception::class);

		// Init
		$this->set_config([
			'type' => 'sodium',
			'key' => '1234',
			'cipher' => Encrypt_Engine_Sodium::AES_256_GCM
		]);

		// Expect Exception
		$encrypt = Encrypt::instance();
	}

	/**
	 * Test with invalid / not set key and expect exception
	 *
	 * @throws KO7_Exception
	 */
	public function test_invalid_key()
	{
		$this->expectException(KO7_Exception::class);

		// Init
		$this->set_config([
			'type' => 'sodium',
			'key' => NULL,
			'cipher' => Encrypt_Engine_Sodium::AES_256_GCM
		]);

		// Expect Exception
		$encrypt = Encrypt::instance();
	}

	/**
	 * Test with non existent Engine Name and expect Exception
	 *
	 * @throws KO7_Exception
	 */
	public function test_non_existent_engine()
	{
		$this->expectException(KO7_Exception::class);

		// Init
		$this->set_config([
			'type' => 'doesnotexist',
			'key' => EncryptTestBase::KEY32,
			'cipher' => Encrypt_Engine_Sodium::AES_256_GCM
		]);

		// Expect Exception
		$encrypt = Encrypt::instance();
	}

	/**
	 * Testing to string function
	 *
	 * @throws KO7_Exception
	 */
	public function test___toString()
	{
		$encrypt = Encrypt::instance();
		$this->assertSame("Encrypt_Engine_Sodium (default)", (string)$encrypt);
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
					'iv' => '000000000000',
					'ciphertext' => 'eyJpdiI6Ik1EQXdNREF3TURBd01EQXciLCJ2YWx1ZSI6Inp2MncyTmdQWW5TcFdBclV2Y2xsblVTVzFQMD0ifQ==',
					'plaintext' => 'test',
					'cipher' => Encrypt_Engine_Sodium::AES_256_GCM
				]
			],
			[
				[
					'iv' => '111111111111',
					'ciphertext' => 'eyJpdiI6Ik1URXhNVEV4TVRFeE1URXgiLCJ2YWx1ZSI6ImxSVXgwWDBNQklkSXRUOGw5cGIwVmtMSm96XC9GIn0=',
					'plaintext' => 'test2',
					'cipher' => NULL
				]
			],
			[
				[
					'iv' => '222222222222',
					'ciphertext' => 'eyJpdiI6Ik1qSXlNakl5TWpJeU1qSXkiLCJ2YWx1ZSI6Ik5OWGxETGNmZmlhKzlZd2xOSDFvSzZCd1wvRmlBIn0=',
					'plaintext' => 'test3',
					'cipher' => NULL
				]
			],
			[
				[
					'iv' => '33333333',
					'ciphertext' => 'eyJpdiI6Ik16TXpNek16TXpNPSIsInZhbHVlIjoibGZBYjZ3eGdISGZmQ2o2aWduSE1sWlg5UEQ4aSJ9',
					'plaintext' => 'test4',
					'cipher' => Encrypt_Engine_Sodium::CHACHA_POLY
				]
			],
			[
				[
					'iv' => '44444444',
					'ciphertext' => 'eyJpdiI6Ik5EUTBORFEwTkRRPSIsInZhbHVlIjoiZ2xaVXRMaFJEN29KK2RLU3pxdjA4RlpwdFVNcyJ9',
					'plaintext' => 'test5',
					'cipher' => Encrypt_Engine_Sodium::CHACHA_POLY
				]
			],
			[
				[
					'iv' => '55555555',
					'ciphertext' => 'eyJpdiI6Ik5UVTFOVFUxTlRVPSIsInZhbHVlIjoiYTJNbjNXWFdRbVNhbnZuXC9XeTFQeHM3ZHJvXC9qIn0=',
					'plaintext' => 'test6',
					'cipher' => Encrypt_Engine_Sodium::CHACHA_POLY
				]
			],
			[
				[
					'iv' => '666666666666',
					'ciphertext' => 'eyJpdiI6Ik5qWTJOalkyTmpZMk5qWTIiLCJ2YWx1ZSI6IldNcDVFdXVZTGZuM3F1SFlBaE5jZlVGTkxcL1IxIn0=',
					'plaintext' => 'test7',
					'cipher' => Encrypt_Engine_Sodium::CHACHA_POLY_IETF
				]
			],
			[
				[
					'iv' => '777777777777',
					'ciphertext' => 'eyJpdiI6Ik56YzNOemMzTnpjM056YzMiLCJ2YWx1ZSI6ImFxd0ZMM1ZBXC8rUjI0Vzk4aHRTbkhOS29ibEE5In0=',
					'plaintext' => 'test8',
					'cipher' => Encrypt_Engine_Sodium::CHACHA_POLY_IETF
				]
			],
			[
				[
					'iv' => '888888888888',
					'ciphertext' => 'eyJpdiI6Ik9EZzRPRGc0T0RnNE9EZzQiLCJ2YWx1ZSI6InZGR0Q0Nnhac0NmMWlnQWd0dnM5cXpVZjA2eFoifQ==',
					'plaintext' => 'test9',
					'cipher' => Encrypt_Engine_Sodium::CHACHA_POLY_IETF
				]
			],
			[
				[
					'iv' => '999999999999999999999999',
					'ciphertext' => 'eyJpdiI6Ik9UazVPVGs1T1RrNU9UazVPVGs1T1RrNU9UazVPVGs1IiwidmFsdWUiOiJkVXRrZ3lzUG9SN3FMblhveU5mdTlPeG5uT3puOVE9PSJ9',
					'plaintext' => 'test10',
					'cipher' => Encrypt_Engine_Sodium::XCHACHA_POLY_IETF
				]
			],
			[
				[
					'iv' => '000000000000000000000000',
					'ciphertext' => 'eyJpdiI6Ik1EQXdNREF3TURBd01EQXdNREF3TURBd01EQXdNREF3IiwidmFsdWUiOiJmUyt3dzBQdENkUDlRZ1ZxeW1yN2Y4dkg0ZFlsMlE9PSJ9',
					'plaintext' => 'test11',
					'cipher' => Encrypt_Engine_Sodium::XCHACHA_POLY_IETF
				]
			],
			[
				[
					'iv' => '111111111111111111111111',
					'ciphertext' => 'eyJpdiI6Ik1URXhNVEV4TVRFeE1URXhNVEV4TVRFeE1URXhNVEV4IiwidmFsdWUiOiJPMU9oalE5UmVVdXRqM0pXbEhIWVp2aWN2SmtjTEE9PSJ9',
					'plaintext' => 'test12',
					'cipher' => Encrypt_Engine_Sodium::XCHACHA_POLY_IETF
				]
			]
		];
	}
}
