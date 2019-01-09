<?php

/**
 * @package    KO7/Minion
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class KO7_Minion_Exception extends KO7_Exception {
	/**
	 * Inline exception handler, displays the error message, source of the
	 * exception, and the stack trace of the error.
	 *
	 * Should this display a stack trace? It's useful.
	 *
	 * @uses    KO7_Exception::text
	 * @param   Exception   $e
	 * @return  boolean
	 */
	public static function handler($e)
	{
		try
		{
			// Log the exception
			KO7_Exception::log($e);

			if ($e instanceof Minion_Exception)
			{
				echo $e->format_for_cli();
			}
			else
			{
				echo KO7_Exception::text($e);
			}

			$exit_code = $e->getCode();

			// Never exit "0" after an exception.
			if ($exit_code == 0)
			{
				$exit_code = 1;
			}

			exit($exit_code);
		}
		catch (Exception $e)
		{
			// Clean the output buffer if one exists
			ob_get_level() and ob_clean();

			// Display the exception text
			echo KO7_Exception::text($e), "\n";

			// Exit with an error status
			exit(1);
		}
	}

	public function format_for_cli()
	{
		return KO7_Exception::text($this);
	}

}