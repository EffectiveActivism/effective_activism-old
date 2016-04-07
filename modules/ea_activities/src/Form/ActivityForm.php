<?php

/**
 * @file
 * Contains \Drupal\ea_activities\Form\ActivityForm.
 */

namespace Drupal\ea_activities\Form;

use Drupal\ea_activities\Entity\ActivityType;
use Drupal\ea_data\Entity\DataType;
use Drupal\ea_data\Entity\Data;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Activity edit forms.
 *
 * @ingroup ea_activities
 */
class ActivityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\ea_activities\Entity\Activity */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;
    // Load the entity bundle that this entity belongs to.
    $activity_type_id = $entity->getType();
    $activity_type = ActivityType::load($activity_type_id);
    // Add ActivityType description.
    $form['description'] = array(
      '#type' => 'item',
      '#title' => $this->t('Description'),
      '#description' => $activity_type->description,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);
    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created an Activity.'));
        break;
      default:
        drupal_set_message($this->t('Saved an Activity.'));
    }
    $form_state->setRedirect('entity.activity.canonical', ['activity' => $entity->id()]);
  }

}
