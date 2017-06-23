<?php

namespace Drupal\ml_engine\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ModelController extends ControllerBase {

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

  public function refresh_overview_page() {
    \Drupal::service('ml_engine.model')->UpdateModelList();
    drupal_set_message("Model list refreshed", "status");
    $url = new RedirectResponse('../list');
    $url->send();
  }


  public function overview() {
    $models_array = $this->get_config()->get('list');
    $header = [$this->t('Model Name'), $this->t('Versions'), $this->t('Regions'), $this->t('Operations')];
    $rows = [];

    if(!$models_array){
      $models_array = [];
    }

    foreach ($models_array as $model) {
      $row = [];
      $model_name = explode("models/", $model['name'])[1];
      $versions_link = $this->l('versions', new Url('ml_engine.test.version.list', ['model_name' => $model_name]));
      $row[] = $model_name;
      $row[] = $versions_link;
      $row[] = implode(",", $model['regions']);
      
      $links['edit'] = [
        'title' => $this->t('Delete'),
        'url' => Url::fromRoute('ml_engine.test.model.delete', ['model' => $model_name])
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
      '#empty' => $this->t('No model available.'),
    ];
    
    //$build['form'] = \Drupal::formBuilder()->getForm('\Drupal\ml_engine\Form\Job\TestJobCancel');
    return $build;
  }

  public function deleteList($model){
    $response = \Drupal::service('ml_engine.model')->delete($model);
    if($response['success']){
      drupal_set_message('Model Deleted Successfully', 'status');
    }else{
      drupal_set_message($response['response']['message'], 'error');
    }
    \Drupal::service('ml_engine.model')->UpdateModelList();
    $url = new RedirectResponse('../list');
    $url->send();
  }


}
