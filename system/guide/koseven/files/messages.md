# Messages

Koseven has a robust key based lookup system so you can define system messages.

## Getting a message

Use the Koseven::message() method to get a message key:

	Koseven::message('forms', 'foobar');

This will look in the `messages/forms.php` file for the `foobar` key:

	<?php
	
	return array(
		'foobar' => 'Hello, world!',
	);

You can also look in subfolders and sub-keys:

	Koseven::message('forms/contact', 'foobar.bar');

This will look in the `messages/forms/contact.php` for the `[foobar][bar]` key:

	<?php
	
	return array(
		'foobar' => array(
			'bar' => 'Hello, world!',
		),
	);

## Notes

 * Don't use __() in your messages files, as these files can be cached and will not work properly.
 * Messages are merged by the cascading file system, not overwritten like classes and views.
