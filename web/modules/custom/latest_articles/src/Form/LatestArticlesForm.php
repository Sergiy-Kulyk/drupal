<?php

declare(strict_types=1);

namespace Drupal\latest_articles\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\latest_articles\LatestArticlesService;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Latest Articles Form.
 */
class LatestArticlesForm extends FormBase {

  /**
   * Articles service.
   */
  protected $articlesService;

  /**
   * Renderer.
   */
  protected $renderer;

  /**
   * Constructor.
   *
   * @param \Drupal\latest_articles\LatestArticlesService $articles_service
   *   Articles service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer.
   */
  public function __construct(LatestArticlesService $articles_service, RendererInterface $renderer) {
    $this->articlesService = $articles_service;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('latest_articles.articles_service'),
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
      $result = $this->articlesService->getArticlesItems($offset);
      $articles_items = [
        ...$articles_items,
        ...$result['items']
      ];
      $loaded_nodes = [
        ...$loaded_nodes,
        ...$result['nodes']
      ];
    } else {
      $result = $this->articlesService->getArticlesItems();
      $articles_items = $result['items'];
      $loaded_nodes = $result['nodes'];
    }
    $form_state->set('items', $articles_items);
    $form_state->set('nodes', $loaded_nodes);
    $total_items = $this->articlesService->getTotalArticlesItems();
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
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $form_state->setRebuild();
  }
}
