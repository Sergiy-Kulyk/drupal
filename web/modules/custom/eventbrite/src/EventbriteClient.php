<?php

namespace Drupal\eventbrite;

use Drupal\Component\Serialization\Json;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;

/**
 * EventbriteApi integration service.
 */
class EventbriteClient implements EventbriteClientInterface {

  private const METHODS = [
    EventbriteApiSettingsOperations::GET_ORGANIZATION_EVENTS_WITH_TIME_FILTER => [
      'http_method' => 'GET',
      'path' => '/v3/organizations/:organization_id/events/',
      'path_params' => [
        'organization_id' => [],
      ],
      'query_params' => [
        'name_filter' => [
          'required' => FALSE,
          'type' => 'string',
        ],
        'currency_filter' => [
          'required' => FALSE,
          'type' => 'string',
        ],
        'order_by' => [
          'required' => FALSE,
          'type' => 'string',
          'allowed_values' => [
            'start_asc',
            'start_desc',
            'created_asc',
            'created_desc',
            'name_asc',
            'name_desc',
          ],
        ],
        'series_filter' => [
          'required' => FALSE,
          'type' => 'array',
          'allowed_array_items_type' => 'string',
        ],
        'show_series_parent' => [
          'required' => FALSE,
          'type' => 'boolean',
        ],
        'status' => [
          'required' => FALSE,
          'type' => 'string',
          'allowed_values' => [
            'draft',
            'live',
            'started',
            'ended',
            'completed',
            'canceled',
            'all',
          ],
        ],
        'continuation' => [
          'required' => FALSE,
          'type' => 'string',
        ],
        'event_group_id' => [
          'required' => FALSE,
          'type' => 'string',
        ],
        'collection_id' => [
          'required' => FALSE,
          'type' => 'string',
        ],
        'page_size' => [
          'required' => FALSE,
          'type' => 'number',
        ],
        'time_filter' => [
          'required' => FALSE,
          'type' => 'string',
          'allowed_values' => [
            'all',
            'past',
            'current_future',
          ],
        ],
        'venue_filter' => [
          'required' => FALSE,
          'type' => 'array',
        ],
        'organizer_filter' => [
          'required' => FALSE,
          'type' => 'array',
        ],
        'inventory_type_filter' => [
          'required' => FALSE,
          'type' => 'array',
          'allowed_array_items_type' => 'string',
          'allowed_array_values' => [
            'limited',
            'reserved',
            'externally_ticketed',
          ],
        ],
        'event_ids_to_exclude' => [
          'required' => FALSE,
          'type' => 'array',
          'allowed_array_items_type' => 'string',
        ],
        'event_ids' => [
          'required' => FALSE,
          'type' => 'array',
          'allowed_array_items_type' => 'string',
        ],
        'collection_ids_to_exclude' => [
          'required' => FALSE,
          'type' => 'array',
          'allowed_array_items_type' => 'string',
        ],
        'expand' => [
          'required' => FALSE,
          'type' => 'string',
        ]
      ],
      'response_type' => 'json_results',
      'response_key' => 'events',
    ],
    EventbriteApiSettingsOperations::GET_ORGANIZATIONS => [
      'http_method' => 'GET',
      'path' => '/v3/users/me/organizations/',
      'path_params' => [],
      'query_params' => [],
      'response_type' => 'json_results',
      'response_key' => 'organizations',
    ],
    EventbriteApiSettingsOperations::GET_VENUE => [
      'http_method' => 'GET',
      'path' => '/v3/venues/:venue_id/',
      'path_params' => [
        'venue_id' => [],
      ],
      'query_params' => [],
      'response_type' => 'json_results',
      'response_key' => FALSE,
    ],
  ];

  /**
   * The HTTP client.
   */
  private HttpClient $httpClient;

