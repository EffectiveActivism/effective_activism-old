<?php
/**
 * @file
 * Contains \Drupal\ea_import\Form\ICalendarForm.
 */

namespace Drupal\ea_import\Form;

use Drupal\ea_import\Storage\ICalendarStorage;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * ICalendar form.
 */
class ICalendarForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ea_import_icalendar';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Grouping $grouping = NULL, $icalendar = NULL) {
    $form = array();
    $form['url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Url'),
      '#description' => $this->t('The url of the ICalendar file.'),
      '#size' => 20,
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $icalendar !== NULL ? $icalendar->url : NULL,
    );
    $form['enabled'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Enabled'),
      '#description' => $this->t('Enable to continuesly import events from this ICalendar file.'),
      '#default_value' => 1,
      '#options' => array(
        1 => $this->t('Enabled'),
        0 => $this->t('Disabled'),
      ),
      '#required' => TRUE,
      '#default_value' => $icalendar !== NULL ? $icalendar->enabled : 1,
    );
    $form['filters'] = array(
      '#type' => 'details',
      '#title' => t('Filters'),
      '#description' => t('You can limit the import to only events that match a filter criteria.'),
    );
    $form['filters']['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title filter'),
      '#description' => $this->t('Only import events that contain this text in their title.'),
      '#size' => 20,
      '#maxlength' => 255,
      '#required' => FALSE,
      '#default_value' => $icalendar !== NULL ? $icalendar->filter_title : NULL,
    );
    $form['filters']['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Description filter'),
      '#description' => $this->t('Only import events that contain this text in their description.'),
      '#size' => 20,
      '#maxlength' => 255,
      '#required' => FALSE,
      '#default_value' => $icalendar !== NULL ? $icalendar->filter_description : NULL,
    );
    $form['filters']['date'] = array(
      '#type' => 'date',
      '#title' => $this->t('Date filter'),
      '#description' => $this->t('Only import events that are newer than this date.'),
      '#required' => FALSE,
      '#default_value' => $icalendar !== NULL ? $icalendar->filter_date : NULL,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#name' => 'import',
      '#value' => $icalendar !== NULL ? $this->t('Update') : $this->t('Add'),
    );
    $form['uid'] = array(
      '#type' => 'value',
      '#value' => \Drupal::currentUser()->id(),
    );
    $form['gid'] = array(
      '#type' => 'value',
      '#value' => $grouping->id(),
    );
    $form['iid'] = array(
      '#type' => 'value',
      '#value' => $icalendar !== NULL ? $icalendar->iid : NULL,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $url = $form_state->getValue('url');
    // Convert webcal scheme to http, as Guzzler may not support webcal.
    if (strpos($url, 'webcal://') === 0) {
      $parsed_url = parse_url($url);
      $parsed_url['scheme'] = 'http';
      $url = 
        (isset($parsed_url['scheme']) ? "{$parsed_url['scheme']}:" : '') . 
        ((isset($parsed_url['user']) || isset($parsed_url['host'])) ? '//' : '') . 
        (isset($parsed_url['user']) ? "{$parsed_url['user']}" : '') . 
        (isset($parsed_url['pass']) ? ":{$parsed_url['pass']}" : '') . 
        (isset($parsed_url['user']) ? '@' : '') . 
        (isset($parsed_url['host']) ? "{$parsed_url['host']}" : '') . 
        (isset($parsed_url['port']) ? ":{$parsed_url['port']}" : '') . 
        (isset($parsed_url['path']) ? "{$parsed_url['path']}" : '') . 
        (isset($parsed_url['query']) ? "?{$parsed_url['query']}" : '') . 
        (isset($parsed_url['fragment']) ? "#{$parsed_url['fragment']}" : '');
      // Change webcal to http in form state.
      $form_state->setValue('url', $url);
    }
    // Validation of the ICalendar file url.
    if (!preg_match("
      /^                                                      # Start at the beginning of the text
      (?:https?):\/\/                                         # Look for http or https schemes
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
    // Check that the import doesn't exist already.
    // Uniqueness is determined by url and grouping.
    $existing_icalendar_imports = ICalendarStorage::load(array(
      'url' => $form_state->getValue('url'),
      'gid' => $form_state->getValue('gid'),
    ));
    $iid = $form_state->getValue('iid');
    if (empty($existing_icalendar_imports) || $iid === $existing_icalendar_imports[0]->iid) {
      // Save the submitted entry.
      $entry = array(
        'url' => $form_state->getValue('url'),
        'enabled' => $form_state->getValue('enabled'),
        'uid' => $form_state->getValue('uid'),
        'gid' => $form_state->getValue('gid'),
        'filter_title' => $form_state->getValue('title'),
        'filter_description' => $form_state->getValue('description'),
        'filter_date' => !empty($form_state->getValue('date')) ? strtotime($form_state->getValue('date')) : 0,
      );
      if ($iid !== NULL) {
        $entry['iid'] = $iid;
        if (ICalendarStorage::update($entry)) {
          drupal_set_message(t('Updated ICalendar file.'));
        }
      }
      else {
        if (ICalendarStorage::insert($entry)) {
          drupal_set_message(t('Added ICalendar file.'));
        }
      }
    }
    else {
      drupal_set_message(t('The ICalendar import already exists.'), 'warning');
    }
  }
}
