<?php
namespace SAF\Projects\Journals;

use SAF\Framework\Mapper\Component;
use SAF\Framework\Tools\Date_Time;
use SAF\Framework\User;
use SAF\Projects\Issues\Issue;

/**
 * Issue journal entry
 *
 * @business
 * @set Journals
 */
class Journal_Entry
{
	use Component;

	//---------------------------------------------------------------------------------------- $entry
	/**
	 * @composite
	 * @link Object
	 * @var Issue
	 */
	public $issue;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @link Object
	 * @var User
	 */
	public $user;

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $date;

	//---------------------------------------------------------------------------------------- $notes
	/**
	 * @max_length 1000000
	 * @multiline
	 * @var string
	 */
	public $notes;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->issue->subject);
	}

}
