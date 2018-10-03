<?php
/**
 * Security helper class.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_Security {

	/**
	 * @var  string  key name used for token storage
	 */
	public static $token_name = 'security_token';

	/**
	 * Generate and store a unique token which can be used to help prevent
	 * [CSRF](http://wikipedia.org/wiki/Cross_Site_Request_Forgery) attacks.
	 *
	 *     $token = Security::token();
	 *
	 * You can insert this token into your forms as a hidden field:
	 *
	 *     echo Form::hidden('csrf', Security::token());
	 *
	 * And then check it when using [Validation]:
	 *
	 *     $array->rules('csrf', array(
	 *         array('not_empty'),
	 *         array('Security::check'),
	 *     ));
	 *
	 * This provides a basic, but effective, method of preventing CSRF attacks.
	 *
	 * @param   boolean $new    force a new token to be generated?
	 * @return  string
	 * @uses    Session::instance
	 */
	public static function token($new = FALSE)
	{
		$session = Session::instance();

		// Get the current token
		$token = $session->get(Security::$token_name);

		if ($new === TRUE OR ! $token)
		{
			$token = Security::_generate_token();

			// Store the new token
			$session->set(Security::$token_name, $token);
		}

		return $token;
	}

	/**
	 * Generate a unique token.
	 *
	 * @return  string
	 */
	protected static function _generate_token()
	{
		if (function_exists('random_bytes'))
		{
			try
			{
				return bin2hex(random_bytes(24));
			}
			catch (Exception $e)
			{
				// Random bytes function is available but no sources of randomness are available
				// so rather than allowing the exception to be thrown - fall back to other methods.
				// @see http://php.net/manual/en/function.random-bytes.php
			}
		}

		if (function_exists('openssl_random_pseudo_bytes'))
		{
			// Generate a random pseudo bytes token if openssl_random_pseudo_bytes is available
			// This is more secure than uniqid, because uniqid relies on microtime, which is predictable
			return base64_encode(openssl_random_pseudo_bytes(32));
		}
		else
		{
			// Otherwise, fall back to a hashed uniqid
			return sha1(uniqid(NULL, TRUE));
		}
	}

	/**
	 * Check that the given token matches the currently stored security token.
	 *
	 *     if (Security::check($token))
	 *     {
	 *         // Pass
	 *     }
	 *
	 * @param   string  $token  token to check
	 * @return  boolean
	 * @uses    Security::token
	 */
	public static function check($token)
	{
		return Security::slow_equals(Security::token(), $token);
	}

	/**
	 * Compare two hashes in a time-invariant manner.
	 * Prevents cryptographic side-channel attacks (timing attacks, specifically)
	 *
	 * @param string $a cryptographic hash
	 * @param string $b cryptographic hash
	 * @return boolean
	 */
	public static function slow_equals($a, $b)
	{
		$diff = strlen($a) ^ strlen($b);
		for($i = 0; $i < strlen($a) AND $i < strlen($b); $i++)
		{
			$diff |= ord($a[$i]) ^ ord($b[$i]);
		}
		return $diff === 0;
	}

	/**
	 * Deprecated for security reasons.
	 * See https://github.com/kohana/kohana/issues/107
	 *
	 * Remove image tags from a string.
	 *
	 *     $str = Security::strip_image_tags($str);
	 *
	 * @deprecated since version 3.3.6
	 * @param   string  $str    string to sanitize
	 * @return  string
	 */
	public static function strip_image_tags($str)
	{
		return preg_replace('#<img\s.*?(?:src\s*=\s*["\']?([^"\'<>\s]*)["\']?[^>]*)?>#is', '$1', $str);
	}

	/**
	 * Encodes PHP tags in a string.
	 *
	 *     $str = Security::encode_php_tags($str);
	 *
	 * @param   string  $str    string to sanitize
	 * @return  string
	 */
	public static function encode_php_tags($str)
	{
		return str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], $str);
	}

}
