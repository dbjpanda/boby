<?php
namespace Drupal\ml_engine\Form\Version;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class TestVersionGet extends FormBase {

  public $config;

  public function getFormId() {
    return 'ml_engine_version_get';
  }

  public function __construct(){
    $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.version.get');
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

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Get Version'),
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
    $required_keys = ['name', 'model_name'];
    
    $para = [];
    foreach($required_keys as $key){
      ${$key} = $form_state->getValue($key); 
      $this->config ->set($key, ${$key}) ->save();
    }
    
    $status = \Drupal::service('ml_engine.version')->get($model_name, $name);
    $emotion = ($status['success'] ? "status" : "error");
    
    drupal_set_message($status['response']['message'], $emotion);
    $this->config->set('response', $status['response'])->save();
  }

}
