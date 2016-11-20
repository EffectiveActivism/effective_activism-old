<?php

namespace Drupal\ea_groupings\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\ea_groupings\Component\Invitation;
use Drupal\ea_groupings\Form\InvitationForm;

/**
 * Provides a block prompting the user to respond to an invitation.
 *
 * @Block(
 *   id = "invitation_block",
 *   admin_label = @Translation("Invitation block"),
 * )
 */
class InvitationBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $content = [];
    // Disable caching.
    $content['#cache']['max-age'] = 0;
    // Check if user is invited to any groupings.
    $user = \Drupal::currentUser();
    if ($user->id() > 0) {
      // Look up user e-mail address in table.
      // If user has a pending invitation, display invitation form.
      $email = $user->getEmail();
      $invitations = Invitation::getInvitations($email);
      if (!empty($invitations)) {
        foreach ($invitations as $invitation) {
          $content['invitation-' . $invitation->id] = \Drupal::formBuilder()->getForm(InvitationForm::class, $invitation);
        }
      }
      else {
        // Otherwise, do not render anything.
      }
    }
    return $content;
  }

}
