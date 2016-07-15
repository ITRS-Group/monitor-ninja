<?php

/**
 * Make sure NoticeManager interacts properly with the Notice models
 */
class Notice_Test extends PHPUnit_Framework_TestCase {

	public function setUp() {
		$this->nm = new NoticeManager_Model();
	}

	public function tearDown() {
		unset($this->nm);
	}

	public function test_manager_does_not_accept_string() {
		$this->setExpectedException('NoticeManager_Exception');
		$this->nm[] = 'trigger error plz';
	}

	public function test_notice_is_a_string() {
		$this->setExpectedException('NoticeException');
		new InformationNotice_Model(3);
	}

	public function test_duplicate_notices_should_be_ignored() {
		$this->nm[] = new InformationNotice_Model('yolo');
		$this->nm[] = new InformationNotice_Model('yolo');
		$this->assertEquals(1, count($this->nm));

		// a new message
		$this->nm[] = new InformationNotice_Model('not yolo');
		$this->assertEquals(2, count($this->nm));
	}

	public function test_message_priority_is_highest_if_multiple_are_given() {
		$message = 'grönt är skönt';
		$this->nm[] = new ErrorNotice_Model($message);
		$this->nm[] = new InformationNotice_Model($message);
		$this->assertEquals(1, count($this->nm));

		foreach($this->nm as $stored_key => $stored_value) {
			$this->assertEquals($message, $stored_value->get_message());
			$this->assertEquals('error', $stored_value->get_typename());
			return;
		}
		$this->assertTrue(false, "Should not have reached this point");
	}

	public function test_cannot_trust_notice_managers_key() {
		$info = 'This is important';
		$key = 'my_key';
		$this->nm[$key] = new InformationNotice_Model($info);

		// we succeeded to add it (also confirmed because didn't
		// trigger an exception)
		$this->assertEquals(1, count($this->nm));
		$this->assertEquals(null, $this->nm[$key]);
		foreach($this->nm as $stored_key => $stored_value) {
			$this->assertNotEquals($key, $stored_key);
			$this->assertEquals($info, $stored_value->get_message());
			$this->assertEquals('info', $stored_value->get_typename());
			return;
		}
		$this->assertTrue(false, "Should not have reached this point");
	}

}
