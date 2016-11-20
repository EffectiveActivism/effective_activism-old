<?php

namespace Drupal\ea_groupings\Entity;

use Drupal\ea_groupings\GroupingInterface;
use Drupal\ea_permissions\Roles;
use Drupal\ea_people\Entity\Person;
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

  const WEIGHTS = [
    'user_id',
    'name',
    'description',
    'parent',
    'phone_number',
    'email_address',
    'location',
    'timezone',
    'managers',
    'organizers',
    'members',
  ];

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
  public function addMember(Person $person) {
    if (!$this->isMember($person)) {
      $this->members[] = $person;
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeMember(Person $person) {
    if (!empty($this->get('members'))) {
      $count = 0;
      foreach ($this->get('members') as $member) {
        if ($member === $person) {
          unset($this->members[$count]);
        }
        $count++;
      }
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isMember(Person $person) {
    $isMember = FALSE;
    if (!empty($this->get('members'))) {
      foreach ($this->get('members') as $member) {
        if ($member === $person) {
          $isMember = TRUE;
          break;
        }
      }
    }
    return $isMember;
  }

  /**
   * {@inheritdoc}
   */
  public static function isAnyMember(Person $person) {
    $isMember = FALSE;
    $query = \Drupal::entityQuery('grouping');
    $result = $query
      ->condition('members', $person->id())
      ->count()
      ->execute();
    if (!empty($result)) {
      $isMember = TRUE;
    }
    return $isMember;
  }

  /**
   * {@inheritdoc}
   */
  public static function getAllGroupingsByUser(AccountProxyInterface $user) {
    // Add all groupings that the user is manager of.
    $query = \Drupal::entityQuery('grouping');
    $result = $query
      ->condition('managers', $user->id())
      ->execute();
    // Also include groupings that are children to the managers groupings.
    if (!empty($result)) {
      $query = \Drupal::entityQuery('grouping');
      $result += $query
        ->condition('parent', $result, 'IN')
        ->execute();
    }
    // Finally, add all groupings that the user is organizer of.
    $query = \Drupal::entityQuery('grouping');
    $result += $query
      ->condition('organizers', $user->id())
      ->execute();
    return Grouping::loadMultiple($result);
  }

  /**
   * {@inheritdoc}
   */
  public static function getAllGroupingsByRole($role, AccountProxyInterface $user = NULL) {
    $result = [];
    if (empty($user)) {
      $user = \Drupal::currentUser();
    }
    switch ($role) {
      case Roles::ORGANIZER_ROLE:
        $query = \Drupal::entityQuery('grouping');
        $result = $query
          ->condition('organizers', $user->id())
          ->execute();
        break;

      case Roles::MANAGER_ROLE:
        $query = \Drupal::entityQuery('grouping');
        $result = $query
          ->condition('managers', $user->id())
          ->execute();
        // Also include groupings that are children to the managers groupings.
        if (!empty($result)) {
          $query = \Drupal::entityQuery('grouping');
          $result += $query
            ->condition('parent', $result, 'IN')
            ->execute();
        }
        break;

    }
    return Grouping::loadMultiple($result);
  }

  /**
   * {@inheritdoc}
   */
  public static function getAllOrganizationsByRole($role, AccountProxyInterface $user = NULL) {
    $result = [];
    if (empty($user)) {
      $user = \Drupal::currentUser();
    }
    switch ($role) {
      case Roles::ORGANIZER_ROLE:
        $query = \Drupal::entityQuery('grouping');
        $result = $query
          ->notExists('parent')
          ->condition('organizers', $user->id())
          ->execute();
        break;

      case Roles::MANAGER_ROLE:
        $query = \Drupal::entityQuery('grouping');
        $result = $query
          ->notExists('parent')
          ->condition('managers', $user->id())
          ->execute();
        break;

    }
    return Grouping::loadMultiple($result);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
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
        'weight' => array_search('user_id', self::WEIGHTS),
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
        'weight' => array_search('name', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => array_search('name', self::WEIGHTS),
      ));
    $fields['phone_number'] = BaseFieldDefinition::create('telephone')
      ->setLabel(t('Phone number'))
      ->setDescription(t('The phone number of the grouping.'))
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => array_search('phone_number', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'telephone_default',
        'weight' => array_search('phone_number', self::WEIGHTS),
      ));
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
        'weight' => array_search('email_address', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'email_default',
        'weight' => array_search('email_address', self::WEIGHTS),
      ));
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
        'weight' => array_search('location', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'location_default',
        'weight' => array_search('location', self::WEIGHTS),
      ));
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
        'weight' => array_search('timezone', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => array_search('timezone', self::WEIGHTS),
      ));
    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Description'))
      ->setDescription(t('The description of the grouping.'))
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
    $fields['members'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Members'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'person')
      ->setSetting('handler', 'default')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => array_search('members', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'inline_entity_form_complex',
        'settings' => array(
          'allow_new' => TRUE,
          'allow_existing' => FALSE,
        ),
        'weight' => array_search('members', self::WEIGHTS),
      ));
    $fields['organizers'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Organizers'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => array_search('organizers', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'inline_organizer_invitation',
        'settings' => array(
          'allow_new' => FALSE,
          'allow_existing' => FALSE,
        ),
        'weight' => array_search('organizers', self::WEIGHTS),
      ));
    $fields['managers'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Managers'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => array_search('managers', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'inline_manager_invitation',
        'settings' => array(
          'allow_new' => FALSE,
          'allow_existing' => FALSE,
        ),
        'weight' => array_search('managers', self::WEIGHTS),
      ));
    $fields['parent'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Parent grouping'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'grouping')
      ->setSetting('handler', 'default')
      ->setCardinality(1)
      ->setDisplayOptions('view', array(
        'type' => 'string',
        'weight' => array_search('parent', self::WEIGHTS),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'parent_grouping_selector',
        'weight' => array_search('parent', self::WEIGHTS),
      ));
    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Grouping is published.'))
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
