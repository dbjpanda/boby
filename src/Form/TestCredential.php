<?php

namespace Drupal\ml_engine\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class TestCredential extends FormBase {

  public $config;

  public function __construct(){
      $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.project');
  }

  public function getFormId() {
    return 'ml_engine_credential';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Project name'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('name')
    );

    $form['credential'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Credential'),
      '#description' => t('Please paste the service account credential json of your Google Cloud Project.'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('credential'),
      '#rows' => 25,
    );

    $form['bucket'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Bucket name'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('bucket')
    );

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Verify and Save'),
      '#button_type' => 'primary',
    );

    if ($response = $this->config->get('response')){
        $form['response'] = array(
          '#type' => 'textarea',
          '#title' => $this->t('Response'),
          '#attributes' => array('readonly' => 'readonly'),
          '#default_value' => 'Succesfully Verified',
          '#weight' => 100,
        );      
    }

    // Print credential verification error.
    if ($error = $this->config->get('error')){
        $form['error'] = array(
          '#type' => 'textarea',
          '#title' => $this->t('Error'),
          '#attributes' => array('readonly' => 'readonly'),
          '#default_value' => json_encode($error, JSON_PRETTY_PRINT),    
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
    $this->config->delete();

    $form_keys = ['credential', 'name', 'bucket'];
    $para = [];
    foreach ($form_keys as $key){
      $para[$key]=${$key} = $form_state->getValue($key);
      $this->config->set($key,${$key})->save();
    }
    
    $credential = json_decode($credential,true);
    $para['credential'] = $credential;
    $status = \Drupal::service('ml_engine.project')->verify_credential($para);

    if($status['success']){
      drupal_set_message('Successfully verified project '.$name, "status");
      $response_job = (array) $status['response'];
      $this->config->set('response', $response_job)->save();
    }else{
      drupal_set_message($status['response']['message'], "error");
      $this->config->set('error', $status['response'])->save();    
    }

  }

}
