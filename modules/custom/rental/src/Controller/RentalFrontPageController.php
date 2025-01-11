<?php

namespace Drupal\rental\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;

/**
 * Default Controller class for Rental Front Page.
 */
class RentalFrontPageController extends ControllerBase {

  /**
   * Content callback method.
   *
   * @return array
   *   Return an empty string in a markup render array.
   */
  public function content(): array {
    return [
      '#type' => 'markup',
      '#markup' =>  Markup::create(''),
    ];
  }

}
