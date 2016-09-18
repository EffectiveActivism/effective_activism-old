<?php

namespace Drupal\effective_activism\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Defines the Overview controller.
 */
class Overview extends ControllerBase {

  /**
   * Returns a render array for the overview page.
   */
  public function content() {
    $content = array(
      '#type' => 'markup',
      '#markup' => $this->t('Overview page'),
    );
    return $content;
  }

}
