<?php

/**
 * @file
 * Contains \Drupal\ea_tasks\TaskInterface.
 */

namespace Drupal\ea_tasks;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Task entities.
 *
 * @ingroup ea_tasks
 */
interface TaskInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Task type.
   *
   * @return string
   *   Type of the Task.
   */
  public function getType();

  /**
   * Sets the Task type.
   *
   * @param string $type
   *   The Task type.
   *
   * @return \Drupal\ea_tasks\TaskInterface
   *   The called Task entity.
   */
  public function setType($type);

  /**
   * Gets the Task creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Task.
   */
  public function getCreatedTime();

  /**
   * Sets the Task creation timestamp.
   *
   * @param int $timestamp
   *   The Task creation timestamp.
   *
   * @return \Drupal\ea_tasks\TaskInterface
   *   The called Task entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Task published status indicator.
   *
   * Unpublished Task are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Task is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Task.
   *
   * @param bool $published
   *   TRUE to set this Task to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\ea_tasks\TaskInterface
   *   The called Task entity.
   */
  public function setPublished($published);

}
