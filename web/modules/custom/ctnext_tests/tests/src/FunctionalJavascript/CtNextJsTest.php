<?php

namespace Drupal\Tests\ctnext_tests\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\node\Entity\Node;

/**
 * Tests the MyModule functionality.
 *
 * @group ctnext_tests
 */
class CtNextJsTest extends WebDriverTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['node', 'block', 'latest_articles', 'ctnext_tests', 'devel'];

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

    $articles_content = [
      'Article1',
      'Article2',
      'Article3',
      'Article4',
      'Article5',
      'Article6',
      'Article7',
      'Article8',
      'Article9',
      'Article10',
    ];

    foreach ($articles_content as $article_content) {
      $article = Node::create(['type' => 'article', 'title' => $article_content]);
      $article->setPublished();
      $article->save();
    }
    $this->container->get('router.builder')->rebuild();
    $block = $this->container->get('entity_type.manager')
      ->getStorage('block')
      ->create([
        'id' => 'test_latest_articles_block',
        'theme' => $this->defaultTheme,
        'plugin' => 'latest_articles_block',
        'region' => 'content',
        'settings' => [],
        'visibility' => [],
      ]);
    $block->save();
  }

  /**
   * Tests ctnext_tests in a real browser.
   */
  public function test_latestArticelsBlock_showsThreeArticles(): void {
    $this->drupalGet('/');
    $widget = $this->getSession()->getPage()->findById('latest-articles-form');
    $items = $widget->findAll('css', '.article-item');
    $original_count = count($items);
    $this->assertEquals(3, $original_count);
  }

  /**
   * Tests ctnext_tests in a real browser.
   */
  public function test_latestArticelsBlock_loadsThreeArticlesPerClick(): void {
    $this->drupalGet('/');
    $widget = $this->getSession()->getPage()->findById('latest-articles-form');
    $load = $widget->findById('edit-load-more');
    $items = $widget->findAll('css', '.article-item');
    $original_count = count($items);
    $this->assertEquals(3, $original_count);
    $load->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $items_updated = $widget->findAll('css', '.article-item');
    $this->assertEquals(6, count($items_updated));
    $load->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $items_updated = $widget->findAll('css', '.article-item');
    $this->assertEquals(9, count($items_updated));
  }

}
