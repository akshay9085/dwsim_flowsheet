<?php /**
 * @file
 * Contains \Drupal\dwsim_flowsheet\Controller\DefaultController.
 */

namespace Drupal\dwsim_flowsheet\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Default controller for the dwsim_flowsheet module.
 */
class DefaultController extends ControllerBase {

  public function dwsim_flowsheet_proposal_pending() {
    /* get pending proposals to be approved */
    $pending_rows = [];
    //$pending_q = db_query("SELECT * FROM {dwsim_flowsheet_proposal} WHERE approval_status = 0 ORDER BY id DESC");
    $query = \Drupal::database()->select('dwsim_flowsheet_proposal');
    $query->fields('dwsim_flowsheet_proposal');
    $query->condition('approval_status', 0);
    $query->orderBy('id', 'DESC');
    $pending_q = $query->execute();
    while ($pending_data = $pending_q->fetchObject()) {
      // @FIXME
// l() expects a Url object, created from a route name or external URI.
// $pending_rows[$pending_data->id] = array(
// 			date('d-m-Y', $pending_data->creation_date),
// 			l($pending_data->name_title . ' ' . $pending_data->contributor_name, 'user/' . $pending_data->uid),
// 			$pending_data->project_title,
// 			l('Approve', 'flowsheeting-project/manage-proposal/approve/' . $pending_data->id) . ' | ' . l('Edit', 'flowsheeting-project/manage-proposal/edit/' . $pending_data->id)
// 		);

    } //$pending_data = $pending_q->fetchObject()
	/* check if there are any pending proposals */
    if (!$pending_rows) {
      \Drupal::messenger()->addStatus(t('There are no pending proposals.'));
      return '';
    } //!$pending_rows
    $pending_header = [
      'Date of Submission',
      'Student Name',
      'Title of the Flowsheet Project',
      'Action',
    ];
    //$output = theme_table($pending_header, $pending_rows);
    // @FIXME
    // theme() has been renamed to _theme() and should NEVER be called directly.
    // Calling _theme() directly can alter the expected output and potentially
    // introduce security issues (see https://www.drupal.org/node/2195739). You
    // should use renderable arrays instead.
    // 
    // 
    // @see https://www.drupal.org/node/2195739
    // $output = theme('table', array(
    // 		'header' => $pending_header,
    // 		'rows' => $pending_rows
    // 	));

    return $output;
  }

  public function dwsim_flowsheet_proposal_all() {
    /* get pending proposals to be approved */
    $proposal_rows = [];
    //$proposal_q = db_query("SELECT * FROM {dwsim_flowsheet_proposal} ORDER BY id DESC");
    $query = \Drupal::database()->select('dwsim_flowsheet_proposal');
    $query->fields('dwsim_flowsheet_proposal');
    $query->orderBy('id', 'DESC');
    $proposal_q = $query->execute();
    while ($proposal_data = $proposal_q->fetchObject()) {
      $approval_status = '';
      switch ($proposal_data->approval_status) {
        case 0:
          $approval_status = 'Pending';
          break;
        case 1:
          $approval_status = 'Approved';
          break;
        case 2:
          $approval_status = 'Dis-approved';
          break;
        case 3:
          $approval_status = 'Completed';
          break;
        default:
          $approval_status = 'Unknown';
          break;
      } //$proposal_data->approval_status
      if ($proposal_data->actual_completion_date == 0) {
        $actual_completion_date = "Not Completed";
      } //$proposal_data->actual_completion_date == 0
      else {
        $actual_completion_date = date('d-m-Y', $proposal_data->actual_completion_date);
      }
      // @FIXME
      // l() expects a Url object, created from a route name or external URI.
      // $proposal_rows[] = array(
      // 			date('d-m-Y', $proposal_data->creation_date),
      // 			l($proposal_data->contributor_name, 'user/' . $proposal_data->uid),
      // 			$proposal_data->project_title,
      // 			$actual_completion_date,
      // 			$approval_status,
      // 			l('Status', 'flowsheeting-project/manage-proposal/status/' . $proposal_data->id) . ' | ' . l('Edit', 'flowsheeting-project/manage-proposal/edit/' . $proposal_data->id)
      // 		);

    } //$proposal_data = $proposal_q->fetchObject()
	/* check if there are any pending proposals */
    if (!$proposal_rows) {
      \Drupal::messenger()->addStatus(t('There are no proposals.'));
      return '';
    } //!$proposal_rows
    $proposal_header = [
      'Date of Submission',
      'Student Name',
      'Title of the Lab',
      'Date of Completion',
      'Status',
      'Action',
    ];
    // @FIXME
    // theme() has been renamed to _theme() and should NEVER be called directly.
    // Calling _theme() directly can alter the expected output and potentially
    // introduce security issues (see https://www.drupal.org/node/2195739). You
    // should use renderable arrays instead.
    // 
    // 
    // @see https://www.drupal.org/node/2195739
    // $output = theme('table', array(
    // 		'header' => $proposal_header,
    // 		'rows' => $proposal_rows
    // 	));

    return $output;
  }

