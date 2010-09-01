<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
 * Filter configurations for project extension
 *
 * @package		Todoyu
 * @subpackage	Project
 */

Todoyu::$CONFIG['FILTERS']['TASK'] = array(
	'key'		=> 'task',
	'right'		=> 'project:task.searchable',
	'config'	=> array(
		'label'				=> 'LLL:task.search.label',
		'position'			=> 10,
		'resultsRenderer'	=> 'TodoyuTaskRenderer::renderTaskListing',
		'class'				=> 'TodoyuTaskFilter',
		'require'			=> 'project.general:use',
		'assets'			=> array(
			array(
				'ext'	=> 'project',
				'type'	=> 'public'
			)
		)
	),
	'widgets' => array(

		/**
		 * OptGroup task
		 */
		'status' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_status',
			'label'		=> 'LLL:core.status',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 8,
				'FuncRef'	=> 'TodoyuTaskFilterDataSource::getStatusOptions',
				'negation'	=> 'default'
			)
		),
		'creatorPerson' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_creatorPerson',
			'label'		=> 'LLL:projectFilter.task.creatorPerson',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuPersonFilterDataSource::autocompletePersons',
				'FuncParams'	=> array(),
				'LabelFuncRef'	=> 'TodoyuPersonFilterDataSource::getLabel',
				'negation'	=> 'default'
			)
		),
		'creatorRoles' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_creatorRoles',
			'label'		=> 'LLL:projectFilter.task.creatorRoles',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 5,
				'FuncRef'	=> 'TodoyuTaskFilterDataSource::getProjectroleOptionDefinitions',
				'negation'	=> 'default'
			)
		),
		'assignedPerson' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_assignedPerson',
			'label'		=> 'LLL:projectFilter.task.assignedPerson',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuPersonFilterDataSource::autocompletePersons',
				'FuncParams'	=> array(),
				'LabelFuncRef'	=> 'TodoyuPersonFilterDataSource::getLabel',
				'negation'	=> 'default'
			)
		),
		'assignedRoles' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_assignedRoles',
			'label'		=> 'LLL:projectFilter.task.assignedRoles',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 5,
				'FuncRef'	=> 'TodoyuTaskFilterDataSource::getProjectroleOptionDefinitions',
				'negation'	=> 'default'
			)
		),
		'ownerPerson' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_ownerPerson',
			'label'		=> 'LLL:projectFilter.task.ownerPerson',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuPersonFilterDataSource::autocompletePersons',
				'FuncParams'	=> array(),
				'LabelFuncRef'	=> 'TodoyuPersonFilterDataSource::getLabel',
				'negation'	=> 'default'
			)
		),
		'ownerRoles' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_ownerRoles',
			'label'		=> 'LLL:projectFilter.task.ownerRoles',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 5,
				'FuncRef'	=> 'TodoyuTaskFilterDataSource::getProjectroleOptionDefinitions',
				'negation'	=> 'default'
			)
		),
		'acknowledged' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_acknowledged',
			'label'		=> 'LLL:projectFilter.task.acknowledged',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuPersonFilterDataSource::autocompletePersons',
				'FuncParams'	=> array(),
				'LabelFuncRef'	=> 'TodoyuPersonFilterDataSource::getLabel',
				'negation'		=> 'default'
			)
		),
		'isPublic'	=> array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_isPublic',
			'label'		=> 'LLL:projectFilter.task.isPublic',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'checkbox',
			'wConf'		=> array(
				'checked'	=> true
			)
		),
		'title' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_title',
			'label'		=> 'LLL:projectFilter.task.title',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'LabelFuncRef'	=> 'TodoyuProjectFilterDataSource::getLabel',
				'negation'		=> 'default'
			)
		),
		'fulltext' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_fulltext',
			'label'		=> 'LLL:projectFilter.task.fulltext',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'LabelFuncRef'	=> 'TodoyuProjectFilterDataSource::getLabel',
				'negation'		=> 'default'
			)
		),
		'type' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_type',
			'label'		=> 'LLL:projectFilter.task.type',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'select',
			'wConf' => array(
				'multiple'	=> false,
				'size'		=> 2,
				'FuncRef'	=> 'TodoyuTaskFilterDataSource::getTypeOptions',
				'negation'	=> 'default'
			)
		),
		'worktype' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_worktype',
			'label'		=> 'LLL:projectFilter.task.worktype',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'select',
			'wConf' => array(
				'multiple'	=> true,
				'FuncRef'	=> 'TodoyuTaskFilterDataSource::getWorktypeOptions',
				'negation'	=> 'default'
			)
		),






		/**
		 * OptGroup project
		 */
		'project' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_project',
			'label'		=> 'LLL:projectFilter.project',
			'optgroup'	=> 'LLL:project.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuProjectFilterDataSource::autocompleteProjects',
				'FuncParams'	=> array(),
				'LabelFuncRef'	=> 'TodoyuProjectFilterDataSource::getLabel',
				'negation'		=> 'default'
			)
		),
		'projectrole' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_projectrole',
			'label'		=> 'LLL:projectFilter.project.projectrole',
			'optgroup'	=> 'LLL:project.search.label',
			'widget'	=> 'projectrole',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuPersonFilterDataSource::autocompletePersons',
				'multiple'		=> true,
				'size'			=> 5,
				'negation'		=> 'default'
			)
		),
		'customer'  => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_company',
			'label'		=> 'LLL:projectFilter.project.company',
			'optgroup'	=> 'LLL:project.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuCompanyFilterDataSource::autocompleteCompanies',
				'FuncParams'	=> array(),
				'LabelFuncRef'	=> 'TodoyuCompanyFilterDataSource::getCompanyLabel',
				'negation'		=> 'default'
			)
		),






		/**
		 * OptGroup time management
		 */
		'deadlinedate'		=> array(
			'funcRef' 	=> 'TodoyuTaskFilter::Filter_deadlinedate',
			'label'		=> 'LLL:projectFilter.task.deadlinedate',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'deadlinedateDyn'	=> array(
			'funcRef' 	=> 'TodoyuTaskFilter::Filter_deadlinedateDyn',
			'label'		=> 'LLL:projectFilter.task.deadlinedateDyn',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuTaskFilterDataSource::getDynamicDateOptions'
			)
		),
		'startdate'		=> array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_startdate',
			'label'		=> 'LLL:projectFilter.task.startdate',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'startdateDyn'	=> array(
			'funcRef' 	=> 'TodoyuTaskFilter::Filter_startdateDyn',
			'label'		=> 'LLL:projectFilter.task.startdateDyn',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuTaskFilterDataSource::getDynamicDateOptions'
			)
		),
		'enddate'		=> array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_enddate',
			'label'		=> 'LLL:projectFilter.task.enddate',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'enddateDyn'	=> array(
			'funcRef' 	=> 'TodoyuTaskFilter::Filter_enddateDyn',
			'label'		=> 'LLL:projectFilter.task.enddateDyn',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuTaskFilterDataSource::getDynamicDateOptions'
			)
		),
		'createdate' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_createdate',
			'label'		=> 'LLL:projectFilter.task.createdate',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'createdateDyn' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_createdateDyn',
			'label'		=> 'LLL:projectFilter.task.createdateDyn',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuTaskFilterDataSource::getDynamicDateOptions'
			)
		),
		'editdate' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_editdate',
			'label'		=> 'LLL:projectFilter.task.editdate',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'editdateDyn' => array(
			'funcRef'	=> 'TodoyuTaskFilter::Filter_editdateDyn',
			'label'		=> 'LLL:projectFilter.task.editdateDyn',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuTaskFilterDataSource::getDynamicDateOptions'
			)
		),



		/**
		 * OptGroup filter
		 */
		'filterSet' => array(
			'funcRef'	=> 'TodoyuFiltersetManager::Filter_filterSet',
			'label'		=> 'LLL:search.filterlabel.filterset',
			'optgroup'	=> 'LLL:core.filter',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 5,
				'FuncRef'	=> 'TodoyuFiltersetManager::getFilterSetSelectionOptions'
			)
		)
	),

	/**
	 * Filters without a widget in the search area
	 */
	'filters' => array(

	)
);



