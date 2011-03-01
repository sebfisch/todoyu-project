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
		'resultsRenderer'	=> 'TodoyuProjectTaskRenderer::renderTaskListing',
		'class'				=> 'TodoyuProjectTaskFilter',
		'defaultSorting'	=> 'ext_project_task.date_deadline',
		'require'			=> 'project.general:use'
	),
	'widgets' => array(

		/**
		 * OptGroup task
		 */
		'status' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_status',
			'label'		=> 'LLL:core.status',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 8,
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getStatusOptions',
				'negation'	=> 'default'
			)
		),
		'creatorPerson' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_creatorPerson',
			'label'		=> 'LLL:projectFilter.task.creatorPerson',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuContactPersonFilterDataSource::autocompletePersons',
				'FuncParams'	=> array(),
				'LabelFuncRef'	=> 'TodoyuContactPersonFilterDataSource::getLabel',
				'negation'	=> 'default'
			)
		),
		'creatorRoles' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_creatorRoles',
			'label'		=> 'LLL:projectFilter.task.creatorRoles',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 5,
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getProjectroleOptionDefinitions',
				'negation'	=> 'default'
			)
		),
		'assignedPerson' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_assignedPerson',
			'label'		=> 'LLL:projectFilter.task.assignedPerson',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuContactPersonFilterDataSource::autocompletePersons',
				'FuncParams'	=> array(),
				'LabelFuncRef'	=> 'TodoyuContactPersonFilterDataSource::getLabel',
				'negation'	=> 'default'
			)
		),
		'assignedRoles' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_assignedRoles',
			'label'		=> 'LLL:projectFilter.task.assignedRoles',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 5,
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getProjectroleOptionDefinitions',
				'negation'	=> 'default'
			)
		),
		'ownerPerson' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_ownerPerson',
			'label'		=> 'LLL:projectFilter.task.ownerPerson',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuContactPersonFilterDataSource::autocompletePersons',
				'FuncParams'	=> array(),
				'LabelFuncRef'	=> 'TodoyuContactPersonFilterDataSource::getLabel',
				'negation'	=> 'default'
			)
		),
		'ownerRoles' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_ownerRoles',
			'label'		=> 'LLL:projectFilter.task.ownerRoles',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 5,
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getProjectroleOptionDefinitions',
				'negation'	=> 'default'
			)
		),
		'acknowledged' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_acknowledged',
			'label'		=> 'LLL:projectFilter.task.acknowledged',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuContactPersonFilterDataSource::autocompletePersons',
				'FuncParams'	=> array(),
				'LabelFuncRef'	=> 'TodoyuContactPersonFilterDataSource::getLabel',
				'negation'		=> 'default'
			)
		),
		'isPublic'	=> array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_isPublic',
			'label'		=> 'LLL:projectFilter.task.isPublic',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'checkbox',
			'wConf'		=> array(
				'checked'	=> true
			)
		),
		'title' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_title',
			'label'		=> 'LLL:projectFilter.task.title',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'LabelFuncRef'	=> 'TodoyuProjectFilterDataSource::getLabel',
				'negation'		=> 'default'
			)
		),
		'fulltext' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_fulltext',
			'label'		=> 'LLL:projectFilter.task.fulltext',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'LabelFuncRef'	=> 'TodoyuProjectFilterDataSource::getLabel',
				'negation'		=> 'default'
			)
		),
		'type' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_type',
			'label'		=> 'LLL:projectFilter.task.type',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'select',
			'wConf' => array(
				'multiple'	=> false,
				'size'		=> 2,
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getTypeOptions',
				'negation'	=> 'default'
			)
		),
		'activity' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_activity',
			'label'		=> 'LLL:projectFilter.task.activity',
			'optgroup'	=> 'LLL:task.search.label',
			'widget'	=> 'select',
			'wConf' => array(
				'multiple'	=> true,
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getActivityOptions',
				'negation'	=> 'default'
			)
		),






		/**
		 * OptGroup project
		 */
		'project' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_project',
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
		'projectstatus' => array(
			'funcRef'		=> 'TodoyuProjectTaskFilter::Filter_projectstatus',
			'label'			=> 'LLL:projectFilter.project.status',
			'optgroup'		=> 'LLL:project.search.label',
				'widget'	=> 'select',
			'wConf'			=> array(
				'multiple'		=> true,
				'size'			=> 5,
				'FuncRef'		=> 'TodoyuProjectFilterDataSource::getStatusOptions',
				'negation'		=> 'default'
			)
		),
		'projectrole' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_projectrole',
			'label'		=> 'LLL:projectFilter.project.projectrole',
			'optgroup'	=> 'LLL:project.search.label',
			'widget'	=> 'projectrole',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuContactPersonFilterDataSource::autocompletePersons',
				'multiple'		=> true,
				'size'			=> 5,
				'negation'		=> 'default'
			)
		),
		'customer'  => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_company',
			'label'		=> 'LLL:projectFilter.project.company',
			'optgroup'	=> 'LLL:project.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuContactCompanyFilterDataSource::autocompleteCompanies',
				'FuncParams'	=> array(),
				'LabelFuncRef'	=> 'TodoyuContactCompanyFilterDataSource::getCompanyLabel',
				'negation'		=> 'default'
			)
		),






		/**
		 * OptGroup time management
		 */
		'deadlinedate'		=> array(
			'funcRef' 	=> 'TodoyuProjectTaskFilter::Filter_deadlinedate',
			'label'		=> 'LLL:projectFilter.task.deadlinedate',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'deadlinedateDyn'	=> array(
			'funcRef' 	=> 'TodoyuProjectTaskFilter::Filter_deadlinedateDyn',
			'label'		=> 'LLL:projectFilter.task.deadlinedateDyn',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'		=> 'TodoyuProjectTaskFilterDataSource::getDynamicDateOptions',
				'negation'		=> 'datetime'
			)
		),
		'startdate'		=> array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_startdate',
			'label'		=> 'LLL:projectFilter.task.startdate',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'startdateDyn'	=> array(
			'funcRef' 	=> 'TodoyuProjectTaskFilter::Filter_startdateDyn',
			'label'		=> 'LLL:projectFilter.task.startdateDyn',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getDynamicDateOptions',
				'negation'		=> 'datetime'
			)
		),
		'enddate'		=> array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_enddate',
			'label'		=> 'LLL:projectFilter.task.enddate',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'enddateDyn'	=> array(
			'funcRef' 	=> 'TodoyuProjectTaskFilter::Filter_enddateDyn',
			'label'		=> 'LLL:projectFilter.task.enddateDyn',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'		=> 'TodoyuProjectTaskFilterDataSource::getDynamicDateOptions',
				'negation'		=> 'datetime'
			)
		),
		'createdate' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_createdate',
			'label'		=> 'LLL:projectFilter.task.createdate',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'createdateDyn' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_createdateDyn',
			'label'		=> 'LLL:projectFilter.task.createdateDyn',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getDynamicDateOptions',
				'negation'		=> 'datetime'
			)
		),
		'editdate' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_editdate',
			'label'		=> 'LLL:projectFilter.task.editdate',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'editdateDyn' => array(
			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_editdateDyn',
			'label'		=> 'LLL:projectFilter.task.editdateDyn',
			'optgroup'	=> 'LLL:projectFilter.task.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getDynamicDateOptions',
				'negation'		=> 'datetime'
			)
		),



		/**
		 * OptGroup filter
		 */
		'filterSet' => array(
			'funcRef'	=> 'TodoyuSearchFiltersetManager::Filter_filterSet',
			'label'		=> 'LLL:search.filterlabel.filterset',
			'optgroup'	=> 'LLL:core.filter',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 5,
				'FuncRef'	=> 'TodoyuSearchFiltersetManager::getFilterSetSelectionOptions'
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
		'resultsRenderer'	=> 'TodoyuProjectProjectRenderer::renderProjectListing',
		'class'				=> 'TodoyuProjectProjectFilter',
		'require'			=> 'project.general:use'
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
			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_company',
			'label'		=> 'LLL:projectFilter.project.company',
			'optgroup'	=> 'LLL:project.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuContactCompanyFilterDataSource::autocompleteCompanies',
				'FuncParams'	=> array(),
				'LabelFuncRef'	=> 'TodoyuContactCompanyFilterDataSource::getCompanyLabel',
				'negation'		=> 'default'
			)
		),
		'projectrole' => array(
			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_projectrole',
			'label'		=> 'LLL:projectFilter.project.projectrole',
			'optgroup'	=> 'LLL:project.search.label',
			'widget'	=> 'projectrole',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuContactPersonFilterDataSource::autocompletePersons',
				'multiple'		=> true,
				'size'			=> 5,
				'negation'		=> 'default'
			)
		),
