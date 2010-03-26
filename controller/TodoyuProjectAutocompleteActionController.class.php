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


	public function init(array $params) {
		restrict('project', 'general:use');
	}

	public function personAction(array $params) {
		$sword		= trim($params['sword']);
		$config		= array();
		$results	= TodoyuPersonFilterDataSource::autocompletePersons($sword, $config);

			// Render & display output
		return TodoyuRenderer::renderAutocompleteList($results);
	}

}

?>