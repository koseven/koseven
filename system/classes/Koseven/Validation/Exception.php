<?php
/**
 * @package    Koseven
 * @category   Exceptions
 * @copyright  (c) 2007-2014  Kohana Team
 * @copyright  (c) 2014-2018  Koseven Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Koseven_Validation_Exception extends Koseven_Exception {

	/**
	 * @var  object  Validation instance
	 */
	public $array;

	/**
	 * @param  Validation   $array      Validation object
	 * @param  string       $message    error message
	 * @param  array        $values     translation variables
	 * @param  int          $code       the exception code
	 */
	public function __construct(Validation $array, $message = 'Failed to validate array', array $values = NULL, $code = 0, Exception $previous = NULL)
	{
		$this->array = $array;

		parent::__construct($message, $values, $code, $previous);
	}

}
