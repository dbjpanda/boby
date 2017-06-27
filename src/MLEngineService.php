<?php

namespace Drupal\ml_engine;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MLEngineService {

  public $credential;

  public function __construct() {
  }

  public static function create(ContainerInterface $container) {
    return new static();
  }

  public function create_service(array $credential=[]){
    // Creting client and services.
    
    if(!$credential) {
      $credential = \Drupal::service('ml_engine.project')->get_credential();
    }
    $client = new \Google_Client();
    $client->setAuthConfig($credential);
    $client->addScope(\Google_Service_CloudMachineLearningEngine::CLOUD_PLATFORM);
    $service = new \Google_Service_CloudMachineLearningEngine($client);
    return $service;
  }

}
