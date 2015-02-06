<?php
namespace SAF\Projects\Issues;

use SAF\Framework\Mapper\Component;
use SAF\Framework\Tools\Date_Time;
use SAF\Framework\User;
use SAF\Projects\Journals\Journal_Entry;
use SAF\Projects\Projects\Project;

/**
 * A project issue
 *
 * @business
 * @representative project.name, status.name, priority.name, subject, assigned_to.login
 * @set Issues
 */
class Issue
{
	use Component;

	//--------------------------------------------------------------------------------------- $number
	/**
	 * @mandatory
	 * @var integer
	 */
	public $number;

	//-------------------------------------------------------------------------------------- $project
	/**
	 * @composite
	 * @link Object
	 * @mandatory
	 * @var Project
	 */
	public $project;

	//-------------------------------------------------------------------------------------- $subject
	/**
	 * @mandatory
	 * @var string
	 */
	public $subject;

	//--------------------------------------------------------------------------------------- $author
	/**
	 * @default User::current
	 * @link Object
	 * @var User
	 */
	public $author;

	//---------------------------------------------------------------------------------- $assigned_to
	/**
	 * @link Object
	 * @var User
	 */
	public $assigned_to;

	//------------------------------------------------------------------------------------- $priority
	/**
	 * @link Object
	 * @mandatory
	 * @var Priority
	 */
	public $priority;

	//--------------------------------------------------------------------------------------- $status
	/**
	 * @link Object
	 * @mandatory
	 * @var Status
	 */
	public $status;

	//------------------------------------------------------------------------------------- $due_date
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $due_date;

	//------------------------------------------------------------------------------------- $journals
	/**
	 * @link Collection
	 * @var Journal_Entry[]
	 */
	public $journals;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->subject);
	}

}
