<?php

namespace Drupal\ml_engine\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AutomateController extends ControllerBase {

  private $cron;

  public function __construct() {
    $this->cron = \Drupal::configFactory()->getEditable('ml_engine.test.automate.cron');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  public function get_config(){
    return $this->cron;
  }

  public function new_task(){
    \Drupal::configFactory()->getEditable('ml_engine.test.automate.create')->delete();
    \Drupal::configFactory()->getEditable('ml_engine.test.automate.cron')->delete();
    \Drupal::configFactory()->getEditable('ml_engine.test.automate.cron')->set('state', 1)->save();
    \Drupal::configFactory()->getEditable('ml_engine.test.automate.cron')->set('run', 0)->save();
    $url = new RedirectResponse('create');
    $url->send();
  }

  public function update_task(){

    if(\Drupal::configFactory()->getEditable('ml_engine.test.automate.cron')->get('state') != 1){
        \Drupal::configFactory()->getEditable('ml_engine.test.automate.cron')->set('run', 1)->save();
        \Drupal::service('ml_engine.automate')->refresh_cron_list();
      }
    $url = new RedirectResponse('status');
    $url->send();
  }

  public function overview() {
    $cron_status_array = $this->get_config()->get('list');

    //print "<pre>";
    //print_r($cron_status_array);
    //print "</pre>";
    //die();
    
    $header = [$this->t('Type'), $this->t('Name'), $this->t('Status')];
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
      '#prefix' => '<h3>' . $this->t('Jobs overview') . '</h3>',
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('Cron not working.'),
    ];
    
    return $build;
  }



}
