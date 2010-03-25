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
 * Constants for project extension
 *
 * @package		Todoyu
 * @subpackage	Project
 */

	// Statuses for projects and tasks
define('STATUS_PLANNING', 	1);
define('STATUS_OPEN',		2);
define('STATUS_PROGRESS', 	3);
define('STATUS_CONFIRM', 	4);
define('STATUS_DONE', 		5);
define('STATUS_ACCEPTED', 	6);
define('STATUS_REJECTED', 	7);
define('STATUS_CLEARED', 	8);
define('STATUS_WARRANTY', 	9);
define('STATUS_CUSTOMER', 	10);

	// Basic task types
define('TASK_TYPE_TASK',		1);
define('TASK_TYPE_CONTAINER',	2);

	// Tasknumber format
define('TASKNUMBER_FORMAT', '/(\d){1,}\.(\d){1,}/')


?>