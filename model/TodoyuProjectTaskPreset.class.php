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
 * Task preset
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectTaskPreset extends TodoyuBaseObject {

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
	 * @return	String
	 */
	public function getTitle() {
		return $this->get('title');
	}



	/**
	 * Get task title
	 *
	 * @return	String
	 */
	public function getTaskTitle() {
		return trim($this->get('tasktitle'));
	}



	/**
	 * Check whether task title is set in preset
	 *
	 * @return	Boolean
	 */
	public function hasTaskTitle() {
		return $this->getTaskTitle() !== '';
	}



	/**
	 * Get description
	 *
	 * @return	String
	 */
	public function getDescription() {
		return trim($this->get('description'));
	}
	


	/**
	 * Check whether description is set in preset
	 *
	 * @return	Boolean
	 */
	public function hasDescription() {
		return $this->getDescription() !== '' && $this->getDescription() !== '<p></p>';
	}



	/**
	 * Get estimated workload
	 *
	 * @return	Integer
	 */
	public function getEstimatedWorkload() {
		return intval($this->get('estimated_workload'));
	}



	/**
	 * Check whether estimated workload is set
	 *
	 * @return	Boolean
	 */
	public function hasEstimatedWorkload() {
		return $this->getEstimatedWorkload() !== 0;
	}



	/**
	 * Get label for estimated workload
	 *
	 * @return	String
	 */
	public function getEstimatedWorkloadLabel() {
		return TodoyuTime::sec2hour($this->getEstimatedWorkload()) . ' ' . Todoyu::Label('core.date.time.hours');
	}



	/**
	 * Get is public flag
	 *
	 * @return	Integer
	 */
	public function getIsPublic() {
		return intval($this->get('is_public'));
	}



	/**
	 * Get label for is public
	 *
	 * @return	String
	 */
	public function getIsPublicLabel() {
		$label	= $this->getIsPublic() ? 'core.global.public' : 'core.global.notpublic';

		return Todoyu::Label($label);
	}



	/**
	 * Get activity ID
	 *
	 * @return	Integer
	 */
	public function getActivityID() {
		return intval($this->get('id_activity'));
	}



	/**
	 * Check whether preset has set an activity
	 *
	 * @return	Boolean
	 */
	public function hasActivity() {
		return $this->getActivityID() !== 0;
	}



	/**
	 * Get activity
	 *
	 * @return	TodoyuProjectActivity
	 */
	public function getActivity() {
		return TodoyuProjectActivityManager::getActivity($this->getActivityID());
	}



	/**
	 * Get status
	 *
	 * @return	Integer
	 */
	public function getStatus() {
		return intval($this->get('status'));
	}



	/**
	 * Check whether a status is set in preset
	 *
	 * @return	Boolean
	 */
	public function hasStatus() {
		return $this->getStatus() !== 0;
	}



	/**
	 * Get label for status
	 *
	 * @return	String
	 */
	public function getStatusLabel() {
		return TodoyuProjectTaskStatusManager::getStatusLabel($this->getStatus());
	}



	/**
	 * Get start date based on dynamic config
	 *
	 * @return	Integer
	 */
	public function getDateStart() {
		return TodoyuProjectTaskManager::getDateFromConfigValue($this->getDateStartKey());
	}



	/**
	 * Get date start key
	 *
	 * @return	Integer
	 */
	public function getDateStartKey() {
		return intval($this->get('date_start'));
	}



	/**
	 * Check whether the start date is set in preset
	 *
	 * @return	Boolean
	 */
	public function hasDateStart() {
		return $this->get('date_start') != 0;
	}



	/**
	 * Get config label for date start
	 *
	 * @return	String
	 */
	public function getDateStartLabel() {
		return TodoyuProjectExtConfViewHelper::getValueDateLabel($this->getDateStartKey());
	}



	/**
	 * Get end date based on dynamic config
	 *
	 * @return	Integer
	 */
	public function getDateEnd() {
		return TodoyuProjectTaskManager::getDateFromConfigValue($this->getDateEndKey());
	}



	/**
	 * Get date end key
	 *
	 * @return	Integer
	 */
	public function getDateEndKey() {
		return intval($this->get('date_end'));
	}



	/**
	 * Check whether the end date is set in preset
	 *
	 * @return	Boolean
	 */
	public function hasDateEnd() {
		return $this->get('date_end') != 0;
	}



	/**
	 * Get config label for date end
	 *
	 * @return	String
	 */
	public function getDateEndLabel() {
		return TodoyuProjectExtConfViewHelper::getValueDateLabel($this->getDateEndKey());
	}



	/**
	 * Get deadline date based on dynamic config
	 *
	 * @return	Integer
	 */
	public function getDateDeadline() {
		return TodoyuProjectTaskManager::getDateFromConfigValue($this->getDateDeadlineKey());
	}



	/**
	 * Get date deadline key
	 *
	 * @return	Integer
	 */
	public function getDateDeadlineKey() {
		return intval($this->get('date_deadline'));
	}



	/**
	 * Check whether the deadline date is set in preset
	 *
	 * @return	Boolean
	 */
	public function hasDateDeadline() {
		return $this->get('date_deadline') != 0;
	}



	/**
	 * Get config label for date end
	 *
	 * @return	String
	 */
	public function getDateDeadlineLabel() {
		return TodoyuProjectExtConfViewHelper::getValueDateLabel($this->getDateDeadlineKey());
	}



	/**
	 * Get ID of assigned person
	 *
	 * @return	Integer
	 */
	public function getPersonAssignedID() {
		return intval($this->get('id_person_assigned'));
	}



	/**
	 * Check whether an assigned person is set in preset
	 *
	 * @return	Boolean
	 */
	public function hasPersonAssigned() {
		return $this->getPersonAssignedID() !== 0;
	}



	/**
	 * Get assigned person
	 *
	 * @return	TodoyuContactPerson
	 */
	public function getPersonAssigned() {
		return TodoyuContactPersonManager::getPerson($this->getPersonAssignedID());
	}



	/**
	 * Get ID of owner person
	 *
	 * @return	Integer
	 */
	public function getPersonOwnerID() {
		return intval($this->get('id_person_owner'));
	}



	/**
	 * Check whether an owner person is set in preset
	 *
	 * @return	Boolean
	 */
	public function hasPersonOwner() {
		return $this->getPersonOwnerID() !== 0;
	}



	/**
	 * Get owner person
	 *
	 * @return	TodoyuContactPerson
	 */
	public function getPersonOwner() {
		return TodoyuContactPersonManager::getPerson($this->getPersonOwnerID());
	}



	/**
	 * Get ID of assigned person fallback
	 *
	 * @return	Integer
	 */
	public function getPersonAssignedFallbackID() {
		return intval($this->get('id_person_assigned_fallback'));
	}



	/**
	 * Check whether an assigned person fallback is set in preset
	 *
	 * @return	Boolean
	 */
	public function hasPersonAssignedFallback() {
		return $this->getPersonAssignedFallbackID() !== 0;
	}



	/**
	 * Get assigned person fallback
	 *
	 * @return	TodoyuContactPerson
	 */
	public function getPersonAssignedFallback() {
		return TodoyuContactPersonManager::getPerson($this->getPersonAssignedFallbackID());
	}



	/**
	 * Get ID of owner person fallback
	 *
	 * @return	Integer
	 */
	public function getPersonOwnerFallbackID() {
		return intval($this->get('id_person_owner_fallback'));
	}



	/**
	 * Check whether an owner person fallback is set in preset
	 *
	 * @return	Boolean
	 */
	public function hasPersonOwnerFallback() {
		return $this->getPersonOwnerFallbackID() !== 0;
	}



	/**
	 * Get owner person fallback
	 *
	 * @return	TodoyuContactPerson
	 */
	public function getPersonOwnerFallback() {
		return TodoyuContactPersonManager::getPerson($this->getPersonOwnerFallbackID());
	}



	/**
	 * Get ID of assigned role fallback
	 *
	 * @return	Integer
	 */
	public function getRoleAssignedFallbackID() {
		return intval($this->get('id_role_assigned_fallback'));
	}



	/**
	 * Check whether an assigned role fallback is set in preset
	 *
	 * @return	Boolean
	 */
	public function hasRoleAssignedFallback() {
		return $this->getRoleAssignedFallbackID() !== 0;
	}



	/**
	 * Get assigned role fallback
	 *
	 * @return	TodoyuProjectProjectrole
	 */
	public function getRoleAssignedFallback() {
		return TodoyuProjectProjectroleManager::getProjectrole($this->getRoleAssignedFallbackID());
	}



	/**
	 * Get ID of owner role fallback
	 *
	 * @return	Integer
	 */
	public function getRoleOwnerFallbackID() {
		return intval($this->get('id_role_owner_fallback'));
	}



	/**
	 * Check whether an owner role fallback is set in preset
	 *
	 * @return	Boolean
	 */
	public function hasRoleOwnerFallback() {
		return $this->getRoleOwnerFallbackID() !== 0;
	}



	/**
	 * Get owner role fallback
	 *
	 * @return	TodoyuProjectProjectrole
	 */
	public function getRoleOwnerFallback() {
		return TodoyuProjectProjectroleManager::getProjectrole($this->getRoleOwnerFallbackID());
	}



	/**
	 * Get quicktask duration days
	 *
	 * @return	Integer
	 */
	public function getQuickTaskDurationDays() {
		return intval($this->get('quicktask_duration_days'));
	}



	/**
	 * Check whether quicktask duration days are set in preset
	 *
	 * @return	Boolean
	 */
	public function hasQuickTaskDurationDays() {
		return $this->getQuickTaskDurationDays() !== 0;
	}



	/**
	 * Get preset data for new tasks
	 *
	 * @return	Array
	 */
	public function getPresetData() {
		$data['title']				= $this->getTaskTitle();
		$data['description']		= $this->getDescription();
		$data['estimated_workload']	= $this->getEstimatedWorkload();
		$data['is_public']			= $this->getIsPublic();
		$data['id_activity']		= $this->getActivityID();
		$data['status']				= $this->getStatus();

		if( $this->hasDateStart() ) {
			$data['date_start'] = $this->getDateStart();
		}
		if( $this->hasDateEnd() ) {
			$data['date_end'] = $this->getDateEnd();
		}
		if( $this->hasDateDeadline() ) {
			$data['date_deadline'] = $this->getDateDeadline();
		}
		if( $this->hasPersonAssigned() ) {
			$data['id_person_assigned'] = $this->getPersonAssignedID();
		}
		if( $this->hasPersonOwner() ) {
			$data['id_person_owner'] = $this->getPersonOwnerID();
		}

		return $data;
	}

}

?>