<?php

namespace Drupal\ea_results\Form;

use Drupal\ea_data\Entity\DataType;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal\ea_permissions\Roles;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ResultTypeForm.
 *
 * @package Drupal\ea_results\Form
 */
class ResultTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $result_type = $this->entity;
    $organization = $result_type->organization;
    $groupings = $result_type->groupings;
    // Get data types.
    $data_types = array();
    if (!empty($result_type->data_types)) {
      $data_types = array_map(function ($data_type) {
        return DataType::load($data_type['target_id']);
      }, $result_type->data_types);
    }
    // Get organizations.
    $organizations = array();
    $organizations = array_reduce(Grouping::getGroupings(FALSE, NULL, Roles::MANAGER_ROLE), function ($result, $grouping) {
      $result[$grouping->id()] = $grouping->get('name')->getValue()[0]['value'];
      return $result;
    }, array());
    // Get available groupings.
    $available_groupings = array();
    if (!empty($organization)) {
      $available_groupings = array_reduce(Grouping::load($organization)->getRelatives(TRUE), function ($result, $grouping) {
        $result[$grouping->id()] = $grouping->get('name')->getValue()[0]['value'];
        return $result;
      }, array());
    }
    $form['#prefix'] = '<div id="ajax">';
    $form['#suffix'] = '</div>';
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $result_type->label(),
      '#description' => $this->t("Label for the Result type."),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $result_type->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\ea_results\Entity\ResultType::load',
      ),
      '#disabled' => !$result_type->isNew(),
    );
    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#maxlength' => 255,
      '#default_value' => $result_type->description,
      '#description' => $this->t("Description for the Result type."),
      '#required' => FALSE,
    );
    $form['data_types'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'data_type',
      '#title' => $this->t('Data types'),
      '#default_value' => $data_types,
      '#tags' => TRUE,
      '#description' => $this->t("Data types available for the Result type."),
      '#required' => FALSE,
    );
    $form['organization'] = array(
      '#type' => 'select',
      '#title' => $this->t('Organization'),
      '#default_value' => $organization,
      '#tags' => TRUE,
      '#description' => $this->t("The organization that the Result type is available for. Once this option is saved, it cannot be changed."),
      '#options' => $organizations,
      '#required' => TRUE,
      '#disabled' => $result_type->isNew() ? FALSE : TRUE,
      '#ajax' => [
        'callback' => [$this, 'updateAvailableGroupings'],
        'wrapper' => 'ajax',
      ],
    );
    $form['groupings'] = array(
      '#type' => 'select',
      '#title' => $this->t('Groupings'),
      '#default_value' => $groupings,
      '#description' => $this->t("The groupings the Result type is available for."),
      '#options' => $available_groupings,
      '#multiple' => TRUE,
      '#required' => FALSE,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result_type = $this->entity;
    $status = $result_type->save();
    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Result type.', [
          '%label' => $result_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Result type.', [
          '%label' => $result_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($result_type->urlInfo('collection'));
  }

  /**
   * Populates the groupings #options element.
   */
  public function updateAvailableGroupings(array &$form, FormStateInterface $form_state) {
    return $form;
  }

}
