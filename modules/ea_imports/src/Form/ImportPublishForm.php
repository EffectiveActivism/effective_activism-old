<?php

namespace Drupal\ea_imports\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ea_imports\Entity\Import;

/**
 * Form controller for Import publish forms.
 *
 * @ingroup ea_imports
 */
class ImportPublishForm extends ConfirmFormBase {

  private $isPublished;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ea_imports_publish_import';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $question = NULL;
    $entity = \Drupal::request()->get('import');
    if (empty($entity)) {
      $question = $this->t('Import not found');
    }
    else {
      $this->isPublished = $entity->isPublished();
      if ($this->isPublished === TRUE) {
        $question = $this->t('Are you sure you want to unpublish the import?');
      }
      else {
        $question = $this->t('Are you sure you want to publish the import?');
      }
    }
    return $question;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $description = NULL;
    if ($this->isPublished === TRUE) {
      $description = $this->t('This action will unpublish the import and all its events.');
    }
    else {
      $description = $this->t('This action will publish the import and all its events.');
    }
    return $description;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    $confirmation = NULL;
    if ($this->isPublished === TRUE) {
      $confirmation = $this->t('Unpublish');
    }
    else {
      $confirmation = $this->t('Publish');
    }
    return $confirmation;
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.import.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $import = NULL) {
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = \Drupal::request()->get('import');
    if (is_bool($this->isPublished)) {
      $this->publishImport($entity);
      if ($this->isPublished === TRUE) {
        drupal_set_message(t('Import has been unpublished'));
      }
      else {
        drupal_set_message(t('Import has been published'));
      }
    }
    // Redirect the user to the list controller when complete.
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * Publishes/unpublishes an import entity.
   *
   * Also changes publish state for the imported events.
   *
   * @param Import $import
   *   The import entity to publish/unpublish.
   */
  private function publishImport(Import $import) {
    $import->setPublished(!$this->isPublished);
    $import->setNewRevision();
    $import->save();
    // Publish/unpublish events.
    if (!empty($import->get('events'))) {
      foreach ($import->get('events') as $event_item) {
        $event = $event_item->entity;
        $event->setPublished(!$this->isPublished);
        $event->setNewRevision();
        $event->save();
      }
    }
  }

}
