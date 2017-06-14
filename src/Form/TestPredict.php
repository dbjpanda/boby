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
class TestPredict extends FormBase {

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

    $config = \Drupal::configFactory()->getEditable('ml_engine.test.prediction');
    $form['model_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Model Name'),
      '#required' => TRUE,
      '#default_value' => $config->get('model_name'),
    );
    $form['json'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('JSON'),
      '#required' => TRUE,
      '#default_value' => $config->get('json'),
      '#rows' => 15    
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Get Prediction'),
      '#button_type' => 'primary',
    );

    // Print prediction response.
    if ($response = $config->get('response')){
        $form['response'] = array(
          '#type' => 'textarea',
          '#title' => $this->t('Response'),
          '#attributes' => array('readonly' => 'readonly'),
          '#default_value' => json_encode($response, JSON_PRETTY_PRINT),    
          '#rows' => 15,
          '#weight' => 100,
        );      
    }

    // Print prediction error.
    if ($error = $config->get('error')){
        $form['error'] = array(
          '#type' => 'textarea',
          '#title' => $this->t('Error'),
          '#attributes' => array('readonly' => 'readonly'),
          '#default_value' => $error,    
          '#rows' => 15,
          '#weight' => 100,
        );      
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  
    $config = \Drupal::configFactory()->getEditable('ml_engine.test.prediction');
    $config->delete();
  
    // Set config variables. 
    $credential = \Drupal::configFactory()->getEditable('ml_engine.test')->get('credential');
    $project = \Drupal::configFactory()->getEditable('ml_engine.test')->get('project');
    $data = $form_state->getValue('json');
    $model_name = $form_state->getValue('model_name');
    $url = $project."/models/".$model_name;

    $config
      ->set('model_name',$model_name)->set('json', $data)
      ->set('credential',$credential)
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
    try{
      $response = $service->projects->predict($url, new \Google_Service_CloudMachineLearningEngine_GoogleCloudMlV1PredictRequest($data_array));
    } catch (\Google_Service_Exception $ex){
      $error = json_decode($ex->getMessage(), true)['error'];
      $message = $error['message'];
      $code = $error['code'];
      $config->set('error',$message)->save();
      drupal_set_message($message,'error');
      return;
    }
    if($error = $response->__get('error')){
      $config->set('error', $error)->save();
      drupal_set_message($error,'error');
      return;
    }
    $predictions = $response->__get('predictions');
    $config->set('response', $predictions)->save();  

  }

}