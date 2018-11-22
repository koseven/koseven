<?php
/**
 * UTF8::ltrim
 *
 * @package    Koseven
 * @copyright  (c) 2005 Harry Fuecks
 * @copyright  (c) 2007-2014  Kohana Team
 * @copyright  (c) 2014-2018  Koseven Team
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _ltrim($str, $charlist = NULL)
{
	if ($charlist === NULL)
		return ltrim($str);

	if (UTF8::is_ascii($charlist))
		return ltrim($str, $charlist);

	$charlist = preg_replace('#[-\[\]:\\\\^/]#', '\\\\$0', $charlist);

	return preg_replace('/^['.$charlist.']+/u', '', $str);
}
