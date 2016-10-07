<?php

namespace Drupal\ea_results\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\ea_results\ResultTypeInterface;

/**
 * Defines the Result type entity.
 *
 * @ConfigEntityType(
 *   id = "result_type",
 *   label = @Translation("Result type"),
 *   handlers = {
 *     "list_builder" = "Drupal\ea_results\ResultTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\ea_results\Form\ResultTypeForm",
 *       "edit" = "Drupal\ea_results\Form\ResultTypeForm",
 *       "delete" = "Drupal\ea_results\Form\ResultTypeDeleteForm"
 *     },
 *     "access" = "Drupal\ea_results\ResultTypeAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\ea_results\ResultTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "result_type",
 *   admin_permission = "administer result entities",
 *   bundle_of = "result",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/effectiveactivism/result-types/{result_type}",
 *     "add-form" = "/effectiveactivism/result-types/add",
 *     "edit-form" = "/effectiveactivism/result-types/{result_type}/edit",
 *     "delete-form" = "/effectiveactivism/result-types/{result_type}/delete",
 *     "collection" = "/effectiveactivism/result-types"
 *   }
 * )
 */
class ResultType extends ConfigEntityBundleBase implements ResultTypeInterface {

  /**
   * The Result type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Result type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Result type description.
   *
   * @var string
   */
  public $description;

  /**
   * The Result type data types.
   *
   * @var array
   */
  public $dataTypes;

  /**
   * The Result type organization.
   *
   * @var int
   */
  public $organization;

  /**
   * The Result type allowed groupings.
   *
   * @var array
   */
  public $groupings;

}
