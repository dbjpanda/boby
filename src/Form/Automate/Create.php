<?php

namespace Drupal\ml_engine\Form\Automate;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;



class Create extends FormBase {


  public function getFormId() {
    return 'ml_engine_automate_create';
  }

  public function __construct(){
    $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.automate.create');
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['package_uri'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Package URI'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('package_uri'),
    );
    $form['module'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Module'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('module'),
    );
    $form['train_data_uri'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Train Data URI'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('train_data_uri'),
    );
    $form['test_data_uri'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Test Data URI'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('test_data_uri'),
    );
    $form['verbosity'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Verbosity'),
      '#default_value' => 'DEBUG',
      '#attributes' => array('readonly' => 'readonly'),
    );

    $form['advanced'] = array(
        '#type' => 'details',
        '#title' => t('Advanced'),
    );

    $form['advanced']['job'] = array(
    '#type' => 'details',
    '#title' => t('Job'),
    );

    $form['advanced']['deployment'] = array(
    '#type' => 'details',
    '#title' => t('Deployment'),
    );

    $form['advanced']['job'] = array_merge($form['advanced']['job'], $this->job_fields());
    $form['advanced']['deployment'] = array_merge($form['advanced']['deployment'], $this->deployment_fields());

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Run'),
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

    $keys = array('job','package_uri','module','train_data_uri',
    'test_data_uri', 'train_steps', 'verbosity','job_dir','region','scale_tier','arguments');

    $jobPara = [];
    
    foreach ($keys as $key) {
      ${$key} = $form_state->getValue($key);
      $jobPara[$key] = ${$key};
      $this->config->set($key,${$key})->save();
    }

    $status = \Drupal::service('ml_engine.job')->JobCreate($jobPara);

    if($status['success']){
      drupal_set_message('Successfully created job '.$job, "status");
      $response_job = (array) $status['response'];
      $this->config->set('response', $response_job)->save();
    }else{
      drupal_set_message($status['response']['message'], "error");
      $this->config->set('error', $status['response'])->save();    
    }

  }

  private function job_fields(){

    $job['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('name'),
    );

    $job['train_steps'] = array(
      '#type' => 'number',
      '#title' => $this->t('Train Steps'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('train_steps'),
    );
    $job['job_dir'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Output Directory'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('job_dir'),
    );
    $job['region'] = array(
      '#type' => 'select',
      '#options' => array(
        'us-central1' => t('us-central1'),
        'us-east1' => t('us-east1'), 
        'europe-west1' => t('europe-west1'), 
        'asia-east1' => t('asia-east1')
      ),
      '#title' => $this->t('Region'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('region'),
    );

    $job['scale_tier'] = array(
      '#type' => 'select',
      '#options' => array(
        'BASIC' => t('BASIC'),
        'STANDARD_1' => t('STANDARD_1'),
        'PREMIUM_1' => t('PREMIUM_1'),
        'BASIC_GPU' => t('BASIC_GPU')
      ),
      '#title' => $this->t('Scale Tier'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('scale_tier'),
    );

    return $job;

  }


  private function deployment_fields(){

    $form['model_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Model Name'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('model_name'),
    );

    $form['version_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Version Name'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('version_name'),
    );
    $form['deployment_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Deployment URL'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('deployment_url'),
    );
    $form['region'] = array(
      '#type' => 'select',
      '#options' => array(
        'us-central1' => t('us-central1'),
        'us-east1' => t('us-east1'), 
        'europe-west1' => t('europe-west1'), 
        'asia-east1' => t('asia-east1')
      ),
      '#title' => $this->t('Region'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('region'),
    );

    return $form;

  }

}
