<?php

namespace Drupal\ml_engine;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\ml_engine\MLEngineBase;

class Storage extends MLEngineBase{

  public $bucket_name;
  public $bucket_repo_name;
  private $time;

  public function __construct() {
      parent::__construct();
      $this->bucket_name = \Drupal::service('ml_engine.project')->get_bucket();
      $this->bucket_repo_name = \Drupal::service('ml_engine.project')->get_bucket_repo();
      $this->time = time();
  }

  public static function create(ContainerInterface $container) {
     return new static();
  }

  public function create_storage_client(){
    $storage = new \Google\Cloud\Storage\StorageClient([
        'projectId' => \Drupal::service('ml_engine.project')->get_name(),
        'keyFile'=> \Drupal::service('ml_engine.project')->get_credential()
    ]);

    return $storage;
  }

  public function create_bucket(){
    $client = $this->create_storage_client();
    $bucket = $client->bucket($this->bucket);
    return $bucket;
  }

  public function upload($file, $upload_name){
    $bucket = $this->create_bucket();
    $upload_name = $this->bucket_repo_name.'/'.$upload_name;

    $options = [
         'metadata' => [
             'contentLanguage' => 'en'
         ],    
         'name' => $upload_name,
         
     ];
    
     try{
        $response = $bucket->upload($file,$options);
        $response = array('message' => "Successfully uploaded file as ".$upload_name);
        
        return array("success" => 1, "response" => $response, "file_path" => "gs://".$this->bucket_name."/".$upload_name);
     }
     catch(\Google\Cloud\Core\Exception\NotFoundException $ex){
        $error = array("message"=>"Upload Not Found Exception error"); //json_decode($ex->getMessage(), true)['error'];
        return array( "success" => 0, "response" => $error);
     }
     catch(\Google\Cloud\Core\Exception\ServiceException $ex){
        $error = array("message"=>"Upload Service Exception error"); //json_decode($ex->getMessage(), true)['error'];
        return array( "success" => 0, "response" => $error);
     }

  }

  public function upload_from_file_path($path,$file_name=""){
    
    if(!$file_name){
      $file_name_split = explode('/', $path);
      $file_name = end($file_name_split);      
    }

    return $this->upload(fopen($path,'r'), $file_name);
  }

  public function get_objects($name=''){
    $bucket = $this->create_bucket();
    
    $para = $name ? array('prefix' => $name) : array();
    
    $objects = $bucket->objects($para);
    return $objects;
  }

  public function get_deployment_uri($name=''){
    $objects = $this->get_objects($name);
    
    foreach ($objects as $object) {
        $path_info = pathinfo($object->name());

        if($path_info['extension']=='pb'){
          return dirname($object->name());
        }
    }

    return '';
  }

}
