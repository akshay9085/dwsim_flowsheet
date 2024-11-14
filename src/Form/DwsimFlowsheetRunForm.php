<?php

/**
 * @file
 * Contains \Drupal\dwsim_flowsheet\Form\DwsimFlowsheetRunForm.
 */

namespace Drupal\dwsim_flowsheet\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class DwsimFlowsheetRunForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dwsim_flowsheet_run_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $options_first = _list_of_flowsheet();
    $url_flowsheet_id = (int) arg(2);
    $flowsheet_data = _flowsheet_information($url_flowsheet_id);
    if ($flowsheet_data == 'Not found') {
      $url_flowsheet_id = '';
    } //$flowsheet_data == 'Not found'
    if (!$url_flowsheet_id) {
      $selected = !$form_state->getValue(['flowsheet']) ? $form_state->getValue(['flowsheet']) : key($options_first);
    } //!$url_flowsheet_id
    elseif ($url_flowsheet_id == '') {
      $selected = 0;
    } //$url_flowsheet_id == ''
    else {
      $selected = $url_flowsheet_id;
    }
    $form = [];
    $form['flowsheet'] = [
      '#type' => 'select',
      '#title' => t('Title of the flowsheet'),
      '#options' => _list_of_flowsheet(),
      '#default_value' => $selected,
      '#ajax' => [
        'callback' => 'dwsim_flowsheet_project_details_callback'
        ],
    ];
    if (!$url_flowsheet_id) {
      $form['flowsheet_details'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_flowsheet_details"></div>',
      ];
      $form['selected_flowsheet'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_selected_flowsheet"></div>',
      ];
    } //!$url_flowsheet_id
    else {
      $flowsheet_default_value = $url_flowsheet_id;
      $form['flowsheet_details'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_flowsheet_details">' . _flowsheet_details($flowsheet_default_value) . '</div>',
      ];
      // @FIXME
      // l() expects a Url object, created from a route name or external URI.
      // $form['selected_flowsheet'] = array(
      // 			'#type' => 'item',
      // 			'#markup' => '<div id="ajax_selected_flowsheet">' . l('Download Abstract', 'flowsheeting-project/download/project-file/' . $flowsheet_default_value) . '<br>' . l('Download Flowsheet', 'flowsheeting-project/full-download/project/' . $flowsheet_default_value,array('attributes' => array('title' => 'This is a zip file containing a pdf (abstract) and a dwxml/dwxmz file which is the DWSIM flow sheet which is to be viewed by right clicking on the file and opening with DWSIM.'))) . '</div>'
      // 		);

    }
    return $form;
  }
  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state){
  }
}
?>
