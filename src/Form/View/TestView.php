<?php
namespace Drupal\ml_engine\Form\View;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Views;
use Drupal\views\ViewsData;
use GuzzleHttp\Exception\RequestException;


class TestView extends FormBase {

  public $config;

  public function getFormId() {
    return 'ml_engine_view';
  }

  public function __construct(){
    $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.view');
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['url'] = array(
      '#type' => 'textfield',
      '#title' => t('URL'),
      '#description' => t('Give a view url'),
      '#default' => $this->config->get('url'),
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
    $url = $form_state->getValue('url');
    $this->config->set('url', $url) ->save();

    $client = \Drupal::httpClient();
    $url = $GLOBALS['base_url'].$url;
    $response = $client->get($url);
    $data = $response->getBody()->getContents();
    $code = $response->getStatusCode();
    $header = $response->getHeaders();
    $data = explode(PHP_EOL,$data);
    print "<pre>";
    //print_r(implode(PHP_EOL, array_slice($data,2, -1)));
    $csv = implode(PHP_EOL, array_slice($data,2, -1));
    $storage_service = \Drupal::service('ml_engine.storage')->upload($csv, 'data_drupal.csv');
    print "</pre>";

  }

}
