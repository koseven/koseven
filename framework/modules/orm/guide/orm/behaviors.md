# Behaviors

Behaviors allow to set and/or modify model field data by attaching the behavior to the model. A behavior can be implemented once an attached to different models, the number of behaviors that can be attached is unlimited.

A good example is a slug for a page, this is based on a title and needs to be updated during the commit. This slug can then be used in the URL to retrieve the model.

	public function behavior()
	{
		return array(
			// Guid generation
			// Generate a guid and store in in the guid column
			'Guid' => array('column' => 'guid'),
			
			// Slug generation
			// Generate a slug from the title column and store in in the slug column
			'Slug' => array('source'=>'title', column' => 'slug'),
		);
	}

## Behaviors types
### Guid

This behavior will generate a GUID and store it in the specified column.

If the `column` key is ommited the '`guid`' column will be used to store the generated GUID.

	public function behavior()
	{
		return array(
			...
			'Guid' => array(
				'column'      => '[column name]'
			),
			...
		);
	}

### Slug

This behavior will generate a slug from the `source` field and store it in the specified column. 

If the `source` key is ommited the '`name`' column will be used to create the slug. If the `column` key is ommited the '`slug`' column will be used to create the slug.

	public function behavior()
	{
		return array(
			...
			'Slug' => array(
				'source'      => '[column name]',
				'column'      => '[column name]'
			),
			...
		);
	}

### LocalBehavior

Allows the creation of a local behavior that is model specific.
The function takes up to two arguments; the first argument contains the type of operation on the model and can be either '`construct`' during construction of the model class, '`update`' when the model is updated or '`create`' when the model is created.

	public function behavior()
	{
		return array(
			...
			// Callback static method
			TRUE => array('MyClass::static_method'),
			// Callback method
			TRUE => array($this, 'custom_behavior')),
			// Function as the callback (PHP 5.3+)
			TRUE => function($action) {
				// Do something to $value and return it.
				return some_function($action);
			},

			...
		);
	}

## Writing new behaviors

It is possible to create custom behaviors that can be reused in different models.

Create a subclass of the `ORM_Behavior` class and implement the `on_construct`, `on_update` and `on_create` functions.

	class ORM_Behavior_MyBehavior extends ORM_Behavior {

		/**
		 * Constructs a new model and loads a record if given
		 *
		 * @param   ORM   $model The model
		 * @param   mixed $id    Parameter for find or object to load
		 */
		public function on_construct($model, $id)
		{
			// return FALSE if the model is loaded from this function.
			return TRUE;
		}

		/**
		 * The model is updated
		 *
		 * @param   ORM   $model The model
		 */
		public function on_update($model)
		{
			// Modify or update the appropriate fields
		}

		/**
		 * A new model is created, add a guid value
		 *
		 * @param   ORM   $model The model
		 */
		public function on_create($model)
		{
			// Modify or update the appropriate fields
		}
	}
