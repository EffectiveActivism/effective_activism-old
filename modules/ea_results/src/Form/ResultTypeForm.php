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
    $selectedOrganization = $this->entity->organization;
    $selectedGroupings = $this->entity->groupings;
    $selectedDataTypes = !empty($this->entity->data_types) ? array_filter(array_values($this->entity->data_types), function ($value) {
      return $value !== 0;
    }) : [];
    // Get available organizations.
    $availableOrganizations = array_reduce(Grouping::getAllOrganizationsByRole(Roles::MANAGER_ROLE), function ($result, $grouping) {
      $result[$grouping->id()] = $grouping->get('name')->getValue()[0]['value'];
      return $result;
    }, []);
    // Get available groupings.
    $availableGroupings = !empty($selectedOrganization) ? array_reduce(Grouping::load($selectedOrganization)->getRelatives(TRUE), function ($result, $grouping) {
      $result[$grouping->id()] = $grouping->get('name')->getValue()[0]['value'];
      return $result;
    }, []) : [];
    // Get available data types.
    $dataBundles = \Drupal::entityManager()->getBundleInfo('data');
    $availableDataTypes = [];
    foreach ($dataBundles as $bundleName => $bundleInfo) {
      $availableDataTypes[$bundleName] = $bundleInfo['label'];
    }
    // Build form.
    $form['#prefix'] = '<div id="ajax">';
    $form['#suffix'] = '</div>';
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
      '#description' => $this->t("Label for the Result type."),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\ea_results\Entity\ResultType::load',
      ),
      '#disabled' => !$this->entity->isNew(),
    );
    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->description,
      '#description' => $this->t("Description for the Result type."),
      '#required' => FALSE,
    );
    $form['data_types'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Data types'),
      '#default_value' => empty($selectedDataTypes) ? [] : $selectedDataTypes,
      '#options' => $availableDataTypes,
      '#description' => $this->t("Data types available for the Result type."),
      '#required' => TRUE,
    );
    $form['organization'] = array(
      '#type' => 'select',
      '#title' => $this->t('Organization'),
      '#default_value' => $selectedOrganization,
      '#tags' => TRUE,
      '#description' => $this->t("The organization that the Result type is available for. Once this option is saved, it cannot be changed."),
      '#options' => $availableOrganizations,
      '#required' => TRUE,
      '#disabled' => $this->entity->isNew() ? FALSE : TRUE,
      '#ajax' => [
        'callback' => [$this, 'updateAvailableGroupings'],
        'wrapper' => 'ajax',
      ],
    );
    $form['groupings'] = array(
      '#type' => 'select',
      '#title' => $this->t('Groupings'),
      '#default_value' => $selectedGroupings,
      '#description' => $this->t("The groupings the Result type is available for."),
      '#options' => $availableGroupings,
      '#multiple' => TRUE,
      '#required' => FALSE,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = $this->entity->save();
    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Result type.', [
          '%label' => $this->entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Result type.', [
          '%label' => $this->entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($this->entity->urlInfo('collection'));
  }

  /**
   * Populates the groupings #options element.
   *
   * @param array $form
   *   The form array.
   * @param FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form array.
   */
  public function updateAvailableGroupings(array &$form, FormStateInterface $form_state) {
    return $form;
  }

}
