<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSD License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/


/**
 *
 */
class TodoyuProjectExportManager {

	/**
	 * Exports Project as csv
	 *
	 * @static
	 * @param 	Array	$projectIDs
	 */
	public static function exportCSV(array $projectIDs) {
		$projectIDs	= TodoyuArray::intval($projectIDs);

		$projectsToExport = self::prepareDataForExport($projectIDs);

		$export		= new TodoyuExportCSV($projectsToExport);

		$export->setFilename('todoyu_project_export_' . date('YmdHis') . '.csv');

		$export->download();
	}



	/**
	 * Prepares projects for export
	 *
	 * @static
	 * @param	Array	$projectIDs
	 * @return	Array
	 */
	protected static function prepareDataForExport(array $projectIDs) {
		$projectIDs	= TodoyuArray::intval($projectIDs);

		$exportData = array();

		foreach($projectIDs as $idProject)	 {
			$project	= TodoyuProjectProjectManager::getProject($idProject);

			$project->loadForeignData();

			$exportData[] = self::parseDataForExport($project);
		}

		return $exportData;
	}



	/**
	 * Parses Project data for CSV export
	 *
	 * @static
	 * @param	TodoyuProjectProject	$project
	 * @return	Array
	 */
	protected static function parseDataForExport(TodoyuProjectProject $project) {
		$exportData = array(
			TodoyuLabelManager::getLabel('LLL:project.attr.id')					=> $project->id,
			TodoyuLabelManager::getLabel('LLL:task.attr.date_create')			=> TodoyuTime::format($project->date_create, 'date'),
			TodoyuLabelManager::getLabel('LLL:core.date_update')				=> TodoyuTime::format($project->date_update, 'date'),
			TodoyuLabelManager::getLabel('LLL:core.id_person_create')			=> TodoyuContactPersonManager::getPerson($project->id_person_create)->getFullName(),
			TodoyuLabelManager::getLabel('LLL:project.attr.date_start')			=> TodoyuTime::format($project->date_start),
			TodoyuLabelManager::getLabel('LLL:project.attr.date_end')			=> TodoyuTime::format($project->date_end),
			TodoyuLabelManager::getLabel('LLL:project.attr.date_deadline')		=> TodoyuTime::format($project->date_deadline),
			TodoyuLabelManager::getLabel('LLL:project.attr.title')				=> $project->title,
			TodoyuLabelManager::getLabel('LLL:core.description')				=> TodoyuString::strictHtml2text($project->description),
			TodoyuLabelManager::getLabel('LLL:project.attr.status')				=> $project->getStatusLabel(),
			TodoyuLabelManager::getLabel('LLL:project.attr.company')			=> $project->getCompany()->getLabel(),
		);

		foreach($project['persons'] as $index => $personData) {
			$exportData[TodoyuLabelManager::getLabel('LLL:contact.person') .'_'. ($index+1)]			= $personData['firstname'] . ' ' . $personData['lastname'];
			$exportData[TodoyuLabelManager::getLabel('LLL:project.attr.persons.role') .'_'. ($index+1)]	= $personData['rolelabel'];
		}

		$exportData = TodoyuHookManager::callHookDataModifier('project', 'projectCSVExportParseData', $exportData, array('project'	=> $project));

		return $exportData;
	}
}

?>