<?php

namespace Drupal\ml_engine;

interface PredictionSettingsInterface {

  /**
   * Provide a label for the prediction response handler.
   *
   * @return string
   *   A string description of the sandwich.
   */
  public function label();

  /**
   * Place an order for a sandwich.
   *
   * This is just an example method on our plugin that we can call to get
   * something back.
   *
   * @param array $extras
   *   An array of extra ingredients to include with this sandwich.
   *
   * @return string
   *   Description of the sandwich that was just ordered.
   */
  public function format(array $extras);

}
