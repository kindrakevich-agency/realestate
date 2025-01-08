<?php

namespace Drupal\rental\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

/**
 * Implements the RentalBooking form controller.
 *
 * Booking rentals form.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class RentalBooking extends FormBase {

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
    $form['#attributes']['id'] = 'booking-form-wrapper';

    $form['error'] = [
      '#markup' => Markup::create('<p class="form-error text-danger"></p>'),
    ];

    $form['phone'] = [
        '#type'           => 'textfield',
        '#required'       => true,
        '#placeholder'    => $this->t('Enter your phone'),
        '#attributes' => [
          'class' => [
            'me-1',
            'mt-0',
            'mb-3'
          ],
          'id' => 'rental-phone-value'
        ],
    ];

    $form['actions_row'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'd-grid',
        ],
      ],
    ];

    $form['actions_row']['actions'] = [
        '#type' => 'button',
        '#value' =>  $this->t('Book now'),
        '#attributes' => [
          'class' => [
            'btn',
            'btn-md',
            'btn-primary',
            'rounded-1',
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
        $html = '<div class="alert alert-success mt-3 mb-0" role="alert">';
          $html.= 'Thank you for your message. Your time and attention are truly appreciated.';
        $html.= '</div>';
        $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand("#booking-form-wrapper", $html));
        $response->addCommand(new \Drupal\Core\Ajax\RemoveCommand("#phone-error"));

        $message = [];
        $current_path = $node->toUrl()->setAbsolute()->toString();
        $message[] = 'Link: '.$current_path;
        $message[] = 'From where: booking';
        $message[] = 'Phone: '.$phone;
        $message = implode(PHP_EOL,$message);

        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'rental';
        $key = 'general_mail';
        $to = \Drupal::config('system.site')->get('mail');
        $params['message'] = $message;
        $params['subject'] = 'From where: booking';
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
    return 'rental_booking_form';
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
