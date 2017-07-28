<?php
namespace Drupal\ml_engine\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ml_engine\PredictionSettingsPluginManager;

/**
 * Form handler for the ML Engine Project add and edit forms.
 */
class PredictionSettings extends EntityForm {

  public $time;

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

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $project = $this->entity;
    $definitions = $this->predictionOutputManager->getDefinitions();
    $options = array();
    foreach ($definitions as $definition) {
      $options[$definition['id']] = $definition['label'];
    }

    $form['formatter'] = array(
      '#type' => 'select',
      '#options' => $options,
      '#title' => $this->t('Select prediction formatter'),
      '#default_value' => $project->get_prediction_plugin(),
    );

/**
    $form['field_list'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Input Field List'),
      '#description' => 'Please enter the fields required for prediction input in the order separated by ","',
      '#required' => TRUE,
      '#default_value' =>  implode(",", $project->get_prediction_field_list() ?: array()),
    );
**/
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $project = $this->entity;
    $project->set_prediction_plugin($form_state->getValue("formatter"))->save();
    //$project->set_prediction_field_list($form_state->getValue("field_list"))->save();
  }

}