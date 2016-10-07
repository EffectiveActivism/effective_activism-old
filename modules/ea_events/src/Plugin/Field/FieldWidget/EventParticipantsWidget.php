<?php

namespace Drupal\ea_events\Plugin\Field\FieldWidget;

use Drupal\ea_groupings\Entity\Grouping;
use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the parent grouping widget.
 *
 * @FieldWidget(
 *   id = "event_participants",
 *   label = @Translation("Event participants widget"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = true
 * )
 */
class EventParticipantsWidget extends InlineEntityFormComplex {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // Determine the wrapper ID for the entire element.
    $wrapper = 'inline-entity-form-' . $this->getIefId();
    // Remove the 'add existing person' button.
    unset($element['actions']['ief_add_existing']);
    // Add custom button to add people from the event grouping.
    $element['actions']['add_people_from_grouping'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add from grouping'),
      '#name' => 'ief-' . $this->getIefId() . '-add-people-from-grouping',
      '#ajax' => [
        'callback' => 'inline_entity_form_get_element',
        'wrapper' => $wrapper,
      ],
      '#submit' => ['\Drupal\ea_events\Plugin\Field\FieldWidget\EventParticipantsWidget::addGroupMembers'],
      '#ief_form' => 'ief_add_existing',
    ];
    return $element;
  }

  /**
   * Submits the form for adding existing entities.
   *
   * Adds the specified entity to the IEF form state.
   *
   * @param array $form
   *   The entity form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   */
  static public function addGroupMembers($form, FormStateInterface $form_state) {
    $ief_id = $form['participants']['widget']['#ief_id'];
    $event_grouping = $form_state->getValue('grouping');
    // Load the group members.
    if (!empty($event_grouping[0]['target_id'])) {
      $grouping = Grouping::load($event_grouping[0]['target_id']);
      $entities = &$form_state->get(['inline_entity_form', $ief_id, 'entities']);
      // Determine the correct weight of the new element.
      $weight = 0;
      if ($entities) {
        $weight = max(array_keys($entities)) + 1;
      }
      foreach ($grouping->get('members')->referencedEntities() as $person) {
        foreach ($entities as $entity) {
          if ($person->id() === $entity['entity']->id()) {
            // Person is already added to event as participant, skip to next.
            continue 2;
          }
        }
        $entities[] = [
          'entity' => $person,
          'weight' => $weight,
          'form' => NULL,
          'needs_save' => FALSE,
        ];
        $weight++;
      }
      $form_state->set(['inline_entity_form', $ief_id, 'entities'], $entities);
      $form_state->setRebuild();
    }
    return $form['participants'];
  }

}
