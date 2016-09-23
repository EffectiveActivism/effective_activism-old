<?php

namespace Drupal\ea_imports\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Import type entity.
 *
 * @ConfigEntityType(
 *   id = "import_type",
 *   label = @Translation("Import type"),
 *   handlers = {
 *     "list_builder" = "Drupal\ea_imports\ImportTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\ea_imports\Form\ImportTypeForm",
 *       "edit" = "Drupal\ea_imports\Form\ImportTypeForm",
 *       "delete" = "Drupal\ea_imports\Form\ImportTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\ea_imports\ImportTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "import_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "import",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/effectiveactivism/import-types/{import_type}",
 *     "add-form" = "/effectiveactivism/import-types/add",
 *     "edit-form" = "/effectiveactivism/import-types/{import_type}/edit",
 *     "delete-form" = "/effectiveactivism/import-types/{import_type}/delete",
 *     "collection" = "/effectiveactivism/import-types"
 *   }
 * )
 */
class ImportType extends ConfigEntityBundleBase implements ImportTypeInterface {

  /**
   * The Import type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Import type label.
   *
   * @var string
   */
  protected $label;

}
