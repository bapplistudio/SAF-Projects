<?php
namespace SAF\Projects\Synch\Redmine;

use SAF\Framework\Dao;
use SAF\Framework\Dao\Sql\Link;
use SAF\Projects\Projects\Project;

/**
 * Synchronise projects with Redmine database
 */
class Projects
{

	//---------------------------------------------------------------------------------- IMPORT_QUERY
	const IMPORT_QUERY = "
		SELECT
			id AS redmine_id,
			name,
			description,
			parent_id AS id_parent
		FROM projects
		WHERE status = 1 {filter}
		ORDER BY name
	";

	//-------------------------------------------------------------------------------- $next_projects
	/**
	 * All projects which some data have not been solved yet (ie id_parent) are stored here for
	 * a second pass.
	 *
	 * @var array
	 */
	private $next_projects;

	//--------------------------------------------------------------------------------- $saf_projects
	/**
	 * All projects that already exist into SAF Project default data list
	 *
	 * @var Project[]|Has_Redmine_Id[]
	 */
	private $saf_projects;

	//--------------------------------------------------------------------------- prepareNextProjects
	/**
	 * Prepare next projects list :
	 * For each next redmine project, get properties that have been removed back.
	 * ie id_parent has been removed as it was not solved, so we get it back to try to solve it on
	 * the next pass.
	 *
	 * @return Project[]|Has_Redmine_Id[]
	 */
	private function prepareNextProjects()
	{
		$Has_Redmine_Ids = [];
		foreach ($this->next_projects as $next_project) {
			/** @var $Has_Redmine_Id Project|Has_Redmine_Id */
			$Has_Redmine_Id = array_shift($next_project);
			foreach ($next_project as $property_name => $value) {
				$Has_Redmine_Id->$property_name = $value;
			}
			$Has_Redmine_Ids[] = $Has_Redmine_Id;
		}
		return $Has_Redmine_Ids;
	}

	//----------------------------------------------------------------------------- resetNextProjects
	/**
	 * Resets next redmine projects list
	 */
	private function resetNextProjects()
	{
		$this->next_projects = [];
	}

	//----------------------------------------------------------------------------------- synchronize
	/**
	 * Synchronize projects from Redmine to SAF Projects
	 *
	 * @return string The report if there are errors, or 'OK' if everything went right
	 */
	public function synchronize()
	{
		$this->saf_projects = Dao::readAll(Project::class, [Dao::key('id_Has_Redmine_Id')]);
		/** @var $dao Link */
		$dao = Dao::get(API::LINK);
		$filter = API::filter(get_class($this));
		$query = $filter
			? str_replace('{filter}', 'AND (' . $filter . ')', static::IMPORT_QUERY)
			: str_replace('{filter}', '', static::IMPORT_QUERY);
		/** @var $Has_Redmine_Ids Project[]|Has_Redmine_Id[] */
		$Has_Redmine_Ids = $dao->query($query, Project::class);
		$maximum = 2;
		while ($Has_Redmine_Ids && $maximum--) {
			$this->resetNextProjects();
			foreach ($Has_Redmine_Ids as $Has_Redmine_Id) {
				$this->translateIdParent($Has_Redmine_Id);
				$this->write($Has_Redmine_Id);
				$Has_Redmine_Ids[$Has_Redmine_Id->redmine_id] = $Has_Redmine_Id;
			}
			// prepare next projects, and give their properties back
			$Has_Redmine_Ids = $this->prepareNextProjects();
		}
		// some projects parents have not been solved
		return $Has_Redmine_Ids
			? ("Some parents could not been solved" . PRE . print_r($Has_Redmine_Ids, true))
			: 'OK';
	}

	//----------------------------------------------------------------------------- translateIdParent
	/**
	 * Look for the real parent project id from the already existing projects list
	 * If not found, then remove id_parent and keep the project into the next redmine projects list
	 * to solve it on next pass.
	 *
	 * @param $Has_Redmine_Id Project|Has_Redmine_Id
	 */
	private function translateIdParent(Project $Has_Redmine_Id)
	{
		// id_parent translation
		if (!empty($Has_Redmine_Id->id_parent)) {
			if (isset($this->saf_projects[$Has_Redmine_Id->id_parent])) {
				$Has_Redmine_Id->id_parent = Dao::getObjectIdentifier(
					$this->saf_projects[$Has_Redmine_Id->id_parent]
				);
			}
			// parent project does not already exist : store for further update
			else {
				$this->next_projects[] = [$Has_Redmine_Id, 'id_parent' => $Has_Redmine_Id->id_parent];
				unset($Has_Redmine_Id->id_parent);
			}
		}
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Creates or Update a project that is ready to be written
	 *
	 * @param $project Project|Has_Redmine_Id
	 */
	private function write(Project $project)
	{
		// update existing project
		if (isset($this->saf_projects[$project->redmine_id])) {
			Dao::replace($project, $this->saf_projects[$project->redmine_id]);
		}
		// create new project
		else {
			Dao::write($project);
			$this->saf_projects[$project->redmine_id] = $project;
		}
	}

}
