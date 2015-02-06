<?php
namespace SAF\Projects\Issues;

/**
 * Issue priority
 *
 * @business
 * @set Issues_Priorities
 */
class Priority
{

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @mandatory
	 * @var string
	 */
	public $name;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

}
