<?php

declare(strict_types=1);

namespace Drupal\latest_articles\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Module hooks.
 */
class LatestArticlesHooks {

  #[Hook('hook_help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    if ($route_name == 'help.page.latest_articles') {
      return new FormattableMarkup('<p>@help_text</p>', [
        '@help_text' => t('The module provides a custom block that displays the latest articles with AJAX loading functionality.'),
      ]);
    }
  }

  #[Hook('hook_theme')]
  public function theme() {
    return [
      'latest_articles_block' => [
        'variables' => [
          'form' => NULL,
        ],
      ],
      'latest_articles_list' => [
        'variables' => [
          'articles_items' => [],
        ],
      ],
    ];
  }

}

