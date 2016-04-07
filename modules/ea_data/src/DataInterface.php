<?php

/**
 * @file
 * Contains \Drupal\ea_data\DataInterface.
 */

namespace Drupal\ea_data;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Data entities.
 *
 * @ingroup ea_data
 */
interface DataInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Data type.
   *
   * @return string
   *   The Data type.
   */
  public function getType();

  /**
   * Gets the Data creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Data.
   */
  public function getCreatedTime();

  /**
   * Sets the Data creation timestamp.
   *
   * @param int $timestamp
   *   The Data creation timestamp.
   *
   * @return \Drupal\ea_data\DataInterface
   *   The called Data entity.
   */
  public function setCreatedTime($timestamp);

}
