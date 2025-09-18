<?php

namespace Drupal\eventbrite;

/**
 * The Eventbrite client interface.
 */
interface EventbriteClientInterface {

  /**
   * Get events by organization, time and status filter.
   *
   * @param string $auth_key
   *   Private token key.
   * @param string $org_id
   *   Organization ID.
   * @param string $time_filter
   *   Time filter value.
   * @param array $config
   *   Additional configuration.
   *
   * @return array|string|null
   *   Result.
   */
  public function getOrganizationEventsByTimeStatus(string $auth_key, string $org_id, string $time_filter, string $status, array $config) : array|string|null;

  /**
   * Get organizations.
   *
   * @param string $auth_key
   *   Private token key.
   *
   * @return array|string|null
   *   Result.
   */
  public function getOrganizations(string $auth_key) : array|string|null;

  /**
   * Get venue.
   *
   * @param string $auth_key
   *   Private token key.
   * @param string $venue_id
   *   Venue id.
   *
   * @return array|string|null
   *   Result.
   */
  public function getVenue(string $auth_key, string $venue_id) : array|string|null;

}
