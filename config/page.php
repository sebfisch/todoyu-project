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

		// Add menu entry
if(  allowed('project', 'general:area') ) {
	TodoyuFrontend::addMenuEntry('project', 'LLL:project.tab.label', '?ext=project', 20);

	$projectEntries	= TodoyuProjectManager::getOpenProjectLabels();

	$entryNum		= 0;
	foreach($projectEntries as $idProject => $title) {
		TodoyuFrontend::addSubmenuEntry('project', 'project' . $idProject, $title, '?ext=project&project=' . $idProject, $entryNum++);
	}
}

	// Register quicktask headlet
if ( allowed('project', 'task:addInOwnProjects') ) {
	TodoyuHeadManager::addHeadlet('TodoyuHeadletQuickTask', 55);
}

?>