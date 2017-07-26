<?php

namespace Drupal\ml_engine;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\ml_engine\MLEngineBase;
use Drupal\ml_engine\ProjectInterface;

class Automate extends MLEngineBase{

  public $config;
  public $time;
  private $max_states;
  public $project;

  public function __construct() {
      parent::__construct();
      $this->time = time();
      $this->max_states = 5;
  }

  public static function create(ContainerInterface $container) {
     return new static();
  }

  public function set_project(ProjectInterface $project){
    $this->project = $project;
    return $this;
  }

  public function get_cron(){
    return $this->project->get_cron();
  }

  public function set_cron($value){
    $this->project->set_cron($value)->save();
    return $this->project;
  }


  public function automate(){
      
    $cron = $this->get_cron();
    $cron['run'] = 1;
    $this->set_cron($cron);

    $this->refresh_cron_list();
  }

  private function add_to_state_list(array $record){
    $cron = $this->get_cron();
    $cron['list'][] = $record;
    $this->set_cron($cron);
    return;
  }

  private function stop_run(){
    $cron = $this->get_cron();
    $cron['run'] = 0;
    $this->set_cron($cron);
  }

  private function add_state(){
    $cron = $this->get_cron();
    $state = $cron['state'];
    $new_state = $state + 1;
    if($new_state >= $this->max_states){
      $new_state = 0;
    }
    $cron['state'] = $new_state;
    $this->set_cron($cron);
  }

  private function handle_response($type, $status){

    $cron = $this->get_cron();
    $job_details = $cron['job'];
    $model_details = $cron['model'];
    $version_details = $cron['version'];

    $record = [$type, ${$type.'_details'}['name'],$status['response']['message']];
    $this->add_to_state_list($record);

    if($status['success']){
        $this->add_state();
        $this->refresh_cron_list();
    }else{
      $this->stop_run();      
    }

  }

  public function refresh_cron_list() {
    $cron = $this->get_cron();

    if(!$cron['run']){
      return;
    }
    
    $job_service = \Drupal::service('ml_engine.job');
    $model_service = \Drupal::service('ml_engine.model');
    $version_service = \Drupal::service('ml_engine.version');

    $job_details = $cron['job'];
    $model_details = $cron['model'];
    $version_details = $cron['version'];

    $state = $cron['state'];
    echo $state;

    if ($state == 1){
        $status = $job_service->jobCreate($job_details);
        $this->handle_response('job', $status);
        return;
    }
    
    if ($state == 3){        
        $status = $model_service->modelCreate($model_details);
        $this->handle_response('model', $status);
        return;
    }

    if ($state == 4){
        $version_details['model_name'] = $model_details['name'];
        $deployment_uri = \Drupal::service('ml_engine.storage')->get_deployment_uri($this->bucket_repo."/".$job_details['output_dir']);
        $deployment_full_uri = "gs://".$this->bucket.'/'.$deployment_uri;
        $version_details['deployment_uri'] = $deployment_full_uri;
        drupal_set_message($version_details['deployment_uri']);
        $status = $version_service->versionCreate($version_details);
        $this->handle_response('version', $status);
        return;
    }
    
    if ($state == 2){

        echo "Entered state 2";
        $status = $job_service->get($job_details['name']);
        $response = $status['response'];
        
        if($status['success']){
        
            $record = ['Job', $response['jobId'], $response['state']];
            $this->add_to_state_list($record);

            if(in_array($response['state'], $job_service->states['failure'])){
              $this->stop_run();
              return;
            }

            if(in_array($response['state'], $job_service->states['success'])){
              $this->add_state();
              $this->refresh_cron_list();
              return;
            }

        
        } else{
            $record = ['Job', $response['jobId'], $response['message']];
            $this->add_to_state_list($record);
            $this->stop_run();
        }

        return;
    }

    $this->stop_run();
    return;

  }

}
