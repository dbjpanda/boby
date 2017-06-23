<?php

namespace Drupal\ml_engine;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\ml_engine\MLEngineBase;

class Version extends MLEngineBase{

  public $config;

  public function __construct() {
      parent::__construct();
      $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.model');
  }

  public static function create(ContainerInterface $container) {
     return new static();
  }

  public function UpdateVersionList($model_name) {
      $service = $this->create_service();
      $parent = $this->project_name.'/models/'.$model_name;
      try{
        $response = $service->projects_models_versions->listProjectsModelsVersions($parent);
      }catch(\Google_Service_Exception $ex){
        die('Model '.$model_name." not found");
      }

      $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.model.'.$model_name);

      $versions = $response->__get('versions');

      $versions_array = [];
      
      for ($i=0; $i<count($versions); $i++){
          $versions_array[$i] = (array) $versions[$i];
      }

      $this->config->clear('list')->save();
      $this->config->set('list',$versions_array)->save();
      return $versions_array;
  }

  public function delete($model, $version){
      $full_name = $this->project_name."/models/".$model."/versions/".$version;
      $service = $this->create_service();

      try{
        $response = $service->projects_models_versions->delete($full_name);
        return array( "success" => 1, "response" => $response );
      }catch (\Google_Service_Exception $ex){
        $error = json_decode($ex->getMessage(), true)['error'];
        return array( "success" => 0, "response" => $error);
      }
  }

  public function get($para){
      $version_full_name = $this->project_name."/models/".$para['model_name']."/versions/".$para['name'];
      $service = $this->create_service();

      try{
        $response = $service->projects_models_versions->get($version_full_name);
        return array( "success" => 1, "response" => $response );
      }catch (\Google_Service_Exception $ex){
        $error = json_decode($ex->getMessage(), true)['error'];
        return array( "success" => 0, "response" => $error);
      }
  }

  private function createVersionObject(array $para){
      $model = new \Google_Service_CloudMachineLearningEngine_GoogleCloudMlV1Version();
      $model->setName($para['name']);
      $model->setDescription($para['description']);
      $model->setDeploymentUri($para['deployment_uri']);
      return $model;
  }

  public function VersionCreate(array $para){
      $version = $this->createVersionObject($para);
      $service = $this->create_service();
      $model_full_name = $this->project_name."/models/".$para['model_name'];
      try{
        $response = $service->projects_models_versions->create($model_full_name,$version);
        return array( "success" => 1, "response" => $response );
      }catch (\Google_Service_Exception $ex){
        $error = json_decode($ex->getMessage(), true)['error'];
        return array( "success" => 0, "response" => $error);
      }
  }

}
