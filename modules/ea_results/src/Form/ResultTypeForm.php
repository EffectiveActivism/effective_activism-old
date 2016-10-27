<?php

namespace Drupal\ea_results\Form;

use Drupal\ea_results\Entity\ResultType;
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
    $selectedDataTypes = !empty($this->entity->datatypes) ? array_filter(array_values($this->entity->datatypes), function ($value) {
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
    $form['importname'] = array(
      '#type' => 'machine_name',
      '#title' => $this->t('Import name'),
      '#description' => $this->t('This name is used when importing results of this type. Can only contain lowercase letters, numbers, and underscores.'),
      '#default_value' => $this->entity->importname(),
      '#machine_name' => array(
        'exists' => '\Drupal\ea_results\Entity\ResultType::checkTypedImportNameExists',
        'label' => $this->t('Import name'),
      ),
      '#required' => TRUE,
      // Disallow changing import name after result type has been created.
      '#disabled' => empty($this->entity->id()) ? FALSE : TRUE,
    );
    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->description,
      '#description' => $this->t("Description for the Result type."),
      '#required' => FALSE,
    );
    $form['datatypes'] = array(
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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $importName = $form_state->getValue('importname');
    $organizationId = $form_state->getValue('organization');
    // Verify that import name is unique within organization.
    // Only perform this check for new result types.
    if (!empty($importName) && !empty($organizationId) && empty($this->entity->id()) && !ResultType::isUniqueImportName($importName, $organizationId)) {
      $form_state->setErrorByName('import_name', $this->t('This import name is already in use for your organization. Please type in another one.'));
    }
    // Derive entity id from import name.
    if (!empty($importName) && empty($this->entity->id())) {
      $form_state->setValue('id', ResultType::createId($importName));
    }
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
