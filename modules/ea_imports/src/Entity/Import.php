<?php

namespace Drupal\ea_imports\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Import entity.
 *
 * @ingroup ea_imports
 *
 * @ContentEntityType(
 *   id = "import",
 *   label = @Translation("Import"),
 *   bundle_label = @Translation("Import type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\ea_imports\ImportListBuilder",
 *     "views_data" = "Drupal\ea_imports\Entity\ImportViewsData",
 *     "translation" = "Drupal\ea_imports\ImportTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\ea_imports\Form\ImportForm",
 *       "add" = "Drupal\ea_imports\Form\ImportForm",
 *       "edit" = "Drupal\ea_imports\Form\ImportForm",
 *       "publish" = "Drupal\ea_imports\Form\ImportPublishForm",
 *       "delete" = "Drupal\ea_imports\Form\ImportDeleteForm",
 *     },
 *     "access" = "Drupal\ea_imports\ImportAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\ea_imports\ImportHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "import",
 *   data_table = "import_field_data",
 *   revision_table = "import_revision",
 *   admin_permission = "administer import entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "revision" = "revision_id",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/effectiveactivism/imports/{import}",
 *     "add-page" = "/effectiveactivism/imports/add",
 *     "add-form" = "/effectiveactivism/imports/add/{import_type}",
 *     "edit-form" = "/effectiveactivism/imports/{import}/edit",
 *     "delete-form" = "/effectiveactivism/imports/{import}/delete",
 *     "publish-form" = "/effectiveactivism/imports/{import}/publish",
 *     "collection" = "/effectiveactivism/imports",
 *   },
 *   bundle_entity_type = "import_type",
 *   field_ui_base_route = "entity.import_type.edit_form"
 * )
 */
class Import extends RevisionableContentEntityBase implements ImportInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['revision_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Revision ID'))
      ->setDescription(t('The Revision ID of the Import entity.'))
      ->setReadOnly(TRUE);
    $fields['grouping'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Grouping'))
      ->setDescription(t('The grouping that this event belongs to.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'grouping')
      ->setSetting('handler', 'default')
      ->setCardinality(1)
      ->setRequired(TRUE)
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'grouping_selector',
        'weight' => -4,
      ));
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Import entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ));
    $fields['events'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Events'))
      ->setDescription(t('The events that belongs to this import.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'event')
      ->setSetting('handler', 'default')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setRequired(FALSE)
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'hidden',
        'weight' => -4,
      ));
    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Import is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));
    return $fields;
  }

}
