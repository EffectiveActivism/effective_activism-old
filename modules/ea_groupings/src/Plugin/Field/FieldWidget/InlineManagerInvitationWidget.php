<?php

namespace Drupal\ea_groupings\Plugin\Field\FieldWidget;

use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the parent grouping widget.
 *
 * @FieldWidget(
 *   id = "inline_manager_invitation",
 *   label = @Translation("Manager invitation widget"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = true
 * )
 */
class InlineManagerInvitationWidget extends InlineEntityFormComplex {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // Determine the wrapper ID for the entire element.
    $wrapper = 'inline-entity-form-' . $this->getIefId();
    // Add custom button to add people from the event grouping.
    $element['actions']['invite_manager'] = [
      '#type' => 'submit',
      '#value' => $this->t('Invite new manager'),
      '#name' => 'ief-' . $this->getIefId() . '-invite',
      '#ajax' => [
        'callback' => 'inline_entity_form_get_element',
        'wrapper' => $wrapper,
      ],
      '#submit' => ['inline_entity_form_open_form'],
      '#ief_form' => 'ief_invite',
    ];
    // Add invitation form.
    if ($form_state->get(['inline_entity_form', $this->getIefId(), 'form']) === 'ief_invite') {
      $element['form'] = [
        '#type' => 'fieldset',
        '#attributes' => ['class' => ['ief-form', 'ief-form-bottom']],
        '#description' => t('An e-mail invitation will be sent containing a unique, one-time link to join the grouping as a manager.'),
      ];
      $element['form']['#title'] = t('Invite a manager');
      $element['form']['invite_email_address'] = [
        '#type' => 'email',
        '#title' => t('E-mail address'),
        '#description' => t('Enter the e-mail address of the person you would like to invite.'),
        '#required' => TRUE,
        '#maxlength' => 255,
      ];
      $element['form']['invite'] = [
        '#type' => 'submit',
        '#value' => t('Invite manager'),
        '#name' => 'ief-reference-invite-' . $this->getIefId(),
        '#limit_validation_errors' => [
          ['managers', 'form', 'invite_email_address'],
        ],
        '#ajax' => [
          'callback' => ['\Drupal\ea_groupings\Plugin\Field\FieldWidget\InlineManagerInvitationWidget', 'invite'],
          'wrapper' => 'inline-entity-form-' . $this->getIefId(),
        ],
        '#submit' => [['\Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex', 'closeForm']],
      ];
      // Hide 'Invite new manager' button.
      unset($element['actions']['invite_manager']);
    }
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
    $email_address = $form_state->getValue(['managers', 'form', 'invite_email_address']);
    // Check that user isn't already added to grouping.
    if (FALSE) {
      // Send e-mail invitation to manager.
    }
    // Display message of invitation status.
    drupal_set_message(t('An invitation to join your grouping as manager has been sent by e-mail to <em>@email_address</em>.', ['@email_address' => $email_address]));
    return $form['managers'];
  }

}
