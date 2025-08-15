<?php

namespace Drupal\ctnext_user\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a greeting block.
 */
#[Block(
  id: "ctnext_user_greeting",
  admin_label: new TranslatableMarkup('CTNEXT user greeting block.'),
  category: new TranslatableMarkup('CTNEXT'),
)]
class UserGreeting extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $currentUser;

  /**
   * Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  public RendererInterface $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->currentUser = $container->get('current_user');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Builds the cart block.
   *
   * @return array
   *   A render array.
   */
  public function build() {
    $id = $this->currentUser->id();
    $user = User::load($id);
    $name = $this->currentUser->getAccountName();
    if ($this->currentUser->isAnonymous()) {
      $name = 'Guest';
    }
    if (in_array('editor', $this->currentUser->getRoles())) {
      $name = 'Editor';
    }
    if (in_array('administrator', $this->currentUser->getRoles())) {
      $name = 'Administrator';
      if (in_array('editor', $this->currentUser->getRoles())) {
        $name = $name . ', Editor';
      }
    }

    $build = [
      '#type' => 'markup',
      '#markup' => new TranslatableMarkup('<div>@greeting</div>', [
        '@greeting' => t('Hello, @name!', ['@name' =>  $name]),
      ]),
    ];
    $this->renderer->addCacheableDependency($build, $user);
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $id = $this->currentUser->id();
    $user = User::load($id);
    $cache_contexts = [];
    if ($user) {
      $cache_contexts = $user->getCacheContexts();
    }
    return Cache::mergeContexts(parent::getCacheContexts(), ['user.roles'], $cache_contexts);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $id = $this->currentUser->id();
    $user = User::load($id);
    $cache_tags = [];
    if ($user) {
      $cache_tags = $user->getCacheTags();
    }
    $original_tags = parent::getCacheTags();
    return Cache::mergeTags($cache_tags, $original_tags);
  }

}