//		'locked' => array(
//			  'funcRef'		=> 'TodoyuProjectProjectFilter::filter_locked',
//			  'label'		=> 'LLL:core.locked',
//			  'optgroup'	=> 'LLL:project.search.label',
//			  'widget'		=> 'checkbox',
//		),



		/**
		 * OptGroup time management
		 */
		'startdate' => array(
			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_startdate',
			'label'		=> 'LLL:projectFilter.project.date_start',
			'optgroup'	=> 'LLL:projectFilter.project.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'enddate' => array(
			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_enddate',
			'label'		=> 'LLL:projectFilter.project.date_end',
			'optgroup'	=> 'LLL:projectFilter.project.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'deadline' => array(
			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_deadline',
			'label'		=> 'LLL:projectFilter.project.deadline',
			'optgroup'	=> 'LLL:projectFilter.project.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),

		'createdate' => array(
			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_createdate',
			'label'		=> 'LLL:projectFilter.project.createdate',
			'optgroup'	=> 'LLL:projectFilter.project.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'createdateDyn' => array(
			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_createdateDyn',
			'label'		=> 'LLL:projectFilter.project.createdateDyn',
			'optgroup'	=> 'LLL:projectFilter.project.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuProjectProjectFilterDataSource::getDynamicDateOptions',
				'negation'		=> 'datetime'
			)
		),
		'editdate' => array(
			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_editdate',
			'label'		=> 'LLL:projectFilter.project.editdate',
			'optgroup'	=> 'LLL:projectFilter.project.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'editdateDyn' => array(
			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_editdateDyn',
			'label'		=> 'LLL:projectFilter.project.editdateDyn',
			'optgroup'	=> 'LLL:projectFilter.project.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuProjectProjectFilterDataSource::getDynamicDateOptions',
				'negation'		=> 'datetime'
			)
		),
	)
);
?>