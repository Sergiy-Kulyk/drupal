<?php

namespace Drupal\Tests\eventbrite\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\eventbrite\EventbriteClient;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

/**
 * Integration tests for EventbriteClient.
 *
 * @coversDefaultClass \Drupal\eventbrite\EventbriteClient
 * @group eventbrite
 */
class EventbriteClientIntegrationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['eventbrite'];

  /**
   * The EventbriteClient instance.
   *
   * @var \Drupal\eventbrite\EventbriteClient
   */
  protected $eventbriteClient;

  /**
   * The mock handler for HTTP client.
   *
   * @var \GuzzleHttp\Handler\MockHandler
   */
  protected $mockHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->mockHandler = new MockHandler();
    $handlerStack = HandlerStack::create($this->mockHandler);
    $httpClient = new HttpClient(['handler' => $handlerStack]);

    $this->eventbriteClient = new EventbriteClient($httpClient);
  }

  /**
   * Tests full workflow with multiple API calls including organizer expansion.
   *
   * @covers ::getOrganizations
   * @covers ::getOrganizationEventsByTimeStatus
   * @covers ::getVenue
   */
  public function testFullWorkflowWithOrganizerExpansion() {
    // Mock organizations response
    $organizations_response = new Response(200, [], json_encode([
      'organizations' => [
        [
          'id' => 'org123',
          'name' => 'Test Organization',
        ]
      ],
      'pagination' => ['has_more_items' => FALSE]
    ]));

    // Mock events response with expanded organizer
    $events_response = new Response(200, [], json_encode([
      'events' => [
        [
          'id' => 'event123',
          'name' => ['text' => 'Test Event'],
          'venue_id' => 'venue123',
          'organizer' => [
            'id' => 'org123',
            'name' => 'Test Organization',
            'description' => [
              'text' => 'Organization description'
            ],
            'logo' => [
              'url' => 'https://example.com/logo.png'
            ]
          ]
        ]
      ],
      'pagination' => ['has_more_items' => FALSE]
    ]));

    // Mock venue response
    $venue_response = new Response(200, [], json_encode([
      'id' => 'venue123',
      'name' => 'Test Venue',
      'address' => [
        'address_1' => '123 Test St',
        'city' => 'Test City',
      ]
    ]));

    // Queue the responses
    $this->mockHandler->append($organizations_response);
    $this->mockHandler->append($events_response);
    $this->mockHandler->append($venue_response);

    // Execute workflow
    $auth_key = 'test_auth_key';

    // Step 1: Get organizations
    $organizations = $this->eventbriteClient->getOrganizations($auth_key);
    $this->assertIsArray($organizations);
    $this->assertArrayHasKey('data', $organizations);
    $org_id = $organizations['data'][0]['id'];

    // Step 2: Get events for organization (with expand=organizer automatically added)
    $events = $this->eventbriteClient->getOrganizationEventsByTimeStatus(
      $auth_key,
      $org_id,
      'current_future',
      'live'
    );
    $this->assertIsArray($events);
    $this->assertArrayHasKey('data', $events);
    $venue_id = $events['data'][0]['venue_id'];

    // Verify organizer data is expanded
    $this->assertArrayHasKey('organizer', $events['data'][0]);
    $this->assertEquals('Test Organization', $events['data'][0]['organizer']['name']);
    $this->assertArrayHasKey('description', $events['data'][0]['organizer']);

    // Step 3: Get venue details
    $venue = $this->eventbriteClient->getVenue($auth_key, $venue_id);
    $this->assertIsArray($venue);
    $this->assertArrayHasKey('data', $venue);
    $this->assertEquals('venue123', $venue['data']['id']);
  }

  /**
   * Tests error handling for HTTP errors.
   *
   * @covers ::doRequest
   */
  public function testHttpErrorHandling() {
    $this->expectException(RequestException::class);

    // Mock 404 response
    $error_response = new RequestException(
      'Client error: 404 Not Found',
      new Request('GET', 'https://www.eventbriteapi.com/v3/test'),
      new Response(404, [], json_encode(['error' => 'Not found']))
    );

    $this->mockHandler->append($error_response);

    $this->eventbriteClient->getOrganizations('invalid_key');
  }

  /**
   * Tests handling of invalid JSON response.
   *
   * @covers ::doExtractJsonResults
   */
  public function testInvalidJsonResponse() {
    $this->expectException(\JsonException::class);

    // Mock response with invalid JSON
    $invalid_json_response = new Response(200, [], 'invalid json content');
    $this->mockHandler->append($invalid_json_response);

    $this->eventbriteClient->getOrganizations('test_key');
  }

  /**
   * Tests large pagination scenario.
   *
   * @covers ::getOrganizations
   */
  public function testLargePaginationScenario() {
    // Create multiple pages of organizations
    $pages = 5;
    $organizations_per_page = 10;

    for ($i = 1; $i <= $pages; $i++) {
      $organizations = [];
      for ($j = 1; $j <= $organizations_per_page; $j++) {
        $org_id = ($i - 1) * $organizations_per_page + $j;
        $organizations[] = [
          'id' => "org{$org_id}",
          'name' => "Organization {$org_id}",
        ];
      }

      $response_data = [
        'organizations' => $organizations,
        'pagination' => [
          'has_more_items' => $i < $pages,
          'page_number' => $i,
        ],
      ];

      if ($i < $pages) {
        $response_data['pagination']['continuation'] = "page{$i}_token";
      }

      $this->mockHandler->append(
        new Response(200, [], json_encode($response_data))
      );
    }

    $result = $this->eventbriteClient->getOrganizations('test_key');

    $this->assertIsArray($result);
    $this->assertArrayHasKey('pages_data', $result);
    $this->assertCount($pages, $result['pages_data']);

    // Verify total number of organizations
    $total_orgs = 0;
    foreach ($result['pages_data'] as $page_data) {
      $total_orgs += count($page_data);
    }
    $this->assertEquals($pages * $organizations_per_page, $total_orgs);
  }

  /**
   * Tests complex query parameters scenario with expand.
   *
   * @covers ::getOrganizationEventsByTimeStatus
   * @covers ::doRequest
   */
  public function testComplexQueryParametersWithExpandScenario() {
    $response_data = [
      'events' => [
        [
          'id' => 'filtered_event_123',
          'name' => ['text' => 'Filtered Event'],
          'organizer' => [
            'id' => 'org456',
            'name' => 'Filtered Organizer',
            'description' => [
              'text' => 'Expanded organizer description'
            ]
          ]
        ]
      ],
      'pagination' => ['has_more_items' => FALSE]
    ];

    $this->mockHandler->append(
      new Response(200, [], json_encode($response_data))
    );

    // Test with complex configuration
    $config = [
      'continue_key' => 'test_continuation',
    ];

    $result = $this->eventbriteClient->getOrganizationEventsByTimeStatus(
      'test_key',
      'test_org_id',
      'current_future',
      'live',
      $config
    );

    $this->assertIsArray($result);
    $this->assertArrayHasKey('data', $result);
    $this->assertEquals('filtered_event_123', $result['data'][0]['id']);

    // Verify organizer expansion worked
    $this->assertArrayHasKey('organizer', $result['data'][0]);
    $this->assertEquals('Filtered Organizer', $result['data'][0]['organizer']['name']);

    // Verify that the last request included the continuation and expand parameters
    $last_request = $this->mockHandler->getLastRequest();
    $query_string = (string) $last_request->getUri()->getQuery();
    $this->assertStringContainsString('continuation=test_continuation', $query_string);
    $this->assertStringContainsString('expand=organizer', $query_string);
  }

  /**
   * Tests authentication header validation.
   *
   * @covers ::generateHeadersBody
   */
  public function testAuthenticationHeaderValidation() {
    $auth_key = 'test_bearer_token';

    $this->mockHandler->append(
      new Response(200, [], json_encode(['organizations' => []]))
    );

    $this->eventbriteClient->getOrganizations($auth_key);

    $last_request = $this->mockHandler->getLastRequest();
    $auth_headers = $last_request->getHeader('Authorization');

    $this->assertCount(1, $auth_headers);
    $this->assertEquals("Bearer {$auth_key}", $auth_headers[0]);
  }

  /**
   * Tests URL construction with different path parameter positions.
   *
   * @covers ::doRequest
   */
  public function testUrlConstructionVariations() {
    $venue_id = 'venue_with_special_chars_123';

    $this->mockHandler->append(
      new Response(200, [], json_encode(['id' => $venue_id]))
    );

    $this->eventbriteClient->getVenue('test_key', $venue_id);

    $last_request = $this->mockHandler->getLastRequest();
    $uri = (string) $last_request->getUri();

    $this->assertStringContainsString("/v3/venues/{$venue_id}/", $uri);
    $this->assertStringContainsString('https://www.eventbriteapi.com', $uri);
  }

  /**
   * Tests concurrent request simulation.
   *
   * @covers ::doRequest
   */
  public function testConcurrentRequestSimulation() {
    // Simulate multiple concurrent requests
    $responses = [];
    for ($i = 1; $i <= 3; $i++) {
      $responses[] = new Response(200, [], json_encode([
        'organizations' => [
          ['id' => "org{$i}", 'name' => "Organization {$i}"]
        ],
        'pagination' => ['has_more_items' => FALSE]
      ]));
    }

    foreach ($responses as $response) {
      $this->mockHandler->append($response);
    }

    // Execute multiple requests
    $results = [];
    for ($i = 1; $i <= 3; $i++) {
      $results[] = $this->eventbriteClient->getOrganizations("auth_key_{$i}");
    }

    // Verify all requests completed successfully
    $this->assertCount(3, $results);
    foreach ($results as $index => $result) {
      $this->assertIsArray($result);
      $this->assertArrayHasKey('data', $result);
      $expected_org_id = 'org' . ($index + 1);
      $this->assertEquals($expected_org_id, $result['data'][0]['id']);
    }
  }

  /**
   * Tests response parsing edge cases.
   *
   * @covers ::doExtractJsonResults
   */
  public function testResponseParsingEdgeCases() {
    // Test empty response
    $this->mockHandler->append(new Response(200, [], '{}'));
    $result = $this->eventbriteClient->getOrganizations('test_key');
    $this->assertIsArray($result);

    // Test response without expected key
    $this->mockHandler->append(
      new Response(200, [], json_encode(['unexpected_key' => 'value']))
    );
    $result = $this->eventbriteClient->getOrganizations('test_key');
    $this->assertIsArray($result);
    $this->assertArrayHasKey('data', $result);

    // Test response with null values
    $this->mockHandler->append(
      new Response(200, [], json_encode([
        'organizations' => null,
        'pagination' => null
      ]))
    );
    $result = $this->eventbriteClient->getOrganizations('test_key');
    $this->assertIsArray($result);
  }

  /**
   * Tests memory usage with large responses including organizer data.
   *
   * @covers ::doExtractJsonResults
   */
  public function testLargeResponseHandlingWithOrganizerData() {
    // Create a large response to test memory handling with expanded organizer data
    $large_events = [];
    for ($i = 1; $i <= 500; $i++) {
      $large_events[] = [
        'id' => "event{$i}",
        'name' => ['text' => "Event {$i}"],
        'description' => ['text' => str_repeat("Large event description ", 50)],
        'organizer' => [
          'id' => "org{$i}",
          'name' => "Organizer {$i}",
          'description' => [
            'text' => str_repeat("Large organizer description ", 50)
          ],
          'logo' => [
            'url' => "https://example.com/logo{$i}.png"
          ]
        ]
      ];
    }

    $large_response = [
      'events' => $large_events,
      'pagination' => ['has_more_items' => FALSE]
    ];

    $this->mockHandler->append(
      new Response(200, [], json_encode($large_response))
    );

    $initial_memory = memory_get_usage();
    $result = $this->eventbriteClient->getOrganizationEventsByTimeStatus(
      'test_key',
      'test_org',
      'current_future',
      'live'
    );
    $final_memory = memory_get_usage();

    $this->assertIsArray($result);
    $this->assertArrayHasKey('data', $result);
    $this->assertCount(500, $result['data']);

    // Verify organizer data is present
    $this->assertArrayHasKey('organizer', $result['data'][0]);
    $this->assertArrayHasKey('description', $result['data'][0]['organizer']);

    // Ensure memory usage is reasonable (less than 100MB increase due to expanded data)
    $memory_increase = $final_memory - $initial_memory;
    $this->assertLessThan(100 * 1024 * 1024, $memory_increase);
  }

  /**
   * Tests expand parameter behavior in different scenarios.
   *
   * @covers ::getOrganizationEventsByTimeStatus
   */
  public function testExpandParameterBehavior() {
    $response_data = [
      'events' => [
        [
          'id' => 'event123',
          'organizer' => [
            'id' => 'org123',
            'name' => 'Expanded Organizer'
          ]
        ]
      ],
      'pagination' => ['has_more_items' => FALSE]
    ];

    // Test multiple scenarios to ensure expand=organizer is always added
    $scenarios = [
      // No config
      [],
      // Config with continue_key
      ['continue_key' => 'token123'],
      // Config with empty continue_key
      ['continue_key' => ''],
    ];

    foreach ($scenarios as $config) {
      $this->mockHandler->append(
        new Response(200, [], json_encode($response_data))
      );
    }

    foreach ($scenarios as $config) {
      $this->eventbriteClient->getOrganizationEventsByTimeStatus(
        'test_key',
        'test_org',
        'current_future',
        'live',
        $config
      );

      // Verify expand=organizer was added in each case
      $last_request = $this->mockHandler->getLastRequest();
      $query_string = (string) $last_request->getUri()->getQuery();
      $this->assertStringContainsString('expand=organizer', $query_string);
    }
  }

}
