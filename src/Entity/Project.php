<?php

namespace Drupal\ml_engine\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\ml_engine\ProjectInterface;

/**
 * Defines the ML Engine Project entity.
 *
 * @ConfigEntityType(
 *   id = "ml_engine_project",
 *   label = @Translation("Project"),
 *   handlers = {
 *     "list_builder" = "Drupal\ml_engine\Controller\ProjectListBuilder",
 *     "form" = {
 *       "add" = "Drupal\ml_engine\Form\ProjectAddForm",
 *       "edit" = "Drupal\ml_engine\Form\ProjectEditForm",
 *       "delete" = "Drupal\ml_engine\Form\ProjectDeleteForm",
 *     }
 *   },
 *   config_prefix = "ml_engine_project",
 *   admin_permission = "administer ml_engine",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   links = {
 *     "edit-form" = "/ml_engine/{ml_engine_project}",
 *     "delete-form" = "/ml_engine/{ml_engine_project}/delete",
 *   }
 * )
 */
class Project extends ConfigEntityBase implements ProjectInterface {

  public $id;

  public $label;

  protected $arguments;

  protected $job_name;

  protected $job_train_steps;

  protected $job_output_dir;

  protected $job_region;

  protected $job_scale_tier;

  protected $job_package_uris;

  protected $job_module;

  protected $model_name;

  protected $model_description;

  protected $model_region;

  protected $version_name;

  protected $version_default;

  protected $version_description;


  // Associate array with keys (job, model, version) to store the settings. It will be set on add/edit form submission.
  // It has the key "run" to indicate to pause and resume the automated task.
  // It has the key "list" to store the status(record) of the task.
  // It has the key "state". It can take values in (1,2,3,4,5,6).
  //    1 => 'start job processing'
  //    2 => 'continuing job processing'
  //    3 => 'start model processing'
  //    4 => 'continuing model processing'
  //    5 => 'start version processing'
  //    6 => 'continuing version processing'
  protected $cron;

  public function arguments() {
    return $this->arguments;
  }

  public function job_name(){
    return $this->job_name;
  }

  public function job_package_uris(){
    return $this->job_package_uris;
  }

  public function job_module(){
    return $this->job_module;
  }

  public function job_train_steps(){
    return $this->job_train_steps;
  }

  public function job_output_dir(){
    return $this->job_output_dir;
  }

  public function job_region(){
    return $this->job_region;
  }

  public function job_scale_tier(){
    return $this->job_scale_tier;
  }

  public function model_name(){
    return $this->model_name;
  }

  public function model_description(){
    return $this->model_description;
  }

  public function model_region(){
    return $this->model_region;
  }

  public function version_name(){
    return $this->version_name;
  }

  public function version_default(){
    return $this->version_default;
  }

  public function version_description(){
    return $this->version_description;
  }

  public function get_cron(){
    return $this->cron;
  }

  public function set_cron($value){
    $this->cron = $value;
    return $this;
  }

  public function get_create(){
    return $this->cron;
  }

  public function set_create($value){
    $this->cron = $value;
    return $this;
  }


}