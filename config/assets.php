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
 * Assets (JS, CSS, SWF, etc.) requirements for project extension
 *
 * @package		Todoyu
 * @subpackage	Project
 */

if( ! defined('TODOYU') ) die('NO ACCESS');


$CONFIG['EXT']['project']['assets'] = array(
		// default assets: loaded all over the installation always
	'default' => array(
		'js' => array(

		),
		'css' => array(

		)
	),


		// public assets: basis assets for this extension
	'public' => array(
		'js' => array(
			array(
				'file'		=> 'ext/project/assets/js/Ext.js',
				'position'	=> 100
			),
			array(
				'file'		=> 'ext/project/assets/js/Project.js',
				'position'	=> 101
			),
			array(
				'file'		=> 'ext/project/assets/js/Task.js',
				'position'	=> 102
			),
			array(
				'file'		=> 'ext/project/assets/js/Container.js',
				'position'	=> 103
			),
			array(
				'file'		=> 'ext/project/assets/js/TaskTree.js',
				'position'	=> 104
			),
			array(
				'file'		=> 'ext/project/assets/js/ContextMenuTask.js',
				'position'	=> 105
			),
			array(
				'file'		=> 'ext/project/assets/js/ContextMenuProject.js',
				'position'	=> 106
			),
			array(
				'file'		=> 'ext/project/assets/js/ProjectTaskTree.js',
				'position'	=> 107
			),
			array(
				'file'		=> 'ext/project/assets/js/TaskParentAc.js',
				'position'	=> 108
			)
		),
		'css' => array(
			array(
				'file'		=> 'ext/project/assets/css/ext.css',
				'media'		=> 'all',
				'position'	=> 100
			),
			array(
				'file'		=> 'ext/project/assets/css/task.css',
				'position'	=> 101
			),
			array(
				'file'		=> 'ext/project/assets/css/project.css',
				'position'	=> 102
			),
			array(
				'file'		=> 'ext/project/assets/css/contextmenu.css',
				'position'	=> 103
			),
			array(
				'file'		=> 'ext/project/assets/css/taskparent-ac.css',
				'position'	=> 104
			)
		)
	),

		// assets of panel widgets
	'panelwidget-projecttree' => array(
		'js' => array(
			array(
				'file'		=> 'ext/project/assets/js/PanelWidgetProjectTree.js',
				'position'	=> 110
			),
			array(
				'file'		=> 'ext/project/assets/js/PanelWidgetProjectTreeFilter.js',
				'position'	=> 111
			)
		),
		'css' => array(
			array(
				'file'		=> 'ext/project/assets/css/panelwidget-projecttree.css',
				'media'		=> 'all',
				'position'	=> 110
			)
		)
	),
	
	'panelwidget-statusfilter' => array(
		'js' => array(
			array(
				'file' => 'ext/project/assets/js/PanelWidgetStatusFilter.js',
				'position' => 120,
			)
		),
		'css' => array(
			array(
				'file' => 'ext/project/assets/css/panelwidget-statusfilter.css',
				'position' => 120,
			)
		)
	),
	
	'panelwidget-quickproject' => array (
		'css' => array(
			array(
				'file' => 'ext/project/assets/css/panelwidget-quickproject.css',
				'position' => 130,
			)
		)
	)
);

?>