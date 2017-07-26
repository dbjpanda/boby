<?php

namespace Drupal\ml_engine\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\ml_engine\ProjectInterface;

class ProjectController extends ControllerBase {

  public function run(ProjectInterface $ml_engine_project){

    $project = $ml_engine_project;
    $cron = $project->get_cron();
    print "<pre>";
    print_r($cron);
    print "</pre>";
    \Drupal::service('ml_engine.automate')->set_project($ml_engine_project)->automate();
    return $this->redirect('entity.ml_engine_project.status', ['ml_engine_project' => $ml_engine_project->id()]);
  }

  public function stop(ProjectInterface $ml_engine_project){
    $cron = $ml_engine_project->get_cron();
    $cron["run"] = 0;
    $ml_engine_project->set_cron($cron)->save();
    return $this->redirect('entity.ml_engine_project.status', ['ml_engine_project' => $ml_engine_project->id()]);
  }

  public function refresh(ProjectInterface $ml_engine_project){
    $cron = $ml_engine_project->get_cron();
    $cron["run"] = 1;
    $ml_engine_project->set_cron($cron)->save();
    \Drupal::service('ml_engine.automate')->set_project($ml_engine_project)->refresh_cron_list();
    return $this->redirect('entity.ml_engine_project.status', ['ml_engine_project' => $ml_engine_project->id()]);
  }

  public function status(ProjectInterface $ml_engine_project){

    $cron = $ml_engine_project->get_cron();

    if(array_key_exists("list", $cron)){
      $cron_status_array = $cron['list'];
    }else{
      $cron_status_array = [];
    }

    $header = [t('Type'), t('Name'), t('Status')];
    $rows = [];

    if($cron_status_array){
      foreach($cron_status_array as $status) {
        $row = [];
        $row[] = $status[0];
        $row[] = $status[1];
        $row[] = $status[2];
        $rows[] = $row;
      }
    }

    $build['feeds'] = [
      '#prefix' => '<h3>' . t('Jobs overview') . '</h3>',
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('Empty status.'),
    ];

    return $build;

  }

}