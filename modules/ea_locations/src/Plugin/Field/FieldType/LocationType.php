<?php

namespace Drupal\ea_locations\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\ea_locations\Controller\LocationController;

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

  const ADDRESS_MAXLENGTH = 255;

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['address'] = DataDefinition::create('string')
      ->setLabel(t('Address'));
    $properties['extra_information'] = DataDefinition::create('string')
      ->setLabel(t('Extra information'));
    $properties['latitude'] = DataDefinition::create('float')
      ->setLabel(t('Latitude'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\ea_locations\Coordinates\Latitude');
    $properties['longitude'] = DataDefinition::create('float')
      ->setLabel(t('Longitude'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\ea_locations\Coordinates\Longitude');
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'address' => [
          'type' => 'char',
          'length' => self::ADDRESS_MAXLENGTH,
          'not null' => FALSE,
        ],
        'extra_information' => [
          'type' => 'char',
          'length' => self::ADDRESS_MAXLENGTH,
          'not null' => FALSE,
        ],
        'latitude' => [
          'type' => 'float',
          'size' => 'big',
          'not null' => FALSE,
        ],
        'longitude' => [
          'type' => 'float',
          'size' => 'big',
          'not null' => FALSE,
        ],
      ],
      'indexes' => [
        'address' => ['address'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $address = $this->get('address')->getValue();
    return empty($address);
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints = parent::getConstraints();
    $constraints[] = $constraint_manager->create('ComplexData', [
      'address' => [
        'Length' => [
          'max' => self::ADDRESS_MAXLENGTH,
          'maxMessage' => t('%name: the location address may not be longer than @max characters.', [
            '%name' => $this->getFieldDefinition()->getLabel(),
            '@max' => self::ADDRESS_MAXLENGTH,
          ]),
        ],
      ],
    ]);
    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    if (!empty($values['address'])) {
      // Retrieve the GPS coordinates from the cached locations table.
      $location = \Drupal::database()
        ->select(LocationController::LOCATION_CACHE_TABLE, 'location')
        ->fields('location', [
          'lat',
          'lon',
        ])
        ->condition('address', $values['address'])
        ->execute()
        ->fetchAssoc();
      $values['latitude'] = $location['lat'];
      $values['longitude'] = $location['lon'];
    }
    parent::setValue($values, $notify);
  }

}
