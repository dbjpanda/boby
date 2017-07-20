<?php

namespace Drupal\ml_engine\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\ConfigFormBaseTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base form for ML Engine Project edit forms.
 */
class ProjectEdit extends EntityForm implements ContainerInjectionInterface {
    use ConfigFormBaseTrait;

    /**
     * Constructs a new MLEngineProjectEditForm.
     */
    public function __construct() {
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static();
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function form(array $form, FormStateInterface $form_state) {
        $form = parent::form($form, $form_state);

        $project = $this->entity;

        $form['label'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Label'),
            '#maxlength' => 255,
            '#default_value' => $project->label(),
            '#required' => TRUE,
        ];


        $form['id'] = [
            '#type' => 'machine_name',
            '#default_value' => $project->id(),
            '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
            '#machine_name' => [
                'exists' => '\Drupal\ml_engine\Entity\Project::load',
            ],
            '#disabled' => !$project->isNew(),
        ];

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
    public function validateForm(array &$form, FormStateInterface $form_state) {
        parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $form, FormStateInterface $form_state) {
        $contact_form = $this->entity;
        $status = $contact_form->save();

        $view_link = $contact_form->link($contact_form->label(), 'canonical');
        if ($status == SAVED_UPDATED) {
            drupal_set_message($this->t('Contact form %label has been updated.', ['%label' => $view_link]));
        }
        else {
            drupal_set_message($this->t('Contact form %label has been added.', ['%label' => $view_link]));
        }

        $form_state->setRedirectUrl($contact_form->urlInfo('collection'));
    }

  private function job_fields(){

    $job['job_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
#      '#default_value' => $this->getValue('job_name'),
    );

    $job['job_train_steps'] = array(
      '#type' => 'number',
      '#title' => $this->t('Train Steps'),
 #     '#default_value' => $this->getValue('job_train_steps'),
    );
    $job['job_output_dir'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Output Directory'),
#      '#default_value' => $this->getValue('job_output_dir'),
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
#      '#default_value' => $this->getValue('job_region'),
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
#      '#default_value' => $this->getValue('job_scale_tier'),
    );

    return $job;

  }

  private function model_fields(){

    $form['model_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Model Name'),
#      '#default_value' => $this->getValue('model_name'),
    );

    $form['model_description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Model Description'),
#      '#default_value' => $this->getValue('model_description'),
    );

    $form['model_region'] = array(
      '#type' => 'select',
      '#options' => array(
        'us-central1' => t('us-central1'),
        'us-east1' => t('us-east1'),
      ),
      '#title' => $this->t('Region'),
#      '#default_value' => $this->getValue('model_region'),
    );
    return $form;
  }

  private function version_fields(){
    $form['version_name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
#      '#default_value' => $this->getValue('version_name'),
    );

    $form['version_default'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Make it default version?'),
#      '#default_value' => $this->getValue('version_default'),
    );

    $form['version_description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
#      '#default_value' => $this->getValue('version_description'),
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

}
