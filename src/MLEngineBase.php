<?php

namespace Drupal\ml_engine;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for aggregator module routes.
 */
class MLEngineBase {

  public $project_name;
  public $bucket;

  public function __construct() {
    $project = \Drupal::service('ml_engine.project');
    $this->project_name = $project->get_name();
    $this->bucket = $project->get_bucket();
    $this->bucket_repo = $project->get_bucket_repo();
  }

  public static function create(ContainerInterface $container) {
    return new static();
  }

  public function create_service(){
    return \Drupal::service('ml_engine.cloud_service')->create_service();
  }

  public function object_dismount($object) {
    $reflectionClass = new \ReflectionClass(get_class($object));
    $array = array();
    foreach ($reflectionClass->getProperties() as $property) {
        $property->setAccessible(true);
        $array[$property->getName()] = $property->getValue($object);
        $property->setAccessible(false);
    }
    return $array;
  }


}
