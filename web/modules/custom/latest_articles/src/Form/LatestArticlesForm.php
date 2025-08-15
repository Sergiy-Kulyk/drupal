<?php

declare(strict_types=1);

namespace Drupal\latest_articles\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Latest Articles Form.
 */
class LatestArticlesForm extends FormBase {

  /**
   * The entity type manager.
   */
  protected $entityTypeManager;

  /**
   * Renderer.
   */
  protected $renderer;

  /**
   * Items per page.
   */
  const ITEMS_PER_PAGE = 3;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'latest_articles_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    if ($form_state->get('items')) {
      $articles_items = $form_state->get('items');
      $loaded_nodes = $form_state->get('nodes');
      $offset = count($articles_items);
      $result = $this->getArticlesItems($offset);
      $articles_items = [
        ...$articles_items,
        ...$result['items']
      ];
      $loaded_nodes = [
        ...$loaded_nodes,
        ...$result['nodes']
      ];
    } else {
      $result = $this->getArticlesItems();
      $articles_items = $result['items'];
      $loaded_nodes = $result['nodes'];
    }
    $form_state->set('items', $articles_items);
    $form_state->set('nodes', $loaded_nodes);
    $total_items = $this->getTotalArticlesItems();
    $form['articles_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'articles-list-container',
      ],
    ];
    $form['articles_container']['articles_list'] = [
      '#theme' => 'latest_articles_list',
      '#articles_items' => $articles_items,
    ];

    foreach ($loaded_nodes as $node) {
      $this->renderer->addCacheableDependency($form['articles_container']['articles_list'], $node);
    }

    if (count($articles_items) <= $total_items) {
      $form['load_more'] = [
        '#type' => 'submit',
        '#name' => 'load_more',
        '#value' => $this->t('Load More'),
        '#ajax' => [
          'callback' => '::loadMoreCallback',
          'wrapper' => 'articles-list-container',
          'effect' => 'fade',
        ],
        '#attributes' => [
          'class' => ['btn', 'btn-load-more'],
        ],
      ];
    }

    return $form;
  }

  /**
   * AJAX update articles listing.
   */
  public function loadMoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['articles_container'];
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
  protected function getArticlesItems(int $offset = 0, int $limit = self::ITEMS_PER_PAGE): array {
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
        'link' => $node->toLink($this->t('Read More'))->toRenderable(),
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
  protected function getTotalArticlesItems(): int {
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
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $form_state->setRebuild();
  }
}
