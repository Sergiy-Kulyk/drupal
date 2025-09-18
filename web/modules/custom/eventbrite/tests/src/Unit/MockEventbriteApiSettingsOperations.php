<?php

namespace Drupal\Tests\eventbrite\Unit;

/**
 * Mock class for EventbriteApiSettingsOperations constants.
 *
 * This mock is needed for testing since the actual constants
 * may not be available in the test environment.
 */
class MockEventbriteApiSettingsOperations {

  /**
   * Base URL for Eventbrite API.
   */
  const EVENTBRITE_ENDPOINT_BASE_URL = 'https://www.eventbriteapi.com';

  /**
   * Operation: Get organization events with time filter.
   */
  const GET_ORGANIZATION_EVENTS_WITH_TIME_FILTER = 'get_organization_events_with_time_filter';

  /**
   * Operation: Get organizations.
   */
  const GET_ORGANIZATIONS = 'get_organizations';

  /**
   * Operation: Get venue.
   */
  const GET_VENUE = 'get_venue';

}
