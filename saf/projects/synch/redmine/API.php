<?php
namespace SAF\Projects\Synch\Redmine;

use SAF\Framework\Plugin\Configurable;
use SAF\Framework\Session;

/**
 * Redmine synchronization API
 */
class API implements Configurable
{

	//------------------------------------------------ Redmine API configuration array keys constants
	const FILTERS = 'filters';
	const LINK    = 'redmine';

	//-------------------------------------------------------------------------------------- $filters
	/**
	 * @var string[] key is the class name, value is the filter
	 */
	public $filters;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration array
	 */
	public function __construct($configuration)
	{
		foreach ($configuration as $property_name => $value) {
			$this->$property_name = $value;
		}
	}

	//---------------------------------------------------------------------------------------- filter
	/**
	 * Gets class name filter
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function filter($class_name)
	{
		/** @var $api self */
		$api = Session::current()->plugins->get(__CLASS__);
		return isset($api->filters[$class_name]) ? $api->filters[$class_name] : null;
	}

}
