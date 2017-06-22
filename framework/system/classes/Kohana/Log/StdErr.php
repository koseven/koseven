<?php
/**
 * STDERR log writer. Writes out messages to STDERR.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_Log_StdErr extends Log_Writer {
	/**
	 * Writes each of the messages to STDERR.
	 *
	 *     $writer->write($messages);
	 *
	 * @param   array   $messages
	 * @return  void
	 */
	public function write(array $messages)
	{
		foreach ($messages as $message)
		{
			// Writes out each message
			fwrite(STDERR, $this->format_message($message).PHP_EOL);
		}
	}

}
