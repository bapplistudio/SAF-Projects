<?php
namespace SAF\Projects\Projects;

/**
 * A project
 *
 * @business
 * @representative name
 */
class Project
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @mandatory
	 * @var string
	 */
	public $name;

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * @link Object
	 * @var Project
	 */
	public $parent;

	//------------------------------------------------------------------------------------- $children
	/**
	 * @link Collection
	 * @var Project[]
	 */
	public $children;

	//---------------------------------------------------------------------------------- $description
	/**
	 * @max_length 1000000
	 * @multiline
	 * @var string
	 */
	public $description;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

}
