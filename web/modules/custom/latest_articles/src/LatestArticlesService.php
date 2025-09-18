<?php

namespace Drupal\latest_articles;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\NodeInterface;

class LatestArticlesService {

  /**
   * The entity type manager.
   */
  protected $entityTypeManager;

  /**
   * Items per page.
   */
  const ITEMS_PER_PAGE = 3;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager ) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get articles.
   *
   * @param int $offset
   *   Items to skip.
   * @param int $limit
   *   Number of items.
   *
   * @return array
   *   Array with items and loaded nodes.
   */
  public function getArticlesItems(int $offset = 0, int $limit = self::ITEMS_PER_PAGE): array {
    $node_storage = $this->entityTypeManager->getStorage('node');
    $query = $node_storage->getQuery()
      ->accessCheck()
      ->condition('type', 'article')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range($offset, $limit);

    $nids = $query->execute();

    if (empty($nids)) {
      return ['items' => [], 'nodes' => []];
    }

    $nodes = $node_storage->loadMultiple($nids);
    $articles_items = [];
    foreach ($nodes as $node) {
      $articles_items[] = [
        'title' => $node->label(),
        'teaser' => $this->getTeaser($node),
        'link' => $node->toLink(t('Read More'))->toRenderable(),
        'nid' => $node->id(),
      ];
    }

    return [
      'items' => $articles_items,
      'nodes' => $nodes,
    ];
  }

  /**
   * Return total number of items.
   *
   * @return int
   *   Number of articles.
   */
  public function getTotalArticlesItems(): int {
    $node_storage = $this->entityTypeManager->getStorage('node');
    $query = $node_storage->getQuery()
      ->condition('type', 'article')
      ->condition('status', 1)
      ->accessCheck();

    return $query->count()->execute();
  }

  /**
   * Return teaser text.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   *
   * @return string|null
   *   Text.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getTeaser(NodeInterface $node): ?string {
    $teaser = NULL;
    if ($node->hasField('body') && !$node->get('body')->isEmpty()) {
      $body = $node->get('body')->first();
      if ($body) {
        $teaser = $body->get('summary')->getValue();
        if (empty($teaser)) {
          $body_value = $body->get('value')->getValue();
          if (!empty($body_value)) {
            $teaser = text_summary($body_value, NULL, 200);
          }
        }
      }
    }

    return $teaser;
  }

  /**
   * Check.
   *
   * @return bool
   */
  public function checkRequirements(): bool {
    return drupal_check_module('latest_articles');
  }
}
