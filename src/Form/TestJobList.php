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
class TestJobList extends FormBase {

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

    $config = \Drupal::configFactory()->getEditable('ml_engine.test.job.list');
    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Project Name'),
      '#required' => TRUE,
      '#default_value' => $config->get('name'),
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Get Jobs'),
      '#button_type' => 'primary',
    );

    // Print Job List response.
    if ($response = $config->get('jobs')){
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
  
    $config = \Drupal::configFactory()->getEditable('ml_engine.test.job.list');
    $config->delete();
    $credential = \Drupal::configFactory()->getEditable('ml_engine.test')->get('credential');

    // Set config variables. 
    $name = $form_state->getValue('name');    
    $config ->set('name', $name) ->save();


    // Set parameters for prediction request.
    $credential_json = json_decode($credential, true);

    // Creting client and services.
    $client = new \Google_Client();
    $client->setAuthConfig($credential_json);
    $client->addScope(\Google_Service_CloudMachineLearningEngine::CLOUD_PLATFORM);
    $service = new \Google_Service_CloudMachineLearningEngine($client);

    // Get Job details.
    $response = $service->projects_jobs->listProjectsJobs($name);
    $jobs = $response->__get('jobs');
    $jobs_array = [];
    
    for ($i=0; $i<count($jobs); $i++){
        $jobs_array[$i] = (array) $jobs[$i];
    }
    
    $config->set('jobs',$jobs_array)->save();
    
    print "<pre>";
    print_r($jobs_array);
    print "</pre>";

  }

}
