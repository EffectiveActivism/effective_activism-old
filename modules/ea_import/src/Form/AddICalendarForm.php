<?php
/**
 * @file
 * Contains \Drupal\ea_import\Form\AddICalendarForm.
 */

namespace Drupal\ea_import\Form;

use Drupal\ea_import\Storage\ICalendarStorage;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * ICalendar form.
 */
class AddICalendarForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ea_import_add_icalendar';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Grouping $grouping = NULL) {
    $form = array();
    $form['import'] = array(
      '#type' => 'fieldset',
      '#title' => t('Import'),
      '#description' => t('Import an ICalendar file from Facebook, Google Calendar or any other ICalendar-compatible website.'),
    );
    $form['import']['url'] = array(
      '#type' => 'url',
      '#title' => $this->t('Url'),
      '#description' => $this->t('The url of the ICalendar file.'),
      '#size' => 20,
      '#maxlength' => 255,
      '#required' => TRUE,
    );
    $form['import']['enabled'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Enabled'),
      '#description' => $this->t('Enable to continuesly import events from this ICalendar file.'),
      '#default_value' => 1,
      '#options' => array(
        1 => $this->t('Enabled'),
        0 => $this->t('Disabled'),
      ),
      '#required' => TRUE,
    );
    $form['import']['grouping'] = array(
      '#type' => 'value',
      '#value' => $grouping->id(),
    );
    $form['import']['filters'] = array(
      '#type' => 'details',
      '#title' => t('Filters'),
      '#description' => t('You can limit the import to only events that match a filter criteria.'),
    );
    $form['import']['filters']['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title filter'),
      '#description' => $this->t('Only import events that contain this text in their title.'),
      '#size' => 20,
      '#maxlength' => 255,
      '#required' => FALSE,
    );
    $form['import']['filters']['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Description filter'),
      '#description' => $this->t('Only import events that contain this text in their description.'),
      '#size' => 20,
      '#maxlength' => 255,
      '#required' => FALSE,
    );
    $form['import']['filters']['date'] = array(
      '#type' => 'date',
      '#title' => $this->t('Date filter'),
      '#description' => $this->t('Only import events that are newer than this date.'),
      '#required' => FALSE,
    );
    $form['import']['submit'] = array(
      '#type' => 'submit',
      '#name' => 'import',
      '#value' => $this->t('Add'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $url = $form_state->getValue('url');
    // Validation of the ICalendar file url.
    if (!preg_match("
      /^                                                      # Start at the beginning of the text
      (?:https?|webcal):\/\/                                  # Look for http, https or webcal schemes
      (?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+                        # A domain name or a IPv4 address
        |(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\])         # or a well formed IPv6 address
      )
      (?::[0-9]+)?                                            # Server port number (optional)
      (?:[\/|\?]
        (?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})   # The path and query (optional)
      *)?
      $/xi", $url)) {
      $form_state->setErrorByName('url', $this->t('Please type in a correct ICalendar url.'));
    }
    // Basic validation of the ICalendar file.
    $client = \Drupal::httpClient();
    $request = $client->get($url);
    $file = (string) $request->getBody();
    if (!preg_match("
      /BEGIN:VCALENDAR.*VERSION:[12]\.0.*END:VCALENDAR/s", $file)) {
      $form_state->setErrorByName('url', $this->t('The ICalendar file format is not recognized.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the submitted entry.
    $entry = array(
      'url' => $form_state->getValue('url'),
      'enabled' => $form_state->getValue('enabled'),
      'grouping' => $form_state->getValue('grouping'),
      'filter_title' => $form_state->getValue('title'),
      'filter_description' => $form_state->getValue('description'),
      'filter_date' => !empty($form_state->getValue('date')) ? $form_state->getValue('date') : 0,
    );
    $return = ICalendarStorage::insert($entry);
    if ($return) {
      drupal_set_message(t('Added ICalendar file.'));
    }
  }
}
