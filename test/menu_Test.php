<?php
class Menu_Test extends PHPUnit_Framework_TestCase {

  /**
   * Test that the explicit order is enforced no matter insertion
   */
  public function test_order_explicit () {

    $expected = array('foo', 'bar', 'zoo');
    $menu = new Menu_Model();

    $menu->set('Bar', 'bar', 1);
    $menu->set('Zoo', 'zoo', 2);
    $menu->set('Foo', 'foo', 0);

    $branch = $menu->get_branch();
    foreach ($branch as $index => $node) {
      $this->assertEquals($node->get_href(), $expected[$index]);
    }

  }

  /**
   * Test that the implicit order is order of insertion
   */
  public function test_order_implicit () {

    $expected = array('foo', 'zoo', 'bar');
    $menu = new Menu_Model();

    $menu->set('Foo', 'foo', 0);
    $menu->set('Zoo', 'zoo');
    $menu->set('Bar', 'bar');

    $branch = $menu->get_branch();
    foreach ($branch as $index => $node) {
      $this->assertEquals($node->get_href(), $expected[$index]);
    }

  }

  /**
   * Test that namespace produces correct hierarchy
   */
  public function test_namespace_hierarchy () {

    $menu = new Menu_Model();

    $menu->set('Foo.Bar.Zoo.Baz', 'testvalue');
    $node = $menu->get('Foo.Bar.Zoo');

    # The Foo.Bar.Zoo node has been declared and has children
    $this->assertTrue($node->has_children());

    # Get that child and see that it has the corrent href value
    $branch = $node->get_branch();
    $child = $branch[0];

    $this->assertEquals($child->get_href(), 'testvalue');

  }

  /**
   * Test that undeclared hierarchy cannot be gotten
   */
  public function test_undeclared_hierarchy () {

    $menu = new Menu_Model();

    $menu->set('Foo.Bar.Zoo.Baz', 'testvalue');
    $node = $menu->get('Foo.Bar.Baz');

    $this->assertNull($node);

  }

}