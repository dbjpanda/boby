<?php

namespace Drupal\ml_engine;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\ml_engine\MLEngineBase;

class Predict extends MLEngineBase{

  public static function create(ContainerInterface $container) {
     return new static();
  }

  public function predict($model,array $data){
      $model_full_name = $this->project_name."/models/".$model;
      
      $service = $this->create_service();
      try{
        $response = $service->projects->predict($model_full_name, new \Google_Service_CloudMachineLearningEngine_GoogleCloudMlV1PredictRequest($data));
          return array( "success" => 1, "response" => $response );
      }catch (\Google_Service_Exception $ex){
          $error = json_decode($ex->getMessage(), true)['error'];
          return array( "success" => 0, "response" => $error);
      }
  }

}
