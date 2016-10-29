<?php

namespace Drupal\ea_groupings\Plugin\Field\FieldWidget;

use Drupal\ea_groupings\Entity\Grouping;
use Drupal\ea_permissions\Roles;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the parent grouping widget.
 *
 * @FieldWidget(
 *   id = "parent_grouping_selector",
 *   label = @Translation("Parent grouping widget"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ParentGroupingWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $currentGrouping = $form_state->getFormObject()->getEntity();
    $currentId = !empty($currentGrouping) ? (int) $currentGrouping->id() : NULL;
    $allowed_groupings = Grouping::getAllOrganizationsByRole(Roles::MANAGER_ROLE);
    $options = [];
    foreach ($allowed_groupings as $gid => $grouping) {
      if ($gid !== $currentId) {
        $options[$gid] = $grouping->getName();
      }
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
    // Only show form element if grouping is not an organization or is new.
    if ((!empty($currentId) && !empty($items[$delta]->target_id)) || empty($currentId)) {
      $element['target_id'] = [
        '#title' => $this->t('Parent'),
        '#type' => 'radios',
        '#default_value' => $defaultValue,
        '#options' => $options,
      ];
    }
    return $element;
  }

}
