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
 * Project View Helper
 * Helper functions for project rendering
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectViewHelper {

	/**
	 * Get config array for one status option
	 *
	 * @param	Integer		$index
	 * @param	String		$statusKey
	 * @param	String		$label
	 * @return	Array
	 */
	public static function getStatusOption($index, $statusKey, $label) {
		$option = array(
			'index'		=> $index,
			'value'		=> $index,
			'key'		=> $statusKey,
			'class'		=> 'status' . ucwords($statusKey),
			'label'		=> $label
		);

		return $option;
	}



	/**
	 * Get options of project persons (assigned)
	 *
	 * @param	TodoyuFormElement	$formElement
	 * @return	Array
	 */
	public static function getProjectPersonOptions(TodoyuFormElement $formElement) {
		$formData	= $formElement->getForm()->getFormData();
		$idProject	= intval($formData['id_project']);

		$persons= TodoyuProjectManager::getProjectPersons($idProject);
		$options= array();

		foreach($persons as $person) {
			$options[] = array(
				'value'	=> $person['id'],
				'label'	=> TodoyuPersonManager::getLabel($person['id'])
			);
		}

		return $options;
	}



	/**
	 * Get label for person in project
	 *
	 * @param	TodoyuFormElement		$formElement		The form element
	 * @param	Array			$data				Form data for the current record to be labeled
	 * @return	String			Record label
	 */
	public static function getProjectPersonLabel(TodoyuFormElement $formElement, array $data) {
		$idPerson	= intval($data['id']);

		if( $idPerson === 0 ) {
			return TodoyuLanguage::getLabel('project.persons.new');
		} else {
			return $data['firstname'] . ' ' . $data['lastname'] . ' - ' . TodoyuDiv::getLabel($data['rolelabel']);
		}
	}



	/**
	 * Get project status options. Only allowed "changeto" statuses and currently set one are available
	 *
	 * @param TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getProjectStatusOptions(TodoyuFormElement $field) {
		$values		= $field->getValue();
		$idStatus	= intval($values[0]);
		$options	= array();
		$statuses	= TodoyuProjectStatusManager::getStatuses($idStatus);

		foreach($statuses as $statusID => $statusKey) {
			$options[] = array(
				'value'		=> $statusID,
				'label'		=> TodoyuProjectStatusManager::getStatusLabel($statusKey)
			);
		}

		return $options;
	}



	/**
	 * Get projectroles options
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getProjectroleOptions(TodoyuFormElement $field) {
		$roles	= TodoyuProjectroleManager::getProjectroles();
		$reform	= array(
			'id'	=> 'value',
			'title'	=> 'label'
		);

		$roles	= TodoyuArray::reform($roles, $reform);

		return $roles;
	}

}

?>