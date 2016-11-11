<?php

namespace Drupal\ea_events\Form;

use Drupal\ea_groupings\Entity\Grouping;
use Drupal\ea_results\Entity\ResultType;
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
    $form['#prefix'] = '<div id="ajax">';
    $form['#suffix'] = '</div>';
    $form['grouping']['widget'][0]['target_id']['#ajax'] = [
      'callback' => [$this, 'updateAvailableResultTypes'],
      'wrapper' => 'ajax',
    ];
    $gid = $form_state->getTemporaryValue('gid');
    // If the form is fresh, it has no grouping id. Use default value instead.
    if (empty($gid) && !empty($form['grouping']['widget'][0]['target_id']['#default_value'])) {
      $gid = $form['grouping']['widget'][0]['target_id']['#default_value'];
    }
    // Limit result inline entity form options by result type access settings.
    if (!empty($form['results']['widget']['actions']['bundle']['#options'])) {
      foreach ($form['results']['widget']['actions']['bundle']['#options'] as $machineName => $humanName) {
        $resultType = ResultType::load($machineName);
        if (!empty($resultType)) {
          if (!in_array($gid, $resultType->get('groupings'))) {
            unset($form['results']['widget']['actions']['bundle']['#options'][$machineName]);
          }
        }
      }
      // If there are no options left, hide add button.
      if (empty($form['results']['widget']['actions']['bundle']['#options'])) {
        unset($form['results']['widget']['actions']['ief_add']);
        unset($form['results']['widget']['actions']['bundle']);
      }
    }
    // ...also check if there is only one result type to add.
    elseif (!empty($form['results']['widget']['actions']['bundle']['#value'])) {
      $resultType = ResultType::load($form['results']['widget']['actions']['bundle']['#value']);
      if (!empty($resultType)) {
        if (!in_array($gid, $resultType->get('groupings'))) {
          unset($form['results']['widget']['actions']['ief_add']);
        }
      }
    }
    // Hide fields.
    $form['user_id']['#attributes']['class'][] = 'hidden';
    $form['revision_log_message']['#attributes']['class'][] = 'hidden';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // Make the selected grouping id persistent across form states.
    // We cannot rely on the form_state values, because inline entity forms
    // only submit a subset of values.
    $form_state->setTemporaryValue('gid', $form_state->getValue('grouping')[0]['target_id']);
    // Only allow user to change grouping if existing results are allowed
    // in new grouping.
    if (!empty($form_state->getValue('results')['entities'])) {
      $gid = $form_state->getValue('grouping')[0]['target_id'];
      // Iterate all inline entity forms to find results.
      foreach ($form_state->get('inline_entity_form') as &$widget_state) {
        if (!empty($widget_state['instance'])) {
          if ($widget_state['instance']->getName() === 'results') {
            foreach ($widget_state['entities'] as $delta => $entity_item) {
              if (!empty($entity_item['entity'])) {
                $result = $entity_item['entity'];
                $resultType = ResultType::load($result->getType());
                if (!empty($resultType)) {
                  $allowedGids = $resultType->get('groupings');
                  if (!in_array($gid, $allowedGids)) {
                    $form_state->setErrorByName('grouping', $this->t('<em>@grouping</em> does not allow the result type <em>@result_type</em>. Please select another grouping or remove the result.', [
                      '@grouping' => Grouping::load($gid)->getName(),
                      '@result_type' => $resultType->get('label'),
                    ]));
                    break 2;
                  }
                }
              }
            }
          }
        }
      }
    }
    // Validate participant inline entity form submission values, if any.
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
    // If an organizer has changed grouping, we check to make sure
    // that all the participants either belong to the grouping or are new
    // ( in which case they will be added to the grouping ).
    foreach ($form_state->get('inline_entity_form') as &$widget_state) {
      if (!empty($widget_state['instance'])) {
        if ($widget_state['instance']->getName() === 'participants') {
          $grouping = Grouping::load($form_state->getValue('grouping')[0]['target_id']);
          $changedGrouping = FALSE;
          foreach ($widget_state['entities'] as $delta => $entity_item) {
            if (!empty($entity_item['entity'])) {
              $person = $entity_item['entity'];
              // If person is not a member of any event grouping,
              // add to current grouping.
              if (!Grouping::isAnyMember($person)) {
                $grouping->addMember($person);
                $changedGrouping = TRUE;
                drupal_set_message($this->t('Added @person as member to @grouping', [
                  '@person' => !empty($person->getName()) ? $person->getName() : 'participant',
                  '@grouping' => $grouping->getName(),
                ]));
              }
            }
          }
          if ($changedGrouping) {
            $grouping->save();
          }
        }
      }
    }
    $entity = $this->entity;
    $entity->setNewRevision();
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

  /**
   * Populates the result types #options element.
   *
   * @param array $form
   *   The form array.
   * @param FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form array.
   */
  public function updateAvailableResultTypes(array &$form, FormStateInterface $form_state) {
    return $form;
  }

}
