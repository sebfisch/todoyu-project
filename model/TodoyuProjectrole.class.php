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
 * Role of a person in a project
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectrole extends TodoyuBaseObject {


	/**
	 * Constructor
	 *
	 * @param	Intger	$idProjectrole
	 */
	public function __construct($idProjectrole) {
		parent::__construct($idProjectrole, 'ext_project_role');
	}



	/**
	 * Get key of projectrole
	 *
	 * @return	String
	 */
	public function getKey() {
		return $this->get('rolekey');
	}



	/**
	 * Get title of projectrole
	 *
	 * @return	String
	 */
	public function getTitle() {
		return TodoyuString::getLabel($this->get('title'));
	}

}

?>