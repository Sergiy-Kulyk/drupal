<?php

namespace Drupal\eventbrite\Feeds\Parser;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\feeds\Event\EventDispatcherTrait;
use Drupal\feeds\Event\FeedsEvents;
use Drupal\feeds\Event\ReportEvent;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\DynamicItem;
use Drupal\feeds\Feeds\Parser\ParserBase;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResult;
use Drupal\feeds\StateInterface;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\eventbrite\EventbriteApiSettingsOperations;
use Drupal\eventbrite\EventbriteClientInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines an Eventbrite feed parser.
 *
 * @FeedsParser(
 *   id = "eventbrite_events_response",
 *   title = "Eventbrite Events Response",
 *   description = @Translation("Parse Eventbrite Events Response."),
 * )
 */
class EventbriteParser extends ParserBase implements ContainerFactoryPluginInterface {

  use EventDispatcherTrait;

  /**
   * The eventbrite feed client service.
   *
   * @var \Drupal\eventbrite\EventbriteClientInterface
   */
  protected $eventbriteClient;

  /**
   * Constructs a EventbriteParser object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\eventbrite\EventbriteClientInterface $eventbrite_client
   *   The eventbrite feed client service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventbriteClientInterface $eventbrite_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->eventbriteClient = $eventbrite_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('eventbrite.eventbrite_api_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function parse(FeedInterface $feed, FetcherResultInterface $fetcher_result, StateInterface $state) {
    $result = new ParserResult();
    $raw = $fetcher_result->getRaw();
    $items = Json::decode($raw);
    $mapping = $this->getMappingSources();
    $state->logMessages($feed);
    foreach ($items as $item) {
      if ($item !== NULL) {
        $feed_item = new DynamicItem();
        foreach ($mapping as $destination_field => $field_settings) {
          $value = NULL;
          if ($field_settings['origin'] == 'feeds_entity') {
            $value = $feed->get($field_settings['feed_data_reference'])
              ->getValue();
          }
          if ($field_settings['origin'] == 'api') {
            $value = $this->prepareApiFieldValue($item, $field_settings, $feed, $state);
          }
          if ($value !== NULL) {
            $feed_item->set($destination_field, $value);
          }
        }
        $result->addItem($feed_item);
      }
    }
    return $result;
  }

  /**
   * Process API data for the field value.
   *
   * @param array $item
   *   API data item array.
   * @param array $field_settings
   *   API data process settings.
   * @param \Drupal\feeds\FeedInterface $feed
   *   Feed.
   * @param \Drupal\feeds\StateInterface $state
   *   The state object.
   *
   * @return array|mixed
   *   Processed value.
   */
  protected function prepareApiFieldValue(array $item, array $field_settings, FeedInterface $feed, StateInterface $state): array|NULL {
    $val = NULL;
    if (isset($field_settings['api_response_call'])) {
      $process_value = $this->prepareApiResponseValue($item, $field_settings, $feed, $state);
      if (is_string($process_value)) {
        $field_settings['conversion'] = 'string';
      }
    }
    else {
      if (isset($field_settings['get_multiple_keys']) && $field_settings['get_multiple_keys'] === TRUE) {
        $process_value = $this->getProcessMultipleKeysValue($item, $field_settings['api_data_reference']);
      } else {
        $process_value = $this->getProcessValue($item, $field_settings['api_data_reference']);
      }
    }
    switch ($field_settings['conversion']) {
      case 'file_reference':
        if ($process_value) {
          $val = $this->prepareFileReference($process_value, $item['id'], $feed, $item);
        }
        break;

      case 'combined_values_string':
        $string_array = [];
        foreach ($field_settings['values_to_combine'] as $subkey) {
          if (isset($process_value[$subkey]) && !empty($process_value[$subkey]) && $process_value[$subkey] !== '') {
            $string_array[] = $process_value[$subkey];
          }
        }
        if (!empty($string_array)) {
          $val = [
            [
              'value' => implode(', ', $string_array),
            ],
          ];
        }
        break;

      case 'term_text_contains_reference':
        $val = [];
        $term_name = 'Other';
        $strings = [
          ['search_val' => 'first aid', 'term_name'=> 'Baby & Child First Aid'],
          ['search_val' => 'nearly new','term_name'=> 'Nearly New Sales'],
          ['search_val' => 'walk and talk', 'term_name'=> 'Walk & Talk'],
          ['search_val' => 'walk & talk', 'term_name'=> 'Walk & Talk'],
          ['search_val' => 'baby cafe', 'term_name'=> 'Baby Cafe'],
        ];
        foreach ($strings as $term_data) {
          $process_value_processed = strtolower($process_value);
          if (str_contains($process_value_processed, $term_data['search_val'])) {
            $term_name = $term_data['term_name'];
            break;
          }
        }
        $terms = \Drupal::entityTypeManager()
          ->getStorage('taxonomy_term')
          ->loadByProperties(['name' => $term_name]);
        if (!empty($terms)) {
          $term = reset($terms);
          $val = [['target_id' => $term->id()]];
        }
        break;

      case 'datetime':
        $date = DrupalDateTime::createFromFormat('Y-m-d\TH:i:s\Z', $process_value, \Drupal::config('system.date')->get('timezone.default'));
        $date_val = $date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
        $val = [['value' => $date_val]];
        break;

      case 'daterange':
        $start_date = $process_value['start']['utc'];
        $end_date = $process_value['end']['utc'];
        $start_date = DrupalDateTime::createFromFormat('Y-m-d\TH:i:s\Z', $start_date, \Drupal::config('system.date')->get('timezone.default'));
        $end_date = DrupalDateTime::createFromFormat('Y-m-d\TH:i:s\Z', $end_date, \Drupal::config('system.date')->get('timezone.default'));
        $start_date_val = $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
        $end_date_val = $end_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
        $val = [[
          'value' => $start_date_val,
          'end_value' => $end_date_val,
        ]];
        break;

      case 'link':
        $val = [[
          'title' => 'Register Now',
          'uri' => $process_value,
          'options' => [],
        ]];
        break;

      case 'string':
        $val = [];
        if (!empty($process_value)) {
          $val = [['value' => $process_value]];
        }
        break;

      case 'string_format':
        $val = [[
          'value' => $process_value,
          'summary' => NULL,
          'format' => 'full_html',
        ]];
        break;

      case 'boolean':
        $val = $field_settings['reverse'] ? [['value' => !$process_value]] : [['value' => $process_value]];
        break;

      case 'float':
        $val = [['value' => floatval($process_value)]];
        break;

      case 'pricing':
        if ($process_value == TRUE) {
          $val = [['value' => 'FREE']];
        } else {
          $val = [['value' => 'Check pricing on the event page - ' . $this->getProcessValue($item, ['url'])]];
        }

        break;

      default:
        $val = NULL;
    }

    return $val;
  }

