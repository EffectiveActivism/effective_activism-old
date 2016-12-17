<?php

namespace Drupal\ea_groupings\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\ea_groupings\Component\Invitation;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal\ea_permissions\Roles;
use Drupal\user\Entity\User;

/**
 * Provides an invitation response form.
 */
class InvitationForm extends FormBase {

  const FORM_ID = 'ea_grouping_invitation';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return self::FORM_ID;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $invitation = NULL) {
    $role = Roles::getRole($invitation->roleid);
    $grouping = Grouping::load($invitation->gid);
    $form_state->setTemporaryValue('invitation_id', $invitation->id);
    $form_state->setTemporaryValue('grouping', $grouping);
    $form_state->setTemporaryValue('roleid', $invitation->roleid);
    $form['form'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('You have been invited to a grouping'),
      '#attributes' => [
        'class' => [
          'grouping-invitatation',
        ],
      ],
    ];
    $form['form']['invitation'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('You have been invited to join <em>@grouping</em> as @role. Please accept or decline the invitation.', [
        '@grouping' => $grouping->getTitle(),
        '@role' => $role,
      ]) . '</p>',
    ];
    $form['form']['accept'] = [
      '#type' => 'submit',
      '#value' => $this->t('Accept'),
      '#name' => 'accept-invitation',
    ];
    $form['form']['decline'] = [
      '#type' => 'submit',
      '#value' => $this->t('Decline'),
      '#name' => 'decline-invitation',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $invitationId = $form_state->getTemporaryValue('invitation_id');
    $submitElement = $form_state->getTriggeringElement();
    if ($submitElement['#name'] === 'accept-invitation') {
      $grouping = $form_state->getTemporaryValue('grouping');
      $roleId = $form_state->getTemporaryValue('roleid');
      $user = User::load(\Drupal::currentUser()->id());
      // Get link to grouping page.
      $url = $grouping->urlInfo()->setOptions([
        'attributes' => [
          'target' => '_blank',
        ],
      ]);
      $link = Link::fromTextAndUrl($grouping->getTitle(), $url)->toString();
      // Add current user to grouping with specified role.
      switch ($roleId) {
        case Roles::MANAGER_ROLE_ID:
          $status = Invitation::getManagerStatus($grouping->id(), $user->getEmail());
          if ($status === Invitation::INVITATION_STATUS_INVITED) {
            $grouping->managers[] = $user->id();
            drupal_set_message(t('You are now a manager for <em>@link</em>.', ['@link' => $link]));
          }
          break;

        case Roles::ORGANIZER_ROLE_ID:
          $status = Invitation::getOrganizerStatus($grouping->id(), $user->getEmail());
          if ($status === Invitation::INVITATION_STATUS_INVITED) {
            $grouping->organizers[] = $user->id();
            drupal_set_message(t('You are now an organizer for <em>@link</em>.', ['@link' => $link]));
          }
          break;
      }
      $grouping->save();
      // Remove current users invitation.
      Invitation::removeInvition($invitationId);
    }
    elseif ($submitElement['#name'] === 'decline-invitation') {
      // Remove current users invitation.
      Invitation::removeInvition($invitationId);
    }
  }

}
