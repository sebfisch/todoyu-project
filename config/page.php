<?php

		// Add menu entry
if( TodoyuAuth::isLoggedIn() ) {
	TodoyuFrontend::addMenuEntry('project', 'LLL:project.tab.label', '?ext=project', 20);

	$projectEntries	= TodoyuProjectManager::getOpenProjectLabels();
//	$entryNum		= 1;
	foreach($projectEntries as $idProject => $title) {
		TodoyuFrontend::addSubmenuEntry('project', 'project' . $idProject, $title, '?ext=project&project=' . $idProject, $entryNum++);
	}
}

?>