  public function dwsim_flowsheet_approved_tab() {
    $page_content = "";
    $result = \Drupal::database()->query("SELECT * from dwsim_flowsheet_proposal where id not in (select proposal_id from dwsim_flowsheet_submitted_abstracts) AND approval_status = 1 order by approval_date desc");
    if ($result->rowCount() == 0) {
      $page_content .= "Approved Proposals under Flowsheeting Project<hr>";
    } //$result->rowCount() == 0
    else {
      $page_content .= "Approved Proposals under Flowsheeting Project: " . $result->rowCount() . "<hr>";
      $preference_rows = [];
      $i = 1;
      while ($row = $result->fetchObject()) {
        $approval_date = date("d-M-Y", $row->approval_date);
        $preference_rows[] = [
          $i,
          $row->project_title,
          $row->contributor_name,
          $row->university,
          $approval_date,
        ];
        $i++;
      }
      $preference_header = [
        'No',
        'Flowsheet Project',
        'Contributor Name',
        'University / Institute',
        'Date of Approval',
      ];
      // @FIXME
      // theme() has been renamed to _theme() and should NEVER be called directly.
      // Calling _theme() directly can alter the expected output and potentially
      // introduce security issues (see https://www.drupal.org/node/2195739). You
      // should use renderable arrays instead.
      // 
      // 
      // @see https://www.drupal.org/node/2195739
      // $page_content .= theme('table', array(
      // 			'header' => $preference_header,
      // 			'rows' => $preference_rows
      // 		));

    }
    return $page_content;
  }

  public function dwsim_flowsheet_uploaded_tab() {
    $page_content = "";
    $result = \Drupal::database()->query("SELECT dfp.project_title, dfp.contributor_name, dfp.id, dfp.university, dfa.abstract_upload_date, dfa.abstract_approval_status from dwsim_flowsheet_proposal as dfp JOIN dwsim_flowsheet_submitted_abstracts as dfa on dfa.proposal_id = dfp.id where dfp.id in (select proposal_id from dwsim_flowsheet_submitted_abstracts) AND approval_status = 1");

    if ($result->rowCount() == 0) {
      $page_content .= "Uploaded Proposals under Flowsheeting Project<hr>";
    }
    else {
      $page_content .= "Uploaded Proposals under Flowsheeting Project: " . $result->rowCount() . "<hr>";
      $preference_rows = [];
      $i = 1;
      while ($row = $result->fetchObject()) {
        $abstract_upload_date = date("d-M-Y", $row->abstract_upload_date);
        $preference_rows[] = [
          $i,
          $row->project_title,
          $row->contributor_name,
          $row->university,
          $abstract_upload_date,
        ];
        $i++;
      }
      $preference_header = [
        'No',
        'Flowsheet Project',
        'Contributor Name',
        'University / Institute',
        'Date of file submission',
      ];
      // @FIXME
      // theme() has been renamed to _theme() and should NEVER be called directly.
      // Calling _theme() directly can alter the expected output and potentially
      // introduce security issues (see https://www.drupal.org/node/2195739). You
      // should use renderable arrays instead.
      // 
      // 
      // @see https://www.drupal.org/node/2195739
      // $page_content .= theme('table', array(
      // 			'header' => $preference_header,
      // 			'rows' => $preference_rows
      // 		));

    }
    return $page_content;
  }

