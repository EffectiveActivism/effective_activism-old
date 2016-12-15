<?php

namespace Drupal\ea_groupings\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ea_events\Entity\Event;
use Drupal\ea_groupings\Entity\Grouping;

/**
 * Form controller for Grouping publish forms.
 *
 * @ingroup ea_groupings
 */
class GroupingPublishForm extends ConfirmFormBase {

  private $isPublished;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ea_groupings_publish_grouping';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $question = NULL;
    $entity = \Drupal::request()->get('grouping');
    if (empty($entity)) {
      $question = $this->t('Grouping not found');
    }
    else {
      $this->isPublished = $entity->isPublished();
      if ($this->isPublished === TRUE) {
        $question = $this->t('Are you sure you want to unpublish <em>@name</em>?', [
          '@name' => $entity->get('name')->value,
        ]);
      }
      else {
        $question = $this->t('Are you sure you want to publish <em>@name</em>?', [
          '@name' => $entity->get('name')->value,
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
      $description = $this->t('This action will unpublish the grouping and all its events, imports and results.');
    }
    else {
      $description = $this->t('This action will publish the grouping and all its events, imports and results.');
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
    return new Url('entity.grouping.collection');
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
    $entity = \Drupal::request()->get('grouping');
    if (is_bool($this->isPublished)) {
      $this->publishGrouping($entity);
      if ($this->isPublished === TRUE) {
        drupal_set_message(t('<em>@name</em> has been unpublished', [
          '@name' => $entity->get('name')->value,
        ]));
      }
      else {
        drupal_set_message(t('<em>@name</em> has been published', [
          '@name' => $entity->get('name')->value,
        ]));
      }
    }
    // Redirect the user to the list controller when complete.
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * Publishes/unpublishes a grouping.
   *
   * Also changes publish state for the grouping events, members and
   * child groupings and their events and members.
   *
   * @param Grouping $grouping
   *   The grouping to publish/unpublish.
   */
  private function publishGrouping(Grouping $grouping) {
    $grouping->setPublished(!$this->isPublished);
    $grouping->setNewRevision();
    $grouping->save();
    // Get events hosted by grouping.
    $query = \Drupal::entityQuery('event');
    $event_ids = $query
      ->condition('grouping', $grouping->id())
      ->execute();
    if (!empty($event_ids)) {
      foreach ($event_ids as $event_id) {
        $event = Event::load($event_id);
        // Publish/unpublish event.
        $event->setPublished(!$this->isPublished);
        $event->setNewRevision();
        $event->save();
        // Publish/unpublish results.
        if (!empty($event->get('results'))) {
          foreach ($event->get('results') as $result_item) {
            $result = $result_item->entity;
            $result->setPublished(!$this->isPublished);
            $result->setNewRevision();
            $result->save();
          }
        }
      }
    }
    // Publish/unpublish members.
    if (!empty($grouping->get('members'))) {
      foreach ($members as $member_item) {
        $member = $member_item->entity;
        $member->setPublished(!$this->isPublished);
        $member->setNewRevision();
        $member->save();
      }
    }
    // Publish/unpublish child groupings.
    $query = \Drupal::entityQuery('grouping');
    $grouping_ids = $query
      ->condition('parent', $grouping->id())
      ->execute();
    if (!empty($grouping_ids)) {
      foreach ($grouping_ids as $grouping_id) {
        $this->publishGrouping(Grouping::load($grouping_id));
      }
    }
  }

}
