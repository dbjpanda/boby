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

    \Drupal::service('ml_engine.storage')->get_objects('drupal-ml-repo');
    die();

    $form['upload1'] = array(
      '#type' => 'file',
      '#title' => t('Image'),
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
    
   if ($file = file_save_upload('upload1',array('file_validate_extensions' => array('csv gz')), FALSE, 0)) {
      $uri = $file->getFileUri();
      $path = drupal_realpath($uri);
    }else{
      drupal_set_message('Select file of formats csv or gz');
      return;
    }

    $status = \Drupal::service('ml_engine.storage')->upload_from_file_path($path, 'trainer.tar.gz');

    $emotion = ($status['success'] ? "status" : "error");
    
    drupal_set_message($status['response']['message'], $emotion);
    $this->config->set('response', $status['response'])->save();
  }

}
