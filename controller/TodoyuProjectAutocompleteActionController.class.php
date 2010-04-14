<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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
 * Controller for project autocomplete
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectAutocompleteActionController extends TodoyuActionController {

	/**
	 * Check controller access
	 *
	 * @param	Array		$params
	 */
	public function init(array $params) {
		restrict('project', 'general:use');
	}



	/**
	 * Autocomplete persons for project
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function personAction(array $params) {
		$sword		= trim($params['sword']);
		$config		= array();
		$results	= TodoyuPersonFilterDataSource::autocompletePersons($sword, $config);

			// Render & display output
		return TodoyuRenderer::renderAutocompleteList($results);
	}



	/**
	 * Get task autocomplete list for new parent tasks
	 * Parent tasks have to be in the same project
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function projecttaskAction(array $params) {
		$formName	= $params['formName'];
		$sword		= trim($params['sword']);
		$idProject	= intval($params[$formName]['id_project']);
		$idTask		= intval($params[$formName]['id']);

		$filters	= array(
			array(
				'filter'=> 'tasknumberortitle',
				'value'	=> $sword
			),
			array(
				'filter'=> 'nottask',
				'value'	=> $idTask
			),
			array(
				'filter'=> 'project',
				'value'	=> $idProject
			)
		);

		$tasks	= TodoyuTaskFilterDataSource::getTaskAutocompleteListByFilter($filters);

		return TodoyuRenderer::renderAutocompleteList($tasks);
	}

	

	/**
	 * Get company autocomplete list
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function companyAction(array $params) {
		$sword	= $params['sword'];
		$results = TodoyuPersonFilterDataSource::autocompleteCompanies($sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}



	/**
	 * Get project autocomplete list
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function projectAction(array $params) {
		$sword	= $params['sword'];
		$config	= array();

		$data	= TodoyuProjectFilterDataSource::autocompleteProjects($sword, $config);

		return TodoyuRenderer::renderAutocompleteList($data);
	}


}

?>