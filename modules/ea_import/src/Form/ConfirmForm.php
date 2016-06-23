<?php
/**
 * @file
 * Contains \Drupal\ea_import\Form\ConfirmForm.
 */

namespace Drupal\ea_import\Form;

use Drupal\ea_import\Storage\ICalendarStorage;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal\effectiveactivism\Form\MultistepFormBase;
use Drupal;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * ICalendar form.
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
    $entries = $this->store->get('entries');
    $entity_type = $this->store->get('entity_type');
    // Validate entries according to entity type requirements.
    foreach ($entries as $entry) {
      try {
        {$entity_type}::create($entry);
      }
      catch (Exception $e) {
        
      }
    }
    while ($iterator < $count) {
      $row = array(
        'date_start' => $entries[$iterator],
      );
      $table['rows'] = $row;
    }
    $form['preview'] = array(
      '#type' => 'table',
      '#name' => t('Preview'),
      '#description' => t('This is a preview of the import as it will appear on the site.'),
      '#value' => $table;
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
