<?php

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
 *     "label" = "id",
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
    $fields['event_repeats'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Repeats'))
      ->setDefaultValue('none')
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings(array(
        'allowed_values' => array(
          'none' => 'Do not repeat',
          'daily' => 'Daily',
          'weekly' => 'Weekly',
          'monthly' => 'Monthly',
          'yearly' => 'Yearly',
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
    $fields['event_repeat_every'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Repeat every'))
      ->setDefaultValue('1')
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
    $fields['event_repeat_by'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Repeat by'))
      ->setTranslatable(TRUE)
      ->setRequired(TRUE)
      ->setDefaultValue('day_of_the_month')
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
    $fields['event_repeat_on'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Repeat on'))
      ->setTranslatable(TRUE)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSettings(array(
        'allowed_values' => array(
          'monday' => 'M',
          'tuesday' => 'T',
          'wednesday' => 'W',
          'thursday' => 'T',
          'friday' => 'F',
          'saturday' => 'S',
          'sunday' => 'S',
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
      ->setDefaultValue('never')
      ->setRequired(TRUE)
      ->setSettings(array(
        'allowed_values' => array(
          'never' => 'Never',
          'after' => 'After',
          'on' => 'On',
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
    $fields['event_occurences'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Occurences'))
      ->setDefaultValue(1)
      ->setRequired(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'integer',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['event_on'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('On'))
      ->setRequired(TRUE)
      ->setDefaultValue('')
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
