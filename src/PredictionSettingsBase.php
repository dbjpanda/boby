<?php

namespace Drupal\ml_engine;

use Drupal\Component\Plugin\PluginBase;


abstract class PredictionSettingsBase extends PluginBase implements PredictionSettingsInterface {

  /**
   * {@inheritdoc}
   */
  public function label() {
    // Retrieve the @label property from the annotation and return it.
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  abstract public function format(array $extras);

}