  public function dwsim_flowsheet_abstract() {
    $user = \Drupal::currentUser();
    $return_html = "";
    $proposal_data = dwsim_flowsheet_get_proposal();
    if (!$proposal_data) {
      drupal_goto('');
      return;
    } //!$proposal_data
    //$return_html .= l('Upload abstract', 'flowsheeting-project/abstract-code/upload') . '<br />';
	/* get experiment list */
    $query = \Drupal::database()->select('dwsim_flowsheet_submitted_abstracts');
    $query->fields('dwsim_flowsheet_submitted_abstracts');
    $query->condition('proposal_id', $proposal_data->id);
    $abstracts_q = $query->execute()->fetchObject();
    if ($abstracts_q) {
      if ($abstracts_q->is_submitted == 1) {
        \Drupal::messenger()->addError(t('Your abstract is under review, you can not edit exisiting abstract without reviewer permission.'));
        //drupal_goto('flowsheeting-project/abstract-code');
        //return;
      } //$abstracts_q->is_submitted == 1
    } //$abstracts_q
    $query_pro = \Drupal::database()->select('dwsim_flowsheet_proposal');
    $query_pro->fields('dwsim_flowsheet_proposal');
    $query_pro->condition('id', $proposal_data->id);
    $abstracts_pro = $query_pro->execute()->fetchObject();
    $query_pdf = \Drupal::database()->select('dwsim_flowsheet_submitted_abstracts_file');
    $query_pdf->fields('dwsim_flowsheet_submitted_abstracts_file');
    $query_pdf->condition('proposal_id', $proposal_data->id);
    $query_pdf->condition('filetype', 'A');
    $abstracts_pdf = $query_pdf->execute()->fetchObject();
    if ($abstracts_pdf == TRUE) {
      if ($abstracts_pdf->filename != "NULL" || $abstracts_pdf->filename != "") {
        $abstract_filename = $abstracts_pdf->filename;
      } //$abstracts_pdf->filename != "NULL" || $abstracts_pdf->filename != ""
      else {
        $abstract_filename = "File not uploaded";
      }
    } //$abstracts_pdf == TRUE
    else {
      $abstract_filename = "File not uploaded";
    }
    $query_process = \Drupal::database()->select('dwsim_flowsheet_submitted_abstracts_file');
    $query_process->fields('dwsim_flowsheet_submitted_abstracts_file');
    $query_process->condition('proposal_id', $proposal_data->id);
    $query_process->condition('filetype', 'S');
    $abstracts_query_process = $query_process->execute()->fetchObject();
    if ($abstracts_query_process == TRUE) {
      if ($abstracts_query_process->filename != "NULL" || $abstracts_query_process->filename != "") {
        $abstracts_query_process_filename = $abstracts_query_process->filename;
      } //$abstracts_query_process->filename != "NULL" || $abstracts_query_process->filename != ""
      else {
        $abstracts_query_process_filename = "File not uploaded";
      }
      if ($abstracts_q->is_submitted == '') {
        // @FIXME
// l() expects a Url object, created from a route name or external URI.
// $url = l('Upload abstract', 'flowsheeting-project/abstract-code/upload');

      } //$abstracts_q->is_submitted == ''
      else {
        if ($abstracts_q->is_submitted == 1) {
          $url = "";
        } //$abstracts_q->is_submitted == 1
        else {
          if ($abstracts_q->is_submitted == 0) {
            // @FIXME
// l() expects a Url object, created from a route name or external URI.
// $url = l('Edit abstract', 'flowsheeting-project/abstract-code/upload');

          }
        }
      } //$abstracts_q->is_submitted == 0
      if ($abstracts_q->unit_operations_used_in_dwsim == '') {
        $unit_operations_used_in_dwsim = "Not entered";
      } //$abstracts_q->unit_operations_used_in_dwsim == ''
      else {
        $unit_operations_used_in_dwsim = $abstracts_q->unit_operations_used_in_dwsim;
      }
      if ($abstracts_q->thermodynamic_packages_used == '') {
        $thermodynamic_packages_used = "Not entered";
      } //$abstracts_q->thermodynamic_packages_used == ''
      else {
        $thermodynamic_packages_used = $abstracts_q->thermodynamic_packages_used;
      }
      if ($abstracts_q->logical_blocks_used == '') {
        $logical_blocks_used = "Not entered";
      } //$abstracts_q->logical_blocks_used == ''
      else {
        $logical_blocks_used = $abstracts_q->logical_blocks_used;
      }
    } //$abstracts_query_process == TRUE
    else {
      // @FIXME
// l() expects a Url object, created from a route name or external URI.
// $url = l('Upload abstract', 'flowsheeting-project/abstract-code/upload');

      $unit_operations_used_in_dwsim = "Not entered";
      $thermodynamic_packages_used = "Not entered";
      $logical_blocks_used = "Not entered";
      $abstracts_query_process_filename = "File not uploaded";
    }
    $headers = [
      "Name of compound for which process development is carried out",
      "CAS No.",
    ];
    $rows = [];
    $item = [
      "{$proposal_data->process_development_compound_name}",
      "{$proposal_data->process_development_compound_cas_number}"
      ,
    ];
    array_push($rows, $item);
    // @FIXME
    // theme() has been renamed to _theme() and should NEVER be called directly.
    // Calling _theme() directly can alter the expected output and potentially
    // introduce security issues (see https://www.drupal.org/node/2195739). You
    // should use renderable arrays instead.
    // 
    // 
    // @see https://www.drupal.org/node/2195739
    // $prodata = theme('table', array(
    // 		'header' => $headers,
    // 		'rows' => $rows
    // 	));

    $uploaded_user_defined_compound_filepath = basename($proposal_data->user_defined_compound_filepath) ? basename($proposal_data->user_defined_compound_filepath) : "Not uploaded";
    $return_html .= '<strong>Proposer Name:</strong><br />' . $proposal_data->name_title . ' ' . $proposal_data->contributor_name . '<br /><br />';
    $return_html .= '<strong>Title of the Flowsheet Project:</strong><br />' . $proposal_data->project_title . '<br /><br />';
    $return_html .= '<strong>DWSIM version:</strong><br />' . $proposal_data->version . '<br /><br />';
    $return_html .= '<strong>Unit Operations used in DWSIM:</strong><br />' . $unit_operations_used_in_dwsim . '<br /><br />';
    $return_html .= '<strong>Thermodynamic Packages Used:</strong><br />' . $thermodynamic_packages_used . '<br /><br />';
    $return_html .= '<strong>Logical Blocks used:</strong><br />' . $logical_blocks_used . '<br /><br />';
    $return_html .= '<strong>Name of compound for which process development is carried out:</strong><br />' . $prodata . '<br />';
    $return_html .= '<strong>List of compounds from DWSIM Database used in process flowsheet:</strong><br />' . $proposal_data->dwsim_database_compound_name . '<br /><br />';
    $return_html .= '<strong>List of user defined compounds used in process flowsheet:</strong><br />' . _dwsim_flowsheet_list_of_user_defined_compound($proposal_data->id) . '<br />';
    $return_html .= '<strong>Uploaded user defined compound file:</strong><br />' . $uploaded_user_defined_compound_filepath . '<br /><br />';
    $return_html .= '<strong>Uploaded an abstract (brief outline) of the project:</strong><br />' . $abstract_filename . '<br /><br />';
    $return_html .= '<strong>Upload the DWSIM flowsheet for the developed process:</strong><br />' . $abstracts_query_process_filename . '<br /><br />';
    $return_html .= $url . '<br />';
    return $return_html;
  }

