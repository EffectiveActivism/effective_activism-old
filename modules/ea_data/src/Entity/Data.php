<?php

namespace Drupal\ea_data\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\ea_data\DataInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Data entity.
 *
 * @ingroup ea_data
 *
 * @ContentEntityType(
 *   id = "data",
 *   label = @Translation("Data"),
 *   bundle_label = @Translation("Data type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\ea_data\DataListBuilder",
 *     "views_data" = "Drupal\ea_data\Entity\DataViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\ea_data\Form\DataForm",
 *       "add" = "Drupal\ea_data\Form\DataForm",
 *       "edit" = "Drupal\ea_data\Form\DataForm",
 *       "delete" = "Drupal\ea_data\Form\DataDeleteForm",
 *     },
 *     "access" = "Drupal\ea_data\DataAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\ea_data\DataHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "data",
 *   revision_table = "data_revision",
 *   admin_permission = "administer data entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "revision" = "revision_id",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/effectiveactivism/data/{data}",
 *     "add-form" = "/effectiveactivism/data/add/{data_type}",
 *     "edit-form" = "/effectiveactivism/data/{data}/edit",
 *     "delete-form" = "/effectiveactivism/data/{data}/delete",
 *     "collection" = "/effectiveactivism/data",
 *   },
 *   bundle_entity_type = "data_type",
 *   field_ui_base_route = "entity.data_type.edit_form"
 * )
 */
class Data extends RevisionableContentEntityBase implements DataInterface {

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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Data entity.'))
      ->setReadOnly(TRUE);
    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The Data type/bundle.'))
      ->setSetting('target_type', 'data_type')
      ->setRequired(TRUE);
    $fields['revision_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Revision ID'))
      ->setDescription(t('The Revision ID of the Data entity.'))
      ->setReadOnly(TRUE);
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Data entity.'))
      ->setReadOnly(TRUE);
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Data entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
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
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setRevisionable(TRUE)
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Data entity.'))
      ->setDisplayOptions('form', array(
        'type' => 'language_select',
        'weight' => 10,
      ))
      ->setDisplayConfigurable('form', TRUE);
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));
    return $fields;
  }

}
