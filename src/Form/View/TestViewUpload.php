<?php
namespace Drupal\ml_engine\Form\View;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class TestViewUpload extends FormBase {

  public $config;

  public function getFormId() {
    return 'ml_engine_job_get';
  }

  public function __construct(){
    $this->config = \Drupal::configFactory()->getEditable('ml_engine.test.view.upload');
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    /**
      $result = \Drupal::entityQuery('node')
      ->condition('type', 'test1')
      ->range(0, 1000)
      ->execute();
      entity_delete_multiple('node', $result);
    **/
    $form['file_path'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('File Path'),
      '#required' => TRUE,
      '#default_value' => $this->config->get('file_path'),
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    );


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  
    $this->config->delete();
    $path = $form_state->getValue('file_path');
    $this->config ->set('file_path', $form_state->getValue('file_path')) ->save();

/**
    $field_map = array(
      "field_age",
      "field_workclass",
      "field_fnlwgt",
      "field_education",
      "field_education_num",      
      "field_marital_status",
      "field_occupation",
      "field_relationship",
      "field_race",
      "field_gender",
      "field_capital_gain",
      "field_capital_loss",
      "field_hours_per_week",
      "field_native_country",
      "field_income"
      );
**/ 
    $file = fopen("$path","r");

    print "<pre>";

    $count = 1;
    while(! feof($file)) {
      $row = fgetcsv($file);
      $this->create_node($row, $count);
      $count++;
    }

    print "</pre>";

    fclose($file);
  }
/**
  function create_node($field_map, $row){
      print "<hr>";
      for ($i=0; $i<sizeof($row); $i++){
        print $field_map[$i]. " => " . $row[$i];
        print "<br>";
      }
  }
**/

  function create_node($row, $count = 0){

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    
    $node_array = array(
              'type' => 'test1',
              'title' => 'Census data '.$count,
              'langcode' => $language,
              'uid' => 1,
              'status' => 1,
              'field_date' => array("2000-01-30"),
              "field_age" => array($row[0]),
              "field_workclass" => array($row[1]),
              "field_fnlwgt" => array($row[2]),
              "field_education" => array($row[3]),
              "field_education_num" => array($row[4]),      
              "field_marital_status" => array($row[5]),
              "field_occupation" => array($row[6]),
              "field_relationship" => array($row[7]),
              "field_race" => array($row[8]),
              "field_gender" => array($row[9]),
              "field_capital_gain" => array($row[10]),
              "field_capital_loss" => array($row[11]),
              "field_hours_per_week" => array($row[12]),
              "field_native_country" => array($row[13]),
              "field_income" => array($row[14])
        );

    $node = \Drupal\node\Entity\Node::create($node_array);
    $node->save();

  }




}
