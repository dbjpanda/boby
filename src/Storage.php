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

  public $config;
  public $states;

  public function __construct() {
      parent::__construct();
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
    $bucket_name = \Drupal::service('ml_engine.project')->get_bucket();
    $bucket = $client->createBucket($bucket_name);
    return $bucket;
  }

  public function upload($file, $upload_name){
    $bucket = $this->create_bucket();
    
    $options = [
         'metadata' => [
             'contentLanguage' => 'en',
             'name' => $upload_name,
         ]
     ];
    
    $response = $bucket->upload($file,$options);
    return $response;
  }

}
