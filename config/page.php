<?php

		// Add menu entry
if( TodoyuAuth::isLoggedIn() ) {
	TodoyuFrontend::addMenuEntry('project', 'LLL:project.tab.label', '?ext=project', 20);

	$projectEntries	= TodoyuProjectManager::getOpenProjectSubmenuEntryTitles();
	$entryNum		= 30;
	foreach($projectEntries as $idProject => $title) {
		TodoyuFrontend::addSubmenuEntry('project', 'project', $title, '?ext=project&project=' . $idProject, $entryNum);
		$entryNum++;
	}
}

?>