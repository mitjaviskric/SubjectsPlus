<?php
use SubjectsPlus\Control\Querier;
use SubjectsPlus\Control\Guide;
use SubjectsPlus\Control\Record; 

error_reporting(E_ERROR);
//start session
//session_start();
// Give them a permission ordinarily bequeathed by header.php
//$_SESSION["eresource_mgr"] = 1;

// Setting the Content-Type header with charset

header('Content-type: text/html; charset=UTF-8');

$subcat = "records";
$subsubcat = "index.php";
$page_title = "Browse Items";

//if (isset($_GET["secret_id"]) && $_GET["secret_id"] == "prestuu4UFrE9uKucrUsp-wepRAdRa!EbRU24") {
  include("../../includes/config.php");
  include("../../includes/functions.php");
  include("../../includes/autoloader.php");
  
 // include("../includes/classes/sp_Guide.php");
/*
  try {
    $dbc = new sp_DBConnector($uname, $pword, $dbName_SPlus, $hname);
  } catch (Exception $e) {
    echo $e;
  }
} else {

  exit;
  include("../includes/header.php");
}
*/



////////////////////
// File Locations //
////////////////////

$update_file = "erm_data/DisplayList.txt";
$delete_file = "erm_data/RemoveList.txt";
$message = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"
\"http://www.w3.org/TR/html4/loose.dtd\"><html>";


$staff_owner_id = '381';

////////////////////////////////////
// Check if our DELETE file exists
////////////////////////////////////

if (!file_exists($delete_file)) {
  // file doesn't exist; abort
  $message .= "<h3>Delete file ($delete_file)</h3>
  The file $delete_file does not exist";
} else {

  // check when file last modded
  $last_mod_delete = date("F d Y H:i:s.", filemtime($delete_file));
  $last_mod_delete_unix = filemtime($delete_file);

  // let's find out something about file size / # of records
  $delete_file_size = filesize($delete_file);
  $del_records = file($delete_file);
  $delete_record_count = count($del_records);

  if ($delete_record_count > 0) {
    $message .= "<h3>Delete file ($delete_file)</h3>
<p>Last Modified: $last_mod_delete</p>
<p>File Size: $delete_file_size</p>
<p># of Records to delete (if still present): $delete_record_count</p>";
    $message .= deleteData($del_records);
  }
}

/////////////////////
// Check if our UPDATE file exists
/////////////////////

if (!file_exists($update_file)) {
	// file doesn't exist; abort
	$message .= "The file $update_file does not exist";
} else {
	// check when file last modded
	$last_mod_update = date("F d Y H:i:s.", filemtime($update_file));
	$last_mod_update_unix = filemtime($update_file);

	// let's find out something about file size / # of records
	$update_file_size = filesize($update_file);
	$records = file($update_file);
	$update_record_count = count($records);
	$message .= "<h3>Update file ($update_file)</h3>
<p>Last Modified: $last_mod_update</p>
<p>File Size: $update_file_size</p>
<p># of Records to Insert/Update: $update_record_count</p>";

	if ($update_record_count > 0) {
		//print $message;

		/* added by david:
		   ** append to message what functions returned
		*/
		$message .= "<br />" . getAllSubs($records);
		$message .= "<br />" . ingestData($records);
	}
}

$message .= '</html>';

print "$message<p>$last_mod_update_unix" ;

/* added by david:
** remove for testing purpose only
*/
$to  = 'agdarby@gmail.com,j.little@miami.edu';
//$to  = 'david.gonzalez036@gmail.com';
$subject = 'ERM Update Data';

// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\n";

// Additional headers
$headers .= 'From: UM Libraries WET <agdarby@miami.edu>' . "\n";


// Mail it
//mail($to, $subject, $message, $headers);
/////////////////////////////
// Local Functions
// Other functions from functions.php are used!	
// as are sp_Guide and sp_Record classes
//////////////////////////////

