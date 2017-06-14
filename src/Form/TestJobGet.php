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
class TestJobGet extends FormBase {

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

    $config = \Drupal::configFactory()->getEditable('ml_engine.test.job.get');
    $form['job_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Job Name'),
      '#required' => TRUE,
      '#default_value' => $config->get('job_name'),
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Get Job'),
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
          '#weight' => 100
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
          '#weight' => 100
        );      
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  
    $config = \Drupal::configFactory()->getEditable('ml_engine.test.job.get');
    $config->delete();
    $credential = \Drupal::configFactory()->getEditable('ml_engine.test')->get('credential');

    // Set config variables. 
    $project_name = \Drupal::configFactory()->getEditable('ml_engine.test')->get('project');
    $job_name = $form_state->getValue('job_name');    
    $config ->set('job_name', $job_name) ->save();
    $name = $project_name."/jobs/".$job_name;
    
    // Set parameters for prediction request.
    $credential_json = json_decode($credential, true);

    // Creting client and services.
    $client = new \Google_Client();
    $client->setAuthConfig($credential_json);
    $client->addScope(\Google_Service_CloudMachineLearningEngine::CLOUD_PLATFORM);
    $service = new \Google_Service_CloudMachineLearningEngine($client);

    // Get Job details.
    try{
    $response = $service->projects_jobs->get($name);
    }catch (\Google_Service_Exception $ex){
      $error = json_decode($ex->getMessage(), true)['error'];
      $message = $error['message'];
      $code = $error['code'];
      $config->set('error',$message)->save();
      drupal_set_message($message,'error');
      return;
    }
    $job = (array) $response;

    $config->set('response', $job)->save();


  }

}
