<?php

namespace Drupal\final_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class that provides table display and work.
 */
class TableController extends ControllerBase {

  /**
   * This variable contains the Drupal service for building forms.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuild;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->formBuild = $container->get('form_builder');
    return $instance;
  }

  /**
   * Method that build and control table.
   */
  public function build() {
    return $this->formBuild->getForm('Drupal\final_module\Form\TableForm');
  }

}
