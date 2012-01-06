<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
 * Add project search engine types: tasks, projects
 *
 * @package		Todoyu
 * @subpackage	Project
 */

if( Todoyu::allowed('project', 'general:use') ) {
	TodoyuSearchManager::addEngine('task', 'TodoyuProjectTaskSearch::getSuggestions', 'project.task.search.label', 'project.task.search.mode.label', 10);
}

if( Todoyu::allowed('project', 'general:use') ) {
	TodoyuSearchManager::addEngine('project', 'TodoyuProjectProjectSearch::getSuggestions', 'project.ext.search.label', 'project.ext.search.mode.label', 20);
}

?>