<?php
/**
 * UTF8::strcasecmp
 *
 * @package    KO7
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strcasecmp($str1, $str2)
{
	if (UTF8::is_ascii($str1) AND UTF8::is_ascii($str2))
		return strcasecmp($str1, $str2);

	$str1 = UTF8::strtolower($str1);
	$str2 = UTF8::strtolower($str2);
	// 8.2.0	This function now returns -1 or 1, where it previously returned a negative or positive number.
	return strcmp($str1, $str2);
}
