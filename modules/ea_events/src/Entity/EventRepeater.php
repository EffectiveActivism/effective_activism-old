<?php

/**
 * @file
 * Contains \Drupal\ea_events\Entity\EventRepeater.
 */

namespace Drupal\ea_events\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Event repeater entity.
 *
 * @ingroup ea_events
 *
 * @ContentEntityType(
 *   id = "event_repeater",
 *   label = @Translation("Event repeater"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\ea_events\EventRepeaterListBuilder",
 *     "views_data" = "Drupal\ea_events\Entity\EventRepeaterViewsData",
 *     "translation" = "Drupal\ea_events\EventRepeaterTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\ea_events\Form\EventRepeaterForm",
 *       "add" = "Drupal\ea_events\Form\EventRepeaterForm",
 *       "edit" = "Drupal\ea_events\Form\EventRepeaterForm",
 *       "delete" = "Drupal\ea_events\Form\EventRepeaterDeleteForm",
 *     },
 *     "access" = "Drupal\ea_events\EventRepeaterAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\ea_events\EventRepeaterHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "event_repeater",
 *   data_table = "event_repeater_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer event repeater entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/effective-activism/event_repeater/{event_repeater}",
 *     "add-form" = "/effective-activism/event_repeater/add",
 *     "edit-form" = "/effective-activism/event_repeater/{event_repeater}/edit",
 *     "delete-form" = "/effective-activism/event_repeater/{event_repeater}/delete",
 *     "collection" = "/effective-activism/event_repeater",
 *   },
 *   field_ui_base_route = "event_repeater.settings"
 * )
 */
class EventRepeater extends ContentEntityBase implements EventRepeaterInterface {

  use EntityChangedTrait;

  /**
   * EventRepeater default values
   */
  const DEFAULT_VALUES = array(
    
    'event_freq' => 'none',
    'event_count' => '1',
    'event_until' => '1970-01-01',
    'event_interval' => '1',
    'event_bymonth' => '1',
    'event_bymonthday' => '1',
    'event_byday' => 'MO',
    'event_bysetpos' => '1',
    'event_ends' => 'never',
    'event_byday_multiple' => '',
    'event_repeat_by' => 'day_of_the_month',
  );

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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Event repeater entity.'))
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
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['event_freq'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Frequency'))
      ->setDefaultValue(self::DEFAULT_VALUES['event_freq'])
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings(array(
        'allowed_values' => array(
          'none' => 'Do not repeat',
          'DAILY' => 'Daily',
          'WEEKLY' => 'Weekly',
          'MONTHLY' => 'Monthly',
          'YEARLY' => 'Yearly',
        ),
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['event_interval'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Repeat every'))
      ->setDefaultValue(self::DEFAULT_VALUES['event_interval'])
      ->setRequired(TRUE)
      ->setSettings(array(
        'min' => 1,
        'max' => 999,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['event_repeat_by'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Repeat by'))
      ->setTranslatable(TRUE)
      ->setRequired(TRUE)
      ->setDefaultValue(self::DEFAULT_VALUES['event_repeat_by'])
      ->setSettings(array(
        'allowed_values' => array(
          'day_of_the_month' => 'day of the month',
          'day_of_the_week' => 'day of the week',
        ),
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_buttons',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['event_bymonthday'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Every'))
      ->setDefaultValue(self::DEFAULT_VALUES['event_bymonthday'])
      ->setRequired(TRUE)
      ->setSettings(array(
        'allowed_values' => array(
          '1' => '1',
          '2' => '2',
          '3' => '3',
          '4' => '4',
          '5' => '5',
          '6' => '6',
          '7' => '7',
          '8' => '8',
          '9' => '9',
          '10' => '10',
          '11' => '11',
          '12' => '12',
          '13' => '13',
          '14' => '14',
          '15' => '15',
          '16' => '16',
          '17' => '17',
          '18' => '18',
          '19' => '19',
          '20' => '20',
          '21' => '21',
          '22' => '22',
          '23' => '23',
          '24' => '24',
          '25' => '25',
          '26' => '26',
          '27' => '27',
          '28' => '28',
          '29' => '29',
          '30' => '30',
          '31' => '31',
        ),
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['event_bysetpos'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('On'))
      ->setDefaultValue(self::DEFAULT_VALUES['event_bysetpos'])
      ->setRequired(TRUE)
      ->setSettings(array(
        'allowed_values' => array(
          '1' => 'First',
          '2' => 'Second',
          '3' => 'Third',
          '4' => 'Fourth',
          '-1' => 'Last',
        ),
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['event_byday'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Weekday'))
      ->setDefaultValue(self::DEFAULT_VALUES['event_byday'])
      ->setRequired(TRUE)
      ->setSettings(array(
        'allowed_values' => array(
          'MO' => 'Monday',
          'TU' => 'Tuesday',
          'WE' => 'Wednesday',
          'TH' => 'Thursday',
          'FR' => 'Friday',
          'SA' => 'Saturday',
          'SU' => 'Sunday',
        ),
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['event_bymonth'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Month'))
      ->setDefaultValue(self::DEFAULT_VALUES['event_bymonth'])
      ->setRequired(TRUE)
      ->setSettings(array(
        'allowed_values' => array(
          '1' => 'January',
          '2' => 'February',
          '3' => 'March',
          '4' => 'April',
          '5' => 'May',
          '6' => 'June',
          '7' => 'July',
          '8' => 'August',
          '9' => 'September',
          '10' => 'October',
          '11' => 'November',
          '12' => 'December',
        ),
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['event_byday_multiple'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Repeat on'))
      ->setTranslatable(TRUE)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setRequired(FALSE)
      ->setSettings(array(
        'allowed_values' => array(
          'MO' => 'M',
          'TU' => 'T',
          'WE' => 'W',
          'TH' => 'T',
          'FR' => 'F',
          'SA' => 'S',
          'SU' => 'S',
        ),
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_buttons',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['event_ends'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Ends'))
      ->setTranslatable(TRUE)
      ->setDefaultValue(self::DEFAULT_VALUES['event_ends'])
      ->setRequired(TRUE)
      ->setSettings(array(
        'allowed_values' => array(
          'never' => 'Never',
          'COUNT' => 'After',
          'UNTIL' => 'Until',
        ),
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['event_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Occurences'))
      ->setDefaultValue(self::DEFAULT_VALUES['event_count'])
      ->setRequired(TRUE)
      ->setSettings(array(
        'min' => 1,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['event_until'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('On'))
      ->setRequired(TRUE)
      ->setDefaultValue(self::DEFAULT_VALUES['event_until'])
      ->setSettings(array(
        'default_value' => '',
        'text_processing' => 0,
        'datetime_type' => 'date',
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
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));
    return $fields;
  }

}
