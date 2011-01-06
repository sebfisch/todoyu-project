<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
	 * @static
	 * @param 	Array	$projectIDs
	 */
	public static function exportCSV(array $projectIDs) {
		$projectIDs	= TodoyuArray::intval($projectIDs);

		$projectsToExport = self::prepareDataForExport($projectIDs);

		$export		= new TodoyuExportCSV($projectsToExport);

		$export->download();
	}



	/**
	 * @static
	 * @param	Array	$projectIDs
	 * @return	Array
	 */
	protected static function prepareDataForExport(array $projectIDs) {
		$projectIDs	= TodoyuArray::intval($projectIDs);

		$exportData = array();

		foreach($projectIDs as $idProject)	 {
			$project	= TodoyuProjectManager::getProject($idProject);

			$project->loadForeignData();

			$exportData[] = self::parseDataForExport($project);
		}

		return $exportData;
	}



	/**
	 * @static
	 * @param	TodoyuProject	$project
	 * @return	Array
	 */
	protected static function parseDataForExport(TodoyuProject $project) {
		$exportData = array(
			TodoyuLanguage::getLabel('LLL:project.attr.id')					=> $project->id,
			TodoyuLanguage::getLabel('LLL:task.attr.date_create')			=> TodoyuTime::format($project->date_create, 'date'),
			'date_update[Label]'											=> TodoyuTime::format($project->date_update, 'date'),
			'id_person_create[Label]'										=> TodoyuPersonManager::getPerson($project->id_person_create)->getFullName(),
			TodoyuLanguage::getLabel('LLL:project.attr.date_start')			=> TodoyuTime::format($project->date_start),
			TodoyuLanguage::getLabel('LLL:project.attr.date_end')			=> TodoyuTime::format($project->date_end),
			TodoyuLanguage::getLabel('LLL:project.attr.date_deadline')		=> TodoyuTime::format($project->date_deadline),
			TodoyuLanguage::getLabel('LLL:project.attr.title')				=> $project->title,
			TodoyuLanguage::getLabel('LLL:core.description')				=> TodoyuString::strictHtml2text($project->description),
			TodoyuLanguage::getLabel('LLL:project.attr.status')				=> $project->getStatusLabel(),
			TodoyuLanguage::getLabel('LLL:project.attr.company')			=> $project->getCompany()->getLabel(),
		);

		foreach($project['persons'] as $index => $personData)	{
			$exportData[TodoyuLanguage::getLabel('LLL:contact.person') .'_'. ($index+1)]			= $personData['firstname'] . ' ' . $personData['lastname'];
			$exportData[TodoyuLanguage::getLabel('LLL:project.attr.persons.role') .'_'. ($index+1)]	= $personData['rolelabel'];
		}

		$exportData = TodoyuHookManager::callHookDataModifier('project', 'onProjectCSVExportParseData', $exportData, array('project'	=> $project));

		return $exportData;
	}
}

?>