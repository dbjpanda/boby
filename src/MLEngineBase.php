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
  }

  public static function create(ContainerInterface $container) {
    return new static();
  }

  public function create_service(){
    return \Drupal::service('ml_engine.cloud_service')->create_service();
  }


}
