<?php

/**
 * @file
 * Contains \Drupal\ea_locations\Form\LocationsSettingsForm.
 */

namespace Drupal\ea_locations\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class LocationsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ea_location_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['ea_locations.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ea_locations.settings');
    $form['key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Google Maps API key'),
      '#description' => $this->t('An API key is needed to retrieve data from the Google Maps API. To get a key, create a Google account and go to @link', ['@link' => 'https://code.google.com/apis/console']),
      '#required' => TRUE,
      '#default_value' => $config->get('key'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('ea_locations.settings')
      ->set('key', $form_state->getvalue('key'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
