<?php
/**
 * Default auth role
 *
 * @package    KO7/Auth
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class Model_Auth_Role extends ORM {

	// Relationships
	protected array $_has_many = [
		'users' => ['model' => 'User','through' => 'roles_users'],
	];

	public function rules()
	{
		return [
			'name' => [
				['not_empty'],
				['min_length', [':value', 4]],
				['max_length', [':value', 32]],
			],
			'description' => [
				['max_length', [':value', 255]],
			]
		];
	}

} // End Auth Role Model
