<?php

namespace Drupal\ea_people\Plugin\Field\FieldWidget;

use Drupal\ea_groupings\Entity\Grouping;
use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the parent grouping widget.
 *
 * @FieldWidget(
 *   id = "people_selector",
 *   label = @Translation("People widget"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = true
 * )
 */
class PeopleWidget extends InlineEntityFormComplex {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // Get the entity type labels for the UI strings.
    $labels = $this->getEntityTypeLabels();
    // Determine the wrapper ID for the entire element.
    $wrapper = 'inline-entity-form-' . $this->getIefId();
    // Build a parents array for this element's values in the form.
    $parents = array_merge($element['#field_parents'], [
      $items->getName(),
      'form',
    ]);
    // Remove the 'add existing person' button.
    unset($element['actions']['ief_add_existing']);
    // Add custom button to add people from the event grouping.
    $element['actions']['add_people_from_grouping'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add from grouping'),
      '#name' => 'ief-' . $this->getIefId() . '-add-people-from-grouping',
      '#limit_validation_errors' => [array_merge($parents, ['actions'])],
      '#ajax' => [
        'callback' => 'inline_entity_form_get_element',
        'wrapper' => $wrapper,
      ],
      '#submit' => ['inline_entity_form_open_form'],
      '#ief_form' => 'ief_add_existing',
    ];
    return $element;
  }

}
