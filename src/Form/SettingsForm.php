<?php

namespace Drupal\ml_engine\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure site information settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ml_engine_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ml_engine.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ml_engine.settings');

    $form['credential'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Credential json'),
      '#required' => TRUE,
      '#rows'=>20,
      '#description'=> t('Please enter the <a href="https://developers.google.com/identity/protocols/OAuth2ServiceAccount">service account</a> credential for Google Cloud Platform.'),
      '#default_value' => $config->get('credential')
    ];

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('ml_engine.settings')
      ->set('credential', $form_state->getValue('credential'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