function ingestData($records) {

  $return_message = '';

  foreach ($records as $line_num => $line) {
    $this_line = explode("|", $line);

  	//if array does not contain 12 elements, got to next line and report that currrent line contains garbage
  	if(count($this_line) != 11 )
  	{
  		$return_message .= "Field count does not equal 12. Line $line_num was skipped. <br />";

  		next;
  	}

    // Fields
	
	// e10010373
    $erm_id = trim($this_line[0]);

    // Clean up titles
	// ASTM Standards and Engineering Digital Library
    $erm_title = trim($this_line[1]);
    $erm_title = utf8_encode($erm_title);
	
	


  	if($erm_title == '')
  	{
  		$return_message .= 'Record cotained blank title and was skipped.<br />';

  		next;
  	}

    // Alternate Title info
    $erm_alt_title = trim($this_line[2]);
    $erm_alt_title = utf8_encode($erm_alt_title);

    // use only second description?
	
	// With support from the German Research Foundation, the Codices Electronici Ecclesiae Coloniensis (CEEC) is 
	// working to digitize the full medieval manuscript holdings of the Episcopal and Cathedral Library Cologne, 
	// which includes manuscripts of medieval music. (Note: to view the site in English, click on the "Optionen" tab, then select "English.")
	
    $erm_description = trim($this_line[3]);
    $erm_description = explode("^", $erm_description);
    $erm_description = $erm_description[0];
    $erm_description = utf8_encode($erm_description);

	
	// https://iiiprxy.library.miami.edu/login?url=http://apsjournals.apsnet.org/search
    $erm_url = trim($this_line[4]);
    // remove proxy if present

    $erm_url = preg_replace('/https:\/\/iiiprxy\.library\.miami\.edu\/login\?url=/', '', $erm_url);

	// Trial Database:  End Date -  08-11-2012    <a href="mailto:d.roose@miami.edu">Provide Feedback</a>
    $erm_trial = trim($this_line[5]);
	
	// UM Restricted
    $erm_access = trim($this_line[6]);
	
	// Government Documents^Political Science^Multidisciplinary^International Studies
    $erm_subjects = trim($this_line[7]);
	
	// Abstract/Citation/Index^Mobile Enabled
    $erm_labels = trim($this_line[8]);
	
	
	// ?
    $erm_note = trim($this_line[9]);
	
	// -, t, 
    $erm_is_trial = trim($this_line[10]);
	
	
	/*
    $erm_subj_cluster = trim($this_line[11]);
    print "our cluster is: $erm_subj_cluster . <br />";
    // add our trial to the labels field
    if ($erm_is_trial == "t") {
      $erm_labels .= "^Database_Trial";
    }

    // add our cluster to subject field
    switch ($erm_subj_cluster) {
      case 1:
        $erm_subjects = $erm_subjects . "^General";
        break;
      case 2:
        $erm_subjects = $erm_subjects . "^Social Sciences";
        break;
      case 3:
        $erm_subjects = $erm_subjects . "^Science";
        break;
      case 4:
        $erm_subjects = $erm_subjects . "^Humanities";
        break;
    }

	*/
	
	
    //print $erm_subjects;
	//print "</br>";
    // put subjects into array
    
	$erm_subjects = explode("^", $erm_subjects);

    $erm_labels = explode("^", $erm_labels);

    // insert into records
    $updater = modifydb($erm_id, $erm_title, $erm_description, $erm_url, $erm_trial, $erm_access, $erm_subjects, $erm_labels, $erm_note, $erm_alt_title);

  	$return_message .= $updater . "<br />";
  }

	return $return_message;
}

