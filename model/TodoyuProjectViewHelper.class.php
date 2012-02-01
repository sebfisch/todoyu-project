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
 * General project view helper
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectViewHelper {

	/**
	 * Get task preset options
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getFallbackTaskPresetOptions(TodoyuFormElement $field) {
		$presets= TodoyuProjectTaskPresetManager::getAllTaskPresets();
		$reform	= array(
			'id'	=> 'value',
			'title'	=> 'label'
		);

		return TodoyuArray::reform($presets, $reform);
	}



	/**
	 * Add custom "Please select" label
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 * @todo	Use TodoyuArray::prependSelectOption() as soon as this extension requires core 2.2
	 */
	public static function getPresetOwnerOptions(TodoyuFormElement $field) {
		$options	= TodoyuContactViewHelper::getInternalPersonOptions($field);
		$select		= array(
			'value'		=> 0,
			'label'		=> 'project.ext.taskpreset.pleaseSelectOwner'
		);
		$separator	= array(
			'value'		=> 0,
			'label'		=> '---------------------------',
			'disabled'	=> true
		);

		array_unshift($options, $select, $separator);

		return $options;
	}

}

?>