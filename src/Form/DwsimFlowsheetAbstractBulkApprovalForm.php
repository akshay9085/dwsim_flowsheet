<?php

/**
 * @file
 * Contains \Drupal\dwsim_flowsheet\Form\DwsimFlowsheetAbstractBulkApprovalForm.
 */

namespace Drupal\dwsim_flowsheet\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class DwsimFlowsheetAbstractBulkApprovalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dwsim_flowsheet_abstract_bulk_approval_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $options_first = _bulk_list_of_flowsheet_project();
    $selected = !$form_state->getValue(['flowsheet_project']) ? $form_state->getValue([
      'flowsheet_project'
      ]) : key($options_first);
    $form = [];
    $form['flowsheet_project'] = [
      '#type' => 'select',
      '#title' => t('Title of the flowsheeting project'),
      '#options' => _bulk_list_of_flowsheet_project(),
      '#default_value' => $selected,
      '#ajax' => [
        'callback' => 'ajax_bulk_flowsheet_abstract_details_callback'
        ],
      '#suffix' => '<div id="ajax_selected_flowsheet"></div><div id="ajax_selected_flowsheet_pdf"></div>',
    ];
    $form['flowsheet_actions'] = [
      '#type' => 'select',
      '#title' => t('Please select action for Flowsheeting project'),
      '#options' => _bulk_list_flowsheet_actions(),
      '#default_value' => 0,
      '#prefix' => '<div id="ajax_selected_flowsheet_action" style="color:red;">',
      '#suffix' => '</div>',
      '#states' => [
        'invisible' => [
          ':input[name="flowsheet_project"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => t('If Dis-Approved please specify reason for Dis-Approval'),
      '#prefix' => '<div id= "message_submit">',
      '#states' => [
        'visible' => [
          [
            ':input[name="flowsheet_actions"]' => [
              'value' => 3
              ]
            ],
          'or',
          [':input[name="flowsheet_actions"]' => ['value' => 4]],
        ]
        ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
      '#states' => [
        'invisible' => [
          ':input[name="lab"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $msg = '';
    $root_path = dwsim_flowsheet_document_path();
    if ($form_state->get(['clicked_button', '#value']) == 'Submit') {
      if ($form_state->getValue(['flowsheet_project']))
        // dwsim_flowsheet_abstract_del_lab_pdf($form_state['values']['flowsheet_project']);
 {
        if (\Drupal::currentUser()->hasPermission('dwsim flowsheet bulk manage abstract')) {
          $query = \Drupal::database()->select('dwsim_flowsheet_proposal');
          $query->fields('dwsim_flowsheet_proposal');
          $query->condition('id', $form_state->getValue(['flowsheet_project']));
          $user_query = $query->execute();
          $user_info = $user_query->fetchObject();
          $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($user_info->uid);
          if ($form_state->getValue(['flowsheet_actions']) == 1) {
            // approving entire project //
            $query = \Drupal::database()->select('dwsim_flowsheet_submitted_abstracts');
            $query->fields('dwsim_flowsheet_submitted_abstracts');
            $query->condition('proposal_id', $form_state->getValue(['flowsheet_project']));
            $abstracts_q = $query->execute();
            $experiment_list = '';
            while ($abstract_data = $abstracts_q->fetchObject()) {
              \Drupal::database()->query("UPDATE {dwsim_flowsheet_submitted_abstracts} SET abstract_approval_status = 1, approver_uid = :approver_uid WHERE id = :id", [
                ':approver_uid' => $user->uid,
                ':id' => $abstract_data->id,
              ]);
              \Drupal::database()->query("UPDATE {dwsim_flowsheet_submitted_abstracts_file} SET file_approval_status = 1, approvar_uid = :approver_uid WHERE submitted_abstract_id = :submitted_abstract_id", [
                ':approver_uid' => $user->uid,
                ':submitted_abstract_id' => $abstract_data->id,
              ]);
            } //$abstract_data = $abstracts_q->fetchObject()
            \Drupal::messenger()->addStatus(t('Approved Flosheeting project.'));
            // email 
            // @FIXME
            // // @FIXME
            // // This looks like another module's variable. You'll need to rewrite this call
            // // to ensure that it uses the correct configuration object.
            // $email_subject = t('[!site_name][Flowsheeting Project] Your uploaded flowsheeting project have been approved', array(
            // 						'!site_name' => variable_get('site_name', '')
            // 					));

            // @FIXME
            // // @FIXME
            // // This looks like another module's variable. You'll need to rewrite this call
            // // to ensure that it uses the correct configuration object.
            // $email_body = array(
            // 						0 => t('
            // 
            // Dear ' . $user_info->contributor_name . ',
            // 
            // Congratulations!
            // Your DWSIM flowsheet and abstract with the following details have been approved.
            // 
            // Full Name: ' . $user_info->name_title . ' ' . $user_info->contributor_name . '
            // Project Title: ' . $user_info->project_title . '
            // Name of compound for which process development is carried out  : ' . $user_info->process_development_compound_name . '
            // 
            // Kindly send us the internship forms as early as possible for processing your honorarium on time. In case you have already sent these forms, please share the the consignment number or tracking id with us.
            // 
            // Note: It will take upto 30 days from the time we receive your forms, to process your honorarium.
            // 
            // 
            // Best Wishes,
            // 
            // !site_name Team
            // FOSSEE, IIT Bombay', array(
            // 							'!site_name' => variable_get('site_name', ''),
            // 							'!user_name' => $user_data->name
            // 						))
            // 					);

            /** sending email when everything done **/
            $email_to = $user_data->mail;
            $from = \Drupal::config('dwsim_flowsheet.settings')->get('dwsim_flowsheet_from_email');
            $bcc = \Drupal::config('dwsim_flowsheet.settings')->get('dwsim_flowsheet_emails');
            $cc = \Drupal::config('dwsim_flowsheet.settings')->get('dwsim_flowsheet_cc_emails');
            $params['standard']['subject'] = $email_subject;
            $params['standard']['body'] = $email_body;
            $params['standard']['headers'] = [
              'From' => $from,
              'MIME-Version' => '1.0',
              'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
              'Content-Transfer-Encoding' => '8Bit',
              'X-Mailer' => 'Drupal',
              'Cc' => $cc,
              'Bcc' => $bcc,
            ];
            if (!drupal_mail('dwsim_flowsheet', 'standard', $email_to, language_default(), $params, $from, TRUE)) {
              $msg = \Drupal::messenger()->addError('Error sending email message.');
            } //!drupal_mail('dwsim_flowsheet', 'standard', $email_to, language_default(), $params, $from, TRUE)
          } //$form_state['values']['flowsheet_actions'] == 1
          elseif ($form_state->getValue(['flowsheet_actions']) == 2) {
            //pending review entire project 
            $query = \Drupal::database()->select('dwsim_flowsheet_submitted_abstracts');
            $query->fields('dwsim_flowsheet_submitted_abstracts');
            $query->condition('proposal_id', $form_state->getValue(['flowsheet_project']));
            $abstracts_q = $query->execute();
            $experiment_list = '';
            while ($abstract_data = $abstracts_q->fetchObject()) {
              \Drupal::database()->query("UPDATE {dwsim_flowsheet_submitted_abstracts} SET abstract_approval_status = 0, approver_uid = :approver_uid WHERE id = :id", [
                ':approver_uid' => $user->uid,
                ':id' => $abstract_data->id,
              ]);
              \Drupal::database()->query("UPDATE {dwsim_flowsheet_submitted_abstracts_file} SET file_approval_status = 0, approvar_uid = :approver_uid WHERE submitted_abstract_id = :submitted_abstract_id", [
                ':approver_uid' => $user->uid,
                ':submitted_abstract_id' => $abstract_data->id,
              ]);
            } //$abstract_data = $abstracts_q->fetchObject()
            \Drupal::messenger()->addStatus(t('Approved Flosheeting project.'));
            // email 
            // @FIXME
            // // @FIXME
            // // This looks like another module's variable. You'll need to rewrite this call
            // // to ensure that it uses the correct configuration object.
            // $email_subject = t('[!site_name][Flowsheeting Project] Your uploaded flowsheeting project have been marked as pending', array(
            // 						'!site_name' => variable_get('site_name', '')
            // 					));

            // @FIXME
            // // @FIXME
            // // This looks like another module's variable. You'll need to rewrite this call
            // // to ensure that it uses the correct configuration object.
            // $email_body = array(
            // 						0 => t('
            // 
            // Dear ' . $user_info->contributor_name . ',
            // 
            // Your all the uploaded flowsheeting project with Title : ' . $user_info->project_title . ' have been marked as pending to be reviewed.
            // 
            // You will be able to see the flowsheeting project after approved by one of our reviewers.
            // 
            // Best Wishes,
            // 
            // !site_name Team
            // FOSSEE, IIT Bombay', array(
            // 							'!site_name' => variable_get('site_name', ''),
            // 							'!user_name' => $user_data->name
            // 						))
            // 					);

            /** sending email when everything done **/
            $email_to = $user_data->mail;
            $from = \Drupal::config('dwsim_flowsheet.settings')->get('dwsim_flowsheet_from_email');
            $bcc = \Drupal::config('dwsim_flowsheet.settings')->get('dwsim_flowsheet_emails');
            $cc = \Drupal::config('dwsim_flowsheet.settings')->get('dwsim_flowsheet_cc_emails');
            $params['standard']['subject'] = $email_subject;
            $params['standard']['body'] = $email_body;
            $params['standard']['headers'] = [
              'From' => $from,
              'MIME-Version' => '1.0',
              'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
              'Content-Transfer-Encoding' => '8Bit',
              'X-Mailer' => 'Drupal',
              'Cc' => $cc,
              'Bcc' => $bcc,
            ];
            if (!drupal_mail('dwsim_flowsheet', 'standard', $email_to, language_default(), $params, $from, TRUE)) {
              \Drupal::messenger()->addError('Error sending email message.');
            } //!drupal_mail('dwsim_flowsheet', 'standard', $email_to, language_default(), $params, $from, TRUE)
          } //$form_state['values']['flowsheet_actions'] == 2
          elseif ($form_state->getValue(['flowsheet_actions']) == 3) //disapprove and delete entire flowsheeting project
 {
            if (strlen(trim($form_state->getValue(['message']))) <= 30) {
              $form_state->setErrorByName('message', t(''));
              $msg = \Drupal::messenger()->addError("Please mention the reason for disapproval. Minimum 30 character required");
              return $msg;
            } //strlen(trim($form_state['values']['message'])) <= 30
            if (!\Drupal::currentUser()->hasPermission('dwsim flowsheet bulk delete abstract')) {
              $msg = \Drupal::messenger()->addError(t('You do not have permission to Bulk Dis-Approved and Deleted Entire Lab.'));
              return $msg;
            } //!user_access('flowsheet bulk delete code')
            if (dwsim_flowsheet_abstract_delete_project($form_state->getValue(['flowsheet_project']))) //////
 {
              \Drupal::messenger()->addStatus(t('Dis-Approved and Deleted Entire Flowsheeting project.'));
              // @FIXME
              // // @FIXME
              // // This looks like another module's variable. You'll need to rewrite this call
              // // to ensure that it uses the correct configuration object.
              // $email_subject = t('[!site_name][Flowsheeting Project] Your uploaded flowsheeting project have been marked as dis-approved', array(
              // 						'!site_name' => variable_get('site_name', '')
              // 						));

              // @FIXME
              // // @FIXME
              // // This looks like another module's variable. You'll need to rewrite this call
              // // to ensure that it uses the correct configuration object.
              // $email_body = array(
              // 						0 => t('
              // 
              // Dear ' . $user_info->contributor_name . ',
              // 
              // We regret to inform you that your DWSIM flowsheet and abstract with the following details have been disapproved:
              // 
              // Full Name: ' . $user_info->name_title . ' ' . $user_info->contributor_name . '
              // Project Title: ' . $user_info->project_title . '
              // Name of compound for which process development is carried out  : ' . $user_info->process_development_compound_name . '
              // Reason for dis-approval: ' . $form_state['values']['message'] . '
              // 
              // Best Wishes,
              // 
              // !site_name Team
              // FOSSEE, IIT Bombay', array(
              // 					'!site_name' => variable_get('site_name', ''),
              // 					'!user_name' => $user_data->name
              // 					))
              // 						);

              $email_to = $user_data->mail;
              $from = \Drupal::config('dwsim_flowsheet.settings')->get('dwsim_flowsheet_from_email');
              $bcc = \Drupal::config('dwsim_flowsheet.settings')->get('dwsim_flowsheet_emails');
              $cc = \Drupal::config('dwsim_flowsheet.settings')->get('dwsim_flowsheet_cc_emails');
              $params['standard']['subject'] = $email_subject;
              $params['standard']['body'] = $email_body;
              $params['standard']['headers'] = [
                'From' => $from,
                'MIME-Version' => '1.0',
                'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
                'Content-Transfer-Encoding' => '8Bit',
                'X-Mailer' => 'Drupal',
                'Cc' => $cc,
                'Bcc' => $bcc,
              ];
              if (!drupal_mail('dwsim_flowsheet', 'standard', $email_to, language_default(), $params, $from, TRUE)) {
                \Drupal::messenger()->addError('Error sending email message.');
              }
            } //dwsim_flowsheet_abstract_delete_project($form_state['values']['flowsheet_project'])
            else {
              \Drupal::messenger()->addError(t('Error Dis-Approving and Deleting Entire flowsheeting project.'));
            }
          }//$form_state['values']['flowsheet_actions'] == 3
          elseif ($form_state->getValue(['flowsheet_actions']) == 4) {
            if (strlen(trim($form_state->getValue(['message']))) <= 30) {
              $form_state->setErrorByName('message', t(''));
              $msg = \Drupal::messenger()->addError("Please mention the reason for disapproval/deletion. Minimum 30 character required");
              return $msg;
            } //strlen(trim($form_state['values']['message'])) <= 30
            $query = \Drupal::database()->select('dwsim_flowsheet_abstract_experiment');
            $query->fields('dwsim_flowsheet_abstract_experiment');
            $query->condition('proposal_id', $form_state->getValue(['lab']));
            $query->orderBy('number', 'ASC');
            $experiment_q = $query->execute();
            $experiment_list = '';
            while ($experiment_data = $experiment_q->fetchObject()) {
              $experiment_list .= '<p>' . $experiment_data->number . ') ' . $experiment_data->title . '<br> Description :  ' . $experiment_data->description . '<br>';
              $experiment_list .= ' ';
              $experiment_list .= '</p>';
            } //$experiment_data = $experiment_q->fetchObject()
            if (!\Drupal::currentUser()->hasPermission('lab migration bulk delete code')) {
              $msg = \Drupal::messenger()->addError(t('You do not have permission to Bulk Delete Entire Lab Including Proposal.'));
              return $msg;
            } //!user_access('lab migration bulk delete code')
            // check if dependency files are present 
            $dep_q = \Drupal::database()->query("SELECT * FROM {dwsim_flowsheet_abstract_dependency_files} WHERE proposal_id = :proposal_id", [
              ":proposal_id" => $form_state->getValue(['lab'])
              ]);
            if ($dep_data = $dep_q->fetchObject()) {
              $msg = \Drupal::messenger()->addError(t("Cannot delete lab since it has dependency files that can be used by others. First delete the dependency files before deleting the lab."));
              return $msg ;
            } //$dep_data = $dep_q->fetchObject()
            if (dwsim_flowsheet_abstract_delete_lab($form_state->getValue(['lab']))) {
              \Drupal::messenger()->addStatus(t('Dis-Approved and Deleted Entire Lab solutions.'));
              $query = \Drupal::database()->select('dwsim_flowsheet_abstract_experiment');
              $query->fields('dwsim_flowsheet_abstract_experiment');
              $query->condition('proposal_id', $form_state->getValue(['lab']));
              $experiment_q = $query->execute()->fetchObject();
              $dir_path = $root_path . $experiment_q->directory_name;
              if (is_dir($dir_path)) {
                $res = rmdir($dir_path);
                if (!$res) {
                  $msg = \Drupal::messenger()->addError(t("Cannot delete Lab directory : " . $dir_path . ". Please contact administrator."));
                  return $msg;
                } //!$res
              } //is_dir($dir_path)
              else {
                \Drupal::messenger()->addStatus(t("Lab directory not present : " . $dir_path . ". Skipping deleting lab directory."));
              }
              //deleting full proposal 
              //$proposal_q = db_query("SELECT * FROM {dwsim_flowsheet_abstract_proposal} WHERE id = %d", $form_state['values']['lab']);
              $proposal_q = \Drupal::database()->query("SELECT * FROM {dwsim_flowsheet_abstract_proposal} WHERE id = :id", [
                ":id" => $form_state->getValue(['lab'])
                ]);
              $proposal_data = $proposal_q->fetchObject();
              $proposal_id = $proposal_data->id;
              \Drupal::database()->query("DELETE FROM {dwsim_flowsheet_abstract_experiment} WHERE proposal_id = :proposal_id", [
                ":proposal_id" => $proposal_id
                ]);
              \Drupal::database()->query("DELETE FROM {dwsim_flowsheet_abstract_proposal} WHERE id = :id", [
                ":id" => $proposal_id
                ]);
              \Drupal::messenger()->addStatus(t('Deleted Lab Proposal.'));
              //email 
              // @FIXME
              // // @FIXME
              // // This looks like another module's variable. You'll need to rewrite this call
              // // to ensure that it uses the correct configuration object.
              // $email_subject = t('[!site_name] Your uploaded Lab Migration solutions including the Lab proposal have been deleted', array(
              // 							'!site_name' => variable_get('site_name', '')
              // 						));

              // @FIXME
              // // @FIXME
              // // This looks like another module's variable. You'll need to rewrite this call
              // // to ensure that it uses the correct configuration object.
              // $email_body = array(
              // 							0 => t('
              // 
              // Dear !user_name,
              // 
              // We regret to inform you that all the uploaded Experiments of your Lab with following details have been deleted permanently.
              // 
              // Title of Lab :' . $user_info->lab_title . '
              // 
              // List of experiments : ' . $experiment_list . '
              // 
              // Reason for dis-approval: ' . $form_state['values']['message'] . '
              // 
              // Best Wishes,
              // 
              // !site_name Team,
              // FOSSEE,IIT Bombay', array(
              // 								'!site_name' => variable_get('site_name', ''),
              // 								'!user_name' => $user_data->name
              // 							))
              // 						);

              // email 
              //  $email_subject = t('Your uploaded Lab Migration solutions including the Lab proposal have been deleted');
              $email_body = [
                0 => t('Your all the uploaded solutions including the Lab proposal have been deleted permanently.')
                ];
            } //dwsim_flowsheet_abstract_delete_lab($form_state['values']['lab'])
            else {
              $msg = \Drupal::messenger()->addError(t('Error Dis-Approving and Deleting Entire Lab.'));
            }
          } //$form_state['values']['flowsheet_actions'] == 4
          else {
            $msg = \Drupal::messenger()->addError(t('You do not have permission to bulk manage code.'));
          }
        }
      } //user_access('flowsheet project bulk manage code')
      return $msg;
    } //$form_state['clicked_button']['#value'] == 'Submit'
  }

}
?>
