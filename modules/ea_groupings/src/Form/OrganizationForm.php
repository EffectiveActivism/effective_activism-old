<?php

namespace Drupal\ea_groupings\Form;

use Drupal\ea_groupings\Entity\Grouping;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for creating organizations.
 *
 * @ingroup ea_groupings
 */
class OrganizationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'ea_groupings_add_organization';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Organization name'),
      '#description' => $this->t('The name of the organization.'),
      '#required' => TRUE,
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t('A brief description of the organization.'),
      '#required' => TRUE,
    ];
    $form['phone_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone number'),
      '#description' => $this->t('The phone number of the organization.'),
    ];
    $form['email_address'] = [
      '#type' => 'email',
      '#title' => $this->t('E-mail address'),
      '#description' => $this->t('The e-mail address of the organization.'),
    ];
    $form['address'] = [
      '#title' => $this->t('Address'),
      '#type' => 'textfield',
      '#autocomplete_route_name' => 'ea_locations.autocomplete',
      '#autocomplete_route_parameters' => [],
      '#size' => 30,
      '#maxlength' => 255,
      '#element_validate' => [
        'LocationWidget::validateAddress',
      ],
      '#attached' => [
        'library' => ['ea_locations/autocomplete'],
      ],
    ];
    $form['extra_information'] = [
      '#title' => $this->t('Other location information'),
      '#type' => 'textfield',
      '#size' => 30,
      '#maxlength' => 255,
    ];
    $form['timezone'] = [
      '#type' => 'select',
      '#title' => $this->t('Timezone'),
      '#description' => $this->t('Select the timezone that the organization mainly uses.'),
      '#options' => system_time_zones(),
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#name' => 'add_organization',
      '#value' => $this->t('Create organization'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // Validate entity.
    $values = [
      'name' => $form_state->getValue('name'),
      'description' => $form_state->getValue('description'),
      'phone_number' => $form_state->getValue('phone_number'),
      'email_address' => $form_state->getValue('email_address'),
      'location' => [
        'address' => $form_state->getValue('address'),
        'extra_information' => $form_state->getValue('extra_information'),
      ],
      'timezone' => $form_state->getValue('timezone'),
    ];
    $entity = Grouping::create($values);
    foreach ($entity->validate() as $violation) {
      if (in_array($violation->getPropertyPath(), array_keys($values))) {
        $form_state->setErrorByName($violation->getPropertyPath(), $this->t($violation->getMessage()));
      }
      else {
        $form_state->setError(NULL, $this->t($violation->getMessage()));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = [
      'name' => $form_state->getValue('name'),
      'description' => $form_state->getValue('description'),
      'phone_number' => $form_state->getValue('phone_number'),
      'email_address' => $form_state->getValue('email_address'),
      'location' => [
        'address' => $form_state->getValue('address'),
        'extra_information' => $form_state->getValue('extra_information'),
      ],
      'timezone' => $form_state->getValue('timezone'),
    ];
    $entity = Grouping::create($values);
    $entity->save();
    drupal_set_message($this->t('Created the organization %name', [
      '%name' => $form_state->getValue('name'),
    ]));
    $form_state->setRedirect('entity.grouping.canonical', ['grouping' => $entity->id()]);
  }

}
