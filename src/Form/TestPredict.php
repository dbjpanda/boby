<?php

namespace Drupal\ml_engine\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class TestPredict extends FormBase {

  public function __construct(){
      $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.prediction');
  }

  public function getFormId() {
    return 'ml_engine_get_test_prediction';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['model_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Model Name'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('model_name'),
    );
    $form['data'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('JSON'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('data'),
      '#rows' => 15    
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Get Prediction'),
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
          '#weight' => 100,
        );      
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {  
    $this->config->delete();
    $keys = ["data", "model_name"];
    foreach ($keys as $key){
      ${$key} = $form_state->getValue($key); 
      $this->config->set($key, ${$key})->save();
    }
    $data_array = ["instances" => json_decode($data,true)];
    $response = \Drupal::service('ml_engine.predict')->predict($model_name,$data_array);

    if($response['success']){
      $this->config->set('response',(array) $response['response'])->save();
      drupal_set_message('Succesfully Got Prediction', 'status');
      return;
    }else{
      $this->config->set('error', (array) $response['response'])->save();
      drupal_set_message($response['response']['message'], 'error');
      return;
    }

  }

}
