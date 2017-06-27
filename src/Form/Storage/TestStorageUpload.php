<?php
namespace Drupal\ml_engine\Form\Storage;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class TestStorageUpload extends FormBase {

  public $config;

  public function getFormId() {
    return 'ml_engine_storage_upload';
  }

  public function __construct(){
    $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.storage.upload');
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['upload1'] = array(
      '#type' => 'managed_file',
      '#title' => t('Image'),
      '#upload_location' => 'public://ml_engine_images/',
      '#upload_validators'  => array('file_validate_extensions' => array('png')),
      //'#required' => TRUE,
      '#description' => t('Upload a file'),
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Upload'),
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

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    $path = $this->get_file_absolute_path($form, 'upload1');
    $status = \Drupal::service('ml_engine.storage')->upload_from_file_path($path, 'image.png');

    $emotion = ($status['success'] ? "status" : "error");
    
    drupal_set_message($status['response']['message'], $emotion);
    $this->config->set('response', $status['response'])->save();
  }

  public function get_file_absolute_path($form, $fid){
    
    $value = $form[$fid]['#value']['fids'][0];
    $file = \Drupal::entityManager()->getStorage('file')->load($value);  
    $uri = $file->getFileUri();    
    $path = drupal_realpath($uri);
    return $path;
  }

}
