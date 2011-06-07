<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
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

TodoyuHookManager::registerHook('core', 'substituteLinkableElements', 'TodoyuProjectTaskManager::linkTaskIDsInText');

TodoyuHookManager::registerHook('project', 'taskdata', 			'TodoyuProjectTaskManager::getTaskDataAttributes');
TodoyuHookManager::registerHook('project', 'projectdata', 		'TodoyuProjectProjectManager::getProjectDataAttributes');
TodoyuHookManager::registerHook('project', 'projectpresetdata', 'TodoyuProjectProjectManager::getProjectPresetDataAttributes');
TodoyuHookManager::registerHook('project', 'taskIcons', 		'TodoyuProjectTaskManager::getTaskIcons');
TodoyuHookManager::registerHook('project', 'taskinfo',			'TodoyuProjectTaskManager::addContainerInfoToTaskData');
TodoyuHookManager::registerHook('project', 'taskDefaultData',	'TodoyuProjectTaskManager::hookTaskDefaultDataFromPreset', 50);
TodoyuHookManager::registerHook('project', 'taskDefaultData',	'TodoyuProjectTaskManager::hookTaskDefaultDataFromEnvironment', 90);





//TodoyuHookManager::registerHook('sysmanager', 'renderExtContent-project',		'TodoyuProjectExtManagerRenderer::onRenderExtConfig');
TodoyuHookManager::registerHook('sysmanager', 'renderRecordsBody-taskpreset',	'TodoyuProjectSysmanagerRenderer::onRenderTaskpresetRecordsBody');

TodoyuFormHook::registerBuildForm('ext/project/config/form/task.xml', 'TodoyuProjectTaskManager::hookModifyFormfieldsForTask');
TodoyuFormHook::registerBuildForm('ext/project/config/form/task.xml', 'TodoyuProjectTaskManager::hookModifyFormfieldsForContainer');
TodoyuFormHook::registerLoadData('ext/project/config/form/quicktask.xml', 'TodoyuProjectTaskManager::hookLoadDefaultTaskFormData');
//TodoyuFormHook::registerLoadData('ext/project/config/form/task.xml', 'TodoyuProjectTaskManager::hookLoadDefaultTaskFormData');

?>