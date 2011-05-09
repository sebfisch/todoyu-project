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

/**
 * Taskpreset
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectTaskpreset extends TodoyuBaseObject {

	/**
	 * Constructor
	 *
	 * @param	Integer	$idTaskpreset
	 */
	public function __construct($idTaskpreset) {
		$idTaskpreset	= intval($idTaskpreset);

		parent::__construct($idTaskpreset, 'ext_project_taskpreset');
	}



	/**
	 * Get title of task preset
	 *
	 * @param	Boolean		$parsed
	 * @return	String
	 */
	public function getTitle($parse = true) {
		return $parse ? Todoyu::Label($this->get('title')) : $this->get('title');
	}

}

?>