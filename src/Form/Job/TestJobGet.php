<?php
namespace Drupal\ml_engine\Form\Job;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class TestJobGet extends FormBase {

  public $config;

  public function getFormId() {
    return 'ml_engine_job_get';
  }

  public function __construct(){
    $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.job.get');
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
      '#value' => $this->t('Get Job'),
      '#button_type' => 'primary',
    );

    // Print prediction response.
    if ($response = $this->config->get('response')){
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
    if ($error = $this->config->get('error')){
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
  
    $this->config->delete();
    $job = $form_state->getValue('job_name');
    $this->config ->set('job_name', $job) ->save();
    
    $status = \Drupal::service('ml_engine.job')->get($job);
    if($status['success']){
      drupal_set_message('Successfully got job '.$job, "status");
      $response_job = (array) $status['response'];
      $this->config->set('response', $response_job)->save();
    }else{
      drupal_set_message($status['response']['message'], "error");
      $this->config->set('error', $status['response'])->save();    
    }
  }

}
