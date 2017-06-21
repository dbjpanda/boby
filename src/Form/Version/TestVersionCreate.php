<?php

namespace Drupal\ml_engine\Form\Version;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;



class TestVersionCreate extends FormBase {


  public function getFormId() {
    return 'ml_engine_version_create';
  }

  public function __construct(){
    $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.version.create');
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Version Name'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('name'),
    );

    $form['model_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Model Name'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('model_name'),
    );

    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('description'),
    );

    $form['deployment_uri'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Deployment URI'),
      '#description' => $this->t('Enter the Google Cloud Storage output path you specified in your training job.'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('deployment_uri'),
    );


    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Create Version'),
      '#button_type' => 'primary',
    );

    // Print prediction response.
    if ($response = $this->config->get('response')){
        $form['response'] = array(
          '#type' => 'textarea',
          '#title' => $this->t('Response'),
          '#attributes' => array('readonly' => 'readonly'),
          '#default_value' => json_encode($response,JSON_PRETTY_PRINT),    
          '#rows' => 15,
          '#weight' => 100
        );      
    }

    // Print prediction error.
    if ($error = $this->config->get('error')){
        $form['error'] = array(
          '#type' => 'textarea',
          '#title' => $this->t('Error'),
          '#attributes' => array('readonly' => 'readonly'),
          '#default_value' => json_encode($error,JSON_PRETTY_PRINT),    
          '#rows' => 15,
          '#weight' => 100
        );      
    }

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config->delete();
    $keys = array('name', 'model_name', 'description', 'deployment_uri');
    $para = [];
    foreach ($keys as $key) {
      ${$key} = $form_state->getValue($key);
      $para[$key] = ${$key};
      $this->config->set($key,${$key})->save();
    }

    $status = \Drupal::service('ml_engine.version')->VersionCreate($para);

    if($status['success']){
      drupal_set_message('Successfully created model '.$name, "status");
      $response_job = (array) $status['response'];
      $this->config->set('response', $response_job)->save();
    }else{
      drupal_set_message($status['response']['message'], "error");
      $this->config->set('error', $status['response'])->save();    
    }

  }

}
