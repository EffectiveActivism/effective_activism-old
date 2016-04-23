<?php

/**
 * @file
 * Contains \Drupal\ea_events\Entity\Event.
 */

namespace Drupal\ea_events\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\ea_events\EventInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Event entity.
 *
 * @ingroup ea_events
 *
 * @ContentEntityType(
 *   id = "event",
 *   label = @Translation("Event"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\ea_events\EventListBuilder",
 *     "views_data" = "Drupal\ea_events\Entity\EventViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\ea_events\Form\EventForm",
 *       "add" = "Drupal\ea_events\Form\EventForm",
 *       "edit" = "Drupal\ea_events\Form\EventForm",
 *       "delete" = "Drupal\ea_events\Form\EventDeleteForm",
 *     },
 *     "access" = "Drupal\ea_events\EventAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\ea_events\EventHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "event",
 *   admin_permission = "administer event entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/effective_activism/events/event/{event}",
 *     "add-form" = "/effective_activism/events/event/add",
 *     "edit-form" = "/effective_activism/events/event/{event}/edit",
 *     "delete-form" = "/effective_activism/events/event/{event}/delete",
 *     "collection" = "/effective_activism/events/event",
 *   },
 *   field_ui_base_route = "event.settings"
 * )
 */
class Event extends ContentEntityBase implements EventInterface {

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
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Event entity.'))
      ->setReadOnly(TRUE);
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Event entity.'))
      ->setReadOnly(TRUE);
    $fields['start_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Start date'))
      ->setDescription(t('The beginning of the event.'))
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue(array(
        0 => array(
          'default_date_type' => 'now',
          'default_date' => 'tomorrow noon',
      )))
      ->setDisplayOptions('view', array(
        'type' => 'datetime_default',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'datetime_default',
        'weight' => 1,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['end_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('End date'))
      ->setDescription(t('The end of the event.'))
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue(array(
        0 => array(
          'default_date_type' => 'now',
          'default_date' => 'tomorrow 13:00',
      )))
      ->setDisplayOptions('view', array(
        'type' => 'datetime_default',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'datetime_default',
        'weight' => 1,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['location'] = BaseFieldDefinition::create('location')
      ->setLabel(t('Location'))
      ->setDescription(t('The location of the event.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'location_default',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'location_default',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Description'))
      ->setDescription(t('The description of the event.'))
      ->setDisplayOptions('form', array(
        'type' => 'string_textarea',
        'weight' => 2,
        'settings' => array(
          'rows' => 6,
        ),
      ))
      ->setDisplayOptions('view', array(
        'type' => 'basic_string',
        'weight' => 2,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['tasks'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Tasks'))
      ->setDescription(t('The tasks to do before, under and after the event.'))
      ->setSetting('target_type', 'task')
      ->setSetting('handler', 'default')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', array(
        'type' => 'string',
      ))
      ->setDisplayOptions('form', array(
        'type' => 'inline_entity_form_complex',
        'settings' => array(
          'allow_new' => TRUE,
          'allow_existing' => FALSE,
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['participants'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Participants'))
      ->setSetting('target_type', 'person')
      ->setSetting('handler', 'default')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', array(
        'type' => 'string',
      ))
      ->setDisplayOptions('form', array(
        'type' => 'inline_entity_form_complex',
        'settings' => array(
          'allow_new' => TRUE,
          'allow_existing' => TRUE,
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['activities'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Results'))
      ->setSetting('target_type', 'activity')
      ->setSetting('handler', 'default')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', array(
        'type' => 'string',
      ))
      ->setDisplayOptions('form', array(
        'type' => 'inline_entity_form_complex',
        'settings' => array(
          'allow_new' => TRUE,
          'allow_existing' => FALSE,
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Event entity.'))
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
    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Event is published.'))
      ->setDefaultValue(TRUE);
    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Event entity.'))
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
