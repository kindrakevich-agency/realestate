<?php

namespace Drupal\rental\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Markup;

/**
 * Provides a 'Map block' block.
 *
 * Rental Map block
 *
 * @Block(
 *   id = "rental_map_block",
 *   admin_label = @Translation("Map block for rental node")
 * )
 */
class RentalMapBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    $url = \Drupal::service('plugin.manager.geolocation.mapprovider')->getMapProvider('google_maps')->getGoogleMapsApiUrl();
    parse_str(parse_url($url, PHP_URL_QUERY), $url_query);
    return [
      '#markup' => Markup::create('<div id="rentalmap" style="width:100%;height:400px;"></div>'),
      '#attached' => [
        'library' => [
          'rental/rental.map',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'google_map_url' => $url,
            'google_map_url_info' => $url_query,
          ],
          'locations' => $this->getLocationMapData(),
        ],
      ],
    ];
  }

  public function getLocationMapData() {
    $result = [];
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
        $result['title'] = $node->getTitle();
        $result['coords'] = false;
        if(isset($node->field_approximate_location->value)){
          $coords = $node->field_approximate_location->value;
          $result['coords'] = explode(',', $coords);
        }
    }
    return $result;
  }

}
