<?php

namespace Drupal\ea_results\Form;

use Drupal\ea_results\Entity\ResultType;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Result edit forms.
 *
 * @ingroup ea_results
 */
class ResultForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\ea_results\Entity\Result */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;
    // Load the entity bundle that this entity belongs to.
    $result_type_id = $entity->getType();
    $result_type = ResultType::load($result_type_id);
    // Add ResultType description.
    $form['description'] = array(
      '#type' => 'item',
      '#title' => $this->t('Description'),
      '#description' => $result_type->description,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $entity->setNewRevision();
    $status = parent::save($form, $form_state);
    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created an Result.'));
        break;

      default:
        drupal_set_message($this->t('Saved an Result.'));
    }
    $form_state->setRedirect('entity.result.canonical', ['result' => $entity->id()]);
  }

}
