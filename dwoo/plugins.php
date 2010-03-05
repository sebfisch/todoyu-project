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
 * Project specific Dwoo plugins
 *
 * @package		Todoyu
 * @subpackage	Template
 */



/**
 * Get task status label by given status ID
 * Usage example:	{taskStatusLabel $statusID}
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param 	Dwoo 		$dwoo
 * @param 	String		$extension
 * @param	String		$action
 * @param	String		$params
 * @return	String
 */
function Dwoo_Plugin_taskStatusLabel(Dwoo $dwoo, $idStatus) {
	$idStatus	= intval($idStatus);

	return TodoyuProjectStatusManager::getTaskStatusLabel($idStatus);
}



/**
 * Render task status key
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	Dwoo	$dwoo
 * @param	Integer	$statusID
 * @return	String
 */
function Dwoo_Plugin_taskStatusKey(Dwoo $dwoo, $idStatus) {
	$idStatus	= intval($idStatus);

	return TodoyuProjectStatusManager::getTaskStatusKey($idStatus);
}



/**
 * Get project status label
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	Dwoo		$dwoo
 * @param	Integer		$statusID
 * @return	String
 */
function Dwoo_Plugin_projectStatusLabel(Dwoo $dwoo, $idStatus) {
	$idStatus	= intval($idStatus);

	return TodoyuProjectStatusManager::getProjectStatusLabel($idStatus);
}



/**
 * Get project status key
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	Dwoo	$dwoo
 * @param	Integer	$idStatus
 * @return	String
 */
function Dwoo_Plugin_projectStatusKey(Dwoo $dwoo, $idStatus) {
	$idStatus	= intval($idStatus);

	return TodoyuProjectStatusManager::getProjectStatusKey($idStatus);
}


function Dwoo_Plugin_canSeeProjectDetails(Dwoo $dwoo, $idProject) {
	return  allowed('project', 'project:seeAll') || TodoyuProjectManager::isPersonAssigned($idProject);
}

?>