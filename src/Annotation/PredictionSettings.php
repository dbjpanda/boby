<?php

namespace Drupal\ml_engine\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a PredictionSettings annotation object.
 *
 * @Annotation
 */
class PredictionSettings extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * A brief, human readable, name of the prediction output handler.
   *
   * This property is designated as being translatable because it will appear
   * in the user interface. This provides a hint to other developers that they
   * should use the Translation() construct in their annotation when declaring
   * this property.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
