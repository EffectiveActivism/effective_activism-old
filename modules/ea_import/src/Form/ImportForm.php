<?php

namespace Drupal\ea_import\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityStorageException;

/**
 * Form controller for Import edit forms.
 *
 * @ingroup ea_import
 */
class ImportForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\ea_import\Entity\Import */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    try {
      $status = parent::save($form, $form_state);
      switch ($status) {
        case SAVED_NEW:
          drupal_set_message($this->t('Created the %label Import.', [
            '%label' => $entity->label(),
          ]));
          break;
        default:
          drupal_set_message($this->t('Saved the %label Import.', [
            '%label' => $entity->label(),
          ]));
      }
      $form_state->setRedirect('entity.import.canonical', ['import' => $entity->id()]);
    }
    catch (EntityStorageException $exception) {
      // Custom field validation for configured bundle types is not yet possible.
      // It is also not yet possible to define bundle types as classes.
      // Therefore, as a temporary solution, we throw exceptions in a
      // hook_entity_presave call and catch them here.
      // This has the downside that the entity form state is discarded
      // whenever custom field validation fails.
      drupal_set_message($exception->getMessage(), 'error');
    }
  }
}