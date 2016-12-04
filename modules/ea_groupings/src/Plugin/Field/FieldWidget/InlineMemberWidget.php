<?php

namespace Drupal\ea_groupings\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex;

/**
 * Plugin implementation of the parent grouping widget.
 *
 * @FieldWidget(
 *   id = "inline_group_member",
 *   label = @Translation("Manager invitation widget"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = true
 * )
 */
class InlineMemberWidget extends InlineEntityFormComplex {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // Remove 'add existing user' button.
    // This step is necessary because 'allow_existing' must be TRUE in order for
    // entities not to be deleted when their reference is removed.
    unset($element['actions']['ief_add_existing']);
    return $element;
  }

}
