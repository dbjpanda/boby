<?php

/**
 * @file
 * Contains Drupal\slack\Form\SendTestMessageForm.
 */

namespace Drupal\ml_engine\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Firebase\JWT\JWT;


/**
 * Class SendTestMessageForm.
 *
 * @package Drupal\slack\Form
 */
class TestCredential extends FormBase {

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

    $config = \Drupal::configFactory()->getEditable('ml_engine.test');

    $form['project'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Project name'),
      '#required' => TRUE,
      '#default_value' => $config->get('project')
    );

    $form['credential'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Credential'),
      '#required' => TRUE,
      '#default_value' => $config->get('credential'),
      '#rows' => 25,
    );

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );

    // Print credential verification statue.
    if ($status = $config->get('status')){
        $form['response'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Response'),
          '#attributes' => array('readonly' => 'readonly'),
          '#default_value' => $status,
          '#weight' => 100,
        );      
    }

    // Print credential verification error.
    if ($error = $config->get('error')){
        $form['error'] = array(
          '#type' => 'textarea',
          '#title' => $this->t('Error'),
          '#attributes' => array('readonly' => 'readonly'),
          '#default_value' => $error,    
          '#rows' => 15,
          '#weight' => 101,
        );      
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  
    $config = \Drupal::configFactory()->getEditable('ml_engine.test');
    $config->delete();
    $credential = $form_state->getValue('credential');
    $project = $form_state->getValue('project');
    $config ->set('credential',$credential) ->set('project',$project) ->save();
    
    $credential_json = json_decode($credential,true);      
    $credential_json_required_keys = ['type', 'project_id', 'private_key_id', 'private_key',
                                      'client_email', 'client_id', 'auth_uri', 'token_uri',
                                      'auth_provider_x509_cert_url', 'client_x509_cert_url'];
    
    $array_difference = array_diff($credential_json_required_keys, array_keys($credential_json));
    
    if ($array_difference){
      $message = t("Credential Keys [ '@keys' ] are missing",array('@keys'=>join("', '",$array_difference)));
      drupal_set_message($message,'error');
      $config ->set('status',"Verification Failed") ->set('error', $message)->save();
      return;
    }
    $client = new \Google_Client();
    $client->setAuthConfig($credential_json);
    $client->addScope(\Google_Service_CloudMachineLearningEngine::CLOUD_PLATFORM);
    $service = new \Google_Service_CloudMachineLearningEngine($client);

    try{
        $response = $service->projects->getConfig($project);
        $config ->set('status', "Verification Successfull")->save();
        drupal_set_message("Successfully set project and credential");
    } catch (\DomainException $ex){
      $config ->set('status',"Verification Failed") ->set('error', "Please check the private key")->save();
      drupal_set_message($ex->getMessage(),'error');
      return;
    } catch (\Google_Service_Exception $ex){
      $config ->set('status',"Verification Failed") ->set('error', $ex->getMessage())->save();
      drupal_set_message($ex->getMessage(),'error');
      return;      
    }
  }

}
