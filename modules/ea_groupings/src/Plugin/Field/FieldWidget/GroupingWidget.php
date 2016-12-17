<?php

namespace Drupal\ea_groupings\Plugin\Field\FieldWidget;

use Drupal\ea_groupings\Entity\Grouping;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the parent grouping widget.
 *
 * @FieldWidget(
 *   id = "grouping_selector",
 *   label = @Translation("Parent grouping widget"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class GroupingWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $currentGrouping = $form_state->getFormObject()->getEntity();
    $allowed_groupings = Grouping::getAllGroupingsManagedByUser() + Grouping::getAllGroupingsOrganizedByUser();
    $options = [];
    foreach ($allowed_groupings as $gid => $grouping) {
      $options[$gid] = $grouping->getName();
    }
    // Force a default value if possible.
    if (!empty($items[$delta]->target_id)) {
      $defaultValue = $items[$delta]->target_id;
    }
    elseif (!empty($options)) {
      $keys = array_keys($options);
      $defaultValue = reset($keys);
    }
    else {
      $defaultValue = NULL;
    }
    $element['target_id'] = array(
      '#title' => $this->t('Grouping'),
      '#type' => 'radios',
      '#default_value' => $defaultValue,
      '#options' => $options,
    );
    return $element;
  }

}
