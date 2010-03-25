<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSC License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/

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
		return $parse ? TodoyuString::getLabel($this->title) : $this->title;
	}

}

?>