<?php

namespace Drupal\ml_engine;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\ml_engine\MLEngineBase;

class Job extends MLEngineBase{

  public $config;

  public function __construct() {
      parent::__construct();
      $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.job');
  }

  public static function create(ContainerInterface $container) {
     return new static();
  }

  public function UpdateJobList() {
      $service = $this->create_service();
      $response = $service->projects_jobs->listProjectsJobs($this->project_name);
      $jobs = $response->__get('jobs');
      
      $jobs_array = [];
      
      for ($i=0; $i<count($jobs); $i++){
          $jobs_array[$i] = (array) $jobs[$i];
      }

      $this->config->clear('list')->save();
      $this->config->set('list',$jobs_array)->save();
      return $jobs_array;
  }

  public function cancel($job){
      $job_full_name = $this->project_name."/jobs/".$job;
      $service = $this->create_service();

      try{
        $response = $service->projects_jobs->cancel($job_full_name, new \Google_Service_CloudMachineLearningEngine_GoogleCloudMlV1CancelJobRequest());
        return array( "success" => 1, "response" => $response );
      }catch (\Google_Service_Exception $ex){
        $error = json_decode($ex->getMessage(), true)['error'];
        return array( "success" => 0, "response" => $error);
      }
  }

  public function get($job){
      $job_full_name = $this->project_name."/jobs/".$job;
      $service = $this->create_service();

      try{
        $response = $service->projects_jobs->get($job_full_name);
        return array( "success" => 1, "response" => $response );
      }catch (\Google_Service_Exception $ex){
        $error = json_decode($ex->getMessage(), true)['error'];
        return array( "success" => 0, "response" => $error);
      }
  }

  private function createInputObject(array $para){
      foreach (array_keys($para) as $key) {
        ${$key} = $para[$key];
      }
      $arguments_array = ['--train--files', $train_data_uri, '--eval-files', $test_data_uri,
                          '--train-steps', $train_steps, 'verbosity', $verbosity];
      $package_array = array($package_uri);
      $input = new \Google_Service_CloudMachineLearningEngine_GoogleCloudMlV1TrainingInput();
      
      $input->setScaleTier($scale_tier);
      $input->setPackageUris($package_array);
      $input->setPythonModule($module);
      $input->setRegion($region);
      $input->setJobDir($job_dir);
      $input->setArgs($arguments_array);
      
      return $input;
  }

  private function createJobObject(array $para){
      $input = $this->createInputObject($para);
      
      $job = new \Google_Service_CloudMachineLearningEngine_GoogleCloudMlV1Job();
      $job->setJobId($para['job']);
      $job->setTrainingInput($input);

      return $job;
  }

  public function JobCreate(array $para){
      $job = $this->createJobObject($para);
      $service = $this->create_service();

      try{
        $response = $service->projects_jobs->create($this->project_name,$job);
        return array( "success" => 1, "response" => $response );
      }catch (\Google_Service_Exception $ex){
        $error = json_decode($ex->getMessage(), true)['error'];
        return array( "success" => 0, "response" => $error);
      }
  }

}