<?php

namespace Drupal\ml_engine;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Project {

  private $credential_required_keys;

  public function __construct() {
    $this->credential_required_keys = ['type', 'project_id', 'private_key_id', 'private_key', 'client_email', 'client_id', 'auth_uri', 'token_uri', 'auth_provider_x509_cert_url', 'client_x509_cert_url'];
  }

  public static function create(ContainerInterface $container) {
    return new static();
  }

  public function get_project(){
    return \Drupal::configFactory()->getEditable('ml_engine.test.project');
  }

  public function get_credential(){
    return json_decode($this->get_project()->get('credential'),true);
  }

  public function get_name(){
    return $this->get_project()->get('name');
  }

  public function get_bucket(){
    return 'drupal-ml';
  }

  public function get_bucket_repo(){
    return 'drupal-ml-repo';
  }

  public function verify_credential($para){

    $key_difference = array_diff($this->credential_required_keys, array_keys($para['credential']));

    if ($key_difference){
      $message = t("Credential Keys [ '@keys' ] are missing",array('@keys'=>join("', '",$key_difference)));
      return array(
        'success' => 0,
        'response'=>array('message'=>$message),
      );
    }

    $service = \Drupal::service('ml_engine.cloud_service')->create_service();

    try{ 
        $response = $service->projects->getConfig($para['name']);
        return array('success' => 1,'response' =>$response);
    } catch (\DomainException $ex){
        $error = array('message' => $ex->getMessage());
        return array('success' => 0,'response'=> array('message'=>"Please check the private key"));
    } catch (\Google_Service_Exception $ex){
        $error = json_decode($ex->getMessage(), true)['error'];
        return array( "success" => 0, "response" => $error);
    }

  }

}
