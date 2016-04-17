<?php

/**
 * @file
 * Contains \Drupal\ea_locations\Plugin\Field\FieldType\LocationType.
 */

namespace Drupal\ea_locations\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Plugin implementation of the 'location' field type.
 *
 * @FieldType(
 *   id = "location",
 *   label = @Translation("Location"),
 *   description = @Translation("Stores a human-readable string and coordinates of a location."),
 *   category = @Translation("Effective Activism"),
 *   default_widget = "location_default",
 *   default_formatter = "location_default"
 * )
 */
class LocationType extends FieldItemBase {

  const EALOCATION_ADDRESS_MAXLENGTH = 255;

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['address'] = DataDefinition::create('string')
      ->setLabel(t('Address'));
    $properties['latitude'] = DataDefinition::create('float')
      ->setLabel(t('Latitude'));
    $properties['longitude'] = DataDefinition::create('float')
      ->setLabel(t('Longitude'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'address' => array(
          'type' => 'char',
          'length' => static::EALOCATION_ADDRESS_MAXLENGTH,
          'not null' => FALSE,
        ),
        'latitude' => array(
          'type' => 'float',
          'size' => 'normal',
          'not null' => FALSE,
        ),
        'longitude' => array(
          'type' => 'float',
          'size' => 'normal',
          'not null' => FALSE,
        ),
      ),
      'indexes' => array(
        'value' => array('value'),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $address = $this->get('address')->getValue();
    return $address === NULL || $address === '';
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints = parent::getConstraints();
    $constraints[] = $constraint_manager->create('ComplexData', array(
      'address' => array(
        'Length' => array(
          'max' => static::EALOCATION_ADDRESS_MAXLENGTH,
          'maxMessage' => t('%name: the location address may not be longer than @max characters.', array('%name' => $this->getFieldDefinition()->getLabel(), '@max' => static::EALOCATION_ADDRESS_MAXLENGTH)),
        )
      ),
    ));
    return $constraints;
  }
}
