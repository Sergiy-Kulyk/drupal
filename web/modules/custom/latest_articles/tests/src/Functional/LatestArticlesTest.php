<?php

namespace Drupal\Tests\ctnext_tests\Functional;
use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;

class LatestArticlesTest extends CtNextTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['node', 'ctnext_tests', 'latest_articles'];

  protected function setUp(): void {
    parent::setUp();
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

  public function test_LatestArticles_checkRequirements() {
    $articles_service = $this->container->get('latest_articles.articles_service');
    $result = $articles_service->checkRequirements();
    $this->assertTrue($result);
  }

}