  public function dwsim_flowsheet_download_solution_file() {
    $solution_file_id = arg(3);
    $root_path = dwsim_flowsheet_path();
    // $solution_files_q = db_query("SELECT * FROM {dwsim_flowsheet_solution_files} WHERE id = %d LIMIT 1", $solution_file_id);
    $solution_files_q = \Drupal::database()->query("SELECT lmsf.*, lmp.directory_name FROM dwsim_flowsheet_solution_files lmsf JOIN dwsim_flowsheet_solution lms JOIN dwsim_flowsheet_experiment lme JOIN dwsim_flowsheet_proposal lmp WHERE lms.id = lmsf.solution_id AND lme.id = lms.experiment_id AND lmp.id = lme.proposal_id AND lmsf.id = :solution_id LIMIT 1", [
      ':solution_id' => $solution_file_id
      ]);
    $solution_file_data = $solution_files_q->fetchObject();
    header('Content-Type: ' . $solution_file_data->filemime);
    //header('Content-Type: application/octet-stram');
    header('Content-disposition: attachment; filename="' . str_replace(' ', '_', ($solution_file_data->filename)) . '"');
    header('Content-Length: ' . filesize($root_path . $solution_file_data->directory_name . '/' . $solution_file_data->filepath));
    readfile($root_path . $solution_file_data->directory_name . '/' . $solution_file_data->filepath);
  }

  public function dwsim_flowsheet_download_abstract() {
    $proposal_id = arg(3);
    $root_path = dwsim_flowsheet_path();
    //var_dump($proposal_id);die;
    $query = \Drupal::database()->select('dwsim_flowsheet_submitted_abstracts_file');
    $query->fields('dwsim_flowsheet_submitted_abstracts_file');
    $query->condition('proposal_id', $proposal_id);
    $query->condition('filetype', 'A');
    $result = $query->execute();
    $flowsheet_project_files = $result->fetchObject();
    //var_dump($custom_model_project_files);die;
    $query1 = \Drupal::database()->select('dwsim_flowsheet_proposal');
    $query1->fields('dwsim_flowsheet_proposal');
    $query1->condition('id', $proposal_id);
    $result1 = $query1->execute();
    $flowsheet = $result1->fetchObject();
    $directory_name = $flowsheet->directory_name;
    $samplecodename = $flowsheet_project_files->filename;
    ob_clean();
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Type: application/pdf");
    header('Content-disposition: attachment; filename="' . $samplecodename . '"');
    header("Content-Length: " . filesize($root_path . $directory_name . '/' . $samplecodename));
    header("Content-Transfer-Encoding: binary");
    header("Expires: 0");
    header("Pragma: no-cache");
    ob_clean();
    readfile($root_path . $directory_name . '/' . $samplecodename);
    //ob_end_flush();
    //ob_clean();
  }

  public function dwsim_flowsheet_download_solution() {
    $solution_id = arg(3);
    $root_path = dwsim_flowsheet_path();
    $query = \Drupal::database()->select('dwsim_flowsheet_solution');
    $query->fields('dwsim_flowsheet_solution');
    $query->condition('id', $solution_id);
    $solution_q = $query->execute();
    $solution_data = $solution_q->fetchObject();
    $query = \Drupal::database()->select('dwsim_flowsheet_experiment');
    $query->fields('dwsim_flowsheet_experiment');
    $query->condition('id', $solution_data->experiment_id);
    $experiment_q = $query->execute();
    $experiment_data = $experiment_q->fetchObject();
    $solution_files_q = \Drupal::database()->query("SELECT lmsf.*, lmp.directory_name FROM dwsim_flowsheet_solution_files lmsf JOIN dwsim_flowsheet_solution lms JOIN dwsim_flowsheet_experiment lme JOIN dwsim_flowsheet_proposal lmp WHERE lms.id = lmsf.solution_id AND lme.id = lms.experiment_id AND lmp.id = lme.proposal_id AND lmsf.id = :solution_id", [
      ':solution_id' => $solution_id
      ]);
    $query = \Drupal::database()->select('dwsim_flowsheet_solution_dependency');
    $query->fields('dwsim_flowsheet_solution_dependency');
    $query->condition('solution_id', $solution_id);
    $solution_dependency_files_q = $query->execute();
    $CODE_PATH = 'CODE' . $solution_data->code_number . '/';
    /* zip filename */
    $zip_filename = $root_path . 'zip-' . time() . '-' . rand(0, 999999) . '.zip';
    /* creating zip archive on the server */
    $zip = new ZipArchive();
    $zip->open($zip_filename, ZipArchive::CREATE);
    while ($solution_files_row = $solution_files_q->fetchObject()) {
      $zip->addFile($root_path . $solution_files_row->directory_name . '/' . $solution_files_row->filepath, $CODE_PATH . str_replace(' ', '_', ($solution_files_row->filename)));
    }
    /* dependency files */
    while ($solution_dependency_files_row = $solution_dependency_files_q->fetchObject()) {
      $query = \Drupal::database()->select('dwsim_flowsheet_dependency_files');
      $query->fields('dwsim_flowsheet_dependency_files');
      $query->condition('id', $solution_dependency_files_row->dependency_id);
      $query->range(0, 1);
      $dependency_file_data = $query->execute()->fetchObject();
      if ($dependency_file_data) {
        $zip->addFile($root_path . $dependency_file_data->filepath, $CODE_PATH . 'DEPENDENCIES/' . str_replace(' ', '_', ($dependency_file_data->filename)));
      }
    } //$solution_dependency_files_row = $solution_dependency_files_q->fetchObject()
    $zip_file_count = $zip->numFiles;
    $zip->close();
    if ($zip_file_count > 0) {
      /* download zip file */
      header('Content-Type: application/zip');
      header('Content-disposition: attachment; filename="CODE' . $solution_data->code_number . '.zip"');
      header('Content-Length: ' . filesize($zip_filename));
      ob_clean();
      //flush();
      readfile($zip_filename);
      unlink($zip_filename);
    } //$zip_file_count > 0
    else {
      \Drupal::messenger()->addError("There are no files in this solutions to download");
      drupal_goto('lab-migration/lab-migration-run');
    }
  }

