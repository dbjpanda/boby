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
class TestJobCreate extends FormBase {

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

    $config = \Drupal::configFactory()->getEditable('ml_engine.test.job.create');
    $form['job_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Job Name'),
      '#required' => TRUE,
      '#default_value' => $config->get('job_name'),
    );
    $form['package_uri'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Package URI'),
      '#required' => TRUE,
      '#default_value' => $config->get('package_uri'),
    );
    $form['module'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Module'),
      '#required' => TRUE,
      '#default_value' => $config->get('module'),
    );
    $form['train_data_uri'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Train Data URI'),
      '#required' => TRUE,
      '#default_value' => $config->get('train_data_uri'),
    );
    $form['test_data_uri'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Test Data URI'),
      '#required' => TRUE,
      '#default_value' => $config->get('test_data_uri'),
    );
    $form['train_steps'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Train Steps'),
      '#required' => TRUE,
      '#default_value' => $config->get('train_steps'),
    );
    $form['verbosity'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Verbosity'),
      '#required' => TRUE,
      '#default_value' => $config->get('verbosity'),
    );
    $form['job_dir'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Output Directory'),
      '#required' => TRUE,
      '#default_value' => $config->get('job_dir'),
    );
    $form['region'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Region'),
      '#required' => TRUE,
      '#default_value' => $config->get('region'),
    );
    $form['scale_tier'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Scale Tier'),
      '#required' => TRUE,
      '#default_value' => $config->get('scale_tier'),
    );
    $form['arguments'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Arguments'),
      '#required' => TRUE,
      '#default_value' => $config->get('arguments'),
      '#rows' => 15,
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Create Job'),
      '#button_type' => 'primary',
    );

    // Print prediction response.
    if ($state = $config->get('state')){
        $form['response'] = array(
          '#type' => 'textarea',
          '#title' => $this->t('Response'),
          '#attributes' => array('readonly' => 'readonly'),
          '#default_value' => $state,    
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
  
    $config = \Drupal::configFactory()->getEditable('ml_engine.test.job.create');
    $config->delete();

    // Set config variables. 
    $project_name = \Drupal::configFactory()->getEditable('ml_engine.test')->get('project');
    $job_name = $form_state->getValue('job_name');
    
    $package_uri = $form_state->getValue('package_uri');
    $module = $form_state->getValue('module');

    $job_dir = $form_state->getValue('job_dir');
    $region = $form_state->getValue('region');
    $scale_tier = $form_state->getValue('scale_tier');
    $credential = \Drupal::configFactory()->getEditable('ml_engine.test')->get('credential');

    $arguments = $form_state->getValue('arguments');
    $train_data_uri = $form_state->getValue('train_data_uri');
    $test_data_uri = $form_state->getValue('test_data_uri');
    $train_steps = $form_state->getValue('train_steps');
    $verbosity = $form_state->getValue('verbosity');

    $new_arguments = [
                        '--train--files', 
                        $train_data_uri,
                        '--eval-files',
                        $test_data_uri,
                        '--train-steps',
                        $train_steps,
                        'verbosity',
                        $verbosity
                      ];

    $package_array = array($package_uri);

    $config
      ->set('job_name', $job_name)
      ->set('package_uri', $package_uri)
      ->set('module', $module)
      ->set('train_data_uri',$train_data_uri)
      ->set('test_data_uri',$test_data_uri)
      ->set('train_steps',$train_steps)
      ->set('verbosity',$verbosity)
      ->set('job_dir', $job_dir)
      ->set('region', $region)
      ->set('scale_tier', $scale_tier)
      ->set('arguments', $arguments)
      ->set('credential',$credential)
      ->save();


    // Set parameters for prediction request.
    $credential_json = json_decode($credential, true);

    // Creting client and services.
    $client = new \Google_Client();
    $client->setAuthConfig($credential_json);
    $client->addScope(\Google_Service_CloudMachineLearningEngine::CLOUD_PLATFORM);
    $service = new \Google_Service_CloudMachineLearningEngine($client);
    
    $input = new \Google_Service_CloudMachineLearningEngine_GoogleCloudMlV1TrainingInput();
    $input->setScaleTier($scale_tier);
    $input->setPackageUris($package_array);
    $input->setPythonModule($module);
    $input->setRegion($region);
    $input->setJobDir($job_dir);
    $input->setArgs($new_arguments);

    $job = new \Google_Service_CloudMachineLearningEngine_GoogleCloudMlV1Job();
    $job->setJobId($job_name);
    $job->setTrainingInput($input);

    // Send prediction request.
    try{
      $response = $service->projects_jobs->create($project_name,$job);
    } catch (\Google_Service_Exception $ex){
      $error = json_decode($ex->getMessage(), true)['error'];
      $message = $error['message'];
      $code = $error['code'];
      $config->set('error',$message)->save();
      drupal_set_message($message,'error');
      return;
    }
    $error = $response->__get('error');
    $state = $response->getState();

    print "<pre>";
    print $state;
    print "</pre>";


    $config->set('state', $state)->save(); 
    $config->set('error', $error)->save(); 

  }

}
