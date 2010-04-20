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

		// Add menu entry
if(  allowed('project', 'general:area') ) {
	TodoyuFrontend::addMenuEntry('project', 'LLL:project.tab.label', '?ext=project', 20);

	$projectEntries	= TodoyuProjectManager::getOpenProjectLabels();
	
	$entryNum		= 0;
	foreach($projectEntries as $idProject => $title) {
		TodoyuFrontend::addSubmenuEntry('project', 'project' . $idProject, $title, '?ext=project&project=' . $idProject, $entryNum++);
	}


		// Register quicktask headlet
	if( allowed('project', 'task:addViaQuickCreateHeadlet') ) {
		TodoyuHeadManager::addHeadlet('TodoyuHeadletQuickTask', 55);
	}
}

?>