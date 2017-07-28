<?php

namespace Drupal\ml_engine\Plugin\ml_engine;

use Drupal\ml_engine\PredictionSettingsBase;

/**
 * @PredictionSettings(
 *   id = "classification",
 *   label = @Translation("Classification")
 * )
 */
class Classification extends PredictionSettingsBase {

  /**
   * @param array $extras
   *   Array of extras to include with this order.
   *
   * @return string
   *   A description of the sandwich ordered.
   */
  public function format(array $item) {

    $probabilities = $item['probabilities'];
    
    $index = array_keys($probabilities, max($probabilities))[0];

    return 'Class '.(string)$index;
  }

}
