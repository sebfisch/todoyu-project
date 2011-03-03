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
		'label'				=> 'LLL:project.task.search.label',
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
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_status',
			'label'		=> 'LLL:core.global.status',
			'optgroup'	=> 'LLL:project.task.search.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 8,
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getStatusOptions',
				'negation'	=> 'default'
			)
		),
		'creatorPerson' => array(
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_creatorPerson',
			'label'		=> 'LLL:project.filter.task.creatorPerson',
			'optgroup'	=> 'LLL:project.task.search.label',
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
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_creatorRoles',
			'label'		=> 'LLL:project.filter.task.creatorRoles',
			'optgroup'	=> 'LLL:project.task.search.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 5,
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getProjectroleOptionDefinitions',
				'negation'	=> 'default'
			)
		),
		'assignedPerson' => array(
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_assignedPerson',
			'label'		=> 'LLL:project.filter.task.assignedPerson',
			'optgroup'	=> 'LLL:project.task.search.label',
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
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_assignedRoles',
			'label'		=> 'LLL:project.filter.task.assignedRoles',
			'optgroup'	=> 'LLL:project.task.search.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 5,
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getProjectroleOptionDefinitions',
				'negation'	=> 'default'
			)
		),
		'ownerPerson' => array(
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_ownerPerson',
			'label'		=> 'LLL:project.filter.task.ownerPerson',
			'optgroup'	=> 'LLL:project.task.search.label',
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
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_ownerRoles',
			'label'		=> 'LLL:project.filter.task.ownerRoles',
			'optgroup'	=> 'LLL:project.task.search.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 5,
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getProjectroleOptionDefinitions',
				'negation'	=> 'default'
			)
		),
		'acknowledged' => array(
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_acknowledged',
			'label'		=> 'LLL:project.filter.task.acknowledged',
			'optgroup'	=> 'LLL:project.task.search.label',
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
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_isPublic',
			'label'		=> 'LLL:project.filter.task.isPublic',
			'optgroup'	=> 'LLL:project.task.search.label',
			'widget'	=> 'checkbox',
			'wConf'		=> array(
				'checked'	=> true
			)
		),
		'title' => array(
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_title',
			'label'		=> 'LLL:project.filter.task.title',
			'optgroup'	=> 'LLL:project.task.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'LabelFuncRef'	=> 'TodoyuProjectProjectFilterDataSource::getLabel',
				'negation'		=> 'default'
			)
		),
		'fulltext' => array(
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_fulltext',
			'label'		=> 'LLL:project.filter.task.fulltext',
			'optgroup'	=> 'LLL:project.task.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'LabelFuncRef'	=> 'TodoyuProjectProjectFilterDataSource::getLabel',
				'negation'		=> 'default'
			)
		),
		'type' => array(
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_type',
			'label'		=> 'LLL:project.filter.task.type',
			'optgroup'	=> 'LLL:project.task.search.label',
			'widget'	=> 'select',
			'wConf' => array(
				'multiple'	=> false,
				'size'		=> 2,
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getTypeOptions',
				'negation'	=> 'default'
			)
		),
		'activity' => array(
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_activity',
			'label'		=> 'LLL:project.filter.task.activity',
			'optgroup'	=> 'LLL:project.task.search.label',
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
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_project',
			'label'		=> 'LLL:project.filter.project',
			'optgroup'	=> 'LLL:project.ext.search.label',
			'widget'	=> 'text',
			'wConf' => array(
				'autocomplete'	=> true,
				'FuncRef'		=> 'TodoyuProjectProjectFilterDataSource::autocompleteProjects',
				'FuncParams'	=> array(),
				'LabelFuncRef'	=> 'TodoyuProjectProjectFilterDataSource::getLabel',
				'negation'		=> 'default'
			)
		),
		'projectstatus' => array(
//			'funcRef'		=> 'TodoyuProjectTaskFilter::Filter_projectstatus',
			'label'			=> 'LLL:project.filter.project.status',
			'optgroup'		=> 'LLL:project.ext.search.label',
				'widget'	=> 'select',
			'wConf'			=> array(
				'multiple'		=> true,
				'size'			=> 5,
				'FuncRef'		=> 'TodoyuProjectProjectFilterDataSource::getStatusOptions',
				'negation'		=> 'default'
			)
		),
		'projectrole' => array(
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_projectrole',
			'label'		=> 'LLL:project.filter.project.projectrole',
			'optgroup'	=> 'LLL:project.ext.search.label',
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
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_company',
			'label'		=> 'LLL:project.filter.project.company',
			'optgroup'	=> 'LLL:project.ext.search.label',
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
//			'funcRef' 	=> 'TodoyuProjectTaskFilter::Filter_deadlinedate',
			'label'		=> 'LLL:project.filter.task.deadlinedate',
			'optgroup'	=> 'LLL:project.filter.task.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'deadlinedateDyn'	=> array(
//			'funcRef' 	=> 'TodoyuProjectTaskFilter::Filter_deadlinedateDyn',
			'label'		=> 'LLL:project.filter.task.deadlinedateDyn',
			'optgroup'	=> 'LLL:project.filter.task.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'		=> 'TodoyuProjectTaskFilterDataSource::getDynamicDateOptions',
				'negation'		=> 'datetime'
			)
		),
		'startdate'		=> array(
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_startdate',
			'label'		=> 'LLL:project.filter.task.startdate',
			'optgroup'	=> 'LLL:project.filter.task.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'startdateDyn'	=> array(
//			'funcRef' 	=> 'TodoyuProjectTaskFilter::Filter_startdateDyn',
			'label'		=> 'LLL:project.filter.task.startdateDyn',
			'optgroup'	=> 'LLL:project.filter.task.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getDynamicDateOptions',
				'negation'		=> 'datetime'
			)
		),
		'enddate'		=> array(
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_enddate',
			'label'		=> 'LLL:project.filter.task.enddate',
			'optgroup'	=> 'LLL:project.filter.task.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'enddateDyn'	=> array(
//			'funcRef' 	=> 'TodoyuProjectTaskFilter::Filter_enddateDyn',
			'label'		=> 'LLL:project.filter.task.enddateDyn',
			'optgroup'	=> 'LLL:project.filter.task.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'		=> 'TodoyuProjectTaskFilterDataSource::getDynamicDateOptions',
				'negation'		=> 'datetime'
			)
		),
		'createdate' => array(
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_createdate',
			'label'		=> 'LLL:project.filter.task.createdate',
			'optgroup'	=> 'LLL:project.filter.task.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'createdateDyn' => array(
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_createdateDyn',
			'label'		=> 'LLL:project.filter.task.createdateDyn',
			'optgroup'	=> 'LLL:project.filter.task.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuProjectTaskFilterDataSource::getDynamicDateOptions',
				'negation'		=> 'datetime'
			)
		),
		'editdate' => array(
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_editdate',
			'label'		=> 'LLL:project.filter.task.editdate',
			'optgroup'	=> 'LLL:project.filter.task.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'editdateDyn' => array(
//			'funcRef'	=> 'TodoyuProjectTaskFilter::Filter_editdateDyn',
			'label'		=> 'LLL:project.filter.task.editdateDyn',
			'optgroup'	=> 'LLL:project.filter.task.timemanagement.label',
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
			'label'		=> 'LLL:search.ext.filterlabel.filterset',
			'optgroup'	=> 'LLL:core.global.filter',
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
		'label'				=> 'LLL:project.ext.search.label',
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
//			'funcRef'	=> 'TodoyuProjectProjectFilter::filter_title',
			'label'		=> 'LLL:core.global.title',
			'optgroup'	=> 'LLL:project.ext.search.label',
			'widget'	=> 'text',
			'wConf'		=> array(
				'negation'	=> 'default'
			)
		),
		'fulltext' => array(
//			'funcRef'	=> 'TodoyuProjectProjectFilter::filter_fulltext',
			'label'		=> 'LLL:project.filter.project.fulltext',
			'optgroup'	=> 'LLL:project.ext.search.label',
			'widget'	=> 'text',
		),
		'status' => array(
//			'funcRef'	=> 'TodoyuProjectProjectFilter::filter_status',
			'label'		=> 'LLL:core.global.status',
			'optgroup'	=> 'LLL:project.ext.search.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'multiple'	=> true,
				'size'		=> 5,
				'FuncRef'	=> 'TodoyuProjectProjectFilterDataSource::getStatusOptions',
				'negation'	=> 'default'
	  		)
		),
		'company' => array(
//			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_company',
			'label'		=> 'LLL:project.filter.project.company',
			'optgroup'	=> 'LLL:project.ext.search.label',
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
//			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_projectrole',
			'label'		=> 'LLL:project.filter.project.projectrole',
			'optgroup'	=> 'LLL:project.ext.search.label',
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
//			  'label'		=> 'LLL:core.global.locked',
//			  'optgroup'	=> 'LLL:project.ext.search.label',
//			  'widget'		=> 'checkbox',
//		),



		/**
		 * OptGroup time management
		 */
		'startdate' => array(
//			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_startdate',
			'label'		=> 'LLL:project.filter.project.date_start',
			'optgroup'	=> 'LLL:project.filter.project.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'enddate' => array(
//			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_enddate',
			'label'		=> 'LLL:project.filter.project.date_end',
			'optgroup'	=> 'LLL:project.filter.project.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'deadline' => array(
//			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_deadline',
			'label'		=> 'LLL:project.filter.project.deadline',
			'optgroup'	=> 'LLL:project.filter.project.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),

		'createdate' => array(
//			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_createdate',
			'label'		=> 'LLL:project.filter.project.createdate',
			'optgroup'	=> 'LLL:project.filter.project.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'createdateDyn' => array(
//			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_createdateDyn',
			'label'		=> 'LLL:project.filter.project.createdateDyn',
			'optgroup'	=> 'LLL:project.filter.project.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuProjectProjectFilterDataSource::getDynamicDateOptions',
				'negation'		=> 'datetime'
			)
		),
		'editdate' => array(
//			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_editdate',
			'label'		=> 'LLL:project.filter.project.editdate',
			'optgroup'	=> 'LLL:project.filter.project.timemanagement.label',
			'widget'	=> 'date',
			'wConf'		=> array(
				'negation'	=> 'datetime'
			)
		),
		'editdateDyn' => array(
//			'funcRef'	=> 'TodoyuProjectProjectFilter::Filter_editdateDyn',
			'label'		=> 'LLL:project.filter.project.editdateDyn',
			'optgroup'	=> 'LLL:project.filter.project.timemanagement.label',
			'widget'	=> 'select',
			'wConf'		=> array(
				'FuncRef'	=> 'TodoyuProjectProjectFilterDataSource::getDynamicDateOptions',
				'negation'		=> 'datetime'
			)
		),
	)
);
?>