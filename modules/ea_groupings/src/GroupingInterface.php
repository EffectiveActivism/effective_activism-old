<?php

namespace Drupal\ea_groupings;

use Drupal\ea_people\Entity\Person;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Provides an interface for defining Grouping entities.
 *
 * @ingroup ea_groupings
 */
interface GroupingInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Grouping title.
   *
   * @return string
   *   Title of the Grouping.
   */
  public function getTitle();

  /**
   * Sets the Grouping title.
   *
   * @param string $title
   *   The Grouping title.
   *
   * @return \Drupal\ea_groupings\GroupingInterface
   *   The called Grouping entity.
   */
  public function setTitle($title);

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

  /**
   * Get relatives of the grouping.
   *
   * @param bool $include_parent
   *   Whether to include the parent grouping.
   *
   * @return array
   *   An array of groupings related to this entity, including itself.
   */
  public function getRelatives($include_parent);

  /**
   * Add a member to the grouping.
   *
   * @param Person $person
   *   The person to add.
   */
  public function addMember(Person $person);

  /**
   * Remove a member from the grouping.
   *
   * @param Person $person
   *   The member to remove.
   */
  public function removeMember(Person $person);

  /**
   * Checks if person is member of the grouping.
   *
   * @param Person $person
   *   The person to check for.
   *
   * @return bool
   *   TRUE if person is member, FALSE otherwise.
   */
  public function isMember(Person $person);

  /**
   * Checks if person is member of any grouping.
   *
   * @param Person $person
   *   The person to check for.
   *
   * @return bool
   *   TRUE if person is member, FALSE otherwise.
   */
  public static function isAnyMember(Person $person);

  /**
   * Get all groupings that a user is attached to.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   The user object to check relationship for.
   * @param bool $loadGroupings
   *   Whether to load groupings or not.
   *
   * @return array
   *   An array of groupings that the user is attached to.
   */
  public static function getAllGroupingsByUser(AccountProxyInterface $user, $loadGroupings);

  /**
   * Get groupings that a user is manager of.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   The user object to check relationship for.
   * @param bool $loadGroupings
   *   Whether to load groupings or not.
   *
   * @return array
   *   An array of groupings that the user is manager of.
   */
  public static function getAllGroupingsManagedByUser(AccountProxyInterface $user, $loadGroupings);

  /**
   * Get groupings that a user is organizer of.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   The user object to check relationship for.
   * @param bool $loadGroupings
   *   Whether to load groupings or not.
   *
   * @return array
   *   An array of groupings that the user is organizer of.
   */
  public static function getAllGroupingsOrganizedByUser(AccountProxyInterface $user, $loadGroupings);

  /**
   * Get organizations that a user is manager of.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   The user object to check relationship for.
   * @param bool $loadGroupings
   *   Whether to load groupings or not.
   *
   * @return array
   *   An array of organizations that the user is manager of.
   */
  public static function getAllOrganizationsManagedByUser(AccountProxyInterface $user, $loadGroupings);

  /**
   * Get organizations that a user is organizer of.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   The user object to check relationship for.
   * @param bool $loadGroupings
   *   Whether to load groupings or not.
   *
   * @return array
   *   An array of organizations that the user is organizer of.
   */
  public static function getAllOrganizationsOrganizedByUser(AccountProxyInterface $user, $loadGroupings);

}
