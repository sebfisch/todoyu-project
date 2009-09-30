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
 * Userrole
 *
 * @package		Todoyu
 * @subpackage	Project
 */

class TodoyuUserrole extends TodoyuBaseObject {


	/**
	 * Constructor
	 *
	 * @param	Intger	$idUserrole
	 */
	public function __construct($idUserrole) {
		$idUserrole	= intval($idUserrole);

		parent::__construct($idUserrole, 'ext_project_userrole');
	}



	/**
	 * Get key of user role
	 *
	 * @return	String
	 */
	public function getKey() {
		return $this->get('rolekey');
	}



	/**
	 * Get title of user role
	 *
	 * @param	Boolean	$parsed
	 * @return	String
	 */
	public function getTitle($parsed = true) {
		if( $parsed ) {
			return Label($this->get('title'));
		} else {
			return $this->get('title');
		}
	}

}

?>