<?php

/**
 * @file
 * Contains Drupal\slack\Form\SendTestMessageForm.
 */

namespace Drupal\ml_engine\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


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

    $config = \Drupal::configFactory()->getEditable('ml_engine.settings');
    $form['url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Prediction URL'),
      '#required' => TRUE,
      '#default_value' => $config->get('test_url'),
    );
    $form['json'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('JSON'),
      '#required' => TRUE,
      '#default_value' => $config->get('test_json'),
      '#rows' => 15    
    );
    $form['credential'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Credential'),
      '#required' => TRUE,
      '#default_value' => $config->get('test_credential'),    
      '#rows' => 15
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Get Prediction'),
      '#button_type' => 'primary',
    );

    // Print prediction response.
    if ($response = $config->get('test_response')){
        $form['response'] = array(
          '#type' => 'textarea',
          '#title' => $this->t('Response'),
          '#attributes' => array('readonly' => 'readonly'),
          '#default_value' => json_encode($response, JSON_PRETTY_PRINT),    
          '#rows' => 15
        );      
    }

    // Print prediction error.
    if ($error = $config->get('test_error')){
        $form['error'] = array(
          '#type' => 'textarea',
          '#title' => $this->t('Error'),
          '#attributes' => array('readonly' => 'readonly'),
          '#default_value' => $error,    
          '#rows' => 15
        );      
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  
    $config = \Drupal::configFactory()->getEditable('ml_engine.settings');
  
    // Set config variables. 
    $credential = $form_state->getValue('credential');
    $data = $form_state->getValue('json');
    $url = $form_state->getValue('url');

    $config
      ->set('test_url',$url)->set('test_json', $data)
      ->set('test_credential',$credential)
      ->save();

    // Set parameters for prediction request.
    $credential_json = json_decode($credential, true);
    $data_array = ["instances" => json_decode($data,true)];

    // Creting client and services.
    $client = new \Google_Client();
    $client->setAuthConfig($credential_json);
    $client->addScope(\Google_Service_CloudMachineLearningEngine::CLOUD_PLATFORM);
    $service = new \Google_Service_CloudMachineLearningEngine($client);

    // Send prediction request.
    $response = $service->projects->predict($url, new \Google_Service_CloudMachineLearningEngine_GoogleCloudMlV1PredictRequest($data_array));

    $error = $response->__get('error');
    $predictions = $response->__get('predictions');
    $config->set('test_response', $predictions)->save(); 
    $config->set('test_error', $error)->save(); 

    print "<pre>";
    print_r($response->__get('error'));
    print "</pre>";
    //die();   
  }

}