Todoyu::$CONFIG['FILTERS']['PROJECT'] = array(
	'key'		=> 'project',
	'right'		=> 'project:project.searchable',
	'config' 	=> array(
		'label'				=> 'LLL:project.search.label',
		'position'			=> 20,
		'resultsRenderer'	=> 'TodoyuProjectRenderer::renderProjectListing',
		'class'				=> 'TodoyuProjectFilter',
		'require'			=> 'project.general:use',
		'assets'			=> array(
			array(
				'ext'	=> 'project',
				'type'	=> 'public'
			)
		)
	),
	'widgets' => array(



		/**
		 * OptGroup project
		 */
		'title' => array(
			'funcRef'	=> 'TodoyuProjectFilter::filter_title',
			'label'		=> 'LLL:core.title',
			'optgroup'	=> 'LLL:project.search.label',
			'widget'	=> 'text',
			'wConf'		=> array(
				'negation'	=> 'default'
			)
		),
		'fulltext' => array(
			'funcRef'	=> 'TodoyuProjectFilter::filter_fulltext',
			'label'		=> 'LLL:projectFilter.project.fulltext',
			'optgroup'	=> 'LLL:project.search.label',
			'widget'	=> 'text',
		),
		'status' => array(
			'funcRef'	=> 'TodoyuProjectFilter::filter_status',
			'label'		=> 'LLL:core.status',
			'optgroup'	=> 'LLL:project.search.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 5,
				'FuncRef'	=> 'TodoyuProjectFilterDataSource::getStatusOptions',
				'negation'	=> 'default'
	  		)
		),
		'company' => array(
			'funcRef'	=> 'TodoyuProjectFilter::Filter_company',
			'label'		=> 'LLL:projectFilter.project.company',
			'optgroup'	=> 'LLL:project.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuCompanyFilterDataSource::autocompleteCompanies',
				'FuncParams'	=> array(),
				'LabelFuncRef'	=> 'TodoyuCompanyFilterDataSource::getCompanyLabel',
				'negation'		=> 'default'
			)
		),
		'projectrole' => array(
			'funcRef'	=> 'TodoyuProjectFilter::Filter_projectrole',
			'label'		=> 'LLL:projectFilter.project.projectrole',
			'optgroup'	=> 'LLL:project.search.label',
			'widget'	=> 'projectrole',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuPersonFilterDataSource::autocompletePersons',
				'multiple'		=> true,
				'size'			=> 5,
				'negation'		=> 'default'
			)
		),



		/**
		 * OptGroup time management
		 */
		'startdate' => array(
			'funcRef'	=> 'TodoyuProjectFilter::Filter_startdate',
			'label'		=> 'LLL:projectFilter.project.date_start',
			'optgroup'	=> 'LLL:projectFilter.project.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'enddate' => array(
			'funcRef'	=> 'TodoyuProjectFilter::Filter_enddate',
			'label'		=> 'LLL:projectFilter.project.date_end',
			'optgroup'	=> 'LLL:projectFilter.project.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'deadline' => array(
			'funcRef'	=> 'TodoyuProjectFilter::Filter_deadline',
			'label'		=> 'LLL:projectFilter.project.deadline',
			'optgroup'	=> 'LLL:projectFilter.project.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),

		'createdate' => array(
			'funcRef'	=> 'TodoyuProjectFilter::Filter_createdate',
			'label'		=> 'LLL:projectFilter.project.createdate',
			'optgroup'	=> 'LLL:projectFilter.project.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'createdateDyn' => array(
			'funcRef'	=> 'TodoyuProjectFilter::Filter_createdateDyn',
			'label'		=> 'LLL:projectFilter.project.createdateDyn',
			'optgroup'	=> 'LLL:projectFilter.project.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuProjectFilterDataSource::getDynamicDateOptions'
			)
		),
		'editdate' => array(
			'funcRef'	=> 'TodoyuProjectFilter::Filter_editdate',
			'label'		=> 'LLL:projectFilter.project.editdate',
			'optgroup'	=> 'LLL:projectFilter.project.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'editdateDyn' => array(
			'funcRef'	=> 'TodoyuProjectFilter::Filter_editdateDyn',
			'label'		=> 'LLL:projectFilter.project.editdateDyn',
			'optgroup'	=> 'LLL:projectFilter.project.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuProjectFilterDataSource::getDynamicDateOptions'
			)
		),
	)
);
?>