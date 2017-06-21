<?php

namespace Drupal\ml_engine\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for aggregator module routes.
 */
class JobController extends ControllerBase {

  public function __construct() {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  public function get_config(){
    return \Drupal::configFactory()->getEditable('ml_engine.test.job');
  }

  public function refresh_overview_page() {
    \Drupal::service('ml_engine.job')->UpdateJobList();
    drupal_set_message("Job list refreshed", "status");
    $url = new RedirectResponse('../list');
    $url->send();
  }


  public function overview() {
    $jobs_array = $this->get_config()->get('list');
    $header = [$this->t('Job Name'), $this->t('Status'), $this->t('Create Time'), $this->t('End Time'), $this->t('Operations')];
    $rows = [];

    if(!$jobs_array){
      $jobs_array = [];
    }

    foreach ($jobs_array as $job) {
      $row = [];
      $row[] = $job['jobId'];
      $row[] = $job['state'];
      $row[] = $job['createTime'];
      $row[] = $job['endTime'];
      $links = [];

      // There are eight training states, refer to
      // https://cloud.google.com/ml-engine/reference/rest/v1/projects.jobs#State
      
      $training_states = array('QUEUED','PREPARING','RUNNING');
      
      if(in_array($job['state'],$training_states)){
        $links['edit'] = [
          'title' => $this->t('Cancel'),
          'url' => Url::fromRoute('ml_engine.test.job.cancel', ['job' => $job['jobId']])
        ];
      }

      $row[] = [
        'data' => [
          '#type' => 'operations',
          '#links' => $links,
        ],
      ];
      $rows[] = $row;
    }
    $build['feeds'] = [
      '#prefix' => '<h3>' . $this->t('Jobs overview') . '</h3>',
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No jobs available.'),
    ];

    return $build;
  }

  public function cancelList($job){
    $response = \Drupal::service('ml_engine.job')->cancel($job);
    if($response['success']){
      drupal_set_message('Job Cancelled Successfully', 'status');
    }else{
      drupal_set_message($response['response']['message'], 'error');
    }
    \Drupal::service('ml_engine.job')->UpdateJobList();
    $url = new RedirectResponse('../list');
    $url->send();
  }


}
