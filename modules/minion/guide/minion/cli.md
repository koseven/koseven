# CLI

The module contains helper functions to read and write to the CLI.

# User input

## Read input

It is possible to read data from the CLI, the `read` method will wait for the user input and return the string:

	// Prompt 'how many items:'
	$result = Minion_CLI::read('How many items');


## Limit the number of choices

To limit the amount of possible choices for the user input, additional options can be added to the `read` method.
If an invalid option is entered the `$invalid_option_msg` will be displayed (default value *'This is not a valid option. Please try again.'*).

	<?php

	class Minion_Task_Demo extends Minion_Task
	{
		// Override the default message
		public static $invalid_option_msg = 'Oops this is not a valid option!';

		protected function _execute(array $params)
		{
			// Wait for <enter>
			$result = Minion_CLI::read('Are you sure', ['y', 'n']);
		}
	}


## Wait for input

To wait for <enter> the `wait` method can be used, the default message that will be *Press any key to continue...*, this can be replaced by overriding the `$wait_msg` variable.

	<?php

	class Minion_Task_Demo extends Minion_Task
	{
		// Override the default message
		public static $wait_msg = 'Press <enter> to ...';

		protected function _execute(array $params)
		{
			// Wait for <enter>
			Minion_CLI::wait();
		}
	}

To wait an amount of seconds before the user can continue the number of seconds can be added. To display a counter the second argument should be set to `TRUE`.

	<?php

	class Minion_Task_Demo extends Minion_Task
	{
		// Wait 10 seconds
		Minion_CLI::wait(10, TRUE);
	}

# Console output

## Simple output

To output a simple text the `write` method is available.

	Minion_CLI::write('Done sending emails');


Under Linux there is to option to colorize the output.
The first argument is the foreground color; valid colors are `black`, `dark_gray`, `blue`, `light_blue`, `green`, `light_green`, `cyan`, `light_cyan`, `red`, `light_red`, `purple`, `light_purple`, `brown`, `yellow`, `light_gray` and `white`.
The second argument is the background color; valid background colors are `black`, `red`, `green`, `yellow`, `blue`, `magenta`, `cyan` and `light_gray`.

	Minion_CLI::write(Minion_CLI::color('Something went wrong', 'white', 'red'));
	

## Replacing text

It is possible to write a text to the console and overwriting it later.

	Minion_CLI::write_replace('Updating database');
	// ... 
	Minion_CLI::write_replace('Update finished', TRUE);

[!!] Under Windows it is not possible to clear the line, this means that all texts should have the same length and padded with spaces to clear old messages.