function modifydb($erm_id, $erm_title, $erm_description, $erm_url, $erm_trial, $erm_access, $erm_subjects, $erm_labels, $erm_note, $erm_alt_title) {

  // init our message to be returned
  $message = "";

  $_POST["title_id"] = "";
  $_POST["title"] = $erm_title;
  $_POST["alternate_title"] = $erm_alt_title;
  $_POST["description"] = $erm_description;
  $_POST["prefix"] = "";

  // data stored in location table
  $_POST["location_id"] = array(""); // array
  $_POST["location"] = array("$erm_url"); // array
  $_POST["call_number"] = array("$erm_id"); // array
  $_POST["format"] = array("1"); // array INT

  // Determine access restrictions
  switch ($erm_access) {
    case "UM Restricted":
      $_POST["access_restrictions"] = array("2");
      break;
    case "UM Restricted No Proxy":
      $_POST["access_restrictions"] = array("4");
      break;
    default:
      $_POST["access_restrictions"] = array("1");
  }

  if ($erm_note != "") {
    $_POST["display_note"] = array("$erm_note"); // array
  } else {
    $_POST["display_note"] = array("$erm_trial"); // array
  }

  $_POST["eres_display"] = array("Y"); // array
  $ingest_ctags = ""; // array
  // Set up some empty arrays
  $_POST["subject"] = array();
  $_POST["rank"] = array();
  $_POST["source"] = array();
  $_POST["description_override"] = array();
  $_POST["helpguide"] = array();

  // data stored in rank table
  $i = 0;
  foreach ($erm_subjects as $value) {
    // look up subject_id -- aargh!
    $shortie = preg_replace("/[^A-Za-z0-9]/", "", $value);
    $q = "SELECT `subject_id` FROM `subject` WHERE shortform = '$shortie'";
	
	$db = new Querier;
    $r = $db->query($q);

	//	var_dump ($r);
    // if we have a match, populate the $_POST array values to be read by the
    // sp_Record functions

    if (!empty($r)) {
      $this_subject_id = $r[0];

      $_POST["subject"][$i] = $this_subject_id[0];
      $_POST["rank"][$i] = 1;
      $_POST["source"][$i] = 1;
      $_POST["description_override"][$i] = "";
      $_POST["helpguide"][$i] = "";
      $i++;
    }
  }

  $_POST["ctags"] = "";

  // Let's check if "new databases" is a subject; if so, we'll make this a ctag
  if (in_array("New Databases", $erm_subjects)) {
    $ingest_ctags = array("New_Databases");
  } else {
    $ingest_ctags = array();
  }


  foreach ($erm_labels as $value) {

    // remove any goofy final semi-colons
    $value = preg_replace('/;$/', "", $value);

    switch ($value) {
      case "Full-Text Database":
      case "contains full text":
      case "Database (Contains Full Text)":
      case "Books (Contains Full Text)":
      case "Full-Text": // preferred ?
        $ingest_ctags[] = "full_text";
        break;
      case "Government Documents":
        $ingest_ctags[] = "Government_Documents";
        break;
      case "Images":
      case "Image Database":
      case "Image": // preferred
        $ingest_ctags[] = "images";
        break;
      case "News":
      case "News & Newspapers":
        $ingest_ctags[] = "News_and_Newspapers";
        break;
      case "Papers": // preferred
        $ingest_ctags[] = "Papers";
        break;

      case "Primary Sources":
      case "Primary Source Documents": // preferred
        $ingest_ctags[] = "Primary_Source_Documents";
        break;
      case "Proceedings": // preferred
        $ingest_ctags[] = "Proceedings";
        break;
      case "Reference": // preferred
        $ingest_ctags[] = "Reference";
        break;
      case "Standards": // preferred
        $ingest_ctags[] = "Standards";
        break;
      case "A & I Database":
      case "Abstract/Citation Database":
      case "Indexes & Abstracts":
      case "Abstract/Citation/Index": // preferred?

        $ingest_ctags[] = "Abstract/Citation/Index";
        break;

      case "E-books":
      case "E-Text":
      case "E-Text Collection":
      case "Electronic Books":
      case "Electronic Texts":
      case "E-Book": // preferred?
      case "E-Book Collection": // preferred?
        $ingest_ctags[] = "E-Books";
        break;
      case "E-Music":
      case "Audio": // preferred?
        $ingest_ctags[] = "audio";
        break;
      case "Music Scores": // preferred?
        $ingest_ctags[] = "Music_Scores";
        break;
      case "Maps": // pref
        $ingest_ctags[] = "Maps";
        break;
      case "E-Video":
      case "Video": // PREF
        $ingest_ctags[] = "video";
        break;
      case "Mobile Enabled": //pref
        $ingest_ctags[] = "Mobile_Enabled";
        break;
      case "Statistics":
      case "Statistics & Data": // pref
      case "Data Set":
        $ingest_ctags[] = "Statistics_and_Data";
        break;
      case "Dissertations & Theses":
      case "Dissertations_and_Theses": // preferred ?
        $ingest_ctags[] = "thesis";
        break;
      case "E-Journal Collection":
      case "E-Journal":
        $ingest_ctags[] = "E-Journals";
        break;
      case "Database_Trial":
        $ingest_ctags[] = "Database_Trial";
        break;
    }
  }

  // de-dupe
  $ingest_ctags = array_unique($ingest_ctags);

  $data = "";

  foreach ($ingest_ctags as $value) {
    $data .= "$value|";
  }
  // remove final pipe
  $data = preg_replace('/\|$/', "", $data);

  $_POST["ctags"] = array("$data");

  $qcheck = "SELECT title_id, location.location_id FROM location, location_title WHERE location.location_id = location_title.location_id AND call_number = '$erm_id'";
  // print "</br>";
  // print $qcheck;
  
	$db = new Querier;
	$rcheck = $db->query($qcheck);

  if (isset($rcheck)) {
  
   //  $myrow = $db->fetch($qcheck);

	//echo $myrow . "\n";
	// var_dump($_POST);
	
   $num_rows = count($rcheck);

    if ($num_rows == 0) {
      $message .= "Add Record for <strong>" . $_POST["title"] . "</strong><br />\n";
      $item = new Record("", "post");

      //the insertRecord method echos only when a error occurs. If the method
      //echos something, add error message to return message
      ob_start();
      $item->insertRecord(1);
      $return = ob_get_contents();
      ob_end_clean();

      if($return != '')
      {
      	//$message .= $return . "<br />\n";
      }

      //print $item->deBug();
    } else {
	
		
      $_POST["title_id"] = $rcheck[0][0];
      $_POST["location_id"] = $rcheck[0][1];

     // print "title = " . $myrow[0] . "-- location_id = " . $myrow[1];
      $message .= "Update Record for <strong>" . $_POST["title"] . "</strong><br />\n";
      
	  
	  $item = new Record("", "post");

		
      //the updateRecord method echos only when a error occurs. If the method
      //echos something, add error message to return message
      ob_start();
      $item->updateRecord(1);
  	  $return = ob_get_contents();
      ob_end_clean();

      if($return != '')
      {
      	$message .= $return . "\n";
      }

     // print $item->deBug();
    }
  }

 
  return $message;
}

