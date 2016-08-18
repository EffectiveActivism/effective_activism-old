<?php
/**
 * @file
 * Contains \Drupal\ea_import\Form\ConfirmForm.
 */

namespace Drupal\ea_import\Form;

use Drupal\ea_import\Storage\ICalendarStorage;
use Drupal\ea_events\Entity\Event;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal\ea_import\Form\MultistepFormBase;
use Drupal;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Confirm form.
 */
class ConfirmForm extends MultistepFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ea_import_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Grouping $grouping = NULL) {
    $form = parent::buildForm($form, $form_state);
    $groupings = $this->store->get('groupings');
    $stored_events = $this->store->get('events');
    // Validate entries according to entity type requirements.
    $event_rows = array();
    $max_examples = 10;
    $counter = 0;
    foreach ($stored_events as $stored_event) {
      if ($counter > 10) {
        break;
      }
      try {
        $event = Event::create($stored_event);
      }
      catch (TypeError $e) {
        drupal_set_message($this->t('An event could not be created.'), 'error');
        continue;
      }
      if (!$event) {
        drupal_set_message($this->t('An event could not be created.'), 'error');
        continue;
      }
      $row = array(
       'data' => array(),
      );
      $row['data'][] = $event->get('title')->getValue()[0]['value'];
      $row['data'][] = $event->get('start_date')->getValue()[0]['value'];
      $row['data'][] = $event->get('end_date')->getValue()[0]['value'];
      $row['data'][] = $event->get('location')->getValue()[0]['address'];
      $event_rows[] = $row;
      $counter++;
    }
    $form['event_preview'] = array(
      '#type' => 'table',
      '#title' => $this->t('Event preview'),
      '#description' => $this->t('This is a preview of the import as it will appear on the site.'),
      '#header' => array(
        $this->t('Title'),
        $this->t('Start date'),
        $this->t('End date'),
        $this->t('Location'),
      ),
      '#rows' => $event_rows,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#name' => 'import',
      '#value' => $this->t('Confirm'),
    );
    $form['uid'] = array(
      '#type' => 'value',
      '#value' => \Drupal::currentUser()->id(),
    );
    $form['gid'] = array(
      '#type' => 'value',
      '#value' => $grouping->id(),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }
}
