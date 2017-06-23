<?php

namespace Drupal\ml_engine\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class VersionController extends ControllerBase {

  private $config;
  private $project;

  public function __construct() {
    $this->project = \Drupal::service('ml_engine.project')->get_name();
    $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.model');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  public function get_config(){
    return $this->config;
  }

  public function refresh_overview_page($model) {
    \Drupal::service('ml_engine.version')->UpdateVersionList($model);
    drupal_set_message("Version list refreshed", "status");
    $url = new RedirectResponse('../versions');
    $url->send();
  }


  public function overview($model_name) {
    $models_array = $this->get_config()->get('list');
    $model_in_list = False;

    foreach ($models_array as $model) {
      $name = explode("models/", $model['name'])[1];

      if($name == $model_name){
        $model_in_list = True;
        break;
      }
    }

    if(!$model_in_list){
      die('Model Not Found');
    }

    $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.model.'.$model_name);
    $versions_array = $this->config->get('list');

    $header = [$this->t('Name'), $this->t('Deployment URL'), $this->t('Create Time'), $this->t('Default'), $this->t('Operations')];
    $rows = [];

    if(!$versions_array){
      $versions_array = [];
    }

    foreach ($versions_array as $version) {
      $row = [];
      $version_name = explode("versions/", $version['name'])[1];
      $row[] = $version_name;
      $row[] = $version['deploymentUri'];
      $row[] = $version['createTime'];
      $row[] = $version['isDefault'] ? 'Yeah':"";
      
      $links['edit'] = [
        'title' => $this->t('Delete'),
        'url' => Url::fromRoute('ml_engine.test.version.delete', ['model' => $model_name, 'version' => $version_name])
      ];

      $row[] = [
        'data' => [
          '#type' => 'operations',
          '#links' => $links,
        ],
      ];

      $rows[] =  $row;      
    }
    $build['feeds'] = [
      '#prefix' => '<h3>' . $this->t('Models overview') . '</h3>',
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No Version available.'),
    ];
    
    //$build['form'] = \Drupal::formBuilder()->getForm('\Drupal\ml_engine\Form\Job\TestJobCancel');
    return $build;
  }

  public function deleteList($model,$version){
    $response = \Drupal::service('ml_engine.version')->delete($model,$version);
    if($response['success']){
      drupal_set_message('Model Deleted Successfully', 'status');
    }else{
      drupal_set_message($response['response']['message'], 'error');
    }
    \Drupal::service('ml_engine.version')->UpdateVersionList($model);
    $url = new RedirectResponse('../../versions');
    $url->send();
  }


}
