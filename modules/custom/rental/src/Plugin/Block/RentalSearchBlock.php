<?php

namespace Drupal\rental\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\rental\Form\RentalSearch;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Search rental block form' block.
 *
 * Rental search block form
 *
 * @Block(
 *   id = "rental_search_block",
 *   admin_label = @Translation("Search rental block form")
 * )
 */
class RentalSearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output['form'] = $this->formBuilder->getForm(RentalSearch::class);
    return $output;
  }

}
