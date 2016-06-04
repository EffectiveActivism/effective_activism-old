<?php

/**
 * @file
 * Contains \Drupal\ea_import\Form\DeleteICalendarForm.
 */

namespace Drupal\ea_import\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;

/**
 * Delete form for ea_people.
 */
class DeleteICalendarForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ea_import_delete_icalendar';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $icontact = \Drupal::request()->get('icalendar');
    if (!empty($icontact)) {
      return $this->t('Are you sure you want to delete the ICalendar import?');
    }
    else {
      return $this->t('ICalendar not found');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Please confirm deletion.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete ICalendar');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('ea_import.icalendar');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Grouping $grouping = NULL, $icalendar = NULL) {
    $form['iid'] = array(
      '#type' => 'value',
      '#value' => $icalendar->iid,
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $iid = $form_state->getValue('iid');
    $icalendars = ICalendarStorage::load(array('iid' => $iid));
    if (!empty($icalendars)) {
      $icalendar = $icalendars[0];
      // Delete the person.
      ICalendarStorage::delete(array('iid' => $iid));
      drupal_set_message(t('Deleted ICalendar import.'));
    }
    else {
      drupal_set_message(t('ICalendar import not found'));
    }
    // Redirect the user to the list controller when complete.
    $form_state->setRedirectUrl($this->getCancelUrl());
  }
}
