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
 * Configure panel widgets to be shown in project area
 */

	// PanelWidget Projectlist
TodoyuPanelWidgetManager::addPanelWidget('project', 'project', 'ProjectList', 20);
	// PanelWidget Status Filter for Projects
TodoyuPanelWidgetManager::addPanelWidget('project', 'project', 'StatusFilterProject', 21);
	// PanelWidget Status Filter for Tasks
TodoyuPanelWidgetManager::addPanelWidget('project', 'project', 'StatusFilterTask', 30);

?>