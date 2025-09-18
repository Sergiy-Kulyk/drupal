<?php

namespace Drupal\eventbrite\Feeds\Processor;

use Drupal\Core\Entity\EntityInterface;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\ItemInterface;
use Drupal\feeds\Feeds\Processor\NodeProcessor;

/**
 * Defines a Eventbrite node processor.
 *
 * Creates/updates nodes from feed items.
 *
 * @FeedsProcessor(
 *   id = "eventbrite_node",
 *   title = @Translation("Eventbrite Events Node"),
 *   description = @Translation("Creates eventbrite events nodes from feed items."),
 *   entity_type = "node",
 *   form = {
 *     "configuration" = "Drupal\feeds\Feeds\Processor\Form\DefaultEntityProcessorForm",
 *     "option" = "Drupal\feeds\Feeds\Processor\Form\EntityProcessorOptionForm",
 *   },
 * )
 */
class EventbriteNodeProcessor extends NodeProcessor {

  /**
   * {@inheritdoc}
   */
  public function entityLabel() {
    return $this->t('Eventbrite Node');
  }

  /**
   * {@inheritdoc}
   */
  public function entityLabelPlural() {
    return $this->t('Eventbrite Nodes');
  }

  /**
   * {@inheritdoc}
   */
  protected function map(FeedInterface $feed, EntityInterface $entity, ItemInterface $item) {
    $data = $item->toArray();
    if ($this->hasEntityDifferences($entity, $data)) {
      foreach ($data as $field => $value) {
        if ($entity->hasField($field) && $value !== NULL) {
          $entity->set($field, $value);
        }
      }
    }
    if ($entity->isNew()) {
      if ($feed->hasField('field_status') && $feed->get('field_status')->value == '1') {
        $entity->setPublished();
      }
      else {
        $entity->setUnpublished();
      }
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  protected function existingEntityId(FeedInterface $feed, ItemInterface $item) {
    $data = $item->toArray();
    $node_storage = $this->entityTypeManager->getStorage('node');
    if (isset($data['field_eventbrite_id'][0]['value'])) {
      $event_id = $data['field_eventbrite_id'][0]['value'];
      $nodes = $node_storage->loadByProperties([
        'field_eventbrite_id' => $event_id,
      ]);
      if (!empty($nodes)) {
        /** @var \Drupal\node\NodeInterface $node */
        $node = reset($nodes);
        return $node->id();
      }
    }
  }

  /**
   * Checks if given entity values match current entity field values.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The Drupal entity.
   * @param array $converted_values
   *   The field values for given Drupal entity.
   *
   * @return bool
   *   The check result.
   */
  public static function hasEntityDifferences(EntityInterface $entity, array $converted_values) : bool {
    $has_differences = FALSE;
    foreach ($converted_values as $field_name => $values) {
      if ($entity->hasField($field_name)) {
        $has_differences = $has_differences || ($entity->get($field_name)->getValue() != $values);
      }
    }

    return $has_differences;
  }

}
