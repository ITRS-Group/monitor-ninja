<?php defined('SYSPATH') OR die('No direct access allowed.');
Auth::instance()->force_login('monitor');
/**
 * @package    NINJA
 * @author     op5
 * @license    GPL
 */
class Livestatus_Test extends TapUnit {
    public function setUp() {
        $this->ls = Livestatus::instance();
        $this->ok(is_object($this->ls), 'created livestatus object');
        $this->lsb = $ls->getBackend();
        $this->ok(is_object($this->lsb), 'fetched livestatus backend');
    }

    public function test_basic_filter() {
        # basic filter 1
        $filter = array('name' => 'test');
        $expect = 'Filter: name = test';
        $filter = chop($this->lsb->getQueryFilter(false, $filter));
        $this->ok($filter === $expect, "basic filter 1: \nexpect:\n".$expect."\n\ngot:\n".$filter);

        # basic filter 2
        $filter = array('name' => array('!=' => 'test'));
        $expect = 'Filter: name != test';
        $filter = chop($this->lsb->getQueryFilter(false, $filter));
        $this->ok($filter === $expect, "basic filter 2: \nexpect:\n".$expect."\n\ngot:\n".$filter);

        # basic filter 3
        $filter = "";
        $expect = '';
        $filter = chop($this->lsb->getQueryFilter(false, $filter));
        $this->ok($filter === $expect, "basic filter 3: \nexpect:\n".$expect."\n\ngot:\n".$filter);

        # basic filter 4
        $filter = array('', 'num_hosts' => array('>' => 0));
        $expect = 'Filter: num_hosts > 0';
        $filter = chop($this->lsb->getQueryFilter(false, $filter));
        $this->ok($filter === $expect, "basic filter 4: \nexpect:\n".$expect."\n\ngot:\n".$filter);

    }

    public function test_and_filter() {
        # and filter 1
        $filter = $filter = array(
            'name' => array('-and' => array( 'test1' ) )
        );
        $expect = "Filter: name = test1";
        $filter = chop($this->lsb->getQueryFilter(false, $filter));
        $this->ok($filter === $expect, "and filter 1: \nexpect:\n".$expect."\n\ngot:\n".$filter);

        # and filter 2
        $filter = array( 'host_has_been_checked' => 1, 'host_state' => 0 );
        $expect = "Filter: host_has_been_checked = 1\nFilter: host_state = 0\nAnd: 2";
        $filter = chop($this->lsb->getQueryFilter(false, $filter));
        $this->ok($filter === $expect, "and filter 2: \nexpect:\n".$expect."\n\ngot:\n".$filter);

        # and filter 3
        $filter = array(
            '-and' => array(
                        array( 'host_has_been_checked' => 1, 'host_state' => 0 ),
                        array( 'has_been_checked' => 1, 'state' => 3 )
                      )
        );
        $expect = "Filter: host_has_been_checked = 1\nFilter: host_state = 0\nAnd: 2\nFilter: has_been_checked = 1\nFilter: state = 3\nAnd: 2\nAnd: 2";
        $filter = chop($this->lsb->getQueryFilter(false, $filter));
        $this->ok($filter === $expect, "and filter 3: \nexpect:\n".$expect."\n\ngot:\n".$filter);
    }

    public function test_or_filter() {
        # or filter 1
        $filter = $filter = array(
            'name' => array('-or' => array( 'test1', 'test2' ) )
        );
        $expect = "Filter: name = test1\nFilter: name = test2\nOr: 2";
        $filter = chop($this->lsb->getQueryFilter(false, $filter));
        $this->ok($filter === $expect, "or filter 1: \nexpect:\n".$expect."\n\ngot:\n".$filter);

        # or filter 2
        $filter = $filter = array(
            'name' => array('-or' => array(
                                'test1',
                                array( '!=' => 'test2')
            )
                        )
        );
        $expect = "Filter: name = test1\nFilter: name != test2\nOr: 2";
        $filter = chop($this->lsb->getQueryFilter(false, $filter));
        $this->ok($filter === $expect, "or filter 2: \nexpect:\n".$expect."\n\ngot:\n".$filter);

        # or filter 3
        $filter = $filter = array(
            '-or' => array('name1' => 'test1',
                           'name2' => 'test2')
        );
        $expect = "Filter: name1 = test1\nFilter: name2 = test2\nOr: 2";
        $filter = chop($this->lsb->getQueryFilter(false, $filter));
        $this->ok($filter === $expect, "or filter 3: \nexpect:\n".$expect."\n\ngot:\n".$filter);

        # or filter 4
        $filter = $filter = array(
            '-or' => array('name1' => 'test1',
                           'name2' => array('!=' => 'test2'))
        );
        $expect = "Filter: name1 = test1\nFilter: name2 != test2\nOr: 2";
        $filter = chop($this->lsb->getQueryFilter(false, $filter));
        $this->ok($filter === $expect, "or filter 4: \nexpect:\n".$expect."\n\ngot:\n".$filter);
    }

}
