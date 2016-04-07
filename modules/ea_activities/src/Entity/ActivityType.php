<?php

/**
 * @file
 * Contains \Drupal\ea_activities\Entity\ActivityType.
 */

namespace Drupal\ea_activities\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\ea_activities\ActivityTypeInterface;

/**
 * Defines the Activity type entity.
 *
 * @ConfigEntityType(
 *   id = "activity_type",
 *   label = @Translation("Activity type"),
 *   handlers = {
 *     "list_builder" = "Drupal\ea_activities\ActivityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\ea_activities\Form\ActivityTypeForm",
 *       "edit" = "Drupal\ea_activities\Form\ActivityTypeForm",
 *       "delete" = "Drupal\ea_activities\Form\ActivityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\ea_activities\ActivityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "activity_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "activity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/effectiveactivism/activity_type/{activity_type}",
 *     "add-form" = "/effectiveactivism/activity_type/add",
 *     "edit-form" = "/effectiveactivism/activity_type/{activity_type}/edit",
 *     "delete-form" = "/effectiveactivism/activity_type/{activity_type}/delete",
 *     "collection" = "/effectiveactivism/activity_type"
 *   }
 * )
 */
class ActivityType extends ConfigEntityBundleBase implements ActivityTypeInterface {

  /**
   * The Activity type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Activity type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Activity type description.
   *
   * @var string
   */
  public $description;

  /**
   * The Activity type data types.
   *
   * @var array
   */
  public $data_types;
}
