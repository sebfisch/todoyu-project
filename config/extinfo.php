<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2013, snowflake productions GmbH, Switzerland
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
 * Various project extension info data
 */

Todoyu::$CONFIG['EXT']['project']['info'] = array(
	'title'			=> 'Project and Task Management',
	'description'	=> 'Basic Project and Task Management',
	'author'		=> array(
		'name'		=> 'todoyu Core Developer Team',
		'email'		=> 'team@todoyu.com',
		'company'	=> 'snowflake productions GmbH, Zurich'
	),
	'state'			=> 'stable',
	'version' 		=> '1.5.1',
	'constraints'	=> array(
		'core'		=> '2.3.1',
		'depends'	=> array(
			'contact' => '1.3.1'
		),
		'system'	=> true
	),
	'urlDocumentation'	=> 'http://doc.todoyu.com/?project'
);

?>