<?php

/**
 * @file
 * Contains \Drupal\ea_import\Controller\ListController.
 */

namespace Drupal\ea_import\Controller;

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
  public function listImports() {
    $content = array();
    $content['add_import'] = $this->formBuilder()->getForm('Drupal\ea_import\Form\ImportICalendarForm');
    $content['message'] = array(
      '#markup' => $this->t('Generate a list of all ICalendar imports for this grouping.'),
    );
    $rows = array();
    $header = array(
      array('data' => $this->t('ID'), 'field' => 'import.iid'),
      array('data' => $this->t('Enabled'), 'field' => 'import.enabled'),
      array('data' => $this->t('Provider'), 'field' => 'import.url'),
      $this->t('Edit'),
      $this->t('Delete'),
    );
    foreach ($icalendars = ICalendarStorage::loadSorted($header) as $icalendar) {
      $row = array();
      $row['data']['iid'] = $icalendar->iid;
      $row['data']['enabled'] = ((boolean) $icalendar->enabled ? $this->t('Yes') : $this->t('No'));
      $row['data']['url'] = $icalendar->url;
      // Format edit link.
      $edit_link_url = Url::fromRoute('ea_import.icalendar.edit', array('icontact' => $icalendar->iid));
      $edit_link = \Drupal::l($this->t('Edit'), $edit_link_url);
      // Format delete link.
      $delete_link_url = Url::fromRoute('ea_import.icalendar.delete', array('icontact' => $icalendar->iid));
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
    return $content;
  }
}
