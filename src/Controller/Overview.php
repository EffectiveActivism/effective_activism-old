<?php
/**
 * @file
 * Contains \Drupal\effective_activism\Controller\Overview.
 */

namespace Drupal\effective_activism\Controller;

use Drupal\Core\Controller\ControllerBase;

class Overview extends ControllerBase {
  public function content() {
    $content = array(
      '#type' => 'markup',
      '#markup' => $this->t('Overview page'),
    );
    return $content;
  }
}
