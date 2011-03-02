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

	// Declare ext ID, path
define('EXTID_PROJECT', 112);
define('PATH_EXT_PROJECT', PATH_EXT . '/project');

require_once( PATH_EXT_PROJECT . '/config/constants.php' );
require_once( PATH_EXT_PROJECT . '/config/hooks.php' );
require_once( PATH_EXT_PROJECT . '/dwoo/plugins.php');


	// Register module locales
//TodoyuLabelManager::register('project', 'project', 'ext.xml');
//TodoyuLabelManager::register('task', 'project' , 'task.xml');
//TodoyuLabelManager::register('projectFilter', 'project', 'filter.xml');
//TodoyuLabelManager::register('panelwidget-projectlist', 'project', 'panelwidget-projectlist.xml');
//TodoyuLabelManager::register('panelwidget-statusfilter', 'project', 'panelwidget-statusfilter.xml');
//TodoyuLabelManager::register('headlet-quicktask', 'project', 'headlet-quicktask.xml');

?>