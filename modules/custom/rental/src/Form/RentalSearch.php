<?php

namespace Drupal\rental\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

/**
 * Implements the RentalSearch form controller.
 *
 * Search rentals form.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class RentalSearch extends FormBase {

  /**
   * Build the simple form.
   *
   * A build form method constructs an array that defines how markup and
   * other form elements are included in an HTML form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#attributes']['autocomplete'] = 'off';

    $type = $location = $bedrooms = null;

    // If is term page
    if (\Drupal::routeMatch()->getRouteName() == 'entity.taxonomy_term.canonical') {
      $term_id = \Drupal::routeMatch()->getRawParameter('taxonomy_term');
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term_id);
      $vid = $term->bundle();
      $vocabulary = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->load($vid);
      if($vocabulary->label()=='Type'){
        $type = $term_id;
      }
      if($vocabulary->label()=='Location'){
        $location = $term_id;
      }
    }

    if(!$type){
      $type = $form_state->getValue('type');
    }
    if(!$type){
      $type = 200;
    }

    if(!$location){
      $location = $form_state->getValue('location');
      $location_request = \Drupal::request()->query->get('location');
      if(!$location && $location_request){
        $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($location_request);
        if($term){
          $location = $location_request;
        }
      }
    }

    $bedrooms = \Drupal::request()->query->get('bedrooms');

    $requests = $location.'/'.$bedrooms.'/'.$type;
    //\Drupal::logger('rental')->notice('<pre><code>init:' . print_r($requests, TRUE) . '</code></pre>' );

    if(\Drupal::service('path.matcher')->isFrontPage()){
      $form['#attributes']['class'][] = 'bg-light shadow rounded-3 p-4';
      $form['row'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'row',
            'g-4',
            'align-items-center'
          ]
        ],
      ];
    } else {
      $form['#attributes']['class'][] = 'shadow rounded-3 p-4';
      $form['#attributes']['style'][] = 'background-color:#69a7fe24;';
      $form['row'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'row',
            'align-items-center'
          ]
        ],
      ];
    }

    $form['row']['col'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'col-xl-10'
        ]
      ],
    ];

    $form['row']['col']['row_inner'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'row',
          'g-4'
        ]
      ],
    ];

    // Location
    $form['row']['col']['row_inner']['col_location'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'col-md-6',
          'col-lg-4'
        ],
        'id' => 'location-wrapper'
      ],
    ];
    $form['row']['col']['row_inner']['col_location']['label'] = [
      '#type' => 'html_tag',
      '#tag' => 'label',
      '#value' => $this->t('Location'),
      '#attributes' => [
        'class' => [
          'h6',
          'fw-normal',
          'mb-2'
        ]
      ],
    ];

    $options = [];
    $database = \Drupal::database();
    if($type){
      $sql = "SELECT t.name, t.tid FROM {taxonomy_term_field_data} AS t JOIN {node__field_location} AS l ON t.tid = l.field_location_target_id JOIN {node__field_type} AS ft ON l.entity_id = ft.entity_id JOIN {node_field_data} AS n ON l.entity_id = n.nid  WHERE t.vid = :vid AND n.status = 1 and ft.field_type_target_id = :type ORDER by t.name ASC";
      $result = $database->query($sql, [
        ':vid' => 'location',
        ':type' => $type,
      ]);
    } else {
      $sql = "SELECT t.name, t.tid FROM {taxonomy_term_field_data} AS t JOIN {node__field_location} AS l ON t.tid = l.field_location_target_id JOIN {node_field_data} AS n ON l.entity_id = n.nid WHERE t.vid = :vid AND n.status = 1 ORDER by t.name ASC";
      $result = $database->query($sql, [':vid' => 'location']);
    }
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        $options[$row['tid']] = $row['name'];
      }
    }
    $form['row']['col']['row_inner']['col_location']['location'] = [
      '#type' => 'select',
      '#options' => $options,
      '#empty_option' => $this->t('Select location'),
      '#attributes' => [
        'class' => [
          'form-select',
          'js-choice'
        ]
      ],
      '#ajax' => [
        'callback' => '::updateBedroomsType',
        'event' => 'change',
        'progress' => [
          'type' => 'none',
        ],
      ],
    ];
    if($location){
      $form['row']['col']['row_inner']['col_location']['location']['#default_value'] = $location;
    }

    // Bedrooms
    $form['row']['col']['row_inner']['col_bedrooms'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'col-md-6',
          'col-lg-4'
        ],
        'id' => 'bedrooms-wrapper'
      ],
    ];
    $form['row']['col']['row_inner']['col_bedrooms']['label'] = [
      '#type' => 'html_tag',
      '#tag' => 'label',
      '#value' => $this->t('Bedrooms'),
      '#attributes' => [
        'class' => [
          'h6',
          'fw-normal',
          'mb-0'
        ]
      ],
    ];
    $options = [];
    $database = \Drupal::database();
    if($type && !$location){
      $sql = "SELECT b.field_bedroom_value FROM {node__field_bedroom} AS b JOIN {node__field_type} AS ft ON b.entity_id = ft.entity_id JOIN {node_field_data} AS n ON b.entity_id = n.nid WHERE n.status = 1 and ft.field_type_target_id = :type ORDER by b.field_bedroom_value ASC";
      $result = $database->query($sql, [
        ':type' => $type,
      ]);
    } elseif(!$type && $location){
      $sql = "SELECT b.field_bedroom_value FROM {node__field_bedroom} AS b JOIN {node__field_location} AS fl ON b.entity_id = fl.entity_id JOIN {node_field_data} AS n ON b.entity_id = n.nid WHERE n.status = 1 and fl.field_location_target_id = :location ORDER by b.field_bedroom_value ASC";
      $result = $database->query($sql, [
        ':location' => $location,
      ]);
    } elseif($type && $location){
      $sql = "SELECT b.field_bedroom_value FROM {node__field_bedroom} AS b JOIN {node__field_type} AS ft ON b.entity_id = ft.entity_id JOIN {node__field_location} AS fl ON b.entity_id = fl.entity_id JOIN {node_field_data} AS n ON b.entity_id = n.nid WHERE n.status = 1 and fl.field_location_target_id = :location and ft.field_type_target_id = :type ORDER by b.field_bedroom_value ASC";
      $result = $database->query($sql, [
        ':location' => $location,
        ':type' => $type,
      ]);
    } else {
      $sql = "SELECT b.field_bedroom_value FROM {node__field_bedroom} AS b JOIN {node_field_data} AS n ON b.entity_id = n.nid WHERE n.status = 1 ORDER by b.field_bedroom_value ASC";
      $result = $database->query($sql);
    }
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        $name = $row['field_bedroom_value'];
        $value = $row['field_bedroom_value'];
        if($row['field_bedroom_value'] > 3){
          $name = '4+';
          $value = 4;
        }
        $options[$value] = $name;
      }
    }
    $form['row']['col']['row_inner']['col_bedrooms']['bedrooms'] = [
        '#type' => 'radios',
        '#options' => $options,
        '#default_value' => key($options),
    ];
    if($bedrooms){
      $form['row']['col']['row_inner']['col_bedrooms']['bedrooms']['#default_value'] = $bedrooms;
    }

    // Type
    $options = [];
    $database = \Drupal::database();
    if($location){
      $sql = "SELECT t.name, t.tid FROM {taxonomy_term_field_data} AS t JOIN {node__field_type} AS ft ON t.tid = ft.field_type_target_id JOIN {node__field_location} AS l ON ft.entity_id = l.entity_id JOIN {node_field_data} AS n ON ft.entity_id = n.nid WHERE t.vid = :vid AND n.status = 1 and l.field_location_target_id = :location ORDER by t.name DESC";
      $result = $database->query($sql, [
        ':vid' => 'type',
        ':location' => $location,
      ]);
    } else {
      $sql = "SELECT t.name, t.tid FROM {taxonomy_term_field_data} AS t JOIN {node__field_type} AS ft ON t.tid = ft.field_type_target_id JOIN {node_field_data} AS n ON ft.entity_id = n.nid WHERE t.vid = :vid AND n.status = 1 ORDER by t.name DESC";
      $result = $database->query($sql, [':vid' => 'type']);
    }
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        $name = str_replace('Properties to', '', $row['name']);
        $options[$row['tid']] = $this->t(ucfirst(trim($name)));
      }
    }
    $form['row']['col']['row_inner']['col_type'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'col-md-6',
          'col-lg-4'
        ],
        'id' => 'type-wrapper'
      ],
    ];
    $form['row']['col']['row_inner']['col_type']['label'] = [
      '#type' => 'html_tag',
      '#tag' => 'label',
      '#value' => $this->t('You want'),
      '#attributes' => [
        'class' => [
          'h6',
          'fw-normal',
          'mb-0'
        ]
      ],
    ];
    $form['row']['col']['row_inner']['col_type']['type'] = [
        '#type' => 'radios',
        '#options' => $options,
        '#default_value' => 200,
        '#ajax' => [
          'callback' => '::updateLocationBedrooms',
          'event' => 'change',
          'progress' => [
            'type' => 'none',
          ],
        ],
    ];
    if($type){
      $form['row']['col']['row_inner']['col_type']['type']['#default_value'] = $type;
    }

    // Submit
    $form['row']['row_submit'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'col-xl-2'
        ]
      ],
    ];
    $form['row']['row_submit']['actions'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'd-grid',
          'mt-4'
        ]
      ],
    ];
    $form['row']['row_submit']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#attributes' => [
        'class' => [
          'btn',
          'btn-md',
          'btn-dark',
          'mb-0',
          'rounded-2'
        ]
      ],
    ];

    $form['#attached']['library'][] = 'rental/rental.choices';

    return $form;
  }

  /**
   * Ajax callback for the location dropdown.
   */
  public function updateBedroomsType(array $form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand("#bedrooms-wrapper", $form['row']['col']['row_inner']['col_bedrooms']));
    $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand("#type-wrapper", $form['row']['col']['row_inner']['col_type']));

    // Set default value for bedrooms
    $options = $form['row']['col']['row_inner']['col_bedrooms']['bedrooms']['#options'];
    $selector = '#bedrooms-wrapper input[name=bedrooms][value='.key($options).']';
    $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand($selector, 'click', []));

    return $response;
  }

  /**
   * Ajax callback for the type radios.
   */
  public function updateLocationBedrooms(array $form, FormStateInterface $form_state) {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand("#location-wrapper", $form['row']['col']['row_inner']['col_location']));
    $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand("#bedrooms-wrapper", $form['row']['col']['row_inner']['col_bedrooms']));

    // Set default value for bedrooms
    $options = $form['row']['col']['row_inner']['col_bedrooms']['bedrooms']['#options'];
    $selector = '#bedrooms-wrapper input[name=bedrooms][value='.key($options).']';
    $response->addCommand(new \Drupal\Core\Ajax\InvokeCommand($selector, 'click', []));

    return $response;
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller. It must be
   * unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'rental_search_form';
  }

  /**
   * Implements form validation.
   *
   * The validateForm method is the default method called to validate input on
   * a form.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /*
    $title = $form_state->getValue('title');
    if (strlen($title) < 5) {
      // Set an error for the form element with a key of "title".
      $form_state->setErrorByName('title', $this->t('The title must be at least 5 characters long.'));
    }
    */
  }

  /**
   * Implements a form submit handler.
   *
   * The submitForm method is the default method called for any submit elements.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $location = $form_state->getValue('location');
    $bedrooms = $form_state->getValue('bedrooms');
    $type = $form_state->getValue('type');
    $requests = $location.'/'.$bedrooms.'/'.$type;
    \Drupal::logger('rental')->notice('<pre><code>search:' . print_r($requests, TRUE) . '</code></pre>' );
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($type);
    if (!$term->get('path')->isEmpty()) {
        $path = $term->get('path')->alias;
        $path_param = [];
        if($location){
          $path_param['location'] = $location;
        }
        if($bedrooms){
          $path_param['bedrooms'] = $bedrooms;
        }
        $url = Url::fromUserInput($path, ['query' => $path_param]);
        $form_state->setRedirectUrl($url);
    }
  }

}
