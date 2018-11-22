<?php
/**
 * UTF8::strrev
 *
 * @package    Koseven
 * @copyright  (c) 2005 Harry Fuecks
 * @copyright  (c) 2007-2014  Kohana Team
 * @copyright  (c) 2014-2018  Koseven Team
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strrev($str)
{
	if (UTF8::is_ascii($str))
		return strrev($str);

	preg_match_all('/./us', $str, $matches);
	return implode('', array_reverse($matches[0]));
}
