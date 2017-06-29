<?php

namespace Drupal\ml_engine;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\ml_engine\MLEngineBase;

class Automate extends MLEngineBase{

  public $config;
  public $time;
  private $max_states;
  private $cron;

  public function __construct() {
      parent::__construct();
      $this->time = time();
      $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.automate.create');
      $this->cron = \Drupal::configFactory()->getEditable('ml_engine.test.automate.cron');
      $this->max_states = 5;
  }

  public static function create(ContainerInterface $container) {
     return new static();
  }

  public function getValue($key){
      $value = $this->config->get($key);
      if($value) { return $value; }

      $defaults = array_merge($this->modelDefault(), $this->jobDefault(), $this->versionDefault());
      return $defaults[$key];
  }

  public function automate($job, $model, $version){
      
      $this->cron->set('run',1)->save();
      $this->cron->set('job', $job)->save();
      $this->cron->set('model', $model)->save();
      $this->cron->set('version', $version)->save();
      $this->refresh_cron_list();
  }

  private function add_to_state_list(array $record){
    $status_list = $this->cron->get('list');
    $status_list[] = $record;
    $this->cron->set('list', $status_list)->save();
    return;
  }

  private function stop_run(){
    $this->cron->set('run', 0)->save();
  }

  private function add_state(){
    $state = $this->cron->get('state');
    $new_state = $state + 1;
    if($new_state >= $this->max_states){
      $new_state = 0;
    } 
    $this->cron->set('state', $new_state)->save();
  }

  private function handle_response($type, $status){

    $job_details = $this->cron->get('job');
    $model_details = $this->cron->get('model');
    $version_details = $this->cron->get('version');

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

    if(!$this->cron->get('run')){
      return;
    }
    
    $job_service = \Drupal::service('ml_engine.job');
    $model_service = \Drupal::service('ml_engine.model');
    $version_service = \Drupal::service('ml_engine.version');

    $job_details = $this->cron->get('job');
    $model_details = $this->cron->get('model');
    $version_details = $this->cron->get('version');

    $state = $this->cron->get('state');
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

  public function jobDefault(){
      $default = array(
          'job_package_uri' => '',
          'job_module' => '',
          'job_train_data_uri' => '',
          'job_test_data_uri' => '',
          'job_verbosity' => 'DEBUG',
          'job_name' => 'drupal_job_'.$this->time,
          'job_train_steps' => 1000,
          'job_output_dir' => 'out_'.$this->time,
          'job_region' => 'us-east1',
          'job_scale_tier' => 'BASIC',
      );

      return $default;
  }

  public function modelDefault(){
      $default = array(
        'model_name' => 'drupal_model_'.$this->time,
        'model_description' => 'Model made with Drupal',
        'model_region' => 'us-central1'
      );

      return $default;
  }

  public function versionDefault(){
      $default =  array(
        'version_name' => 'drupal_version_'.$this->time,
        'version_default' => 1,
        'version_description' => 'Version made with Drupal',
      );

      return $default;
  }
}
