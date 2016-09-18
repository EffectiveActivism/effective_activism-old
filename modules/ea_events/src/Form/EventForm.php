<?php

namespace Drupal\ea_events\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Event edit forms.
 *
 * @ingroup ea_events
 */
class EventForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\ea_events\Entity\Event */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $participants = $form_state->getValue('participants');
    if (isset($participants['form']['inline_entity_form'])) {
      $mobile_phone_number = $participants['form']['inline_entity_form']['mobile_phone_number'];
      $email_address = $participants['form']['inline_entity_form']['email_address'];
      // Check if either mobile phone number or e-mail is set.
      if (empty($mobile_phone_number[0]['value']) && empty($email_address[0]['value'])) {
        $form_state->setErrorByName($form_state->getTriggeringElement(), $this->t('Please add at least one contact method.'));
      }
      // Check if e-mail or phone number exists in any grouping
      // related to host grouping.
      if (isset($this->entity->grouping->entity)) {
        $host = $this->entity->grouping->entity;
        $groupings = $host->getRelatives();
        foreach ($groupings as $grouping) {
          // Check each member of the grouping.
          foreach ($grouping->get('members')->referencedEntities() as $member) {
            if ($mobile_phone_number[0]['value'] === $member->get('mobile_phone_number')->value) {
              $form_state->setError($form_state->getTriggeringElement(), $this->t('This mobile phone number already exists.'));
            }
            if ($email_address[0]['value'] === $member->get('email_address')->value) {
              $form_state->setError($form_state->getTriggeringElement(), $this->t('This e-mail address already exists.'));
            }
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);
    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created event.'));
        break;

      default:
        drupal_set_message($this->t('Saved the event.'));
    }
    $form_state->setRedirect('entity.event.canonical', ['event' => $entity->id()]);
  }

}
