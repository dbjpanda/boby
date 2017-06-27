<?php

namespace Drupal\ml_engine\Form\Automate;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;



class Create extends FormBase {

  private $service;
  private $config;

  public function getFormId() {
    return 'ml_engine_automate_create';
  }

  public function __construct(){
    $this->service = \Drupal::service('ml_engine.automate');
    $this->config = $this->service->config;
    //$this->config = $this->service->config;
  }

  private function getValue($key){
    return $this->service->getValue($key);
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['job_package_uri'] = array(
      '#type' => 'file',
      '#title' => $this->t('Package'),
      '#description' => t('Upload tensorflow file as python package. We except .gz file')
      //'#default_value' => $this->getValue('job_package_uri'),
    );
    $form['job_module'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Module'),
      '#required' => TRUE,
      '#default_value' => $this->getValue('job_module'),
    );
    $form['job_train_data_uri'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Train Data URI'),
      '#required' => TRUE,
      '#default_value' => $this->getValue('job_train_data_uri'),
    );
    $form['job_test_data_uri'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Test Data URI'),
      '#required' => TRUE,
      '#default_value' => $this->getValue('job_test_data_uri'),
    );
    $form['job_verbosity'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Verbosity'),
      '#default_value' => $this->getValue('job_verbosity'),
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

    $form['advanced']['job'] = array_merge($form['advanced']['job'], $this->job_fields());

    $form['advanced']['model'] = array(
    '#type' => 'details',
    '#title' => t('model'),
    );

    $form['advanced']['model'] = array_merge($form['advanced']['model'], $this->model_fields());

    $form['advanced']['version'] = array(
    '#type' => 'details',
    '#title' => t('Version'),
    );

    $form['advanced']['version'] = array_merge($form['advanced']['version'], $this->version_fields());

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

    $job_keys=array('package_uri','module','train_data_uri','test_data_uri','verbosity','name','train_steps','output_dir','region','scale_tier');
    $model_keys=array('name', 'description', 'region');
    $version_keys=array('name', 'default','description','deployment_uri');

    $jobPara = [];
    
    foreach ($job_keys as $key) {
      $job_key = "job_".$key; 
      $jobPara[$key] = ${$job_key} = $form_state->getValue($job_key);
      $this->config->set($job_key,${$job_key})->save();
    }

    foreach ($model_keys as $key) {
      $model_key = "model_".$key; 
      $modelPara[$key] = ${$model_key} = $form_state->getValue($model_key);
      $this->config->set($model_key,${$model_key})->save();
    }

    foreach ($version_keys as $key) {
      $version_key = "version_".$key; 
      $versionPara[$key] = ${$version_key} = $form_state->getValue($version_key);
      $this->config->set($version_key,${$version_key})->save();
    }

    if ($trainer_file = file_save_upload('job_package_uri',array('file_validate_extensions' => array('gz')), FALSE, 0)) {
      $trainer_uri = $trainer_file->getFileUri();
      $trainer_path = drupal_realpath($trainer_uri);
    }else{
      drupal_set_message('Select trainer file of format .gz');
      return;
    }

    $trainer_upload_status = \Drupal::service('ml_engine.storage')->upload_from_file_path($trainer_path, 'trainer.tar.gz');
    if(!$trainer_upload_status['success']) {
      drupal_set_message($trainer_upload_status['response']['message']);
      return;
    }

    $jobPara['package_uri'] = $trainer_upload_status['file_path'];

    $status = \Drupal::service('ml_engine.automate')->automate($jobPara, $modelPara, $versionPara);

  }

  private function job_fields(){

    $job['job_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $this->getValue('job_name'),
    );

    $job['job_train_steps'] = array(
      '#type' => 'number',
      '#title' => $this->t('Train Steps'),
      '#default_value' => $this->getValue('job_train_steps'),
    );
    $job['job_output_dir'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Output Directory'),
      '#default_value' => $this->getValue('job_output_dir'),
    );
    $job['job_region'] = array(
      '#type' => 'select',
      '#options' => array(
        'us-central1' => t('us-central1'),
        'us-east1' => t('us-east1'), 
        'europe-west1' => t('europe-west1'), 
        'asia-east1' => t('asia-east1')
      ),
      '#title' => $this->t('Region'),
      '#default_value' => $this->getValue('job_region'),
    );

    $job['job_scale_tier'] = array(
      '#type' => 'select',
      '#options' => array(
        'BASIC' => t('BASIC'),
        'STANDARD_1' => t('STANDARD_1'),
        'PREMIUM_1' => t('PREMIUM_1'),
        'BASIC_GPU' => t('BASIC_GPU')
      ),
      '#title' => $this->t('Scale Tier'),
      '#default_value' => $this->getValue('job_scale_tier'),
    );

    return $job;

  }

  private function model_fields(){

    $form['model_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Model Name'),
      '#default_value' => $this->getValue('model_name'),
    );

    $form['model_description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Model Description'),
      '#default_value' => $this->getValue('model_description'),
    );

    $form['model_region'] = array(
      '#type' => 'select',
      '#options' => array(
        'us-central1' => t('us-central1'),
        'us-east1' => t('us-east1'),
      ),
      '#title' => $this->t('Region'),
      '#default_value' => $this->getValue('model_region'),
    );
    return $form;
  }

  private function version_fields(){
    $form['version_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $this->getValue('version_name'),
    );

    $form['version_default'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Make it default version?'),
      '#default_value' => $this->getValue('version_default'),
    );

    $form['version_description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $this->getValue('version_description'),
    );

    $form['version_deployment_uri'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Deployment URL'),
      '#default_value' => $this->getValue('version_deployment_uri'),
    );
    return $form;
  }



}
