<?php

declare(strict_types=1);

namespace Drupal\latest_articles\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'Latest Articles' Block.
 *
 * @Block(
 *   id = "latest_articles_block",
 *   admin_label = @Translation("Latest Articles Block"),
 *   category = @Translation("Custom"),
 * )
 */
class LatestArticlesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Definition.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   Form builder.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = $this->formBuilder->getForm('Drupal\latest_articles\Form\LatestArticlesForm');

    return [
      '#theme' => 'latest_articles_block',
      '#form' => $form,
      '#attached' => [
        'library' => [
          'latest_articles/latest_articles.main',
        ],
      ],
      '#cache' => [
        'tags' => ['node_list:article'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['node_list:article']);
  }

}
