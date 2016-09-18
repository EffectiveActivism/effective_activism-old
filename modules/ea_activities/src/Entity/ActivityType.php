<?php

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
 *   admin_permission = "administer activity entities",
 *   bundle_of = "activity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/effectiveactivism/activity-types/{activity_type}",
 *     "add-form" = "/effectiveactivism/activity-types/add",
 *     "edit-form" = "/effectiveactivism/activity-types/{activity_type}/edit",
 *     "delete-form" = "/effectiveactivism/activity-types/{activity_type}/delete",
 *     "collection" = "/effectiveactivism/activity-types"
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
  public $dataTypes;

  /**
   * The Activity type organization.
   *
   * @var int
   */
  public $organization;

  /**
   * The Activity type allowed groupings.
   *
   * @var array
   */
  public $groupings;

}
