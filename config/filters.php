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
 * Filter configuration for project extension
 *
 * @package		Todoyu
 * @subpackage	Project
 */

$CONFIG['FILTERS']['TASK'] = array(
	'config' => array(
		'label'				=> 'LLL:task.search.label',
		'RenderFunction'	=> 'TodoyuTaskSearchRenderer::renderResults',
		'assets'			=> array(
			'ext'	=> 'project',
			'type'	=> 'public'
		),
		'filterWidgets' => array(
			/**
			 * optgroup task
			 */
			'status' => array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_status',
				'label'		=> 'LLL:projectFilter.filterlabel.task.status',
				'optgroup'	=> 'LLL:task.search.label',
				'widget'	=> 'select',
				'wConf'		=> array(
				    'multiple'	=> true,
				    'size'		=> 5,
				    'FuncRef'	=> 'TodoyuTaskFilterDataSource::getStatusOptions',
				    'negation'	=> array(
				    		'labelTrue'		=> 'LLL:search.negation.default.true',
				    		'labelFalse'	=> 'LLL:search.negation.default.false',
				    )
		  		)
			),
			'creator' => array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_userCreate',
				'label'		=> 'LLL:projectFilter.filterlabel.task.creator',
				'optgroup'	=> 'LLL:task.search.label',
				'widget'	=> 'textinput',
				'wConf' => array(
					'autocomplete'	=> true,
					'FuncRef'		=> 'TodoyuUserFilterDataSource::autocompleteUsers',
					'FuncParams'	=> array(),
					'LabelFuncRef'	=> 'TodoyuUserFilterDataSource::getLabel',
					'negation'	=> array(
				    		'labelTrue'		=> 'LLL:search.negation.default.true',
				    		'labelFalse'	=> 'LLL:search.negation.default.false',
				    )
				)
			),
			'currentUserIsUserCreate' => array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_currentUserIsUserCreate',
				'label'		=> 'LLL:projectFilter.filterlabel.task.currentUserIsUserCreate',
				'optgroup'	=> 'LLL:task.search.label',
				'widget'	=> 'checkbox',
				'wConf'   => array(
		      		'checked' => true
		    	)
			),
			'userHasAcknowledged' => array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_acknowledged',
				'label'		=> 'LLL:projectFilter.filterlabel.task.acknowledged',
				'optgroup'	=> 'LLL:task.search.label',
				'widget'	=> 'textinput',
				'wConf' => array(
					'autocomplete'	=> true,
					'FuncRef'		=> 'TodoyuUserFilterDataSource::autocompleteUsers',
					'FuncParams'	=> array(),
					'LabelFuncRef'	=> 'TodoyuUserFilterDataSource::getLabel',
					'negation'	=> array(
				    		'labelTrue'		=> 'LLL:search.negation.default.true',
				    		'labelFalse'	=> 'LLL:search.negation.default.false',
				    )
				)
			),
			'currentUserHasAcknowledged' => array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_currentUserHasAcknowledged',
				'label'		=> 'LLL:projectFilter.filterlabel.task.currentUserHasAcknowledged',
				'optgroup'		=> 'LLL:task.search.label',
				'widget'	=> 'checkbox',
				'wConf'   => array(
		      		'checked' => true
		    	)
			),
			'userIsAssigned' => array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_userAssigned',
				'label'		=> 'LLL:projectFilter.filterlabel.task.userAssigned',
				'optgroup'	=> 'LLL:task.search.label',
				'widget'	=> 'textinput',
				'wConf' => array(
					'autocomplete'	=> true,
					'FuncRef'		=> 'TodoyuUserFilterDataSource::autocompleteUsers',
					'FuncParams'	=> array(),
					'LabelFuncRef'	=> 'TodoyuUserFilterDataSource::getLabel',
					'negation'	=> array(
				    		'labelTrue'		=> 'LLL:search.negation.default.true',
				    		'labelFalse'	=> 'LLL:search.negation.default.false',
				    )
				)
			),
			'currentUserIsAssigned' => array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_currentUserIsAssigned',
				'label'		=> 'LLL:projectFilter.filterlabel.task.currentUserAssigned',
				'optgroup'		=> 'LLL:task.search.label',
				'widget'	=> 'checkbox',
				'wConf'   => array(
		      		'checked' => true
		    	)
			),
			'owner' => array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_owner',
				'label'		=> 'LLL:projectFilter.filterlabel.task.owner',
				'optgroup'	=> 'LLL:task.search.label',
				'widget'	=> 'textinput',
				'wConf' => array(
					'autocomplete'	=> true,
					'FuncRef'		=> 'TodoyuUserFilterDataSource::autocompleteUsers',
					'FuncParams'	=> array(),
					'LabelFuncRef'	=> 'TodoyuUserFilterDataSource::getLabel',
					'negation'	=> array(
				    		'labelTrue'		=> 'LLL:search.negation.default.true',
				    		'labelFalse'	=> 'LLL:search.negation.default.false',
				    )
				)
			),
			'currentUserIsOwner' => array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_currentUserIsOwner',
				'label'		=> 'LLL:projectFilter.filterlabel.task.currentUserIsOwner',
				'optgroup'	=> 'LLL:task.search.label',
				'widget'	=> 'checkbox',
				'wConf'   => array(
		      		'checked' => true
		    	)
			),
			'isPublic'	=> array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_isPublic',
				'label'		=> 'LLL:projectFilter.filterlabel.task.isPublic',
				'optgroup'	=> 'LLL:task.search.label',
				'widget'	=> 'checkbox',
				'wConf'		=> array(
					'checked'	=> true
				)
			),
			/**
			 * optgroup timemanagement
			 */
			'deadline'		=> array(
				'funcRef' 	=> 'TodoyuTaskFilter::Filter_deadline',
				'label'		=> 'LLL:projectFilter.filterlabel.task.deadline',
				'optgroup'	=> 'LLL:projectFilter.filterLabel.task.timemanagement.label',
				'widget'	=> 'dateinput',
				'wConf'		=> array(
					'negation'	=> array(
				    		'labelTrue'		=> 'LLL:search.negation.datetime.true',
				    		'labelFalse'	=> 'LLL:search.negation.datetime.false',
				    )
				)
			),
			'deadlineDyn'	=> array(
				'funcRef' 	=> 'TodoyuTaskFilter::Filter_deadlineDyn',
				'label'		=> 'LLL:projectFilter.filterlabel.task.deadlineDyn',
				'optgroup'	=> 'LLL:projectFilter.filterLabel.task.timemanagement.label',
				'widget'	=> 'select',
				'wConf'		=> array(
				    'FuncRef'	=> 'TodoyuTaskFilterDataSource::getDynamicDateinput'
				)
			),
			'startdate'		=> array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_startdate',
				'label'		=> 'LLL:projectFilter.filterlabel.task.startdate',
				'optgroup'	=> 'LLL:projectFilter.filterLabel.task.timemanagement.label',
				'widget'	=> 'dateinput',
				'wConf'		=> array(
					'negation'	=> array(
				    		'labelTrue'		=> 'LLL:search.negation.datetime.true',
				    		'labelFalse'	=> 'LLL:search.negation.datetime.false',
				    )
				)
			),
			'startdateDyn'	=> array(
				'funcRef' 	=> 'TodoyuTaskFilter::Filter_startdateDyn',
				'label'		=> 'LLL:projectFilter.filterlabel.task.startdateDyn',
				'optgroup'	=> 'LLL:projectFilter.filterLabel.task.timemanagement.label',
				'widget'	=> 'select',
				'wConf'		=> array(
				    'FuncRef'	=> 'TodoyuTaskFilterDataSource::getDynamicDateinput'
				)
			),
			'enddate'		=> array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_enddate',
				'label'		=> 'LLL:projectFilter.filterlabel.task.enddate',
				'optgroup'	=> 'LLL:projectFilter.filterLabel.task.timemanagement.label',
				'widget'	=> 'dateinput',
				'wConf'		=> array(
					'negation'	=> array(
				    		'labelTrue'		=> 'LLL:search.negation.datetime.true',
				    		'labelFalse'	=> 'LLL:search.negation.datetime.false',
				    )
				)
			),
			'enddateDyn'	=> array(
				'funcRef' 	=> 'TodoyuTaskFilter::Filter_enddateDyn',
				'label'		=> 'LLL:projectFilter.filterlabel.task.enddateDyn',
				'optgroup'	=> 'LLL:projectFilter.filterLabel.task.timemanagement.label',
				'widget'	=> 'select',
				'wConf'		=> array(
				    'FuncRef'	=> 'TodoyuTaskFilterDataSource::getDynamicDateinput'
				)
			),
			'finishdate'	=> array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_finishdate',
				'label'		=> 'LLL:projectFilter.filterLabel.task.finishdate',
				'optgroup'	=> 'LLL:projectFilter.filterLabel.task.timemanagement.label',
				'widget'	=> 'dateinput',
				'wConf'		=> array(
					'negation'	=> array(
				    		'labelTrue'		=> 'LLL:search.negation.datetime.true',
				    		'labelFalse'	=> 'LLL:search.negation.datetime.false',
				    )
				)
			),
			/**
			 * Optgroup project
			 */
			'project' => array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_project',
				'label'		=> 'LLL:projectFilter.filterlabel.project',
				'optgroup'	=> 'LLL:project.search.label',
		    	'widget'	=> 'textinput',
				'wConf' => array(
					'autocomplete'	=> true,
					'FuncRef'=> 'TodoyuProjectFilterDataSource::autocompleteProjects',
					'FuncParams' => array(),
					'LabelFuncRef' => 'TodoyuProjectFilterDataSource::getLabel',
					'negation'	=> array(
				    		'labelTrue'		=> 'LLL:search.negation.default.true',
				    		'labelFalse'	=> 'LLL:search.negation.default.false',
				    )
				)
			),
			/**
			 * Optgroup filter
			 */
			'filterSet' => array(
				'funcRef'	=> 'TodoyuFiltersetManager::Filter_filterSet',
				'label'		=> 'LLL:search.filterlabel.filterset',
				'optgroup'	=> 'LLL:search.filter.optgroup',
				'widget'	=> 'select',
				'wConf'		=> array(
					'size'		=> 5,
				    'FuncRef'	=> 'TodoyuFiltersetManager::getFilterSetSelectionOptions'
				)
			),


			/*'parentTask' => array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_parentTask'
			),*/


			'fulltext' => array(
				'funcRef'	=> 'TodoyuTaskFilter::Filter_fulltext',
				'label'		=> 'LLL:projectFilter.filterlabel.task.fulltext',
				'optgroup'	=> 'LLL:task.search.label',
		    	'widget'	=> 'textinput',
				'wConf' => array(
					'LabelFuncRef' => 'TodoyuProjectFilterDataSource::getLabel',
					'negation'	=> array(
				    		'labelTrue'		=> 'LLL:search.negation.default.true',
				    		'labelFalse'	=> 'LLL:search.negation.default.false',
				    )
				)

			)
		)
	)
);


