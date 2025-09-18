<?php

namespace Drupal\Tests\ctnext_tests\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests pages are reachable.
 *
 * @group ctnext_tests_ui
 */
class UiPageTest extends BrowserTestBase {

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

  /**
   * {@inheritdoc}
   */
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

  /**
   * Tests that the reaction rule listing page works.
   */
  public function testCtnextTestsReactionRulePage() {
    $account = $this->drupalCreateUser(['administer rules']);
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/workflow/rules');
    $this->assertSession()->statusCodeEquals(200);

    // Test that there is an empty reaction rule listing.
    $this->assertSession()->pageTextContains('There are no enabled reaction rules.');
    $config = $this->config('devel.settings');
    $config->set('debug_logfile', '/app/debug/drupal_debug.txt')->save();
    $config->set('debug_pre', FALSE)->save();
  }

}
