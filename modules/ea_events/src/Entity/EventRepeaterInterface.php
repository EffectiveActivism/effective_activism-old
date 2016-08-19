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

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Event repeater name.
   *
   * @return string
   *   Name of the Event repeater.
   */
  public function getName();

  /**
   * Sets the Event repeater name.
   *
   * @param string $name
   *   The Event repeater name.
   *
   * @return \Drupal\ea_events\Entity\EventRepeaterInterface
   *   The called Event repeater entity.
   */
  public function setName($name);

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

  /**
   * Returns the Event repeater published status indicator.
   *
   * Unpublished Event repeater are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Event repeater is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Event repeater.
   *
   * @param bool $published
   *   TRUE to set this Event repeater to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\ea_events\Entity\EventRepeaterInterface
   *   The called Event repeater entity.
   */
  public function setPublished($published);

}
