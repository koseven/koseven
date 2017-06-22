<?php

class Kohana_ORM_Behavior_LocalBehavior extends ORM_Behavior {

	/**
	 * Callback to execute
	 * @var array
	 */
	protected $_callback;

	/**
	 * Constructs a behavior object
	 *
	 * @param   mixed $callback Callback to execute
	 */  
  protected function __construct($callback)
  {
    $this->_callback = $callback;
  }
  
	/**
	 * Constructs a new model and loads a record if given
	 *
   * @param   ORM   $model The model
	 * @param   mixed $id    Parameter for find or object to load
	 */
	public function on_construct($model, $id)
	{
		$params = array('construct', $id);
		$result = call_user_func_array($this->_callback, $params);
		
		if (is_bool($result))
			return $result;

		// Continue loading the record
		return TRUE;
	}

  /**
   * The model is updated
   */
  public function on_update($model)
  {
		$params = array('update');
		call_user_func_array($this->_callback, $params);
  }
  
  /**
   * A new model is created
   *
   * @param   ORM   $model The model
   */
  public function on_create($model)
  {
		$params = array('create');
		call_user_func_array($this->_callback, $params);
  }
}
