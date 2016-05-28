<?php

namespace Drupal\ea_groupings;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Grouping entities.
 *
 * @ingroup ea_groupings
 */
interface GroupingInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Grouping name.
   *
   * @return string
   *   Name of the Grouping.
   */
  public function getName();

  /**
   * Sets the Grouping name.
   *
   * @param string $name
   *   The Grouping name.
   *
   * @return \Drupal\ea_groupings\GroupingInterface
   *   The called Grouping entity.
   */
  public function setName($name);

  /**
   * Gets the Grouping creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Grouping.
   */
  public function getCreatedTime();

  /**
   * Sets the Grouping creation timestamp.
   *
   * @param int $timestamp
   *   The Grouping creation timestamp.
   *
   * @return \Drupal\ea_groupings\GroupingInterface
   *   The called Grouping entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Grouping published status indicator.
   *
   * Unpublished Grouping are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Grouping is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Grouping.
   *
   * @param bool $published
   *   TRUE to set this Grouping to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\ea_groupings\GroupingInterface
   *   The called Grouping entity.
   */
  public function setPublished($published);

}
