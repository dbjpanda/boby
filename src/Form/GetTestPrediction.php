<?php

/**
 * @file
 * Contains Drupal\slack\Form\SendTestMessageForm.
 */

namespace Drupal\ml_engine\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\slack;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class SendTestMessageForm.
 *
 * @package Drupal\slack\Form
 */
class GetTestPrediction extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ml_engine_get_test_prediction';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ml_engine.settings');
    $form['url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Prediction URL'),
      '#default_value' => $config->get('test_url'),
    );
    $form['json'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('JSON'),
      '#required' => TRUE,
      '#default_value' => $config->get('test_json'),    
    );
    $form['credential'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Credential'),
      '#required' => TRUE,
      '#default_value' => $config->get('test_credential'),    
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Get Prediction'),
      '#button_type' => 'primary',
    );
/**
    if (empty($config->get('credential'))) {
      $url = new RedirectResponse(ml_engine.settings);
      $url->send();

      return FALSE;
    }
    else {
      return $form;
    }
**/
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::configFactory()->getEditable('ml_engine.settings');
    
    $credential = $form_state->getValue('credential');
    $json = $form_state->getValue('json');
    $url = $form_state->getValue('url');

    $config->set('test_url',$url)->set('test_json', $json)->set('test_credential',$credential)
      ->save();

 
    
    
    //\Drupal::service('slack.slack_service')->sendMessage($message, $channel, $username);
  }

}
