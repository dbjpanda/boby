<?php
namespace Drupal\ml_engine\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for the ML Engine Project add and edit forms.
 */
class ProjectAddForm extends EntityForm {

  public $time;

  /**
   * Constructs an ProjectForm object.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   */
  public function __construct(QueryFactory $entity_query) {
    $this->entityQuery = $entity_query;
    $this->time = time();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $project = $this->entity;

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $project->label(),
      '#description' => $this->t("Label for the Project."),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $project->id(),
      '#machine_name' => array(
        'exists' => array($this, 'exist'),
      ),
      '#disabled' => !$project->isNew(),
    );

    $form['arguments'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Arguments'),
      '#maxlength' => 1000,
      '#default_value' => $project->arguments(),
      '#description' => $this->t("Enter the tensorflow arguments."),
      '#required' => TRUE,
    );

################################################################################
        
        $form['job'] = array(
        '#type' => 'details',
        '#title' => t('Job'),
        );

        $form['job'] = array_merge($form['job'], $this->job_fields());

        $form['model'] = array(
        '#type' => 'details',
        '#title' => t('model'),
        );

        $form['model'] = array_merge($form['model'], $this->model_fields());

        $form['version'] = array(
        '#type' => 'details',
        '#title' => t('Version'),
        );

        $form['version'] = array_merge($form['version'], $this->version_fields());
###########################################################################################

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $project = $this->entity;

    $job_keys=array('name', 'package_uris', 'module', 'train_steps','output_dir','region','scale_tier',);
    $model_keys=array('name', 'description', 'region');
    $version_keys=array('name', 'default','description',/**'deployment_uri'**/);

    foreach ($job_keys as $key) {
      $job_key = "job_".$key;
      $jobPara[$key] = ${$job_key} = $form_state->getValue($job_key);
    }

    $jobPara['package_uris'] = explode(",", $form_state->getValue('job_package_uris'));
    $arguments = array_map('trim', explode("\\", $form_state->getValue("arguments")));
    $argument_list = array();
    foreach ($arguments as $argument){
      $split_array = explode(" ", $argument);
      foreach ($split_array as $value){
        $argument_list[] = $value;
      }
    }

    $jobPara['arguments'] = $argument_list;

    foreach ($model_keys as $key) {
      $model_key = "model_".$key;
      $modelPara[$key] = ${$model_key} = $form_state->getValue($model_key);
    }

    foreach ($version_keys as $key) {
      $version_key = "version_".$key;
      $versionPara[$key] = ${$version_key} = $form_state->getValue($version_key);
    }

    $cron = array(
      "job" => $jobPara,
      "model" => $modelPara,
      "version" => $versionPara,
      "state" => 1,
      "run" => 0,
    );

    $project->set_cron($cron);

    $status = $project->save();

    if ($status) {
      drupal_set_message($this->t('Saved the %label ML Engine Project.', array(
        '%label' => $project->label(),
      )));
    }
    else {
      drupal_set_message($this->t('The %label ML Engine Project was not saved.', array(
        '%label' => $project->label(),
      )));
    }

    $form_state->setRedirect('entity.ml_engine_project.collection');
  }

  /**
   * Helper function to check whether an ML Engine Project configuration entity exists.
   */
  public function exist($id) {
    $entity = $this->entityQuery->get('ml_engine_project')
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

  private function job_fields(){

    $project = $this->entity;

      $job['job_name'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Name'),
        '#default_value' => $this->jobDefault()['job_name'],
      );

      $job['job_package_uris'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Package URI'),
        '#default_value' => $project->job_package_uris(),
      );

      $job['job_module'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Module'),
        '#default_value' => $project->job_module(),
      );

      $job['job_train_steps'] = array(
        '#type' => 'number',
        '#title' => $this->t('Train Steps'),
        '#default_value' => $this->jobDefault()['job_train_steps'],
      );
      $job['job_output_dir'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Output Directory'),
        '#default_value' => $this->jobDefault()['job_output_dir'],
      );
      $job['job_region'] = array(
        '#type' => 'select',
        '#options' => array(
          'us-central1' => t('us-central1'),
          'us-east1' => t('us-east1'), 
          'europe-west1' => t('europe-west1'), 
          'asia-east1' => t('asia-east1')
        ),
        '#title' => $this->t('Region'),
        '#default_value' => $this->jobDefault()['job_region'],
      );

      $job['job_scale_tier'] = array(
        '#type' => 'select',
        '#options' => array(
          'BASIC' => t('BASIC'),
          'STANDARD_1' => t('STANDARD_1'),
          'PREMIUM_1' => t('PREMIUM_1'),
          'BASIC_GPU' => t('BASIC_GPU')
        ),
        '#title' => $this->t('Scale Tier'),
        '#default_value' => $this->jobDefault()['job_scale_tier'],
      );

      return $job;

    }

    private function model_fields(){

      $project = $this->entity;

      $form['model_name'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Model Name'),
        '#default_value' => $this->modelDefault()['model_name'],
      );

      $form['model_description'] = array(
        '#type' => 'textarea',
        '#title' => $this->t('Model Description'),
        '#default_value' => $this->modelDefault()['model_description'],
      );

      $form['model_region'] = array(
        '#type' => 'select',
        '#options' => array(
          'us-central1' => t('us-central1'),
          'us-east1' => t('us-east1'),
        ),
        '#title' => $this->t('Region'),
        '#default_value' => $this->modelDefault()['model_region'],
      );
      return $form;
    }

    private function version_fields(){

      $project = $this->entity;

      $form['version_name'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Name'),
        '#default_value' => $this->versionDefault()['version_name'],
      );

      $form['version_default'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Make it default version?'),
        '#default_value' => $this->versionDefault()['version_default'],
      );

      $form['version_description'] = array(
        '#type' => 'textarea',
        '#title' => $this->t('Description'),
        '#default_value' => $this->versionDefault()['version_description'],
      );
  /**
      $form['version_deployment_uri'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Deployment URL'),
        '#default_value' => $this->getValue('version_deployment_uri'),
      );
  **/
      return $form;
    }

  public function jobDefault(){
    $default = array(
      'job_package_uri' => '',
      'job_module' => '',
      'job_train_data_uri' => '',
      'job_test_data_uri' => '',
      'job_verbosity' => 'DEBUG',
      'job_name' => 'drupal_job_'.$this->time,
      'job_train_steps' => 1000,
      'job_output_dir' => 'out_'.$this->time,
      'job_region' => 'us-east1',
      'job_scale_tier' => 'BASIC',
    );

    return $default;
  }

  public function modelDefault(){
    $default = array(
      'model_name' => 'drupal_model_'.$this->time,
      'model_description' => 'Model made with Drupal',
      'model_region' => 'us-central1'
    );

    return $default;
  }

  public function versionDefault(){
    $default =  array(
      'version_name' => 'drupal_version_'.$this->time,
      'version_default' => 1,
      'version_description' => 'Version made with Drupal',
    );

    return $default;
  }

}