  /**
   * The EventbriteClient constructor.
   */
  public function __construct(HttpClient $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrganizationEventsByTimeStatus($auth_key, $org_id, $time_filter, $status, $config = []): null|array|string {
    $query_params = [
      'time_filter' => $time_filter,
      'status' => $status,
    ];
    if (isset($config['continue_key']) && $config['continue_key'] !== '') {
      $query_params['continuation'] = $config['continue_key'];
    }
    $query_params['expand'] = 'organizer';
    return $this->doRequest($auth_key, EventbriteApiSettingsOperations::GET_ORGANIZATION_EVENTS_WITH_TIME_FILTER, ['organization_id' => $org_id], $query_params, [], 'paginated');
  }

  /**
   * {@inheritdoc}
   */
  public function getOrganizations($auth_key): array|string|null {
    $result = $this->doRequest($auth_key, EventbriteApiSettingsOperations::GET_ORGANIZATIONS);
    // Send requests until whole data returned.
    if (isset($result['page_data']['has_more_items']) && $result['page_data']['has_more_items'] == TRUE) {
      $paginated_result = [];
      $paginated_result['pages_data'][] = $result['data'];
      do {
        $continue_key = $result['continue_key'];
        $result = $this->doRequest($auth_key, EventbriteApiSettingsOperations::GET_ORGANIZATIONS, [], ['continuation' => $continue_key]);
        $paginated_result['pages_data'][] = $result['data'];
      } while ($result['page_data']['has_more_items'] == TRUE);
      $paginated_result['page_data'] = $result['page_data'];
      return $paginated_result;
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getVenue(string $auth_key, string $venue_id): array|string|null {
    return $this->doRequest($auth_key, EventbriteApiSettingsOperations::GET_VENUE, ['venue_id' => $venue_id]);
  }

  /**
   * Perform API operation.
   *
   * @param string $auth_key
   *   Private token key.
   * @param string $operation_id
   *   The operation identifier.
   * @param array $path_params
   *   The path parameters for operation.
   * @param array $query_params
   *   The query parameters for operation.
   * @param array $body
   *   The body data for operation.
   * @param string $return
   *   Return all results or paginated. Defaults to all results.
   *
   * @return array|string|null
   *   The operation results.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   *   In case of HTTP request error (or not 200 response code).
   */
  public function doRequest(string $auth_key, string $operation_id, array $path_params = [], array $query_params = [], array $body = [], string $return = 'all') : array|string|null {
    $http_method = self::METHODS[$operation_id]['http_method'];
    $path = self::METHODS[$operation_id]['path'];
    $path_param_definitions = self::METHODS[$operation_id]['path_params'];
    $query_param_definitions = self::METHODS[$operation_id]['query_params'];
    // Process and validate path params.
    $http_path_params = [];
    foreach ($path_param_definitions as $key => $definition) {
      if (isset($path_params[$key])) {
        $http_path_params[$key] = $path_params[$key];
      }
      elseif ($definition['default']) {
        $http_path_params[$key] = $definition['default'];
      }
      else {
        throw new \InvalidArgumentException(sprintf('Path parameter %s is not provided.', $key));
      }
    }
    foreach ($http_path_params as $key => $value) {
      if (\str_ends_with($path, ":$key")) {
        $path = \substr_replace($path, $value, -\strlen(":$key"), \strlen(":$key"));
      }
      else {
        $path = \str_replace(":$key/", $value . '/', $path);
      }
    }

    $url = new Uri(EventbriteApiSettingsOperations::EVENTBRITE_ENDPOINT_BASE_URL . $path);
    // Process and validate query params.
    if ($unexpected_query_params = array_diff_key($query_params, $query_param_definitions)) {
      throw new \InvalidArgumentException(sprintf('Unknown query params: %s', implode(', ', array_keys($unexpected_query_params))));
    }
    $http_query_params = [];
    foreach ($query_param_definitions as $key => $definition) {
      if (isset($query_params[$key])) {
        $definition = $query_param_definitions[$key];
        if (isset($definition['type'])) {
          $type = $definition['type'];
          if ($type == 'string' && !is_string($query_params[$key])) {
            throw new \InvalidArgumentException(sprintf('Query parameter %s is not valid. String value required.', $key));
          }
          if ($type == 'boolean' && !is_bool($query_params[$key])) {
            throw new \InvalidArgumentException(sprintf('Query parameter %s is not valid. Boolean value required.', $key));
          }
          if ($type == 'number' && !is_numeric($query_params[$key])) {
            throw new \InvalidArgumentException(sprintf('Query parameter %s is not valid. Number value required.', $key));
          }
          if ($type == 'array') {
            if (!is_array($query_params[$key])) {
              throw new \InvalidArgumentException(sprintf('Query parameter %s is not valid. Array value required.', $key));
            }
            if (isset($definition['allowed_array_items_type'])) {
              if ($definition['allowed_array_items_type'] == 'string' && $query_params[$key] !== array_filter($query_params[$key], 'is_string')) {
                throw new \InvalidArgumentException(sprintf('Query parameter %s is not valid. Array must contain only strings.', $key));
              }
              if ($definition['allowed_array_items_type'] == 'number' && $query_params[$key] !== array_filter($query_params[$key], 'is_numeric')) {
                throw new \InvalidArgumentException(sprintf('Query parameter %s is not valid. Array must contain only numbers.', $key));
              }
            }
          }
        }
        $http_query_params[$key] = $query_params[$key];
      }
      elseif (isset($definition['default'])) {
        $http_query_params[$key] = $definition['default'];
      }
      elseif ($definition['required']) {
        throw new \InvalidArgumentException(sprintf('Query parameter %s is not provided.', $key));
      }
    }
    if ($http_query_params) {
      $url = Uri::withQueryValues($url, $http_query_params);
    }
    // Add headers.
    $headers_body = $this->generateHeadersBody($auth_key, $http_method, $operation_id, $body);
    $headers = $headers_body['headers'];
    $http_body = $headers_body['http_body'];
    // Send and process request.
    $http_request = new Request($http_method, $url, $headers, $http_body);
    $request_options = [];
    $response = $this->httpClient->send($http_request, $request_options);
    $result = $this->doExtractJsonResults($response);
    if (is_array($result)) {
      $data = $result[self::METHODS[$operation_id]['response_key']] ?? $result;
      if (isset($result['pagination']['has_more_items']) && $result['pagination']['has_more_items'] == TRUE) {
        return [
          'page_data' => $result['pagination'] ?? [],
          'continue_key' => $result['pagination']['continuation'] ?? [],
          'data' => $data,
        ];
      }
      else {
        return [
          'page_data' => $result['pagination'] ?? [],
          'data' => $data,
        ];
      }
    }
    else {
      return $result;
    }
  }

  /**
   * Generate headers and body for the request.
   */
  private function generateHeadersBody($auth_key, $http_method, $operation_id, $body) {
    $headers = [];
    $headers['Authorization'] = 'Bearer ' . $auth_key;
    $http_body = NULL;
    if (in_array($http_method, ['POST', 'PUT'])) {
      $body = $this->doValidateBody($body, self::METHODS[$operation_id]['body']);
      $http_body = Json::encode($body);
      $headers['Content-Type'] = 'application/json';
    }
    return ['headers' => $headers, 'http_body' => $http_body];
  }

  /**
   * Validates body object before sending it.
   *
   * @param mixed $body
   *   The body object.
   * @param array $spec
   *   The body object specification.
   * @param array $context
   *   Content of the current body object (useful for nested objects).
   *
   * @return array|string
   *   Validated body object.
   */
  protected function doValidateBody(mixed $body, array $spec, array $context = []) {
    $result_body = NULL;
    if ($spec['type'] == 'object') {
      if (!is_array($body) && !is_object($body)) {
        throw new \InvalidArgumentException(sprintf('The body element (%s) must be an object or associative array.', empty($context) ? 'ROOT' : implode('/', $context)));
      }
      if (is_object($body)) {
        $body = (array) $body;
      }
      if ($spec['allow_any_key']) {
        if ($unexpected_keys = array_diff_key($body, $spec['fields'])) {
          throw new \InvalidArgumentException(sprintf('Unknown keys: %s', implode(', ', array_keys($unexpected_keys))));
        }
      }
      $result_body = [];
      foreach ($spec['fields'] as $key => $definition) {
        if (isset($body[$key])) {
          $result_body[$key] = $this->doValidateBody($body[$key], $definition, array_merge($context, [$key]));
        }
        elseif (isset($definition['default'])) {
          $result_body[$key] = $definition['default'];
        }
        elseif (!empty($definition['required'])) {
          throw new \InvalidArgumentException(sprintf('Key value %s (%s) is not provided.', $key, implode('/', $context)));
        }
      }
    }
    elseif ($spec['type'] == 'array') {
      if (!is_array($body)) {
        throw new \InvalidArgumentException(sprintf('The body element (%s) must be an array.', empty($context) ? 'ROOT' : implode('/', $context)));
      }
      $result_body = [];
      foreach ($body as $key => $value) {
        $result_body[$key] = $this->doValidateBody($value, $spec['item'], array_merge($context, [$key]));
      }
    }
    elseif ($spec['type'] == 'string') {
      $result_body = (string) $body;
    }
    elseif ($spec['type'] == 'boolean') {
      $result_body = (bool) $body;
    }
    elseif ($spec['type'] == 'integer') {
      $result_body = (int) $body;
    }
    elseif ($spec['type'] == 'float') {
      $result_body = (float) $body;
    }
    else {
      throw new \InvalidArgumentException(sprintf('Unknown type %s', $spec['type']));
    }

    return $result_body;
  }

  /**
   * Extracts results from JSON object.
   *
   * Expected response type is a JSON object with key 'results' which contains
   * an array.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The HTTP response.
   *
   * @return array
   *   The array of results.
   */
  private function doExtractJsonResults(ResponseInterface $response) : array {
    return Json::decode($response->getBody()->getContents());
  }

}
