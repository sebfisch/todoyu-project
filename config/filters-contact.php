<?php
/****************************************************************************
 * todoyu is published under the BSD License:
 * http://www.opensource.org/licenses/bsd-license.php
 *
 * Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
 * Project related person filters - added via hook
 *
 * @package		Todoyu
 * @subpackage	Project
 * @see			TodoyuProjectManager::hookLoadContactFilterConfig
 */

	// Persons assigned in project
Todoyu::$CONFIG['FILTERS']['PERSON']['widgets']['assignedinproject'] = array(
	'funcRef'	=> 'TodoyuProjectPersonFilter::Filter_assignedinproject',
	'label'		=> 'project.filter.person.assignedinproject',
	'optgroup'	=> 'project.filter.optgroup.projects',
	'widget'	=> 'text',
	'wConf' => array(
		'autocomplete'	=> true,
		'FuncRef'		=> 'TodoyuProjectProjectFilterDataSource::autocompleteProjects',
		'FuncParams'	=> array(),
		'LabelFuncRef'	=> 'TodoyuProjectProjectFilterDataSource::getLabel',
		'negation'		=> false
	)
);

	// Company has project(s) with status...
Todoyu::$CONFIG['FILTERS']['COMPANY']['widgets']['projectstatus'] = array(
	'funcRef'	=> 'TodoyuProjectCompanyFilter::Filter_projectstatus',
	'label'		=> 'project.filter.company.projectstatus',
	'optgroup'	=> 'project.filter.optgroup.projects',
	'widget'	=> 'select',
	'wConf'		=> array(
		'multiple'	=> false,
		'size'		=> 5,
		'FuncRef'	=> 'TodoyuProjectProjectFilterDataSource::getStatusOptions',
		'negation'	=> 'default'
	)
);

?>