  /**
   * Return multiple keys value from the API response array.
   *
   * @param array $item
   *   Response array.
   * @param array $reference_array
   *   Indexed array of keys.
   */
  protected function getProcessMultipleKeysValue(array $item, array $reference_array) {
    $process_value = [];

    foreach ($reference_array as $subkey) {
      if (isset($item[$subkey]) && !empty($item[$subkey])) {
        $process_value[$subkey] = $item[$subkey];
      }
    }
    if (empty($process_value)) {
      return NULL;
    }
    return $process_value;
  }

  /**
   * Recursively return value from the API response array by checking levels.
   *
   * @param array $item
   *   Response array.
   * @param array $reference_array
   *   Indexed array of keys.
   * @param int $level
   *   Response level to process.
   */
  protected function getProcessValue(array $item, array $reference_array, int $level = 0) {
    $level_item = $reference_array[$level];
    if (!isset($item[$level_item])) {
      return NULL;
    }
    $process_value = $item[$level_item];
    $total_levels = count($reference_array) - 1;
    // Check next level.
    if ($total_levels > $level) {
      $process_value = $this->getProcessValue($process_value, $reference_array, $level + 1);
    }
    return $process_value;
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingSources() {
    return [
      'title' => [
        'label' => t('Title'),
        'origin' => 'api',
        'api_data_reference' => [
          'name',
          'text',
        ],
        'conversion' => 'string',
      ],
      'field_event_organiser' => [
        'label' => t('Organizer'),
        'origin' => 'api',
        'api_data_reference' => [
          'organiser',
          'name',
        ],
        'conversion' => 'string',
      ],
      'body' => [
        'label' => t('Body'),
        'origin' => 'api',
        'api_data_reference' => [
          'description',
          'html'
        ],
        'conversion' => 'string_format',
      ],
      'field_tickets_link' => [
        'label' => t('Link'),
        'origin' => 'api',
        'api_data_reference' => ['url'],
        'conversion' => 'link',
      ],
      'field_date' => [
        'label' => t('Event date'),
        'origin' => 'api',
        'get_multiple_keys' => TRUE,
        'api_data_reference' => [
          'start',
          'end',
        ],
        'conversion' => 'daterange',
      ],
      'field_eventbrite_id' => [
        'label' => t('External ID'),
        'origin' => 'api',
        'api_data_reference' => ['id'],
        'conversion' => 'string',
      ],
      'field_town_city' => [
        'label' => t('City'),
        'origin' => 'api',
        'api_response_call' => EventbriteApiSettingsOperations::GET_VENUE,
        'api_data_reference' => 'venue_id',
        'api_response_data_reference' => [
          'address',
          'city',
        ],
        // A key to check in the original response.
        'check_key' => 'venue',
        'conversion' => 'string',
      ],
      'field_postcode' => [
        'label' => t('Postcode'),
        'origin' => 'api',
        'api_response_call' => EventbriteApiSettingsOperations::GET_VENUE,
        'api_data_reference' => 'venue_id',
        'api_response_data_reference' => [
          'address',
          'postal_code',
        ],
        // A key to check in the original response.
        'check_key' => 'venue',
        'conversion' => 'string',
      ],
      'field_county' => [
        'label' => t('Region county'),
        'origin' => 'api',
        'api_response_call' => EventbriteApiSettingsOperations::GET_VENUE,
        'api_data_reference' => 'venue_id',
        'api_response_data_reference' => [
          'address',
          'region',
        ],
        // A key to check in the original response.
        'check_key' => 'venue',
        'conversion' => 'string',
      ],
      'field_address_line_1' => [
        'label' => t('Address 1'),
        'origin' => 'api',
        'api_response_call' => EventbriteApiSettingsOperations::GET_VENUE,
        'api_data_reference' => 'venue_id',
        'api_response_data_reference' => [
          'address',
          'address_1',
        ],
        // A key to check in the original response.
        'check_key' => 'venue',
        'conversion' => 'string',
      ],
      'field_address_line_2' => [
        'label' => t('Address 2'),
        'origin' => 'api',
        'api_response_call' => EventbriteApiSettingsOperations::GET_VENUE,
        'api_data_reference' => 'venue_id',
        'api_response_data_reference' => [
          'address',
          'address_2',
        ],
        // A key to check in the original response.
        'check_key' => 'venue',
        'conversion' => 'string',
      ],
      'field_location_name' => [
        'label' => t('Location name'),
        'origin' => 'api',
        'api_response_call' => EventbriteApiSettingsOperations::GET_VENUE,
        'api_data_reference' => 'venue_id',
        'api_response_data_reference' => [
          'name',
        ],
        // A key to check in the original response.
        'check_key' => 'venue',
        'conversion' => 'string',
      ],
      'field_latitude' => [
        'label' => t('Latitude'),
        'origin' => 'api',
        'api_response_call' => EventbriteApiSettingsOperations::GET_VENUE,
        'api_data_reference' => 'venue_id',
        'api_response_data_reference' => [
          'latitude',
        ],
        // A key to check in the original response.
        'check_key' => 'venue',
        'conversion' => 'float',
      ],
      'field_longitude' => [
        'label' => t('Longitude'),
        'origin' => 'api',
        'api_response_call' => EventbriteApiSettingsOperations::GET_VENUE,
        'api_data_reference' => 'venue_id',
        'api_response_data_reference' => [
          'longitude',
        ],
        // A key to check in the original response.
        'check_key' => 'venue',
        'conversion' => 'float',
      ],
      'field_image' => [
        'label' => t('Image'),
        'origin' => 'api',
        'api_data_reference' => [
          'logo',
          'original',
          'url',
        ],
        'conversion' => 'file_reference',
      ],
      'field_thumbnail' => [
        'label' => t('Thumbnail'),
        'origin' => 'api',
        'api_data_reference' => [
          'logo',
          'original',
          'url',
        ],
        'conversion' => 'file_reference',
      ],
      'field_cost_pricing' => [
        'label' => t('Pricing'),
        'origin' => 'api',
        'api_data_reference' => [
          'is_free',
        ],
        'conversion' => 'pricing',
      ],
      'field_event_type' => [
        'label' => t('Event Type'),
        'origin' => 'api',
        'api_data_reference' => [
          'name',
          'text',
        ],
        'conversion' => 'term_text_contains_reference',
      ],
      'field_online' => [
        'label' => t('Online'),
        'origin' => 'api',
        'api_data_reference' => [
          'online_event',
        ],
        'conversion' => 'boolean',
        'reverse' => FALSE,
      ],
//      'field_branch' => [
//        'label' => t('Branch'),
//        'origin' => 'feeds_entity',
//        'feed_data_reference' => 'field_branch',
//      ],
      'field_image_accent_colour' => [
        'label' => t('Color'),
        'origin' => 'feeds_entity',
        'feed_data_reference' => 'field_color',
      ],
    ];
  }

  /**
   * Download file and prepare field value.
   *
   * @param string $url
   *   File url.
   * @param string $id
   *   Event id.
   * @param \Drupal\feeds\FeedInterface $feed
   *   Feed.
   *
   * @return array|null
   *   Result.
   */
  protected function prepareFileReference(string $url, string $id, FeedInterface $feed, array $item): array|null {
    $val = NULL;
    $directory = 'public://eventbrite-files';
    $name = $directory . '/eventbrite-file-' . $id;
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    $file_system->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    try {
      $response = \Drupal::httpClient()->get($url, ['sink' => $name]);
    }
    catch (\Exception $e) {
      \Drupal::logger('eventbrite')->warning($e->getMessage());
      \Drupal::messenger()->addWarning("Can't download file for Event " . $id . ".");
      $this->dispatchEvent(FeedsEvents::REPORT, new ReportEvent($feed, 'skipped', "Can't download file for Event " . $id . ". Skipped processing file."));
      return $val;
    }
    if ($response->getStatusCode() >= 200 || $response->getStatusCode() < 300) {
      $type = $this->mime2ext(mime_content_type($name));
      if (!$type) {
        unlink('eventbrite-file-' . $id, $name);
        return $val;
      }
      rename($directory . '/eventbrite-file-' . $id, $name . '.' . $type);
      $file_name = basename($name . '.' . $type);
      /** @var \Drupal\file\FileStorage $file_storage */
      $file_storage = \Drupal::entityTypeManager()->getStorage('file');
      $files = $file_storage->loadByProperties(['filename' => $file_name]);
      $text = $this->getProcessValue($item, ['name', 'text']);
      if (!empty($files)) {
        $file = reset($files);
        $val = [['target_id' => $file->id(), 'alt' => $text, 'title' => $text]];
      }
      else {
        $file = File::create([
          'filename' => $file_name,
          'uri' => 'public://eventbrite-files/' . $file_name,
          'status' => 0,
          'uid' => 1,
        ]);
        $file->save();
        $val = [['target_id' => $file->id(), 'alt' => $text, 'title' => $text]];
      }
    }
    else {
      \Drupal::messenger()->addWarning("Can't download image for Event " . $id . ".");
      $this->dispatchEvent(FeedsEvents::REPORT, new ReportEvent($feed, 'skipped', "Can't download file for Event " . $id . ". Skipped processing file."));
    }
    return $val;
  }

  /**
   * Get the value from API response.
   *
   * @param array $item
   *   API data item array.
   * @param array $field_settings
   *   API data process settings.
   * @param \Drupal\feeds\FeedInterface $feed
   *   Feed.
   * @param \Drupal\feeds\StateInterface $state
   *   The state object.
   *
   * @return array|string|mixed
   *   Response processed value.
   */
  protected function prepareApiResponseValue(array $item, array $field_settings, FeedInterface $feed, StateInterface $state) {
    $process_value = NULL;
    // Check if event original response key is present.
    if (isset($item[$field_settings['check_key']]) && $item[$field_settings['check_key']] !== NULL) {
      $process_value = $this->getProcessValue($item[$field_settings['check_key']], $field_settings['api_response_data_reference']);
      return $process_value;
    }
    if (isset($field_settings['api_response_call']) && $field_settings['api_response_call'] == 'get_venue') {
      // Get venue data in case we already called it for this event.
      if (property_exists($state, 'venueData') && isset($state->venueData[$item['id']])) {
        $process_value = $this->getProcessValue($state->venueData[$item['id']], $field_settings['api_response_data_reference']);
      }
      else {
        $venue_id = $item[$field_settings['api_data_reference']] ?? $item[$field_settings['api_data_reference']];
        if ($venue_id == NULL && in_array('name', $field_settings['api_response_data_reference'])) {
          return $item['online_event'] ? 'Virtual Session' : 'TBA';
        }
        $venue_data = NULL;
        $key = $feed->get('field_api_key')->value;
        if ($venue_id !== NULL && $key !== NULL) {
          try {
            $venue_data = $this->eventbriteClient->getVenue($key, $venue_id);
          }
          catch (\Exception $e) {
            \Drupal::logger('eventbrite')->warning($e->getMessage());
            $this->dispatchEvent(FeedsEvents::REPORT, new ReportEvent($feed, 'skipped', "Can't get venue for Event " . $item['id'] . ". Skipped processing venue."));
            return $process_value;
          }
        }
        if (!empty($venue_data) && isset($venue_data['data']) && $venue_data['data'] !== NULL) {
          $process_value = $this->getProcessValue($venue_data['data'], $field_settings['api_response_data_reference']);
          // Save event venue data in state for the next field.
          $state->venueData[$item['id']] = $venue_data['data'];
        }
      }
    }
    return $process_value;
  }

  /**
   * Defines file extension text.
   *
   * @param string $mime
   *   Mime type.
   *
   * @return string|bool
   *   Extension text or empty result.
   */
  protected function mime2ext(string $mime): string|bool {
    $mime_map = [
      'image/bmp' => 'bmp',
      'image/x-bmp' => 'bmp',
      'image/x-bitmap' => 'bmp',
      'image/x-xbitmap' => 'bmp',
      'image/x-win-bitmap' => 'bmp',
      'image/x-windows-bmp' => 'bmp',
      'image/ms-bmp' => 'bmp',
      'image/x-ms-bmp' => 'bmp',
      'image/cdr' => 'cdr',
      'image/x-cdr' => 'cdr',
      'image/gif' => 'gif',
      'text/html' => 'html',
      'image/x-icon' => 'ico',
      'image/x-ico' => 'ico',
      'image/vnd.microsoft.icon' => 'ico',
      'image/jp2' => 'jp2',
      'image/jpx' => 'jp2',
      'image/jpm' => 'jp2',
      'image/jpeg' => 'jpeg',
      'image/pjpeg' => 'jpeg',
      'image/png' => 'png',
      'image/x-png' => 'png',
      'image/vnd.adobe.photoshop' => 'psd',
      'image/svg+xml' => 'svg',
      'image/tiff' => 'tiff',
      'image/webp' => 'webp',
    ];

    return $mime_map[$mime] ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedCustomSourcePlugins(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultFeedConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

}
