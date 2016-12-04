<?php

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
    $extra_information = isset($items[$delta]->extra_information) ? $items[$delta]->extra_information : '';
    $element['address'] = [
      '#title' => $this->t('Address'),
      '#type' => 'textfield',
      '#default_value' => $address,
      '#autocomplete_route_name' => 'ea_locations.autocomplete',
      '#autocomplete_route_parameters' => [],
      '#size' => 30,
      '#maxlength' => 255,
      '#element_validate' => array(
        array($this, 'validateAddress'),
      ),
      '#attached' => [
        'library' => ['ea_locations/autocomplete'],
      ],
    ];
    $element['extra_information'] = [
      '#title' => $this->t('Other location information'),
      '#type' => 'textfield',
      '#default_value' => $extra_information,
      '#size' => 30,
      '#maxlength' => 255,
    ];
    return $element;
  }

  /**
   * Validate the address.
   */
  public function validateAddress($element, FormStateInterface $form_state) {
    $address = $element['#value'];
    $locationController = new LocationController();
    if (!empty($address)) {
      if (!$locationController->validateAddress($address)) {
        $form_state->setError($element, t('Please select an address from the list of suggestions.'));
      }
    }
  }

}
