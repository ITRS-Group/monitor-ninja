<?php
require_once ("op5/mayi.php");

class MayITest extends PHPUnit_Framework_TestCase {
	public function test_is_subset_proper_subset() {
		$this->assertTrue(op5MayI::is_subset("a.b:c.d", "a:c"));
		$this->assertTrue(op5MayI::is_subset("abc:def", ":"), ' everything is a subset of (root)');
		$this->assertTrue(op5MayI::is_subset("a.b.c.d:e.f.g", "a:e"), ' both partitions strict subsets of one another');
	}

	public function test_is_subset_nonstrict_subset() {
		$this->assertTrue(op5MayI::is_subset("a:b", ":b"), 'a is a subset of (root), b is a non-strict subset of b');
		$this->assertTrue(op5MayI::is_subset("a:b", "a:b"), 'a is a non-strict subset of a, b is a non-strict subset of b');
		$this->assertTrue(op5MayI::is_subset("a:", ":"), 'a is a subset of (root)');
		$this->assertTrue(op5MayI::is_subset(":b.c.d.e", ":b"));
		$this->assertTrue(op5MayI::is_subset(":", ":"), 'empty set vs empty set in both partitions');
		$this->assertTrue(op5MayI::is_subset("a.b.c.d:e.f.g", "a.b.c.d:e.f.g"), ' both partitions are non-strict subsets of one another');
	}

	public function test_is_subset_right_partition_not_subset() {
		$this->assertFalse(op5MayI::is_subset(":a.b", ":a.c"), 'a.b is not a subset of a.c');
		$this->assertFalse(op5MayI::is_subset(":a.b.c", ":a.x.c"), 'a.b.c is not a subset of a.x.c');
		$this->assertFalse(op5MayI::is_subset(":abcd", ":ab"), 'abcd is not a subset of ab');
		$this->assertFalse(op5MayI::is_subset("abc:", ":abc"), ' abc is a subset of (root), but (root) is not a subset of abc');
		$this->assertFalse(op5MayI::is_subset("a.b:c", "a:c.d"), ' c is not a subset of c.d');
		$this->assertFalse(op5MayI::is_subset("a:b", ":a"), 'b is not a subset of a');
		$this->assertFalse(op5MayI::is_subset("a:", "a:b.c"), '(root) is not a subset of b.c');
	}

	public function test_is_subset_left_partition_not_subset() {
		$this->assertFalse(op5MayI::is_subset("abcd:", "ab:"), 'abcd is not a subset of ab');
		$this->assertFalse(op5MayI::is_subset("abcd.efg:", "abc.defg:"), 'abcd.efg is not a subset of abc.defg');
		$this->assertFalse(op5MayI::is_subset("a:c.d", "a.b:c"), ' a is not a subset of a.b');
		$this->assertFalse(op5MayI::is_subset(":b", "a:b"), '(root) is not a subset of a');
	}

	public function test_is_subset_neither_partition_is_subset() {
		$this->assertFalse(op5MayI::is_subset(":a", "a:b"), '(root) is not a subset of a, and a is not a subset of b');
	}

	public function test_is_subset_malformed_input() {
		$this->assertFalse(op5MayI::is_subset("", "abc:def"), 'malformed parameter A');
		$this->assertFalse(op5MayI::is_subset(":def", ""), 'malformed parameter B');
		$this->assertFalse(op5MayI::is_subset("", ""), 'malformed parameter A & B');
	}
}
