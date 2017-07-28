<?php

namespace Drupal\ml_engine\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ml_engine\PredictionSettingsPluginManager;


class ProjectPredict extends EntityForm {

  public $predictionOutputManager;

  /**
   * Constructs an ProjectForm object.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query.
   */
  public function __construct(QueryFactory $entity_query, PredictionSettingsPluginManager $prediction_output_manager) {
    $this->entityQuery = $entity_query;
    $this->time = time();
    $this->predictionOutputManager = $prediction_output_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('plugin.manager.ml_engine_prediction_output_settings')
    );
  }

  public function form(array $form, FormStateInterface $form_state) {

    $project = $this->entity;

    $form['data'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('JSON'),
      '#required' => TRUE,
      '#default_value' => $project->get_prediction_input(),
      '#rows' => 15    
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Get Prediction'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {  
    $project = $this->entity;
    $data = $form_state->getValue("data");
    $project->set_prediction_input($data)->save();
    $plugin = $this->predictionOutputManager->createInstance($project->get_prediction_plugin());

    $model_name = $project->model_name();
    $data_array = ["instances" => json_decode($data,true)];

    $status = \Drupal::service('ml_engine.predict')->predict($model_name,$data_array);

    if($status['success']){
      if(array_key_exists('modelData',$status['response'])){
        $predictions = $status['response']['modelData']['predictions'][0];  
        $formatted_prediction = $plugin->format($predictions);
        drupal_set_message($formatted_prediction,'status');
      }else{
        drupal_set_message('Error check the prediction arguments', 'error');
      }
      return;
    }else{
      drupal_set_message($status['response']['message'], 'error');
      return;
    }

  }


}
