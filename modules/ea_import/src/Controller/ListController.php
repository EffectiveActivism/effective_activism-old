<?php

/**
 * @file
 * Contains \Drupal\ea_import\Controller\ListController.
 */

namespace Drupal\ea_import\Controller;

use Drupal\ea_import\Storage\ICalendarStorage;
use Drupal\ea_groupings\Entity\Grouping;
use Drupal\user\Entity\User;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Component\Utility;
use Drupal\views\Views;

/**
 * Controller for ea_import.
 */
class ListController extends ControllerBase {

  /**
   * Render a list of entries in the table.
   */
  public function listImports($grouping = NULL) {
    $content = array();
    if ($grouping_object = Grouping::load($grouping)) {
      $content['message'] = array(
        '#markup' => $this->t('Generate a list of all ICalendar imports for this grouping.'),
      );
      $rows = array();
      $header = array(
        array('data' => $this->t('Enabled'), 'field' => 'icalendar.enabled'),
        array('data' => $this->t('Owner'), 'field' => 'icalendar.uid'),
        array('data' => $this->t('Provider'), 'field' => 'icalendar.url'),
        $this->t('Edit'),
        $this->t('Delete'),
      );
      foreach ($icalendars = ICalendarStorage::loadSorted($header) as $icalendar) {
        $row = array();
        $row['data']['enabled'] = ((boolean) $icalendar->enabled ? $this->t('Yes') : $this->t('No'));
        // Format user.
        $user = \Drupal\user\Entity\User::load($icalendar->uid);
        $row['data']['uid'] = $user->getAccountName();
        // Format url.
        $parsed_url = parse_url($icalendar->url);
        $url = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $row['data']['url'] = $url;
        // Format edit link.
        $edit_link_url = Url::fromRoute('ea_import.icalendar.edit', array(
          'grouping' => $grouping,
          'icalendar' => $icalendar->iid,
        ));
        $edit_link = \Drupal::l($this->t('Edit'), $edit_link_url);
        // Format delete link.
        $delete_link_url = Url::fromRoute('ea_import.icalendar.delete', array(
          'grouping' => $grouping,
          'icalendar' => $icalendar->iid,
        ));
        $delete_link = \Drupal::l($this->t('Delete'), $delete_link_url);
        // Add links to row.
        $row['data']['edit_link'] = $edit_link;
        $row['data']['delete_link'] = $delete_link;
        $rows[] = $row;
      }
      $content['table'] = array(
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => t('No ICalendar imports available.'),
      );
      $content['pager'] = array('#type' => 'pager');
      $content['add_import'] = $this->formBuilder()->getForm('Drupal\ea_import\Form\AddICalendarForm', $grouping_object);
    }
    return $content;
  }
}
