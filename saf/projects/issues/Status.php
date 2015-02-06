<?php
namespace SAF\Projects\Issues;

/**
 * Issue status
 *
 * @business
 * @set Issues_Statuses
 */
class Status
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
