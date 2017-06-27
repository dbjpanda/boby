<?php

namespace Drupal\ml_engine\Form\Model;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;



class TestModelCreate extends FormBase {


  public function getFormId() {
    return 'ml_engine_model_create';
  }

  public function __construct(){
    $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.model.create');
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Model Name'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('name'),
    );

    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('description'),
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

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Create Model'),
      '#button_type' => 'primary',
    );

    // Print model response.
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

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config->delete();
    $keys = array('name', 'description', 'region');
    $para = [];
    foreach ($keys as $key) {
      ${$key} = $form_state->getValue($key);
      $para[$key] = ${$key};
      $this->config->set($key,${$key})->save();
    }

    $status = \Drupal::service('ml_engine.model')->ModelCreate($para);
    $emotion = ($status['success'] ? "status" : "error");
    
    drupal_set_message($status['response']['message'], $emotion);
    $this->config->set('response', $status['response'])->save(); 
  }

}
