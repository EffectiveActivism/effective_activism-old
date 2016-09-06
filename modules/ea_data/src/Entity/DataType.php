<?php

/**
 * @file
 * Contains \Drupal\ea_data\Entity\DataType.
 */

namespace Drupal\ea_data\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\ea_data\DataTypeInterface;

/**
 * Defines the Data type entity.
 *
 * @ConfigEntityType(
 *   id = "data_type",
 *   label = @Translation("Data type"),
 *   handlers = {
 *     "list_builder" = "Drupal\ea_data\DataTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\ea_data\Form\DataTypeForm",
 *       "edit" = "Drupal\ea_data\Form\DataTypeForm",
 *       "delete" = "Drupal\ea_data\Form\DataTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\ea_data\DataTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "data_type",
 *   admin_permission = "administer data entities",
 *   bundle_of = "data",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/effectiveactivism/data-types/{data_type}",
 *     "add-form" = "/effectiveactivism/data-types/add",
 *     "edit-form" = "/effectiveactivism/data-types/{data_type}/edit",
 *     "delete-form" = "/effectiveactivism/data-types/{data_type}/delete",
 *     "collection" = "/effectiveactivism/data-types"
 *   }
 * )
 */
class DataType extends ConfigEntityBundleBase implements DataTypeInterface {
  /**
   * The Data type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Data type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Data type description.
   *
   * @var string
   */
  public $description;
}