function getAllSubs($records, $insert = "") {
  global $staff_owner_id;
  $message = "";

  $all_erm_subjects = array();
  // loop through .txt file
  foreach ($records as $line_num => $line) {
    $this_line = explode("|", $line);

  	//if array does not contain 12 elements, got to next line and report that currrent line contains garbage
  	if(count($this_line) != 11)
  	{
  		$return_message .= "Field count does not equal 12. Line $line_num was skipped. <br />";

  		next;
  	}

    $erm_subjects = trim($this_line[7]);
    $erm_subjects = explode("^", $erm_subjects);

    if (!empty($erm_subjects)) {
      $all_erm_subjects = array_merge($all_erm_subjects, $erm_subjects);
    }
  }

  // Now work with this array
  foreach ($all_erm_subjects as $value) {
    // edit out items that begin "Database" and empty ones
    if (!preg_match("/^Database/", $value) && $value != "") {
      $new_subs[] = $value;

      $_POST["subject_id"] = "";
      $_POST["subject"] = $value;
      $shortie = preg_replace("/[^A-Za-z0-9]/", "", $value);
      $_POST["shortform"] = $shortie;
      $_POST["active"] = 1;
      $_POST["type"] = "Subject";
      $_POST["extra"] = '70';


      // data stored in staff_subject table
      $_POST["staff_id"] = array($staff_owner_id);

	  
	
	  // This is actually a subject/guide not a record?
	  
	  
      $record = new Guide("", "post");


	  //the insertRecord method echos only when a error occurs. If the method
      //echos something, add error message to return message
     // ob_start();
      $record->insertRecord(1);
 //     $return = ob_get_contents();
    //  ob_end_clean();

      if($record->getMessage() != 'There is already a guide with this SHORTFORM.  The shortform must be unique.')
      {
      	$message .= "Add Subject for <strong>" . $_POST["subject"] . "</strong><br />\n";
      }

      if($return != '')
      {
      	$message .= $return . "<br />\n";
      }

	  //print $record->deBug();
    }
  }


/*
$new_subs = array_unique($new_subs);
  sort($new_subs);
  $message = $new_subs;
*/
  return $message;
}

function deleteData($records) {
  $message = "";
  foreach ($records as $line_num => $line) {
    $this_line = explode("|", $line);

  	//if array does not contains 12 elements, got to next line and report that currrent line contains garbage
  	if(count($this_line) != 11)
  	{
  		$return_message .= "Field count does not equal 12. Line $line_num was skipped. <br />";

  		next;
  	}

    // Fields
    $erm_id = trim($this_line[0]);

    $qcheck = "SELECT title_id, location.location_id FROM location, location_title WHERE location.location_id = location_title.location_id AND call_number = '$erm_id'";
    //print $qcheck . "<br />";
	
	$db = new Querier;
	
	
    $rcheck = $db->query($qcheck);

	/*
	echo "RCHECCCCCCCCK\n\n";
	var_dump($rcheck);
	echo "\n\n";
	*/
	
    if (!empty($rcheck)) {
	
	//var_dump ($rcheck);
      $myrow = $recheck;

      $num_rows = count($rcheck);

      if ($num_rows != 0) {
        $message .= "Delete Record ID: $erm_id<br />\n";
        $record = new Record($myrow[0]['subject_id'], "delete");
        $record->deleteRecord();
        $record->deBug();
        // Show feedback
        //$message .= $record->getMessage();
      }
    }
  }
  return $message;
}

unset($_COOKIE['eresource_mgr']);
// Finally, destroy the session.