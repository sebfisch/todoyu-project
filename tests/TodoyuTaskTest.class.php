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
 * Test for: TodoyuTask
 *
 * @package		Todoyu
 * @subpackage	Project
 */

/**
 * Test class for TodoyuPortalManager.
 * Generated by PHPUnit on 2010-03-12 at 16:38:16.
 */
class TodoyuTaskTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var TodoyuPortalManager
	 */
	protected $object;

	/**
	 * @var Array
	 */
	private $array;


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
			// Get ID of some task
		$where		= 'deleted = 0 AND type = ' . TASK_TYPE_TASK;
		$idTestTask	= Todoyu::db()->getFieldValue('id', 'ext_project_task', $where, '', '', '0,1', 'id');

		$this->array	= array(
			'id'	=> $idTestTask
		);

			// Construct
		$this->object = new TodoyuTask($idTestTask);
	}



	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {

	}



	/**
	 *	Test TodoyuTask::__construct
	 */
	public function testConstructor() {
			// Test successfull constructor
		$idTask	= TodoyuArray::getColumn($this->array, 'id');

		$task = new TodoyuTask($idTask);
		$this->assertNotNull($task, 'constructor test');

			// Test construction with task ID (0)
		$task = new TodoyuTask(0);
		$this->assertNotNull($task, 'constructor test without ID');
		$title	= $task->getTitle();
		$this->assertEquals(0, strlen($title), 'task 0 title is empty');
	}



	/**
	 * Test TodoyuTask::testGetTitle().
	 */
	public function testGetTitle() {
		$title	= $this->object->getTitle();

		$this->assertType( 'string', $title );
		$this->assertGreaterThan( 0, strlen($title) );
	}



	/**
	 * Test TodoyuTask::testGetFullTitle().
	 */
	public function testGetFullTitle() {
		$fullTitle	= $this->object->getFullTitle();

		$this->assertType( 'string', $fullTitle );
		$this->assertGreaterThan( 0, strlen($fullTitle) );

			// full title must be longer than title
		$title	= $this->object->getTitle();
		$this->assertGreaterThan( strlen($title), strlen($fullTitle) );
	}



	/**
	 * @todo Implement testGetTaskNumber().
	 */
	public function testGetTaskNumber($full = true) {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testHasStatus().
	 */
	public function testHasStatus() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testGetStatus().
	 */
	public function testGetStatus() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testGetStatusKey().
	 */
	public function testGetStatusKey() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testGetStatusLabel().
	 */
	public function testGetStatusLabel() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testHasParentTask().
	 */
	public function testHasParentTask() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testHasSubTasks().
	 */
	public function testHasSubTasks() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testGetParentTask().
	 */
	public function testGetParentTask() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testGetParentTaskID().
	 */
	public function testGetParentTaskID() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testGetProjectID().
	 */
	public function testGetProjectID() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testGetProjectArray().
	 */
	public function testGetProjectArray() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testGetProject().
	 */
	public function testGetProject() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testGetWorktype().
	 */
	public function testGetWorktype() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testGetType().
	 */
	public function testGetType() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testGetStartDate().
	 */
	public function testGetStartDate() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testGetEndDate().
	 */
	public function testGetEndDate() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testGetDeadlineDate().
	 */
	public function testGetDeadlineDate() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testIsContainer().
	 */
	public function testIsContainer() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testIsTask().
	 */
	public function testIsTask() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testIsPublic().
	 */
	public function testIsPublic() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testIsAcknowledged().
	 */
	public function testIsAcknowledged() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testLoadForeignData().
	 */
	public function testLoadForeignData() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}



	/**
	 * @todo Implement testGetTemplateData().
	 */
	public function testGetTemplateData() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		  'This test has not been implemented yet.'
		);
	}

}
?>