  public function dwsim_flowsheet_download_experiment() {
    $experiment_id = (int) arg(3);
    $root_path = dwsim_flowsheet_path();
    /* get solution data */
    $query = \Drupal::database()->select('dwsim_flowsheet_experiment');
    $query->fields('dwsim_flowsheet_experiment');
    $query->condition('id', $experiment_id);
    $experiment_q = $query->execute();
    $experiment_data = $experiment_q->fetchObject();
    $EXP_PATH = 'EXP' . $experiment_data->number . '/';
    /* zip filename */
    $zip_filename = $root_path . 'zip-' . time() . '-' . rand(0, 999999) . '.zip';
    /* creating zip archive on the server */
    $zip = new ZipArchive();
    $zip->open($zip_filename, ZipArchive::CREATE);
    $query = \Drupal::database()->select('dwsim_flowsheet_solution');
    $query->fields('dwsim_flowsheet_solution');
    $query->condition('experiment_id', $experiment_id);
    $query->condition('approval_status', 1);
    $solution_q = $query->execute();
    while ($solution_row = $solution_q->fetchObject()) {
      $CODE_PATH = 'CODE' . $solution_row->code_number . '/';
      $solution_files_q = \Drupal::database()->query("SELECT lmsf.*, lmp.directory_name FROM dwsim_flowsheet_solution_files lmsf JOIN dwsim_flowsheet_solution lms JOIN dwsim_flowsheet_experiment lme JOIN dwsim_flowsheet_proposal lmp WHERE lms.id = lmsf.solution_id AND lme.id = lms.experiment_id AND lmp.id = lme.proposal_id AND lmsf.solution_id = :solution_id", [
        ':solution_id' => $solution_row->id
        ]);
      while ($solution_files_row = $solution_files_q->fetchObject()) {
        $zip->addFile($root_path . $solution_files_row->directory_name . '/' . $solution_files_row->filepath, $EXP_PATH . $CODE_PATH . str_replace(' ', '_', ($solution_files_row->filename)));
      } //$solution_files_row = $solution_files_q->fetchObject()
		/* dependency files */
      $query = \Drupal::database()->select('dwsim_flowsheet_solution_dependency');
      $query->fields('dwsim_flowsheet_solution_dependency');
      $query->condition('solution_id', $solution_row->id);
      $solution_dependency_files_q = $query->execute();
      while ($solution_dependency_files_row = $solution_dependency_files_q->fetchObject()) {
        $query = \Drupal::database()->select('dwsim_flowsheet_dependency_files');
        $query->fields('dwsim_flowsheet_dependency_files');
        $query->condition('id', $solution_dependency_files_row->dependency_id);
        $query->range(0, 1);
        $dependency_file_data = $query->execute()->fetchObject();
        if ($dependency_file_data) {
          $zip->addFile($root_path . $dependency_file_data->filepath, $EXP_PATH . $CODE_PATH . 'DEPENDENCIES/' . str_replace(' ', '_', ($dependency_file_data->filename)));
        }
      } //$solution_dependency_files_row = $solution_dependency_files_q->fetchObject()
    } //$solution_row = $solution_q->fetchObject()
    $zip_file_count = $zip->numFiles;
    $zip->close();
    if ($zip_file_count > 0) {
      /* download zip file */
      header('Content-Type: application/zip');
      header('Content-disposition: attachment; filename="EXP' . $experiment_data->number . '.zip"');
      header('Content-Length: ' . filesize($zip_filename));
      ob_clean();
      //flush();
      readfile($zip_filename);
      unlink($zip_filename);
    } //$zip_file_count > 0
    else {
      \Drupal::messenger()->addError("There are no solutions in this experiment to download");
      drupal_goto('lab-migration/lab-migration-run');
    }
  }

