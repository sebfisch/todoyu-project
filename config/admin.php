<?php

	// Add record infos
TodoyuExtManager::addRecordConfig('project', 'userrole', array(
	'label'	=> 'LLL:project.records.userrole',
	'form'	=> 'ext/project/config/form/admin/userrole.xml',
	'list'	=> 'TodoyuUserroleManager::getRecords',
	'save'	=> 'TodoyuUserroleManager::saveUserrole',
	'delete'=> 'TodoyuUserroleManager::deleteUserrole',
	'object'=> 'TodoyuUserrole',
	'table'	=> 'ext_project_userrole'
));

TodoyuExtManager::addRecordConfig('project', 'worktype', array(
	'label'	=> 'LLL:project.records.worktype',
	'form'	=> 'ext/project/config/form/admin/worktype.xml',
	'list'	=> 'TodoyuWorktypeManager::getRecords',
	'save'	=> 'TodoyuWorktypeManager::saveWorktype',
	'delete'=> 'TodoyuWorktypeManager::deleteWorktype',
	'object'=> 'TodoyuWorktype',
	'table'	=> 'ext_project_worktype'
));

?>