<?php

namespace Drupal\ea_groupings\Plugin\Field\FieldWidget;

use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the parent grouping widget.
 *
 * @FieldWidget(
 *   id = "inline_organizer_invitation",
 *   label = @Translation("Organizer invitation widget"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = true
 * )
 */
class InlineOrganizerInvitationWidget extends InlineEntityFormComplex {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // Determine the wrapper ID for the entire element.
    $wrapper = 'inline-entity-form-' . $this->getIefId();
    // Add custom button to add people from the event grouping.
    $element['actions']['invite_organizer'] = [
      '#type' => 'submit',
      '#value' => $this->t('Invite new organizer'),
      '#name' => 'ief-' . $this->getIefId() . '-invite',
      '#ajax' => [
        'callback' => 'inline_entity_form_get_element',
        'wrapper' => $wrapper,
      ],
      '#submit' => ['\Drupal\ea_groupings\Plugin\Field\FieldWidget\InlineOrganizerInvitationWidget::invite'],
      '#ief_form' => 'ief_add_existing',
    ];
    return $element;
  }

  /**
   * Submits the form for inviting users.
   *
   * @param array $form
   *   The entity form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   */
  static public function invite($form, FormStateInterface $form_state) {
    $ief_id = $form['organizers']['widget']['#ief_id'];
    $email_address = $form_state->getValue('email');
    // Check that user isn't already added to grouping.
    if (FALSE) {
      // Send e-mail invitation to organizer.
    }
    // Display message of invitation status.
    return $form['organizers'];
  }

}