  public function dwsim_flowsheet_download_lab() {
    $user = \Drupal::currentUser();
    $lab_id = arg(3);
    $root_path = dwsim_flowsheet_path();
    /* get solution data */
    $query = \Drupal::database()->select('dwsim_flowsheet_proposal');
    $query->fields('dwsim_flowsheet_proposal');
    $query->condition('id', $lab_id);
    $lab_q = $query->execute();
    $lab_data = $lab_q->fetchObject();
    $LAB_PATH = $lab_data->lab_title . '/';
    /* zip filename */
    $zip_filename = $root_path . 'zip-' . time() . '-' . rand(0, 999999) . '.zip';
    /* creating zip archive on the server */
    $zip = new ZipArchive();
    $zip->open($zip_filename, ZipArchive::CREATE);
    $query = \Drupal::database()->select('dwsim_flowsheet_experiment');
    $query->fields('dwsim_flowsheet_experiment');
    $query->condition('proposal_id', $lab_id);
    $experiment_q = $query->execute();
    while ($experiment_row = $experiment_q->fetchObject()) {
      $EXP_PATH = 'EXP' . $experiment_row->number . '/';
      $query = \Drupal::database()->select('dwsim_flowsheet_solution');
      $query->fields('dwsim_flowsheet_solution');
      $query->condition('experiment_id', $experiment_row->id);
      $query->condition('approval_status', 1);
      $solution_q = $query->execute();
      while ($solution_row = $solution_q->fetchObject()) {
        $CODE_PATH = 'CODE' . $solution_row->code_number . '/';
        $solution_files_q = \Drupal::database()->query("SELECT lmsf.*, lmp.directory_name FROM dwsim_flowsheet_solution_files lmsf JOIN dwsim_flowsheet_solution lms JOIN dwsim_flowsheet_experiment lme JOIN dwsim_flowsheet_proposal lmp WHERE lms.id = lmsf.solution_id AND lme.id = lms.experiment_id AND lmp.id = lme.proposal_id AND lmsf.id = :solution_id", [
          ':solution_id' => $solution_row->id
          ]);
        $query = \Drupal::database()->select('dwsim_flowsheet_solution_dependency');
        $query->fields('dwsim_flowsheet_solution_dependency');
        $query->condition('solution_id', $solution_row->id);
        $solution_dependency_files_q = $query->execute();
        while ($solution_files_row = $solution_files_q->fetchObject()) {
          $zip->addFile($root_path . $solution_files_row->directory_name . '/' . $solution_files_row->filepath, $EXP_PATH . $CODE_PATH . str_replace(' ', '_', ($solution_files_row->filename)));
          //var_dump($zip->numFiles);
        } //$solution_files_row = $solution_files_q->fetchObject()
        // die;
			/* dependency files */
        while ($solution_dependency_files_row = $solution_dependency_files_q->fetchObject()) {
          $query = \Drupal::database()->select('dwsim_flowsheet_dependency_files');
          $query->fields('dwsim_flowsheet_dependency_files');
          $query->condition('id', $solution_dependency_files_row->dependency_id);
          $query->range(0, 1);
          $dependency_file_data = $query->execute()->fetchObject();
          if ($dependency_file_data) {
            $zip->addFile($root_path . $dependency_file_data->filepath, $EXP_PATH . $CODE_PATH . 'DEPENDENCIES/' . str_replace(' ', '_', ($dependency_file_data->filename)));
          }
        } //$solution_dependency_files_row = $solution_dependency_files_q->fetchObject()
      } //$solution_row = $solution_q->fetchObject()
    } //$experiment_row = $experiment_q->fetchObject()
    $zip_file_count = $zip->numFiles;
    $zip->close();
    if ($zip_file_count > 0) {
      if ($user->uid) {
        /* download zip file */
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $lab_data->lab_title) . '.zip"');
        header('Content-Length: ' . filesize($zip_filename));
        ob_clean();
        //flush();
        readfile($zip_filename);
        unlink($zip_filename);
      } //$user->uid
      else {
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $lab_data->lab_title) . '.zip"');
        header('Content-Length: ' . filesize($zip_filename));
        header("Content-Transfer-Encoding: binary");
        header('Expires: 0');
        header('Pragma: no-cache');
        ob_end_flush();
        ob_clean();
        flush();
        readfile($zip_filename);
        unlink($zip_filename);
      }
    } //$zip_file_count > 0
    else {
      \Drupal::messenger()->addError("There are no solutions in this Lab to download");
      drupal_goto('lab-migration/lab-migration-run');
    }
  }

  public function dwsim_flowsheet_download_full_project() {
    $user = \Drupal::currentUser();
    $flowsheet_id = arg(3);
    $root_path = dwsim_flowsheet_path();
    $query = \Drupal::database()->select('dwsim_flowsheet_proposal');
    $query->fields('dwsim_flowsheet_proposal');
    $query->condition('id', $flowsheet_id);
    $flowsheet_q = $query->execute();
    $flowsheet_data = $flowsheet_q->fetchObject();
    $FLOWSHEET_PATH = $flowsheet_data->directory_name . '/';
    /* zip filename */
    $zip_filename = $root_path . 'zip-' . time() . '-' . rand(0, 999999) . '.zip';
    /* creating zip archive on the server */
    $zip = new ZipArchive();
    $zip->open($zip_filename, ZipArchive::CREATE);
    $query = \Drupal::database()->select('dwsim_flowsheet_proposal');
    $query->fields('dwsim_flowsheet_proposal');
    $query->condition('id', $flowsheet_id);
    $flowsheet_udc_q = $query->execute();
    while ($flowsheet_udc_row = $flowsheet_udc_q->fetchObject()) {
      if ($flowsheet_udc_row->user_defined_compound_filepath || $flowsheet_udc_row->user_defined_compound_filepath != 'NULL') {
        $USER_DEFINED_PATH = 'user_defined_compound/';
        $zip->addFile($root_path . $FLOWSHEET_PATH . '/' . $flowsheet_udc_row->user_defined_compound_filepath, $FLOWSHEET_PATH . $USER_DEFINED_PATH . str_replace(' ', '_', basename($flowsheet_udc_row->user_defined_compound_filepath)));
      } //$flowsheet_udc_row->user_defined_compound_filepath || $flowsheet_udc_row->user_defined_compound_filepath != 'NULL'
    } //$flowsheet_udc_row = $flowsheet_udc_q->fetchObject()
    $query = \Drupal::database()->select('dwsim_flowsheet_submitted_abstracts_file');
    $query->fields('dwsim_flowsheet_submitted_abstracts_file');
    $query->condition('proposal_id', $flowsheet_id);
    $flowsheet_f_q = $query->execute();
    while ($flowsheet_f_row = $flowsheet_f_q->fetchObject()) {
      $zip->addFile($root_path . $FLOWSHEET_PATH . '/' . $flowsheet_f_row->filepath, $FLOWSHEET_PATH . str_replace(' ', '_', basename($flowsheet_f_row->filename)));
    } //$flowsheet_f_row = $flowsheet_f_q->fetchObject()
    $zip_file_count = $zip->numFiles;
    $zip->close();
    if ($zip_file_count > 0) {
      if ($user->uid) {
        /* download zip file */
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $flowsheet_data->project_title) . '.zip"');
        header('Content-Length: ' . filesize($zip_filename));
        //ob_end_flush();
        ob_clean();
        //flush();
        readfile($zip_filename);
        unlink($zip_filename);
      } //$user->uid
      else {
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $flowsheet_data->project_title) . '.zip"');
        header('Content-Length: ' . filesize($zip_filename));
        header("Content-Transfer-Encoding: binary");
        header('Expires: 0');
        header('Pragma: no-cache');
        //ob_end_flush();
        ob_clean();
        //flush();
        readfile($zip_filename);
        unlink($zip_filename);
      }
    } //$zip_file_count > 0
    else {
      \Drupal::messenger()->addError("There are no flowsheet project in this proposal to download");
      drupal_goto('flowsheeting-project/full-download/project');
    }
  }

  public function dwsim_flowsheet_download_completed_proposals() {
    $output = "";
    // @FIXME
    // l() expects a Url object, created from a route name or external URI.
    // $output .= "Click ".l("here","/flowsheeting-project/download-proposals-all"). " to download the proposals of the participants" ."<h4>";


    return $output;

  }

  public function dwsim_flowsheet_download_proposals() {
    $root_path = dwsim_flowsheet_path();

    $result = \Drupal::database()->query("SELECT e.contributor_name as contirbutor_name, u.mail as email_id, e.project_title as title, e.contact_no as contact, e.university as university, from_unixtime(creation_date,'%d-%m-%Y') as creation, from_unixtime(approval_date,'%d-%m-%Y') as approval, from_unixtime(actual_completion_date,'%d-%m-%Y') as year, e.approval_status as status FROM dwsim_flowsheet_proposal as e JOIN users as u ON e.uid = u.uid ORDER BY actual_completion_date DESC");

    //var_dump($result->rowCount());die();
    //$all_proposals_q = $result->execute();
    $participants_proposal_id_file = $root_path . "participants-proposals.csv";
    $fp = fopen($participants_proposal_id_file, "w");
    /* making the first row */
    $items = [
      'Contirbutor Name',
      'Email ID',
      'Flowsheet Title',
      'University',
      'Contact',
      'Date of Creation',
      'Date of Approval',
      'Date of Completion',
      'Status of the proposal',
    ];
    fputcsv($fp, $items);
    while ($row = $result->fetchObject()) {
      $status = '';
      switch ($row->status) {
        case 0:
          $status = 'Pending';
          break;
        case 1:
          $status = 'Approved';
          break;
        case 2:
          $status = 'Dis-approved';
          break;
        case 3:
          $status = 'Completed';
          break;
        default:
          $status = 'Unknown';
          break;
      } //$row->status
      if ($row->year == 0) {
        $year = "Not Completed";
      } //$row->year == 0
      else {
        $year = date('d-m-Y', $row->year);
      }

      $items = [
        $row->contirbutor_name,
        $row->email_id,
        $row->title,
        $row->university,
        $row->contact,
        $row->creation,
        $row->approval,
        $row->year,
        $status,
      ];
      fputcsv($fp, $items);
    }
    fclose($fp);
    if ($participants_proposal_id_file) {
      ob_clean();
      header("Pragma: public");
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header("Cache-Control: public");
      header("Content-Description: File Transfer");
      header('Content-Type: application/csv');
      header('Content-disposition: attachment; filename=participants-proposals.csv');
      header('Content-Length:' . filesize($participants_proposal_id_file));
      header("Content-Transfer-Encoding: binary");
      header('Expires: 0');
      header('Pragma: no-cache');
      readfile($participants_proposal_id_file);
      /*ob_end_flush();
            ob_clean();
            flush();*/
    }
  }

  public function dwsim_flowsheet_completed_proposals_all() {
    $output = "";
    $query = \Drupal::database()->select('dwsim_flowsheet_proposal');
    $query->fields('dwsim_flowsheet_proposal');
    $query->condition('approval_status', 3);
    $query->orderBy('actual_completion_date', 'DESC');
    //$query->condition('is_completed', 1);
    $result = $query->execute();
    if ($result->rowCount() == 0) {
      $output .= "Work has been completed for the following flow sheets. We welcome your contributions. For more details, please visit " . \Drupal\Core\Link::fromTextAndUrl("https://dwsim.fossee.in/flowsheeting-project", \Drupal\Core\Url::fromUri("https://dwsim.fossee.in/flowsheeting-project")) . "<br>" . "<h4>" . "If you are looking for flowsheeting project ideas, " . \Drupal\Core\Link::fromTextAndUrl("click here", \Drupal\Core\Url::fromUri("https://dwsim.fossee.in/flowsheeting-ideas")) . "</h4>" . "<hr>";

    } //$result->rowCount() == 0
    else {
      $output .= "Total number of completed flowsheets: " . $result->rowCount() . "<br>";
      $output .= "Work has been completed for the following flow sheets. We welcome your contributions. For more details, please visit " . \Drupal\Core\Link::fromTextAndUrl("https://dwsim.fossee.in/flowsheeting-project", \Drupal\Core\Url::fromUri("https://dwsim.fossee.in/flowsheeting-project")) . "<br>" . "<h4>" . "If you are looking for flowsheeting project ideas, " . \Drupal\Core\Link::fromTextAndUrl("click here", \Drupal\Core\Url::fromUri("https://dwsim.fossee.in/flowsheeting-ideas")) . "</h4>" . "<hr>";
      $preference_rows = [];
      $i = $result->rowCount();
      while ($row = $result->fetchObject()) {
        $completion_date = date("Y", $row->actual_completion_date);
        // @FIXME
        // l() expects a Url object, created from a route name or external URI.
        // $preference_rows[] = array(
        // 				$i,
        // 				l($row->project_title, "flowsheeting-project/dwsim-flowsheet-run/" . $row->id, array('attributes' => array('title' => 'This is a zip file containing a pdf (abstract) and a dwxml/dwxmz file which is the DWSIM flow sheet which is to be viewed by right clicking on the file and opening with DWSIM.'))),
        // 				$row->contributor_name,
        // 				$row->university,
        // 				$completion_date
        // 			);

        $i--;
      } //$row = $result->fetchObject()
      $preference_header = [
        'No',
        'Flowsheet Project',
        'Contributor Name',
        'University / Institute',
        'Year of Completion',
      ];
      // @FIXME
      // theme() has been renamed to _theme() and should NEVER be called directly.
      // Calling _theme() directly can alter the expected output and potentially
      // introduce security issues (see https://www.drupal.org/node/2195739). You
      // should use renderable arrays instead.
      // 
      // 
      // @see https://www.drupal.org/node/2195739
      // $output .= theme('table', array(
      // 			'header' => $preference_header,
      // 			'rows' => $preference_rows
      // 		));

    }
    return $output;
  }

  public function dwsim_flowsheet_progress_all() {
    $page_content = "";
    $query = \Drupal::database()->select('dwsim_flowsheet_proposal');
    $query->fields('dwsim_flowsheet_proposal');
    $query->condition('approval_status', 1);
    $query->condition('is_completed', 0);
    $query->orderBy('approval_date', DESC);
    $result = $query->execute();
    if ($result->rowCount() == 0) {
      $page_content .= "Work is in progress for the following flowsheets under Flowsheeting Project<hr>";
    } //$result->rowCount() == 0
    else {
      $page_content .= "Work is in progress for the following flowsheets under Flowsheeting Project<hr>";
      ;
      $preference_rows = [];
      $i = $result->rowCount();
      while ($row = $result->fetchObject()) {
        $approval_date = date("Y", $row->approval_date);
        $preference_rows[] = [
          $i,
          $row->project_title,
          $row->contributor_name,
          $row->university,
          $approval_date,
        ];
        $i--;
      } //$row = $result->fetchObject()
      $preference_header = [
        'No',
        'Flowsheet Project',
        'Contributor Name',
        'University / Institute',
        'Year',
      ];
      // @FIXME
      // theme() has been renamed to _theme() and should NEVER be called directly.
      // Calling _theme() directly can alter the expected output and potentially
      // introduce security issues (see https://www.drupal.org/node/2195739). You
      // should use renderable arrays instead.
      // 
      // 
      // @see https://www.drupal.org/node/2195739
      // $page_content .= theme('table', array(
      // 			'header' => $preference_header,
      // 			'rows' => $preference_rows
      // 		));

    }
    return $page_content;
  }

  public function dwsim_flowsheet_download_user_defined_compound() {
    $proposal_id = arg(3);
    $root_path = dwsim_flowsheet_document_path();
    $query = \Drupal::database()->select('dwsim_flowsheet_proposal');
    $query->fields('dwsim_flowsheet_proposal');
    $query->condition('id', $proposal_id);
    $query->range(0, 1);
    $result = $query->execute();
    $dwsim_flowsheet_user_compund_data = $result->fetchObject();
    $samplecodename = substr($dwsim_flowsheet_user_compund_data->user_defined_compound_filepath, strrpos($dwsim_flowsheet_user_compund_data->user_defined_compound_filepath, '/') + 1);
    header('Content-Type: txt/zip');
    header('Content-disposition: attachment; filename="' . $samplecodename . '"');
    header('Content-Length: ' . filesize($root_path . $dwsim_flowsheet_user_compund_data->directory_name . '/' . $dwsim_flowsheet_user_compund_data->user_defined_compound_filepath));
    ob_clean();
    readfile($root_path . $dwsim_flowsheet_user_compund_data->directory_name . '/' . $dwsim_flowsheet_user_compund_data->user_defined_compound_filepath);
  }

}
