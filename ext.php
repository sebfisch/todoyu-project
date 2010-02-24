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

/**
 * Extension main file for project extension
 *
 * @package		Todoyu
 * @subpackage	Project
 */

	// Declare ext ID, path
define('EXTID_PROJECT', 112);
define('PATH_EXT_PROJECT', PATH_EXT . '/project');

	// Register module locales
TodoyuLanguage::register('project', PATH_EXT_PROJECT . '/locale/ext.xml');
TodoyuLanguage::register('task', PATH_EXT_PROJECT . '/locale/task.xml');
TodoyuLanguage::register('projectFilter', PATH_EXT_PROJECT . '/locale/filter.xml');
TodoyuLanguage::register('panelwidget-projecttree', PATH_EXT_PROJECT . '/locale/panelwidget-projecttree.xml');
TodoyuLanguage::register('panelwidget-projectlist', PATH_EXT_PROJECT . '/locale/panelwidget-projectlist.xml');
TodoyuLanguage::register('panelwidget-statusfilter', PATH_EXT_PROJECT . '/locale/panelwidget-statusfilter.xml');
TodoyuLanguage::register('headlet-quicktask', PATH_EXT_PROJECT . '/locale/headlet-quicktask.xml');

	// Add quick creation types
TodoyuQuickCreateManager::addEngine('project', 'project', 'project.create.label', 10);
TodoyuQuickCreateManager::addEngine('project', 'task', 'task.create.label', 20);
	// Add area related primary creation engines
TodoyuQuickCreateManager::addAreaEngine(EXTID_PROJECT, 'project', 'project', 'project.create.label', 10);
TodoyuQuickCreateManager::addAreaEngine(EXTID_PROJECT, 'project', 'task', 'task.create.label', 20);


	// Request configurations
require_once( PATH_EXT_PROJECT . '/config/constants.php' );
require_once( PATH_EXT_PROJECT . '/config/extension.php' );
//require_once( PATH_EXT_PROJECT . '/config/filters.php' );
require_once( PATH_EXT_PROJECT . '/config/panelwidgets.php' );
require_once( PATH_EXT_PROJECT . '/config/hooks.php' );
require_once( PATH_EXT_PROJECT . '/dwoo/plugins.php');

?>