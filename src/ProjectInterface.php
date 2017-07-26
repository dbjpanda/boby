<?php

namespace Drupal\ml_engine;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an ML Engine Project entity.
 */
interface ProjectInterface extends ConfigEntityInterface {

  public function arguments();

  public function job_name();

  public function job_train_steps();

  public function job_output_dir();

  public function job_region();

  public function job_scale_tier();

  public function model_name();

  public function model_description();

  public function model_region();

  public function version_name();

  public function version_default();

  public function version_description();

  public function get_cron();

  public function set_cron($value);

  public function get_create();

  public function set_create($value);

}