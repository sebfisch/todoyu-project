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
 * Worktype
 *
 * @package		Todoyu
 * @subpackage	Project
 */

class TodoyuWorktype extends TodoyuBaseObject {


	/**
	 * Constructor
	 *
	 * @param	Intger	$idWorktype
	 */
	public function __construct($idWorktype) {
		$idWorktype	= intval($idWorktype);

		parent::__construct($idWorktype, 'ext_project_worktype');
	}



	/**
	 * Get title of worktype
	 *
	 * @param	Boolean		$parsed
	 * @return	String
	 */
	public function getTitle($parse = true) {
		return $parse ? TodoyuDiv::getLabel($this->title) : $this->title;
	}

}

?>