<?php

namespace Drupal\ea_events\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Event repeater entities.
 *
 * @ingroup ea_events
 */
interface EventRepeaterInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Event repeater creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Event repeater.
   */
  public function getCreatedTime();

  /**
   * Sets the Event repeater creation timestamp.
   *
   * @param int $timestamp
   *   The Event repeater creation timestamp.
   *
   * @return \Drupal\ea_events\Entity\EventRepeaterInterface
   *   The called Event repeater entity.
   */
  public function setCreatedTime($timestamp);

}