$CONFIG['FILTERS']['PROJECT'] = array(
	'config' => array(
		'label'				=> 'LLL:project.search.label',
		'RenderFunction'	=> 'TodoyuProjectSearchRenderer::renderSearchResults',
		'assets'			=> array(
			'ext'	=> 'project',
			'type'	=> 'public'
		),
		'filterWidgets' => array(
			'fulltext' => array(
				'funcRef'	=> 'TodoyuProjectFilter::filter_fulltext',
				'label'		=> 'LLL:projectFilter.filterlabel.project.fulltext',
				'optgroup'	=> 'LLL:project.search.label',
				'widget'	=> 'textinput',
			),
			'status' => array(
				'funcRef'	=> 'TodoyuProjectFilter::filter_status',
				'label'		=> 'LLL:projectFilter.filterlabel.project.status',
				'optgroup'	=> 'LLL:project.search.label',
				'widget'	=> 'select',
				'wConf'		=> array(
				    'multiple'	=> true,
				    'size'		=> 5,
				    'FuncRef'	=> 'TodoyuTaskFilterDataSource::getStatusOptions'
		  		)
			),
			'title' => array(
				'funcRef'	=> 'TodoyuProjectFilter::filter_title',
				'label'		=> 'LLL:projectFilter.filterlabel.project.title',
				'optgroup'	=> 'LLL:project.search.label',
				'widget'	=> 'textinput',
			),
			'customer' => array(
				'funcRef'	=> 'TodoyuProjectFilter::filter_customer',
				'label'		=> 'LLL:projectFilter.filterlabel.project.customer',
				'optgroup'	=> 'LLL:project.search.label',
				'widget'	=> 'textinput',
				'wConf' => array(
					'autocomplete'	=> true,
					'FuncRef'		=> 'TodoyuUserFilterDataSource::autocompleteCustomers',
					'FuncParams'	=> array(),
					'LabelFuncRef'	=> 'TodoyuUserFilterDataSource::getCustomerLabel'
				)
			),
			'projectleader' => array(
				'funcRef'	=> 'TodoyuProjectFilter::filter_projectleader',
				'label'		=> 'LLL:projectFilter.filterlabel.project.projectleader',
				'optgroup'	=> 'LLL:project.search.label',
				'widget'	=> 'textinput',
				'wConf' => array(
					'autocomplete'	=> true,
					'FuncRef'		=> 'TodoyuUserFilterDataSource::autocompleteUsers',
					'FuncParams'	=> array(),
					'LabelFuncRef'	=> 'TodoyuUserFilterDataSource::getLabel'
				)
			),
			'projectsupervisor' => array(
				'funcRef'	=> 'TodoyuProjectFilter::filter_projectsupervisor',
				'label'		=> 'LLL:projectFilter.filterlabel.project.projectsupervisor',
				'optgroup'	=> 'LLL:project.search.label',
				'widget'	=> 'textinput',
				'wConf' => array(
					'autocomplete'	=> true,
					'FuncRef'		=> 'TodoyuUserFilterDataSource::autocompleteUsers',
					'FuncParams'	=> array(),
					'LabelFuncRef'	=> 'TodoyuUserFilterDataSource::getLabel'
				)
			),
			'fixproject' => array(
				'funcRef'	=> 'TodoyuProjectFilter::filter_isfixed',
				'label'		=> 'LLL:projectFilter.filterlabel.project.fixproject',
				'optgroup'	=> 'LLL:project.search.label',
				'widget'	=> 'checkbox',
			),
		)
	)
);
?>