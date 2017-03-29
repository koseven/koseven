<?php
/**
 * Invalid Task Exception
 *
 * @package    Kohana
 * @category   Minion
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_Minion_Exception_InvalidTask extends Minion_Exception {

	public function format_for_cli()
	{
		return 'ERROR: '. $this->getMessage().PHP_EOL;
	}

}
