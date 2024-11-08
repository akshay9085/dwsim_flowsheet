<?php

/**
 * @file
 * Contains \Drupal\dwsim_flowsheet\Form\DwsimFlowsheetCompletedTabForm.
 */

namespace Drupal\dwsim_flowsheet\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class DwsimFlowsheetCompletedTabForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dwsim_flowsheet_completed_tab_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $options_first = _flowsheet_details_year_wise();
    $selected = !$form_state->getValue(['howmany_select']) ? $form_state->getValue(['howmany_select']) : key($options_first);
    $form = [];
    $form['howmany_select'] = [
      '#title' => t('Sorting projects according to year:'),
      '#type' => 'select',
      '#options' => _flowsheet_details_year_wise(),
      /*'#options' => array(
    	'Please select...' => 'Please select...',
    	'2017' => '2017',
    	'2018' => '2018', 
    	'2019' => '2019', 
    	'2020' => '2020', 
    	'2021' => '2021'),*/
      '#default_value' => $selected,
      '#ajax' => [
        'callback' => 'ajax_example_autocheckboxes_callback'
        ],
      '#suffix' => '<div id="ajax-selected-flowsheet"></div>',
    ];
    return $form;
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Handle form submission logic here.
    \Drupal::messenger()->addMessage($this->t('Form submitted successfully!'));
  }
}
?>
