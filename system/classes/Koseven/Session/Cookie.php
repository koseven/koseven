<?php
/**
 * Cookie-based session class.
 *
 * @package    Koseven
 * @category   Session
 * @copyright  (c) 2007-2014  Kohana Team
 * @copyright  (c) 2014-2018  Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Koseven_Session_Cookie extends Session {

	/**
	 * @param   string  $id  session id
	 * @return  string
	 */
	protected function _read($id = NULL)
	{
		return Cookie::get($this->_name, NULL);
	}

	/**
	 * @return  null
	 */
	protected function _regenerate()
	{
		// Cookie sessions have no id
		return NULL;
	}

	/**
	 * @return  bool
	 */
	protected function _write()
	{
		return Cookie::set($this->_name, $this->__toString(), $this->_lifetime);
	}

	/**
	 * @return  bool
	 */
	protected function _restart()
	{
		return TRUE;
	}

	/**
	 * @return  bool
	 */
	protected function _destroy()
	{
		return Cookie::delete($this->_name);
	}

}
