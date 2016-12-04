<?php

namespace Drupal\ea_groupings\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ea_groupings\Component\Invitation;
use Drupal\ea_permissions\Roles;
use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex;

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
    // Remove 'add existing user' button.
    // This step is necessary because 'allow_existing' must be TRUE in order for
    // entities not to be deleted when their reference is removed.
    unset($element['actions']['ief_add_existing']);
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
      '#submit' => ['inline_entity_form_open_form'],
      '#ief_form' => 'ief_invite',
    ];
    // Add invitation form if grouping id is set.
    if ($form_state->get(['inline_entity_form', $this->getIefId(), 'form']) === 'ief_invite') {
      $element['form'] = [
        '#type' => 'fieldset',
        '#attributes' => ['class' => ['ief-form', 'ief-form-bottom']],
        '#description' => t('Type in the e-mail address of the person you would like to invite to the grouping as organizer.'),
      ];
      $element['form']['#title'] = t('Invite an organizer');
      $element['form']['invite_email_address'] = [
        '#type' => 'email',
        '#title' => t('E-mail address'),
        '#description' => t('Enter the e-mail address of the person you would like to invite.'),
        '#required' => TRUE,
        '#maxlength' => 255,
      ];
      $element['form']['invite'] = [
        '#type' => 'submit',
        '#value' => t('Invite organizer'),
        '#name' => 'ief-reference-invite-' . $this->getIefId(),
        '#limit_validation_errors' => [
          ['organizers', 'form', 'invite_email_address'],
        ],
        '#ajax' => [
          'callback' => ['\Drupal\ea_groupings\Plugin\Field\FieldWidget\InlineOrganizerInvitationWidget', 'invite'],
          'wrapper' => 'inline-entity-form-' . $this->getIefId(),
        ],
        '#submit' => [['\Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex', 'closeForm']],
      ];
      $element['form']['cancel'] = [
        '#type' => 'submit',
        '#value' => t('Cancel'),
        '#name' => 'ief-reference-cancel-' . $this->getIefId(),
        '#limit_validation_errors' => [],
        '#ajax' => [
          'callback' => 'inline_entity_form_get_element',
          'wrapper' => 'inline-entity-form-' . $this->getIefId(),
        ],
        '#submit' => [['\Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex', 'closeForm']],
      ];
      // Hide 'Invite new organizer' button.
      unset($element['actions']['invite_organizer']);
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
    $gid = $form_state->getTemporaryValue('gid');
    $email = $form_state->getValue([
      'organizers',
      'form',
      'invite_email_address',
    ]);
    $status = NULL;
    if (!empty($email)) {
      $status = Invitation::getOrganizerStatus($gid, $email);
      // Add invitation.
      if ($status === Invitation::INVITATION_STATUS_NEW_USER || $status === Invitation::INVITATION_STATUS_EXISTING_USER) {
        Invitation::addInvition($gid, Roles::ORGANIZER_ROLE_ID, $email);
      }
      // Display message of invitation status.
      switch ($status) {
        case Invitation::INVITATION_STATUS_ALREADY_ORGANIZER:
          drupal_set_message(t('The user is already an organizer of this grouping.'), 'warning');
          break;

        case Invitation::INVITATION_STATUS_INVITED:
          drupal_set_message(t('The user is already invited to this grouping.'), 'warning');
          break;

        case Invitation::INVITATION_STATUS_NEW_USER:
          drupal_set_message(t('An invitation to join your grouping as organizer will be shown for the user with the e-mail address <em>@email_address</em> once the person registers with the site.', ['@email_address' => $email]));
          break;

        case Invitation::INVITATION_STATUS_EXISTING_USER:
          drupal_set_message(t('An invitation to join your grouping as organizer will be shown for the user with the e-mail address <em>@email_address</em> next time the user logs in.', ['@email_address' => $email]));
          break;
      }
    }
    return $form['organizers'];
  }

}
