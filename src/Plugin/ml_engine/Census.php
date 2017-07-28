<?php

namespace Drupal\ml_engine\Plugin\ml_engine;

use Drupal\ml_engine\PredictionSettingsBase;

/**
 * @PredictionSettings(
 *   id = "census",
 *   label = @Translation("Census")
 * )
 */
class Census extends PredictionSettingsBase {

  /**
   * @param array $extras
   *   Array of extras to include with this order.
   *
   * @return string
   *   A description of the sandwich ordered.
   */
  public function format(array $item) {

    $probabilities = $item['probabilities'];
    print "<pre>";
    print_r($probabilities);
    print "</pre>";
    
    $index = array_keys($probabilities, max($probabilities))[0];

    if($index == 0){
      return "His income is lesser than $50K";
    }else{
      return "His income is greater than $50K";
    }
  }

}
