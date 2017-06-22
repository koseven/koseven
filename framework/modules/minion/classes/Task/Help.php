<?php
/**
 * Help task to display general instructons and list all tasks
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Task_Help extends Minion_Task {

	/**
	 * Generates a help list for all tasks
	 *
	 * @return null
	 */
	protected function _execute(array $params)
	{
		$tasks = $this->_compile_task_list(Kohana::list_files('classes/Task'));

		$view = new View('minion/help/list');

		$view->tasks = $tasks;

		echo $view;
	}

}
