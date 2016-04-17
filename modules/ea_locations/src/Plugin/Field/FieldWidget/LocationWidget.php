<?php

/**
 * @file
 * Contains \Drupal\ea_locations\Plugin\Field\FieldWidget\LocationWidget.
 */

namespace Drupal\ea_locations\Plugin\Field\FieldWidget;

use Drupal\ea_locations\Controller\LocationController;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the location widget.
 *
 * @FieldWidget(
 *   id = "location_default",
 *   label = @Translation("Location widget"),
 *   field_types = {
 *     "location"
 *   }
 * )
 */

class LocationWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $address = isset($items[$delta]->address) ? $items[$delta]->address : '';
    $element += array(
      '#type' => 'textfield',
      '#default_value' => $address,
      '#autocomplete_route_name' => 'locations.autocomplete',
      '#autocomplete_route_parameters' => array(),
      '#size' => 30,
      '#maxlength' => 255,
      '#element_validate' => array(
        array($this, 'validate'),
      ),
    );
    return array('address' => $element);
  }

  /**
   * Validate the location.
   */
  public function validate($element, FormStateInterface $form_state) {
    $address = $element['#value'];
    if (!empty($address)) {
      if (!LocationController::validateLocation($address)) {
        $form_state->setError($element, t('Please select a location from the list of suggestions.'));
      }
    }
  }
}
