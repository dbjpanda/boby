<?php

namespace Drupal\ml_engine;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\ml_engine\MLEngineBase;

class Model extends MLEngineBase{

  public $config;

  public function __construct() {
      parent::__construct();
      $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.model');
  }

  public static function create(ContainerInterface $container) {
     return new static();
  }

  public function UpdateModelList() {
      $service = $this->create_service();
      $response = $service->projects_models->listProjectsModels($this->project_name);
      $models = $response->__get('models');

      $models_array = [];
      
      for ($i=0; $i<count($models); $i++){
          $models_array[$i] = (array) $models[$i];
      }

      $this->config->clear('list')->save();
      $this->config->set('list',$models_array)->save();
      return $models_array;
  }

  public function delete($name){
      $model_full_name = $this->project_name."/models/".$name;
      $service = $this->create_service();

      try{
        $response = $service->projects_models->delete($model_full_name);
        $response = (array) $response;
        $response['message'] = "Successfully deleted model ". $name;

        return array( "success" => 1, "response" => $response );
      }catch (\Google_Service_Exception $ex){
        $error = json_decode($ex->getMessage(), true)['error'];
        return array( "success" => 0, "response" => $error);
      }
  }

  public function get($name){
      $model_full_name = $this->project_name."/models/".$name;
      $service = $this->create_service();

      try{
        $response = $service->projects_models->get($model_full_name);
        $response = (array) $response;
        $response['message'] = "Successfully got model ". $name;

        return array( "success" => 1, "response" => $response );
      }catch (\Google_Service_Exception $ex){
        $error = json_decode($ex->getMessage(), true)['error'];
        return array( "success" => 0, "response" => $error);
      }
  }

  private function createModelObject(array $para){
      $model = new \Google_Service_CloudMachineLearningEngine_GoogleCloudMlV1Model();
      $model->setName($para['name']);
      $model->setDescription($para['description']);
      $model->setRegions($para['region']);
      return $model;
  }

  public function ModelCreate(array $para){
      $model = $this->createModelObject($para);
      $service = $this->create_service();

      try{
        $response = $service->projects_models->create($this->project_name,$model);
        $response = (array) $response;
        $response['message'] = "Successfully created model ". $para['name'];
        
        return array( "success" => 1, "response" => $response );
      }catch (\Google_Service_Exception $ex){
        $error = json_decode($ex->getMessage(), true)['error'];
        return array( "success" => 0, "response" => $error);
      }
  }

}
