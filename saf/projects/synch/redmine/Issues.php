<?php
namespace SAF\Projects\Synch\Redmine;

use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Dao\Mysql\Link;
use SAF\Projects\Issues\Issue;
use SAF\Projects\Issues\Priority;
use SAF\Projects\Issues\Status;
use SAF\Projects\Projects\Project;

/**
 * Synchronise issues with Redmine database
 */
class Issues
{

	//---------------------------------------------------------------------------------- IMPORT_QUERY
	const IMPORT_QUERY = "
		SELECT
			issues.project_id AS id_project,
			issues.id AS number,
			issues.priority_id AS id_priority,
			enumerations.name AS priority_name,
			issues.status_id AS id_status,
			issue_statuses.name AS status_name,
			issues.subject
		FROM issues
		INNER JOIN projects ON projects.id = issues.project_id
		LEFT JOIN enumerations ON enumerations.id = issues.priority_id
		LEFT JOIN issue_statuses ON issue_statuses.id = issues.status_id
		WHERE projects.status = 1 {filter}
		ORDER BY number
	";

	//----------------------------------------------------------------------------------- $priorities
	/**
	 * The priorities
	 *
	 * @var Priority[] key is the priority's redmine_id
	 */
	private $priorities;

	//------------------------------------------------------------------------------------- $statuses
	/**
	 * The statuses
	 *
	 * @var Status[] key is the status' redmine_id
	 */
	private $statuses;

	//------------------------------------------------------------------------------------- $projects
	/**
	 * The projects that have already exist into SAF Projects
	 *
	 * @var Project[] key is the project's redmine_id
	 */
	private $projects;

	//---------------------------------------------------------------------------------- initProjects
	/**
	 * Initializes project list
	 */
	private function initProjects()
	{
		$this->priorities = Dao::readAll(Priority::class, [Dao::key('redmine_id')]);
		$this->projects   = Dao::readAll(Project::class,  [Dao::key('redmine_id')]);
		$this->statuses   = Dao::readAll(Status::class,   [Dao::key('redmine_id')]);
	}

	//----------------------------------------------------------------------------------- synchronize
	/**
	 * Synchronize issues from Redmine to SAF Issues
	 *
	 * @return string The report if there are errors, or 'OK' if everything went right
	 */
	public function synchronize()
	{
		$this->initProjects();
		/** @var $dao Link */
		$dao = Dao::get(API::LINK);
		$filter1 = API::filter(get_class($this));
		$filter2 = API::filter(Projects::class);
		$filter = ($filter1 && $filter2) ? ($filter1 . ') AND (' . $filter2) : ($filter1 . $filter2);
		$query = $filter
			? str_replace('{filter}', 'AND (' . $filter . ')', static::IMPORT_QUERY)
			: str_replace('{filter}', '', static::IMPORT_QUERY);
		/** @var $redmine_issues Issue[] */
		$redmine_issues = $dao->query($query, Issue::class);
		foreach ($redmine_issues as $redmine_issue) {
			$this->translateIdProject($redmine_issue);
			$this->write($redmine_issue);
		}
		return 'NOPE';
	}

	//---------------------------------------------------------------------------- translateIdProject
	/**
	 * Sets the project matching id_project
	 *
	 * @param $redmine_issue Issue
	 */
	private function translateIdProject(Issue $redmine_issue)
	{
		if (isset($redmine_issue->id_project) && isset($this->projects[$redmine_issue->id_project])) {
			$redmine_issue->project = $this->projects[$redmine_issue->id_project];
			unset($redmine_issue->id_project);
		}
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Creates or Update an issue that is ready to be written
	 *
	 * @param $issue Issue
	 */
	private function write(Issue $issue)
	{
		$this->writePriority($issue);
		$this->writeStatus($issue);
		// update existing issue
		if ($existing_issue = Dao::searchOne(['number' => $issue->number], Issue::class)) {
			/** @var $existing_issue Issue */
			Dao::replace($issue, $existing_issue);
		}
		// create new issue
		else {
			Dao::write($issue);
		}
	}

	//--------------------------------------------------------------------------------- writePriority
	/**
	 * Creates or Update a priority
	 *
	 * @param $issue Issue
	 */
	private function writePriority(Issue $issue)
	{
		if (!empty($issue->id_priority)) {
			if (isset($this->priorities[$issue->id_priority])) {
				$issue->priority = $this->priorities[$issue->id_priority];
			}
			elseif (!empty($issue->priority_name)) {
				/** @var $priority Priority|Has_Redmine_Id */
				$priority = Builder::create(Priority::class);
				$priority->name       = $issue->priority_name;
				$priority->redmine_id = $issue->id_priority;
				Dao::write($priority);
				$this->priorities[$priority->redmine_id] = $priority;
				$issue->priority = $priority;
			}
		}
		unset($issue->id_priority);
		unset($issue->priority_name);
	}

	//----------------------------------------------------------------------------------- writeStatus
	/**
	 * Creates or Update a status
	 *
	 * @param $issue Issue
	 */
	private function writeStatus(Issue $issue)
	{
		if (!empty($issue->id_status)) {
			if (isset($this->statuses[$issue->id_status])) {
				$issue->status = $this->statuses[$issue->id_status];
			}
			elseif (!empty($issue->status_name)) {
				/** @var $status Status|Has_Redmine_Id */
				$status = Builder::create(Status::class);
				$status->name       = $issue->status_name;
				$status->redmine_id = $issue->id_status;
				Dao::write($status);
				$this->statuses[$status->redmine_id] = $status;
				$issue->status = $status;
			}
		}
		unset($issue->id_status);
		unset($issue->status_name);
	}

}
