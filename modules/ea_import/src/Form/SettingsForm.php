<?php

/**
 * @file
 * Contains \Drupal\ea_import\Form\SettingsForm.
 */

namespace Drupal\ea_import\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/*
 * Provides a configuration form for import settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ea_import_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['ea_import.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ea_import.settings');
    $form['enabled'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Enabled'),
      '#description' => $this->t('Enable to continuously import events from ICalendar files.'),
      '#default_value' => $config->get('enabled') !== NULL ? $config->get('enabled') : 0,
      '#options' => array(
        0 => $this->t('Disabled'),
        1 => $this->t('Enabled'),
      ),
      '#required' => TRUE,
    );
    $form['interval'] = array(
      '#type' => 'select',
      '#title' => $this->t('Interval'),
      '#description' => $this->t('Specify the interval of the import cron task.'),
      '#default_value' => $config->get('interval') !== NULL ? $config->get('interval') : '86400',
      '#options' => array(
        '3600' => $this->t('1 hour'),
        '7200' => $this->t('2 hours'),
        '10800' => $this->t('3 hours'),
        '18000' => $this->t('5 hours'),
        '36000' => $this->t('10 hours'),
        '43200' => $this->t('12 hours'),
        '86400' => $this->t('1 day'),
        '172800' => $this->t('2 days'),
        '604800' => $this->t('1 week'),
      ),
      '#required' => TRUE,
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('ea_import.settings')
      ->set('enabled', $form_state->getvalue('enabled'))
      ->set('interval', $form_state->getvalue('interval'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}
