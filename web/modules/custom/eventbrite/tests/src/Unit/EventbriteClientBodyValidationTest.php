<?php

namespace Drupal\Tests\eventbrite\Unit;

use Drupal\eventbrite\EventbriteClient;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Client as HttpClient;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * Tests for body validation in EventbriteClient.
 *
 * @coversDefaultClass \Drupal\eventbrite\EventbriteClient
 * @group eventbrite
 */
class EventbriteClientBodyValidationTest extends UnitTestCase {

  use ProphecyTrait;

  /**
   * The EventbriteClient instance.
   *
   * @var \Drupal\eventbrite\EventbriteClient
   */
  protected $eventbriteClient;

  /**
   * The reflection class for accessing protected methods.
   *
   * @var \ReflectionClass
   */
  protected $reflectionClass;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $http_client = $this->prophesize(HttpClient::class);
    $this->eventbriteClient = new EventbriteClient($http_client->reveal());
    $this->reflectionClass = new ReflectionClass($this->eventbriteClient);
  }

  /**
   * Tests doValidateBody with object type.
   *
   * @covers ::doValidateBody
   */
  public function testDoValidateBodyWithObject() {
    $method = $this->reflectionClass->getMethod('doValidateBody');
    $method->setAccessible(TRUE);

    $body = [
      'name' => 'Test Event',
      'description' => 'Test Description',
    ];

    $spec = [
      'type' => 'object',
      'allow_any_key' => FALSE,
      'fields' => [
        'name' => [
          'type' => 'string',
          'required' => TRUE,
        ],
        'description' => [
          'type' => 'string',
          'required' => FALSE,
        ],
        'optional_field' => [
          'type' => 'string',
          'required' => FALSE,
          'default' => 'default_value',
        ],
      ],
    ];

    $result = $method->invoke($this->eventbriteClient, $body, $spec);

    $this->assertIsArray($result);
    $this->assertEquals('Test Event', $result['name']);
    $this->assertEquals('Test Description', $result['description']);
    $this->assertEquals('default_value', $result['optional_field']);
  }

  /**
   * Tests doValidateBody with missing required field.
   *
   * @covers ::doValidateBody
   */
  public function testDoValidateBodyWithMissingRequiredField() {
    $method = $this->reflectionClass->getMethod('doValidateBody');
    $method->setAccessible(TRUE);

    $body = [
      'description' => 'Test Description',
    ];

    $spec = [
      'type' => 'object',
      'allow_any_key' => FALSE,
      'fields' => [
        'name' => [
          'type' => 'string',
          'required' => TRUE,
        ],
        'description' => [
          'type' => 'string',
          'required' => FALSE,
        ],
      ],
    ];

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Key value name (ROOT) is not provided.');

    $method->invoke($this->eventbriteClient, $body, $spec);
  }

  /**
   * Tests doValidateBody with array type.
   *
   * @covers ::doValidateBody
   */
  public function testDoValidateBodyWithArray() {
    $method = $this->reflectionClass->getMethod('doValidateBody');
    $method->setAccessible(TRUE);

    $body = ['item1', 'item2', 'item3'];

    $spec = [
      'type' => 'array',
      'item' => [
        'type' => 'string',
      ],
    ];

    $result = $method->invoke($this->eventbriteClient, $body, $spec);

    $this->assertIsArray($result);
    $this->assertEquals(['item1', 'item2', 'item3'], $result);
  }

  /**
   * Tests doValidateBody with nested objects.
   *
   * @covers ::doValidateBody
   */
  public function testDoValidateBodyWithNestedObjects() {
    $method = $this->reflectionClass->getMethod('doValidateBody');
    $method->setAccessible(TRUE);

    $body = [
      'event' => [
        'name' => 'Test Event',
        'venue' => [
          'name' => 'Test Venue',
          'address' => 'Test Address',
        ],
        'organizer' => [
          'id' => 'org123',
          'name' => 'Test Organizer'
        ]
      ],
    ];

    $spec = [
      'type' => 'object',
      'allow_any_key' => FALSE,
      'fields' => [
        'event' => [
          'type' => 'object',
          'allow_any_key' => FALSE,
          'fields' => [
            'name' => [
              'type' => 'string',
              'required' => TRUE,
            ],
            'venue' => [
              'type' => 'object',
              'allow_any_key' => FALSE,
              'fields' => [
                'name' => [
                  'type' => 'string',
                  'required' => TRUE,
                ],
                'address' => [
                  'type' => 'string',
                  'required' => TRUE,
                ],
              ],
            ],
            'organizer' => [
              'type' => 'object',
              'allow_any_key' => FALSE,
              'fields' => [
                'id' => [
                  'type' => 'string',
                  'required' => TRUE,
                ],
                'name' => [
                  'type' => 'string',
                  'required' => TRUE,
                ],
              ],
            ],
          ],
        ],
      ],
    ];

    $result = $method->invoke($this->eventbriteClient, $body, $spec);

    $this->assertIsArray($result);
    $this->assertEquals('Test Event', $result['event']['name']);
    $this->assertEquals('Test Venue', $result['event']['venue']['name']);
    $this->assertEquals('Test Address', $result['event']['venue']['address']);
    $this->assertEquals('org123', $result['event']['organizer']['id']);
    $this->assertEquals('Test Organizer', $result['event']['organizer']['name']);
  }

  /**
   * Tests doValidateBody with primitive types.
   *
   * @covers ::doValidateBody
   */
  public function testDoValidateBodyWithPrimitiveTypes() {
    $method = $this->reflectionClass->getMethod('doValidateBody');
    $method->setAccessible(TRUE);

    // Test string type
    $result = $method->invoke($this->eventbriteClient, 123, ['type' => 'string']);
    $this->assertEquals('123', $result);

    // Test boolean type
    $result = $method->invoke($this->eventbriteClient, 1, ['type' => 'boolean']);
    $this->assertTrue($result);

    // Test integer type
    $result = $method->invoke($this->eventbriteClient, '123', ['type' => 'integer']);
    $this->assertEquals(123, $result);

    // Test float type
    $result = $method->invoke($this->eventbriteClient, '123.45', ['type' => 'float']);
    $this->assertEquals(123.45, $result);
  }

  /**
   * Tests doValidateBody with invalid object input.
   *
   * @covers ::doValidateBody
   */
  public function testDoValidateBodyWithInvalidObjectInput() {
    $method = $this->reflectionClass->getMethod('doValidateBody');
    $method->setAccessible(TRUE);

    $spec = [
      'type' => 'object',
      'allow_any_key' => FALSE,
      'fields' => [],
    ];

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The body element (ROOT) must be an object or associative array.');

    $method->invoke($this->eventbriteClient, 'not_an_object', $spec);
  }

  /**
   * Tests doValidateBody with invalid array input.
   *
   * @covers ::doValidateBody
   */
  public function testDoValidateBodyWithInvalidArrayInput() {
    $method = $this->reflectionClass->getMethod('doValidateBody');
    $method->setAccessible(TRUE);

    $spec = [
      'type' => 'array',
      'item' => ['type' => 'string'],
    ];

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The body element (ROOT) must be an array.');

    $method->invoke($this->eventbriteClient, 'not_an_array', $spec);
  }

  /**
   * Tests doValidateBody with unknown type.
   *
   * @covers ::doValidateBody
   */
  public function testDoValidateBodyWithUnknownType() {
    $method = $this->reflectionClass->getMethod('doValidateBody');
    $method->setAccessible(TRUE);

    $spec = ['type' => 'unknown_type'];

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown type unknown_type');

    $method->invoke($this->eventbriteClient, 'test', $spec);
  }

  /**
   * Tests doValidateBody with allow_any_key option.
   *
   * @covers ::doValidateBody
   */
  public function testDoValidateBodyWithAllowAnyKey() {
    $method = $this->reflectionClass->getMethod('doValidateBody');
    $method->setAccessible(TRUE);

    $body = [
      'known_field' => 'value1',
      'unknown_field' => 'value2',
    ];

    $spec = [
      'type' => 'object',
      'allow_any_key' => FALSE,
      'fields' => [
        'known_field' => [
          'type' => 'string',
          'required' => FALSE,
        ],
      ],
    ];

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown keys: unknown_field');

    $method->invoke($this->eventbriteClient, $body, $spec);
  }

  /**
   * Tests doValidateBody with stdClass object input.
   *
   * @covers ::doValidateBody
   */
  public function testDoValidateBodyWithStdClassObject() {
    $method = $this->reflectionClass->getMethod('doValidateBody');
    $method->setAccessible(TRUE);

    $body = new \stdClass();
    $body->name = 'Test Event';
    $body->description = 'Test Description';
    $body->organizer = new \stdClass();
    $body->organizer->id = 'org123';
    $body->organizer->name = 'Test Organizer';

    $spec = [
      'type' => 'object',
      'allow_any_key' => FALSE,
      'fields' => [
        'name' => [
          'type' => 'string',
          'required' => TRUE,
        ],
        'description' => [
          'type' => 'string',
          'required' => FALSE,
        ],
        'organizer' => [
          'type' => 'object',
          'allow_any_key' => FALSE,
          'fields' => [
            'id' => [
              'type' => 'string',
              'required' => TRUE,
            ],
            'name' => [
              'type' => 'string',
              'required' => TRUE,
            ],
          ],
        ],
      ],
    ];

    $result = $method->invoke($this->eventbriteClient, $body, $spec);

    $this->assertIsArray($result);
    $this->assertEquals('Test Event', $result['name']);
    $this->assertEquals('Test Description', $result['description']);
    $this->assertEquals('org123', $result['organizer']['id']);
    $this->assertEquals('Test Organizer', $result['organizer']['name']);
  }

  /**
   * Tests doValidateBody with expand-related data structures.
   *
   * @covers ::doValidateBody
   */
  public function testDoValidateBodyWithExpandData() {
    $method = $this->reflectionClass->getMethod('doValidateBody');
    $method->setAccessible(TRUE);

    $body = [
      'events' => [
        [
          'id' => 'event123',
          'name' => 'Test Event',
          'organizer' => [
            'id' => 'org123',
            'name' => 'Test Organizer',
            'description' => [
              'text' => 'Organizer description'
            ]
          ]
        ]
      ]
    ];

    $spec = [
      'type' => 'object',
      'allow_any_key' => FALSE,
      'fields' => [
        'events' => [
          'type' => 'array',
          'item' => [
            'type' => 'object',
            'allow_any_key' => FALSE,
            'fields' => [
              'id' => [
                'type' => 'string',
                'required' => TRUE,
              ],
              'name' => [
                'type' => 'string',
                'required' => TRUE,
              ],
              'organizer' => [
                'type' => 'object',
                'allow_any_key' => FALSE,
                'fields' => [
                  'id' => [
                    'type' => 'string',
                    'required' => TRUE,
                  ],
                  'name' => [
                    'type' => 'string',
                    'required' => TRUE,
                  ],
                  'description' => [
                    'type' => 'object',
                    'allow_any_key' => FALSE,
                    'fields' => [
                      'text' => [
                        'type' => 'string',
                        'required' => FALSE,
                      ],
                    ],
                  ],
                ],
              ],
            ],
          ],
        ],
      ],
    ];

    $result = $method->invoke($this->eventbriteClient, $body, $spec);

    $this->assertIsArray($result);
    $this->assertArrayHasKey('events', $result);
    $this->assertCount(1, $result['events']);
    $this->assertEquals('event123', $result['events'][0]['id']);
    $this->assertEquals('Test Event', $result['events'][0]['name']);
    $this->assertEquals('org123', $result['events'][0]['organizer']['id']);
    $this->assertEquals('Test Organizer', $result['events'][0]['organizer']['name']);
    $this->assertEquals('Organizer description', $result['events'][0]['organizer']['description']['text']);
  }

  /**
   * Tests doValidateBody with complex array validation.
   *
   * @covers ::doValidateBody
   */
  public function testDoValidateBodyWithComplexArrays() {
    $method = $this->reflectionClass->getMethod('doValidateBody');
    $method->setAccessible(TRUE);

    $body = [
      'event_filters' => [
        'event_ids' => ['123', '456', '789'],
        'venue_filters' => ['venue1', 'venue2'],
        'organizer_filters' => ['org1', 'org2']
      ]
    ];

    $spec = [
      'type' => 'object',
      'allow_any_key' => FALSE,
      'fields' => [
        'event_filters' => [
          'type' => 'object',
          'allow_any_key' => FALSE,
          'fields' => [
            'event_ids' => [
              'type' => 'array',
              'item' => [
                'type' => 'string'
              ]
            ],
            'venue_filters' => [
              'type' => 'array',
              'item' => [
                'type' => 'string'
              ]
            ],
            'organizer_filters' => [
              'type' => 'array',
              'item' => [
                'type' => 'string'
              ]
            ]
          ]
        ]
      ]
    ];

    $result = $method->invoke($this->eventbriteClient, $body, $spec);

    $this->assertIsArray($result);
    $this->assertArrayHasKey('event_filters', $result);
    $this->assertEquals([
      '123',
      '456',
      '789'
    ], $result['event_filters']['event_ids']);
    $this->assertEquals([
      'venue1',
      'venue2'
    ], $result['event_filters']['venue_filters']);
    $this->assertEquals([
      'org1',
      'org2'
    ], $result['event_filters']['organizer_filters']);
  }

}
