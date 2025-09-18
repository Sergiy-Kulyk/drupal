<?php

namespace Drupal\eventbrite\Feeds\Fetcher;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\feeds\Exception\FetchException;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\File\FeedsFileSystemInterface;
use Drupal\feeds\Plugin\Type\Fetcher\FetcherInterface;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Result\RawFetcherResult;
use Drupal\feeds\StateInterface;
use Drupal\eventbrite\EventbriteClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines an Eventbrite fetcher.
 *
 * @FeedsFetcher(
 *   id = "eventbrite_events_fetcher",
 *   title = @Translation("Eventbrite API events"),
 *   description = @Translation("Downloads events data from the Eventbrite API."),
 * )
 */
class EventbriteFetcher extends PluginBase implements FetcherInterface, ContainerFactoryPluginInterface {

  /**
   * EventbriteApi service.
   *
   * @var \Drupal\eventbrite\EventbriteClient
   */
  protected $eventbriteApiService;

  /**
   * Drupal file system helper.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Drupal file system helper for Feeds.
   *
   * @var \Drupal\feeds\File\FeedsFileSystemInterface
   */
  protected $feedsFileSystem;

  /**
   * Constructs an UploadFetcher object.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param array $plugin_definition
   *   The plugin definition.
   * @param \Drupal\eventbrite\EventbriteClient $eventbrite_api_service
   *   The Guzzle client.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The Drupal file system helper.
   * @param \Drupal\feeds\File\FeedsFileSystemInterface $feeds_file_system
   *   The Drupal file system helper for Feeds.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EventbriteClient $eventbrite_api_service, FileSystemInterface $file_system, FeedsFileSystemInterface $feeds_file_system) {
    $this->eventbriteApiService = $eventbrite_api_service;
    $this->fileSystem = $file_system;
    $this->feedsFileSystem = $feeds_file_system;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('eventbrite.eventbrite_api_service'),
      $container->get('file_system'),
      $container->get('feeds.file_system.in_progress'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function fetch(FeedInterface $feed, StateInterface $state) {
    $key = $feed->hasField('field_api_key') ? $feed->get('field_api_key')->value : NULL;
    $org_id = $feed->hasField('field_organisation_id') ? $feed->get('field_organisation_id')->value : NULL;
    $events_timing = 'current_future';
    if ($feed->hasField('field_events_timing') && !$feed->get('field_events_timing')->isEmpty()) {
      $events_timing = $feed->get('field_events_timing')->value;
    }
    $events_status = 'live';
    if ($feed->hasField('field_events_status') && !$feed->get('field_events_status')->isEmpty()) {
      $events_status = $feed->get('field_events_status')->value;
    }
    if ($key == NULL || $org_id == NULL) {
      throw new FetchException('Please provide key (field_api_key) and organisation id (field_organisation_id) data.');
    }
    try {
      if (property_exists($state, 'continueKey') && $state->continueKey !== NULL) {
        $response = $this->eventbriteApiService->getOrganizationEventsByTimeStatus($key, $org_id, $events_timing, $events_status, [
          'continue_key' => $state->continueKey,
        ]);
      }
      else {
        $response = $this->eventbriteApiService->getOrganizationEventsByTimeStatus($key, $org_id, $events_timing, $events_status);
      }
    }
    catch (\Exception $e) {
      throw new FetchException($e->getMessage());
    }
    if (isset($response['continue_key']) && $response['continue_key'] !== NULL) {
      // Save the next url in the state so we can get it next run.
      $state->continueKey = $response['continue_key'];
      if (isset($response['page_data'])) {
        if (!isset($state->total) || !$state->total) {
          $state->logMessages($feed);
          $state->total = 0;
        }
        $state->total = $state->total + count($response['data']);
        $state->progress($response['page_data']['object_count'], $state->total);
      }
      else {
        if (!isset($state->total) || !$state->total) {
          $state->total = 1;
        }
        // Up the total fetches by one.
        $state->total = $state->total + 1;
        // We don't know how many there are so say we're one away from one more.
        $state->progress($state->total, $state->total - 1);
      }
    }
    else {
      if (isset($response['page_data'])) {
        $state->total = $state->total + count($response['data']);
        $state->setMessage('Processed: ' . $response['page_data']['object_count'] . '/' . $state->total);
      }
      $state->progress($state->total, $state->total);
      $state->setCompleted();
    }
    if (empty($response)) {
      $state->setMessage($this->t('The feed has not been updated.'));
      throw new EmptyFeedException();
    }
    $encoded = json_encode($response['data']);

    return new RawFetcherResult($encoded, $this->fileSystem);
  }

}
