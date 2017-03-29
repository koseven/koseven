<?php

class Kohana_ORM_Behavior {

	/**
	 * Database query builder
	 * @var Database_Query_Builder_Select
	 */
	protected $_config;

	/**
	 * Creates and returns a new ORM behavior.
	 *
	 * @chainable
	 * @param   string  $type   Type name
	 * @param   mixed   $id     Parameter for find()
	 * @return  ORM
	 */
	public static function factory($behavior, $config = NULL)
	{
		if ( ! is_string($behavior) AND is_array($config))
		{
			if ( ! is_callable($config))
				throw new Kohana_Exception('Behavior cannot be created: function does not exists');

			// This is either a callback as an array or a lambda
			return new ORM_Behavior_LocalBehavior($config);
		}

		// Set class name
		$behavior_name = 'ORM_Behavior_'.ucfirst($behavior);

		return new $behavior_name($config);
	}

	protected function __construct($config)
	{
		$this->_config = $config;
	}

	public function on_construct($model, $id) { return TRUE; }
	public function on_create($model) { }
	public function on_update($model) { }
}
