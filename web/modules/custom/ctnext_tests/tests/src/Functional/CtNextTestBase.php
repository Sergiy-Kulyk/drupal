<?php

namespace Drupal\Tests\ctnext_tests\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * To be extended by other test classes.
 */
abstract class CtNextTestBase extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['node', 'rules', 'ctnext_tests', 'devel'];

  /**
   * Theme to enable.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  protected function setUp(): void {
    parent::setUp();
    // Create an article content type that we will use for testing.
    $type = $this->container->get('entity_type.manager')
      ->getStorage('node_type')
      ->create([
        'type' => 'article',
        'name' => 'Article',
      ]);
    $type->save();
    $this->container->get('router.builder')->rebuild();
  }
}
