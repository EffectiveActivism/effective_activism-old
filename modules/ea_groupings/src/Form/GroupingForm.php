<?php

namespace Drupal\ea_groupings\Form;

use Drupal\ea_groupings\Entity\Grouping;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Grouping edit forms.
 *
 * @ingroup ea_groupings
 */
class GroupingForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\ea_groupings\Entity\Grouping */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $entity = $this->entity;
    $parent = $form_state->getValue('parent');
    // Groupings may not have themselves as parent.
    if (!empty($entity) && !empty($parent[0]['target_id']) && $entity->id() === $parent[0]['target_id']) {
      $form_state->setErrorByName('parent', $this->t('A grouping cannot have itself as parent.'));
    }
    // Groupings may not have children as parents.
    if (!empty($parent[0]['target_id'])) {
      $grouping = Grouping::load($parent[0]['target_id']);
      if (!empty($grouping->get('parent')->entity)) {
        $form_state->setErrorByName('parent', $this->t('The selected parent grouping has a parent itself.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);
    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Grouping.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Grouping.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.grouping.canonical', ['grouping' => $entity->id()]);
  }

}
