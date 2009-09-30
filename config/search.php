<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 snowflake productions gmbh
*  All rights reserved
*
*  This script is part of the todoyu project.
*  The todoyu project is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License, version 2,
*  (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html) as published by
*  the Free Software Foundation;
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


TodoyuSearchManager::addSearchEngine('task', 'TodoyuTaskSearch::getResults', 'TodoyuTaskSearch::getSuggestions', 'task.search.label', 'Tasks durchsuchen', 10);
TodoyuSearchManager::addSearchEngine('project', 'TodoyuProjectSearch::getResults', 'TodoyuProjectSearch::getSuggestions', 'project.search.label', 'Projekte durchsuchen', 20);


//$CONFIG['EXT']['search']['engines']['task'] = array(
//	'search'	=> 'TodoyuTaskSearch::getResults',
//	'suggestion'=> 'TodoyuTaskSearch::getSuggestions',
//	'label'		=> 'task.search.label'
//);
//
//$CONFIG['EXT']['search']['engines']['project'] = array(
//	'search'	=> 'TodoyuProjectSearch::getResults',
//	'suggestion'=> 'TodoyuProjectSearch::getSuggestions',
//	'label'		=> 'project.search.label'
//);
//
//$CONFIG['EXT']['search']['modes']['task'] = array(
//	'mode'		=> 'task',
//	'class'		=> 'searchmode-task',
//	'position'	=> 10,
//	'label'		=> 'Task'
//);
//
//$CONFIG['EXT']['search']['modes']['project'] = array(
//	'mode'		=> 'project',
//	'class'		=> 'searchmode-project',
//	'position'	=> 20,
//	'label'		=> 'Project'
//);

?>