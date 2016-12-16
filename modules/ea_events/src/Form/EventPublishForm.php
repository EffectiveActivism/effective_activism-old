<?php

namespace Drupal\ea_events\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form controller for Event publish forms.
 *
 * @ingroup ea_events
 */
class EventPublishForm extends ConfirmFormBase {

  private $isPublished;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ea_events_publish_event';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $question = NULL;
    $entity = \Drupal::request()->get('event');
    if (empty($entity)) {
      $question = $this->t('Event not found');
    }
    else {
      $this->isPublished = $entity->isPublished();
      if ($this->isPublished === TRUE) {
        $question = empty($entity->get('title')->value) ? $this->t('Are you sure you want to unpublish the event') : $this->t('Are you sure you want to unpublish <em>@title</em>?', [
          '@title' => $entity->get('title')->value,
        ]);
      }
      else {
        $question = empty($entity->get('title')->value) ? $this->t('Are you sure you want to publish the event') : $this->t('Are you sure you want to publish <em>@title</em>?', [
          '@title' => $entity->get('title')->value,
        ]);
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
      $description = $this->t('This action will unpublish the event and its results.');
    }
    else {
      $description = $this->t('This action will publish the event and its results.');
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
    return new Url('entity.event.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $grouping = NULL) {
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = \Drupal::request()->get('event');
    if (is_bool($this->isPublished)) {
      // Publish/unpublish event.
      $entity->setPublished(!$this->isPublished);
      $entity->setNewRevision();
      $entity->save();
      // Publish/unpublish results.
      if (!empty($entity->get('results'))) {
        foreach ($entity->get('results') as $item) {
          $result = $item->entity;
          $result->setPublished(!$this->isPublished);
          $result->setNewRevision();
          $result->save();
        }
      }
      if ($this->isPublished === TRUE) {
        empty($entity->get('title')->value) ? drupal_set_message(t('Event has been unpublished')) : drupal_set_message(t('<em>@title</em> has been unpublished', [
          '@title' => $entity->get('title')->value,
        ]));
      }
      else {
        empty($entity->get('title')->value) ? drupal_set_message(t('Event has been published')) : drupal_set_message(t('<em>@title</em> has been published', [
          '@title' => $entity->get('title')->value,
        ]));
      }
    }
    // Redirect the user to the list controller when complete.
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
