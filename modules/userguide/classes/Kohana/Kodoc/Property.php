<?php
/**
 * Class property documentation generator.
 *
 * @package    Kohana/Userguide
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) Kohana Team
 * @license    https://koseven.ga/LICENSE.md
 */
class Kohana_Kodoc_Property extends Kodoc {

	/**
	 * @var  object  ReflectionProperty
	 */
	public $property;

	/**
	 * @var  string   modifiers: public, private, static, etc
	 */
	public $modifiers = 'public';

	/**
	 * @var  string  variable type, retrieved from the comment
	 */
	public $type;

	/**
	 * @var  string  value of the property
	 */
	public $value;

	/**
	 * @var  string  default value of the property
	 */
	public $default;

	public function __construct($class, $property, $default = NULL)
	{
		$property = new ReflectionProperty($class, $property);

		list($description, $tags) = Kodoc::parse($property->getDocComment());

		$this->description = $description;

		if ($modifiers = $property->getModifiers())
		{
			$this->modifiers = '<small>'.implode(' ', Reflection::getModifierNames($modifiers)).'</small> ';
		}

		if (isset($tags['var']))
		{
			if (preg_match('/^(\S*)(?:\s*(.+?))?$/s', $tags['var'][0], $matches))
			{
				$this->type = $matches[1];

				if (isset($matches[2]))
				{
					$this->description = Kodoc_Markdown::markdown($matches[2]);
				}
			}
		}

		$this->property = $property;

		// Show the value of static properties
		if ($property->isStatic())
		{
			// Force the property to be accessible
			$property->setAccessible(TRUE);

			// Don't debug the entire object, just say what kind of object it is
			if (is_object($property->getValue($class)))
			{
				$this->value = '<pre>object '.get_class($property->getValue($class)).'()</pre>';
			}
			else
			{
				$this->value = Debug::vars($property->getValue($class));
			}
		}

		// Store the defult property
		$this->default = Debug::vars($default);;
	}

} // End Kodoc_Property
