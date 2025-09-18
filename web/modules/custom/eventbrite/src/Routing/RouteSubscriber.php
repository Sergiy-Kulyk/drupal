<?php

namespace Drupal\eventbrite\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Alters routes.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection): void {
    $this->addAdminRouteOption($collection, 'entity.feeds_feed.canonical');
    $this->addAdminRouteOption($collection, 'feeds.item_list');
    $this->addAdminRouteOption($collection, 'feeds.log');
    $this->addAdminRouteOption($collection, 'entity.feeds_import_log.canonical');
    $this->addAdminRouteOption($collection, 'entity.feeds_feed.clear_logs');
  }

  /**
   * Add _admin_route option to the given route name.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection.
   * @param string $route_name
   *   The route name.
   */
  protected function addAdminRouteOption(RouteCollection $collection, string $route_name): void {
    $route = $collection->get($route_name);

    if ($route instanceof Route) {
      $route->setOption('_admin_route', TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = parent::getSubscribedEvents();
    $events[RoutingEvents::ALTER] = ['onAlterRoutes'];
    return $events;
  }

}
