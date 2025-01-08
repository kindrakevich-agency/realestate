<?php

namespace Drupal\rental\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

/**
 * Implements the RentalPhone form controller.
 *
 * Search rentals form.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class RentalPhone extends FormBase {

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

    $form['row'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'row',
          'mb-4',
        ]
      ],
    ];
    $form['row']['col'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'col-12',
        ]
      ],
    ];
    $form['row']['col']['card'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'bg-light',
          'position-relative',
          'overflow-hidden',
          'rounded-3',
          'p-4',
          'p-md-0',
        ]
      ],
    ];
    $form['row']['col']['card']['image'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'position-absolute',
          'bottom-0',
          'end-0',
          'me-n5',
          'd-none',
          'd-md-block',
        ]
      ],
    ];
    $form['row']['col']['card']['figure'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'position-absolute',
          'top-0',
          'start-0',
          'd-none',
          'd-sm-block',
          'z-index-1',
        ]
      ],
    ];

    $form['row']['col']['card']['row_inner'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'row',
          'position-relative',
          'pb-6',
        ]
      ],
    ];
    $form['row']['col']['card']['row_inner']['col_inner'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'col-md-7',
          'text-center',
          'mx-auto',
          'py-md-5',
        ]
      ],
    ];

    $form['row']['col']['card']['row_inner']['col_inner']['title'] = [
      '#markup' => Markup::create('<h3 class="mb-4">'.t('Receive property recommendations!').'</h3>'),
    ];
    $form['row']['col']['card']['row_inner']['col_inner']['text'] = [
      '#markup' => Markup::create('<p class="mb-4">'.t('Everything you need to get started on your Spanish property journey.').'</p>'),
    ];

    $form['row']['col']['card']['row_inner']['col_inner']['error'] = [
      '#markup' => Markup::create('<p class="form-error text-danger"></p>'),
    ];

    $form['row']['col']['card']['row_inner']['col_inner']['form'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'bg-body',
          'd-flex',
          'rounded-2',
          'p-2',
          'position-relative',
          'rental-phone-inner',
        ],
        'id' => 'form-wrapper'
      ],
    ];

    $form['row']['col']['card']['row_inner']['col_inner']['form']['phone'] = [
        '#type'           => 'textfield',
        '#required'       => true,
        '#placeholder'    => t('Enter your phone'),
        '#attributes' => [
          'class' => [
            'border-0',
            'me-1',
            'mt-0'
          ],
          'id' => 'rental-phone-value'
        ],
    ];

    $form['row']['col']['card']['row_inner']['col_inner']['form']['actions'] = [
        '#type' => 'button',
        '#value' => t('Send'),
        '#attributes' => [
          'class' => [
            'btn',
            'btn-dark',
            'flex-shrink-0',
            'mb-0'
          ]
        ],
        '#ajax' => [
          'callback' => '::sendPhone',
          'event' => 'click',
        ],
    ];

    return $form;
  }

  /**
  *
  */
  public function sendPhone(array $form, FormStateInterface $form_state) {
     $response = new \Drupal\Core\Ajax\AjaxResponse();
     $phone = $form_state->getValue('phone');
     if (strlen($phone) < 8) {
       $html = '<div class="alert alert-success" id="phone-error" role="alert">';
        $html.= 'Phone must be at least 8 characters long.';
       $html.= '</div>';
       $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand(".form-error", $html));
     } else {
       $html = '<div class="alert alert-success" role="alert">';
        $html.= 'Thank you for your message. Your time and attention are truly appreciated.';
       $html.= '</div>';
       $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand("#form-wrapper", $html));
       $response->addCommand(new \Drupal\Core\Ajax\RemoveCommand("#phone-error"));

       $message = [];
       $current_path = \Drupal\Core\Url::fromRoute('<current>', [], ['absolute' => 'true'])->toString();
       $message[] = 'Link: '.$current_path;
       $message[] = 'From where: phone form';
       $message[] = 'Phone: '.$phone;
       $message = implode(PHP_EOL,$message);

       $mailManager = \Drupal::service('plugin.manager.mail');
       $module = 'rental';
       $key = 'general_mail';
       $to = \Drupal::config('system.site')->get('mail');
       $params['message'] = $message;
       $params['subject'] = 'From where: phone form';
       $langcode = \Drupal::currentUser()->getPreferredLangcode();
       $send = true;
       $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

     }
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
    return 'rental_phone_form';
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

  }

}
