<?php

namespace Drupal\ml_engine\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ml_engine\PredictionSettingsPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for our example pages.
 */
class PredictionSettingsController extends ControllerBase {

  protected $predictionOutputManager;

  public function __construct(PredictionSettingsPluginManager $prediction_output_manager) {
    $this->predictionOutputManager = $prediction_output_manager;
  }

  public function description() {
    $build = array();

    $definitions = $this->predictionOutputManager->getDefinitions();

    $items = array();
    foreach ($definitions as $definition) {
      $items[] = t("@id ,@label", array(
        '@id' => $definition['id'],
        '@label' => $definition['label'],
      ));
    }

    // Add our list to the render array.
    $build['plugin_definitions'] = array(
      '#theme' => 'item_list',
      '#title' => 'Prediction Output Definitions',
      '#items' => $items,
    );


    $items = array();
    foreach ($definitions as $plugin_id => $definition) {
      $plugin = $this->predictionOutputManager->createInstance($plugin_id);
      $items[] = $plugin->label();
    }

    $build['plugins'] = array(
      '#theme' => 'item_list',
      '#title' => 'Prediction plugins',
      '#items' => $items,
    );

    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * Override the parent method so that we can inject our sandwich plugin
   * manager service into the controller.
   *
   * For more about how dependency injection works read
   * https://www.drupal.org/node/2133171
   *
   * @see container
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.ml_engine_prediction_output_settings'));
  }

}
