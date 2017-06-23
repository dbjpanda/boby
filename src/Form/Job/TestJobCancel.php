<?php

namespace Drupal\ml_engine\Form\Job;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ml_engine\Controller\JobController;


class TestJobCancel extends FormBase {

  public $config;

  public function __construct(){
    $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.job.cancel');
  }

  public function getFormId() {
    return 'ml_engine_job_cancel';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['job_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Job Name'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('job_name'),
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Cancel Job'),
      '#button_type' => 'primary',
    );

    // Print prediction response.
    if ($response = $this->config->get('response')){
        $form['response'] = array(
          '#type' => 'textarea',
          '#title' => $this->t('Response'),
          '#attributes' => array('readonly' => 'readonly'),
          '#default_value' => $response,    
          '#rows' => 15,
          '#weight' => 100,
          
        );      
    }

    // Print prediction error.
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
    $job = $form_state->getValue('job_name');
    $this->config ->set('job_name', $job) ->save();

    $status = \Drupal::service('ml_engine.job')->cancel($job);


    if($status['success']){
      $this->config->set('response','Job Cancelled')->save();
      drupal_set_message('Job Cancelled Successfully', 'status');
      return;
    }else{
      $this->config->set('error', $status['response'])->save();
      drupal_set_message($status['response']['message'], 'error');
      return;
    }
  }
    
}
