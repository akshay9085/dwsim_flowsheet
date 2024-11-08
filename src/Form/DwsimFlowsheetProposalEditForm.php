<?php

/**
 * @file
 * Contains \Drupal\dwsim_flowsheet\Form\DwsimFlowsheetProposalEditForm.
 */

namespace Drupal\dwsim_flowsheet\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class DwsimFlowsheetProposalEditForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dwsim_flowsheet_proposal_edit_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    /* get current proposal */
    $proposal_id = (int) arg(3);
    //$proposal_q = db_query("SELECT * FROM {dwsim_flowsheet_proposal} WHERE id = %d", $proposal_id);
    $query = \Drupal::database()->select('dwsim_flowsheet_proposal');
    $query->fields('dwsim_flowsheet_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
      if ($proposal_data = $proposal_q->fetchObject()) {
        /* everything ok */
      } //$proposal_data = $proposal_q->fetchObject()
      else {
        \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
        drupal_goto('flowsheeting-project/manage-proposal');
        return;
      }
    } //$proposal_q
    else {
      \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
      drupal_goto('flowsheeting-project/manage-proposal');
      return;
    }
    $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid);
    $form['name_title'] = [
      '#type' => 'select',
      '#title' => t('Title'),
      '#options' => [
        'Dr' => 'Dr',
        'Prof' => 'Prof',
        'Mr' => 'Mr',
        'Mrs' => 'Mrs',
        'Ms' => 'Ms',
      ],
      '#required' => TRUE,
      '#default_value' => $proposal_data->name_title,
    ];
    $form['contributor_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name of the Proposer'),
      '#size' => 30,
      '#maxlength' => 250,
      '#required' => TRUE,
      '#default_value' => $proposal_data->contributor_name,
    ];
    $form['version'] = [
      '#type' => 'select',
      '#title' => t('Version'),
      '#required' => TRUE,
      '#default_value' => $proposal_data->version,
      '#options' => _df_list_of_software_version(),
    ];
    $form['student_email_id'] = [
      '#type' => 'item',
      '#title' => t('Email'),
      '#markup' => $user_data->mail,
    ];
    $form['contributor_contact_no'] = [
      '#type' => 'textfield',
      '#title' => t('Contact No.'),
      '#size' => 10,
      '#default_value' => $proposal_data->contact_no,
    ];
    $form['month_year_of_degree'] = [
      '#type' => 'date_popup',
      '#title' => t('Month and year of award of degree'),
      '#date_label_position' => '',
      '#description' => '',
      '#default_value' => $proposal_data->month_year_of_degree,
      '#date_format' => 'M-Y',
      '#date_increment' => 0,
      '#date_year_range' => '1960:+22',
    ];
    $form['university'] = [
      '#type' => 'textfield',
      '#title' => t('University/Institute'),
      '#size' => 80,
      '#maxlength' => 200,
      '#default_value' => $proposal_data->university,
    ];
    $form['project_guide_name'] = [
      '#type' => 'textfield',
      '#title' => t('Project guide'),
      '#size' => 250,
      '#default_value' => $proposal_data->project_guide_name,
    ];
    $form['project_guide_email_id'] = [
      '#type' => 'textfield',
      '#title' => t('Project guide email'),
      '#size' => 30,
      '#default_value' => $proposal_data->project_guide_email_id,
    ];
    $form['project_guide_university'] = [
      '#type' => 'textfield',
      '#title' => t('Project Guide University/ Institute'),
      '#size' => 80,
      '#maxlength' => 200,
      '#default_value' => $proposal_data->project_guide_university,
    ];
    if ($proposal_data->country == 'India') {
      $form['country'] = [
        '#type' => 'select',
        '#title' => t('Country'),
        '#options' => [
          'India' => 'India',
          'Others' => 'Others',
        ],
        '#default_value' => $proposal_data->country,
        '#required' => TRUE,
        '#tree' => TRUE,
        '#validated' => TRUE,
      ];
      $form['all_state'] = [
        '#type' => 'select',
        '#title' => t('State'),
        '#options' => _df_list_of_states(),
        '#default_value' => $proposal_data->state,
        '#validated' => TRUE,
        '#states' => [
          'visible' => [
            ':input[name="country"]' => [
              'value' => 'India'
              ]
            ]
          ],
      ];
      $form['city'] = [
        '#type' => 'select',
        '#title' => t('City'),
        '#options' => _df_list_of_cities(),
        '#default_value' => $proposal_data->city,
        '#states' => [
          'visible' => [
            ':input[name="country"]' => [
              'value' => 'India'
              ]
            ]
          ],
      ];
    }
    else {
      $form['other_country'] = [
        '#type' => 'textfield',
        '#title' => t('Country(Other than India)'),
        '#size' => 100,
        '#default_value' => $proposal_data->country,
        '#attributes' => [
          'placeholder' => t('Enter your country name')
          ],
        '#states' => [
          'visible' => [
            ':input[name="country"]' => [
              'value' => 'Others'
              ]
            ]
          ],
      ];
      $form['other_state'] = [
        '#type' => 'textfield',
        '#title' => t('State(Other than India)'),
        '#size' => 100,
        '#attributes' => [
          'placeholder' => t('Enter your state/region name')
          ],
        '#default_value' => $proposal_data->state,
        '#states' => [
          'visible' => [
            ':input[name="country"]' => [
              'value' => 'Others'
              ]
            ]
          ],
      ];
      $form['other_city'] = [
        '#type' => 'textfield',
        '#title' => t('City(Other than India)'),
        '#size' => 100,
        '#attributes' => [
          'placeholder' => t('Enter your city name')
          ],
        '#default_value' => $proposal_data->city,
        '#states' => [
          'visible' => [
            ':input[name="country"]' => [
              'value' => 'Others'
              ]
            ]
          ],
      ];
    }
    $form['pincode'] = [
      '#type' => 'textfield',
      '#title' => t('Pincode'),
      '#size' => 30,
      '#maxlength' => 6,
      '#default_value' => $proposal_data->pincode,
      '#attributes' => [
        'placeholder' => 'Insert pincode of your city/ village....'
        ],
    ];
    $form['project_title'] = [
      '#type' => 'textarea',
      '#title' => t('Title of the Flowsheet Project'),
      '#size' => 300,
      '#maxlength' => 350,
      '#required' => TRUE,
      '#default_value' => $proposal_data->project_title,
    ];
    $form['reference'] = [
      '#type' => 'textarea',
      '#title' => t('Reference of the Flowsheet Project'),
      '#size' => 300,
      '#maxlength' => 350,
      '#default_value' => $proposal_data->reference,
    ];
    $form['process_development_compound_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name of compound for which process development is carried out'),
      '#size' => 50,
      '#default_value' => $proposal_data->process_development_compound_name,
    ];
    $form['process_development_compound_cas_no'] = [
      '#type' => 'textfield',
      '#title' => t('CAS number for compound which process development is carried out'),
      '#size' => 50,
      '#default_value' => $proposal_data->process_development_compound_cas_number,
    ];
    $form['delete_proposal'] = [
      '#type' => 'checkbox',
      '#title' => t('Delete Proposal'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    // @FIXME
    // l() expects a Url object, created from a route name or external URI.
    // $form['cancel'] = array(
    // 		'#type' => 'item',
    // 		'#markup' => l(t('Cancel'), 'flowsheeting-project/manage-proposal')
    // 	);

    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    /* get current proposal */
    $proposal_id = (int) arg(3);
    // $proposal_q = db_query("SELECT * FROM {dwsim_flowsheet_proposal} WHERE id = %d", $proposal_id);
    $query = \Drupal::database()->select('dwsim_flowsheet_proposal');
    $query->fields('dwsim_flowsheet_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q) {
      if ($proposal_data = $proposal_q->fetchObject()) {
        /* everything ok */
      } //$proposal_data = $proposal_q->fetchObject()
      else {
        \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
        drupal_goto('flowsheeting-project/manage-proposal');
        return;
      }
    } //$proposal_q
    else {
      \Drupal::messenger()->addError(t('Invalid proposal selected. Please try again.'));
      drupal_goto('flowsheeting-project/manage-proposal');
      return;
    }
    /* delete proposal */
    if ($form_state->getValue(['delete_proposal']) == 1) {
      /* sending email */
      $user_data = \Drupal::entityTypeManager()->getStorage('user')->load($proposal_data->uid);
      $email_to = $user_data->mail;
      $from = \Drupal::config('dwsim_flowsheet.settings')->get('dwsim_flowsheet_from_email');
      $bcc = \Drupal::config('dwsim_flowsheet.settings')->get('dwsim_flowsheet_emails');
      $cc = \Drupal::config('dwsim_flowsheet.settings')->get('dwsim_flowsheet_cc_emails');
      $params['dwsim_flowsheet_proposal_deleted']['proposal_id'] = $proposal_id;
      $params['dwsim_flowsheet_proposal_deleted']['user_id'] = $proposal_data->uid;
      //$params['dwsim_flowsheet_proposal_deleted']['file_name'] = $_FILES['files']['name'][$file_form_name];
      $params['dwsim_flowsheet_proposal_deleted']['headers'] = [
        'From' => $from,
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
        'Content-Transfer-Encoding' => '8Bit',
        'X-Mailer' => 'Drupal',
        'Cc' => $cc,
        'Bcc' => $bcc,
      ];
      if (!drupal_mail('dwsim_flowsheet', 'dwsim_flowsheet_proposal_deleted', $email_to, user_preferred_language($user), $params, $from, TRUE)) {
        \Drupal::messenger()->addError('Error sending email message.');
      }
      \Drupal::messenger()->addStatus(t('DWSIM Flowsheeting proposal has been deleted.'));
      //db_query("DELETE FROM {dwsim_flowsheet_proposal} WHERE id = %d", $proposal_id);
      if (rrmdir_project($proposal_id) == TRUE) {
        $query = \Drupal::database()->delete('dwsim_flowsheet_proposal');
        $query->condition('id', $proposal_id);
        $num_deleted = $query->execute();
        \Drupal::database()->query("DELETE FROM {dwsim_flowsheet_user_defined_compound} WHERE proposal_id = :proposal_id", [
          ":proposal_id" => $proposal_id
          ]);
        \Drupal::messenger()->addStatus(t('Proposal Deleted'));
        drupal_goto('flowsheeting-project/manage-proposal');
        return;
      } //rrmdir_project($proposal_id) == TRUE
    } //$form_state['values']['delete_proposal'] == 1
	/* update proposal */
    $v = $form_state->getValues();
    $project_title = $v['project_title'];
    $proposar_name = $v['name_title'] . ' ' . $v['contributor_name'];
    $university = $v['university'];
    $directory_names = _df_dir_name($project_title, $proposar_name);
    if (DF_RenameDir($proposal_id, $directory_names)) {
      $directory_name = $directory_names;
    } //LM_RenameDir($proposal_id, $directory_names)
    else {
      return;
    }
    $query = "UPDATE dwsim_flowsheet_proposal SET 
				name_title=:name_title,
				contributor_name=:contributor_name,
				version = :version,
				university=:university,
				city=:city,
				pincode=:pincode,
				state=:state,
				project_title=:project_title,
				reference=:reference,
				directory_name=:directory_name,
				project_guide_university=:project_guide_university,
				project_guide_email_id=:project_guide_email_id,
				project_guide_name=:project_guide_name,
				month_year_of_degree=:month_year_of_degree,
				process_development_compound_name=:process_development_compound_name,
				process_development_compound_cas_number=:process_development_compound_cas_number
				WHERE id=:proposal_id";
    $args = [
      ':name_title' => $v['name_title'],
      ':contributor_name' => $v['contributor_name'],
      ':version' => $v['version'],
      ':university' => $v['university'],
      ':city' => $v['city'],
      ':pincode' => $v['pincode'],
      ':state' => $v['all_state'],
      ':project_title' => $project_title,
      ':reference' => $v['reference'],
      ':directory_name' => $directory_name,
      ':project_guide_university' => $v['project_guide_university'],
      ':project_guide_email_id' => $v['project_guide_email_id'],
      ':project_guide_name' => $v['project_guide_name'],
      ':month_year_of_degree' => $v['month_year_of_degree'],
      ':process_development_compound_name' => $v['process_development_compound_name'],
      ':process_development_compound_cas_number' => $v['process_development_compound_cas_no'],
      ':proposal_id' => $proposal_id,
    ];
    $result = \Drupal::database()->query($query, $args);
    \Drupal::messenger()->addStatus(t('Proposal Updated'));
  }

}
?>
