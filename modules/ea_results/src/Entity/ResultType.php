<?php

namespace Drupal\ea_results\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\ea_results\ResultTypeInterface;
use Drupal\Core\Form\FormStateInterface;

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
   * The default result types.
   */
  const DEFAULT_RESULT_TYPES = [
    'leafleting' => [
      'label' => 'Leafleting',
      'description' => 'Distribute flyers on sidewalks, city squares, public events and colleges.',
      'datatypes' => [
        'leaflets' => 'leaflets',
      ],
    ],
    'signature_collection' => [
      'label' => 'Signature collection',
      'description' => 'Ask people for a signature ( and usually an e-mail address ) to support a cause.',
      'datatypes' => [
        'signatures' => 'signatures',
      ],
    ],
  ];

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
   * The Result type name formatted as a machine name for use with importing.
   *
   * @var string
   */
  protected $importname;

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
  public $datatypes;

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

  /**
   * Returns the import name.
   */
  public function importname() {
    return $this->importname;
  }

  /**
   * Load a result type by machine name and organization.
   *
   * @param string $value
   *   The value as typed by the user.
   * @param array $elements
   *   An array of form elements.
   * @param FormStateInterface $form_state
   *   The form state.
   *
   * @return bool
   *   Whether the machine name exists within the organization or not.
   */
  public static function checkTypedImportNameExists($value, $elements, FormStateInterface $form_state) {
    $organizationId = $form_state->getValue('organization');
    return !empty($organizationId) ? !self::isUniqueImportName($value, $organizationId) : NULL;
  }

  /**
   * Check if an import name is unique within an organization.
   *
   * @param string $importName
   *   The import name to check.
   * @param int $organizationId
   *   The organization id.
   *
   * @return bool
   *   Whether the import name exists within the organization or not.
   */
  public static function isUniqueImportName($importName, $organizationId) {
    $query = \Drupal::entityQuery('result_type');
    $result = $query
      ->condition('organization', $organizationId)
      ->condition('importname', $importName)
      ->count()
      ->execute();
    return $result === 0 ? TRUE : FALSE;
  }

  /**
   * Return a unique id based on an import name.
   *
   * @param string $importName
   *   The import name to base the id on.
   *
   * @return string
   *   A unique entity id.
   */
  public static function createId($importName) {
    $id = NULL;
    $result = NULL;
    while (TRUE) {
      // Id must be no more than 32 characters long.
      $id = uniqid(substr($importName, 0, 19));
      $query = \Drupal::entityQuery('result_type');
      $result = $query
        ->condition('id', $id)
        ->count()
        ->execute();
      // If no existing result types have the id, return it.
      if ($result === 0) {
        break;
      }
    }
    return $id;
  }

  /**
   * Load a result type by import name and organization id.
   *
   * @param string $importName
   *   The import name of the result type.
   * @param int $organizationId
   *   The organization id.
   *
   * @return ResultType
   *   The loaded result type entity.
   */
  public static function getResultTypeByImportName($importName, $organizationId) {
    $query = \Drupal::entityQuery('result_type');
    $result = $query
      ->condition('importname', $importName)
      ->condition('organization', $organizationId)
      ->execute();
    return !empty($result) ? self::load(array_pop($result)) : NULL;
  }

}
