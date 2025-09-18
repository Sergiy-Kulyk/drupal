<?php

namespace Drupal\eventbrite;

/**
 * Contains a list of known Eventbrite API operations.
 */
final class EventbriteApiSettingsOperations {

  public const EVENTBRITE_ENDPOINT_BASE_URL = 'https://www.eventbriteapi.com';

  public const GET_ORGANIZATION_EVENTS_WITH_TIME_FILTER = 'get_organization_events_with_time';

  public const GET_ORGANIZATIONS = 'get_organisations';

  public const GET_VENUE = 'get_venue';

}
