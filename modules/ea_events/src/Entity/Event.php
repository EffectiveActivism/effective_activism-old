<?php

namespace Drupal\ea_events\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
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
 *       "publish" = "Drupal\ea_events\Form\EventPublishForm",
 *       "delete" = "Drupal\ea_events\Form\EventDeleteForm",
 *     },
 *     "access" = "Drupal\ea_events\EventAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\ea_events\EventHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "event",
 *   revision_table = "event_revision",
 *   admin_permission = "administer event entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/effectiveactivism/events/{event}",
 *     "add-form" = "/effectiveactivism/events/add",
 *     "edit-form" = "/effectiveactivism/events/{event}/edit",
 *     "delete-form" = "/effectiveactivism/events/{event}/delete",
 *     "publish-form" = "/effectiveactivism/events/{event}/publish",
 *     "collection" = "/effectiveactivism/events",
 *   },
 *   field_ui_base_route = "event.settings"
 * )
 */
class Event extends RevisionableContentEntityBase implements EventInterface {

  use EntityChangedTrait;

  const WEIGHTS = [
    'title',
    'description',
    'grouping',
    'start_date',
    'end_date',
    'location',
    'participants',
    'results',
    'user_id',
  ];

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
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the event.'))
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => array_search('title', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => array_search('title', self::WEIGHTS),
      ));
    $fields['start_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Start date'))
      ->setDescription(t('The beginning of the event.'))
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'text_processing' => 0,
      ))
      ->setDefaultValue(array(
        0 => array(
          'default_date_type' => 'now',
          'default_date' => 'tomorrow noon',
        ),
      ))
      ->setDisplayOptions('view', array(
        'type' => 'datetime_default',
        'weight' => array_search('start_date', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'datetime_default',
        'weight' => array_search('start_date', self::WEIGHTS),
      ));
    $fields['end_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('End date'))
      ->setDescription(t('The end of the event.'))
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'text_processing' => 0,
      ))
      ->setDefaultValue(array(
        0 => array(
          'default_date_type' => 'now',
          'default_date' => 'tomorrow 13:00',
        ),
      ))
      ->setDisplayOptions('view', array(
        'type' => 'datetime_default',
        'weight' => array_search('end_date', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'datetime_default',
        'weight' => array_search('end_date', self::WEIGHTS),
      ));
    $fields['location'] = BaseFieldDefinition::create('location')
      ->setLabel(t('Location'))
      ->setDescription(t('The location of the event.'))
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'location_default',
        'weight' => array_search('location', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'location_default',
        'weight' => array_search('location', self::WEIGHTS),
      ));
    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Description'))
      ->setDescription(t('The description of the event.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'string_textarea',
        'weight' => array_search('description', self::WEIGHTS),
        'settings' => array(
          'rows' => 6,
        ),
      ))
      ->setDisplayOptions('view', array(
        'type' => 'basic_string',
        'weight' => array_search('description', self::WEIGHTS),
      ));
    $fields['participants'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Participants'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'person')
      ->setSetting('handler', 'default')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => array_search('participants', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'event_participants',
        'settings' => array(
          'allow_new' => TRUE,
          'allow_existing' => TRUE,
        ),
        'weight' => array_search('participants', self::WEIGHTS),
      ));
    $fields['results'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Results'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'result')
      ->setSetting('handler', 'default')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => array_search('results', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'inline_entity_form_complex',
        'settings' => array(
          'allow_new' => TRUE,
          'allow_existing' => FALSE,
        ),
        'weight' => array_search('results', self::WEIGHTS),
      ));
    $fields['grouping'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Grouping'))
      ->setRevisionable(TRUE)
      ->setDescription(t('The grouping that this event belongs to.'))
      ->setSetting('target_type', 'grouping')
      ->setSetting('handler', 'default')
      ->setCardinality(1)
      ->setRequired(TRUE)
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => array_search('grouping', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'grouping_selector',
        'weight' => array_search('grouping', self::WEIGHTS),
      ));
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
        'type' => 'hidden',
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => array_search('user_id', self::WEIGHTS),
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ));
    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Event is published.'))
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
