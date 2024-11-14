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
      '#default_value' => $selected,
      '#ajax' => [
        'callback' => 'ajax_example_autocheckboxes_callback'
        ],
      '#suffix' => '<div id="ajax-selected-flowsheet"></div>',
    ];
    return $form;
  }
  public function ajax_example_autocheckboxes_callback($form, $form_state)
  {
	  $commands = array();
	  $flowsheet_project_default_value = $form_state['values']['howmany_select'];
	  if ($flowsheet_project_default_value != 0)
	  {
		  $form['howmany_select']['#options'] =  array('Please select...' => 'Please select...');
		  $commands[] = ajax_command_html('#ajax-selected-flowsheet', _flowsheet_details($flowsheet_project_default_value));
		  $commands[] = ajax_command_replace('#ajax_selected_flowsheet_action', drupal_render($form['howmany_select']));
		  
	  } 
	  else
	  {
		  $form['howmany_select']['#options'] =  array('Please select...' => 'Please select...');
		  $commands[] = ajax_command_data('#ajax_selected_flowsheet', 'form_state_value_select', $form_state['values']['howmany_select']);
	  }
	  return array(
		  '#type' => 'ajax',
		  '#commands' => $commands
	  );
  }


  public function _flowsheet_details_year_wise()
{
  $flowsheet_years = array(
  '0' => 'Please select...'
);
  $result = \Drupal::database()->query("SELECT from_unixtime(actual_completion_date, '%Y ') as Year from dwsim_flowsheet_proposal WHERE approval_status = 3 ORDER BY Year ASC");
  
  while ($year_wise_list_data = $result->fetchObject())
    {
      $flowsheet_years[$year_wise_list_data->Year] = $year_wise_list_data->Year;
    }
  return $flowsheet_years;
}

public function _flowsheet_details($flowsheet_proposal_id) {
  $output = "";

  // Define the database connection.
  $connection = Database::getConnection();

  // Define and execute the query.
  $query = $connection->select('dwsim_flowsheet_proposal', 'd')
      ->fields('d')
      ->condition('approval_status', 3)
      ->condition('from_unixtime(actual_completion_date, \'%Y\')', $flowsheet_proposal_id, '=');
  $result = $query->execute();
  $records = $result->fetchAll();
  if ($result->rowCount() == 0) {
      $output .= "Work has been completed for the following flow sheets.";
  } 
  else {
      $output .= "Work has been completed for the following flow sheets: " . $result->rowCount() . "<hr>";
      $preference_rows = [];
      $i = 1;
      $url = Url::fromUri('internal:/flowsheeting-project/dwsim-flowsheet-run/' . $row->id);
      $link = Link::fromTextAndUrl($row->project_title, $url)->toString();
      foreach ($result as $row) {
          $completion_date = date("d-M-Y", $row->actual_completion_date);

          // Create the link for each project.
       

          // Add the row data.
          $preference_rows[] = [
              $i,
              Markup::create($link),
              $row->contributor_name,
              $row->university,
              $completion_date
          ];
          $i++;
      }

      $preference_header = [
          'No',
          'Flowsheet Project',
          'Contributor Name',
          'University / Institute',
          'Date of Completion'
      ];

      // Render the table with the theme function.
      $output .= [
          '#theme' => 'table',
          '#header' => $preference_header,
          '#rows' => $preference_rows,
      ];
  }
  var_dump($output);die;
  return $output;
}
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Handle form submission logic here.
    \Drupal::messenger()->addMessage($this->t('Form submitted successfully!'));
  }
}
?>
