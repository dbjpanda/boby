<?php
namespace Drupal\ml_engine\Form\View;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Views;
use Drupal\views\ViewsData;


class TestView extends FormBase {

  public $config;

  public function getFormId() {
    return 'ml_engine_view';
  }

  public function __construct(){
    $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.view');
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    /**
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $node = \Drupal\node\Entity\Node::create(array(
              'type' => 'article',
              'title' => 'The title',
              'langcode' => $language,
              'uid' => 1,
              'status' => 1,
              'body' => array('The body text'),
              'field_date' => array("2000-01-30"),
                //'field_fields' => array('Custom values'), // Add your custon field values like this
        ));
    $node->save();
    **/
    //$view = Views::getView('aggregator_rss_feed');
    $view = views_get_view_result('sample1','page_1');
    //$view_fields = $view->style_plugin->options['columns'];
    //$views_data = ViewsData::get(NULL);

    //print "<pre>";
    //print_r($view[1]->_entity);
    //print "</pre>";
    //die();
    $form['view'] = array(
      '#type' => 'textfield',
      '#title' => t('View name'),
      '#description' => t('Give a view name'),
      '#default' => $this->config->get('view'),
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Get View Details'),
      '#button_type' => 'primary',
    );

    // Print prediction response.
    if ($response = $this->config->get('response')){
        $form['response'] = array(
          '#type' => 'textarea',
          '#title' => $this->t('Response'),
          '#attributes' => array('readonly' => 'readonly'),
          '#default_value' => json_encode($response, JSON_PRETTY_PRINT),    
          '#rows' => 15,
          '#weight' => 100
        );      
    }

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config->delete();
    $view_name = $form_state->getValue('view');
    $this->config ->set('view', $name) ->save();
  }

}
