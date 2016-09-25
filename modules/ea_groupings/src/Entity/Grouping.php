<?php

namespace Drupal\ea_groupings\Entity;

use Drupal\ea_groupings\GroupingInterface;
use Drupal\ea_permissions\Roles;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Grouping entity.
 *
 * @ingroup ea_groupings
 *
 * @ContentEntityType(
 *   id = "grouping",
 *   label = @Translation("Grouping"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\ea_groupings\GroupingListBuilder",
 *     "views_data" = "Drupal\ea_groupings\Entity\GroupingViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\ea_groupings\Form\GroupingForm",
 *       "add" = "Drupal\ea_groupings\Form\GroupingForm",
 *       "edit" = "Drupal\ea_groupings\Form\GroupingForm",
 *       "delete" = "Drupal\ea_groupings\Form\GroupingDeleteForm",
 *     },
 *     "access" = "Drupal\ea_groupings\GroupingAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\ea_groupings\GroupingHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "grouping",
 *   revision_table = "grouping_revision",
 *   admin_permission = "administer grouping entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/effectiveactivism/groupings/{grouping}",
 *     "add-form" = "/effectiveactivism/groupings/add",
 *     "edit-form" = "/effectiveactivism/groupings/{grouping}/edit",
 *     "delete-form" = "/effectiveactivism/groupings/{grouping}/delete",
 *     "collection" = "/effectiveactivism/groupings",
 *   },
 *   field_ui_base_route = "grouping.settings"
 * )
 */
class Grouping extends RevisionableContentEntityBase implements GroupingInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
      'managers' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
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
  public function getRelatives($include_parent = TRUE) {
    $groupings = [];
    // If grouping has parent, get parents children.
    if (isset($this->get('parent')->entity)) {
      $parent = $this->get('parent')->entity;
    }
    // Otherwise, get this groupings children.
    else {
      $parent = $this;
    }
    if ($include_parent) {
      $groupings[] = $parent;
    }
    $query = \Drupal::entityQuery('grouping')
      ->condition('parent', $parent->id());
    $result = $query->execute();
    $groupings += Grouping::loadMultiple($result);
    return $groupings;
  }

  /**
   * {@inheritdoc}
   */
  public static function getGroupings($include_children = TRUE, AccountProxyInterface $user = NULL, $role = NULL) {
    $groupings = [];
    if (empty($user)) {
      $user = \Drupal::currentUser();
    }
    $query = \Drupal::entityQuery('grouping');
    if (!$include_children) {
      $query->notExists('parent');
    }
    switch ($role) {
      case Roles::MANAGER_ROLE:
        $query->condition('managers', $user->id());
        break;

      case Roles::ORGANIZER_ROLE:
        $query->condition('organizers', $user->id());

      default:
        $group = $query->orConditionGroup()
          ->condition('managers', $user->id())
          ->condition('organizers', $user->id());
        $result = $query->condition($group);
    }
    $result = $query->execute();
    return Grouping::loadMultiple($result);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Grouping entity.'))
      ->setReadOnly(TRUE);
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Grouping entity.'))
      ->setReadOnly(TRUE);
    $fields['revision_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Revision ID'))
      ->setDescription(t('The Revision ID of the Grouping entity.'))
      ->setReadOnly(TRUE);
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Grouping entity.'))
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
    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Grouping entity.'))
      ->setRevisionable(TRUE)
      ->setRequired(TRUE)
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['phone_number'] = BaseFieldDefinition::create('telephone')
      ->setLabel(t('Phone number'))
      ->setDescription(t('The phone number of the grouping, prefixed by country code.'))
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'telephone_default',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['email_address'] = BaseFieldDefinition::create('email')
      ->setLabel(t('E-mail address'))
      ->setDescription(t('The e-mail address of the grouping.'))
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'email_default',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['location'] = BaseFieldDefinition::create('location')
      ->setLabel(t('Location'))
      ->setDescription(t('The location of the grouping.'))
      ->setRevisionable(TRUE)
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
    $fields['timezone'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Timezone'))
      ->setRevisionable(TRUE)
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings(array(
        'allowed_values' => system_time_zones(),
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
    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Description'))
      ->setDescription(t('The description of the grouping.'))
      ->setRevisionable(TRUE)
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
    $fields['members'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Members'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'person')
      ->setSetting('handler', 'default')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => 2,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'inline_entity_form_complex',
        'settings' => array(
          'allow_new' => TRUE,
          'allow_existing' => TRUE,
        ),
        'weight' => 2,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['organizers'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Organizers'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => 2,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'inline_entity_form_complex',
        'settings' => array(
          'allow_new' => TRUE,
          'allow_existing' => TRUE,
        ),
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['managers'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Managers'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => 2,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'inline_entity_form_complex',
        'settings' => array(
          'allow_new' => TRUE,
          'allow_existing' => TRUE,
        ),
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['parent'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Parent grouping'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'grouping')
      ->setSetting('handler', 'default')
      ->setCardinality(1)
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => 2,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'parent_grouping_selector',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Grouping is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);
    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Grouping entity.'))
      ->setRevisionable(TRUE)
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
