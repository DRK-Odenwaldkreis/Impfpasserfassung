<?php

/* **************

Websystem für das Impfpasszentrum
Author: Marc S. Duchene
June 2021

Edit documents of registered person

** ************** */

include_once 'preload.php';
if( isset($GLOBALS['G_sessionname']) ) { session_name ($GLOBALS['G_sessionname']); }
session_start();
$sec_level=1;
$current_site="docs_person";

// Include functions
include_once 'tools.php';
include_once 'auth.php';
include_once 'menu.php';


// Print html header
echo $GLOBALS['G_html_header'];

// Print html menu
echo $GLOBALS['G_html_menu'];
echo $GLOBALS['G_html_menu2'];

// Print html content part A
echo $GLOBALS['G_html_main_right_a'];

// role check
if( A_checkpermission(array(1,2,0,4,5)) ) {


  // Open database connection
  $Db=S_open_db();
  $errorhtml0 ='';


  if( isset($_POST['upload_file']) ) {
    // ///////////////
    // file save in database
    // ///////////////
      // Save file from upload

    if (!empty($_FILES)) {

      $file_content=file_get_contents($_FILES['userfile']['tmp_name']);
      $doc_class=$_POST['doc_class'];
      $v_id=A_sanitize_input($_POST['person_id']);


      if($doc_class!="") {
          
        // write data to database
        S_set_data($Db,'INSERT INTO Nachweise (Vorgangs_id,Beschreibung,Anhang)
        VALUES (CAST('.$v_id.' as int), \''.$doc_class.'\', \''.base64_encode($file_content).'\');');


        $errorhtml0 = H_build_boxinfo( 0, "Datei wurde erfolgreich hochgeladen.", 'green' );

      } else {
          $errorhtml0 = H_build_boxinfo( 0, "Keine Dokumenten-Art gewählt.", 'red' );
      }
        
    } else {
      $errorhtml0 = H_build_boxinfo( 0, "Dokumenten-Fehler", 'red' );

    }

    
    
  } elseif( isset($_GET['deldoc']) && isset($_GET['id']) ) {
    $doc_id=A_sanitize_input($_GET['deldoc']);
    $v_id=A_sanitize_input($_GET['id']);
    S_set_data($Db,'DELETE FROM Nachweise WHERE id=CAST('.$doc_id.' as int) AND Vorgangs_id=CAST('.$v_id.' as int);');
  }

  if( isset($_GET['id']) || ( isset($_POST['upload_file']) && isset($_POST['person_id']) ) ) {

    if( isset($_GET['id']) ) {
      $v_id=A_sanitize_input($_GET['id']);
    } else {
      $v_id=A_sanitize_input($_POST['person_id']);
    }
    
    // Get data
    $array_vorgang=S_get_multientry($Db,'SELECT id,Teststation,0, Registrierungszeitpunkt,Vorname,Nachname,Geburtsdatum,Adresse,Wohnort,0,Mailadresse,Erstimpfstoff_id, Zweitimpfstoff_id, Erstimpfung, Zweitimpfung FROM Vorgang WHERE id=CAST('.$v_id.' AS int);');
    $array_docs=S_get_multientry($Db,'SELECT id,Beschreibung,Updated FROM Nachweise WHERE Vorgangs_id=CAST('.$v_id.' AS int) ORDER BY Updated DESC;');
    


  
    echo '<div class="row">';
    echo '<div class="col-sm-12">
    <h3>Kunden-Akte von '.$array_vorgang[0][4].' '.$array_vorgang[0][5].' - DOB '.$array_vorgang[0][6].'</h3>';
    echo $errorhtml1;
    echo '
    <a class="list-group-item list-group-item-action list-group-item-redtext" href="edit_person.php?id='.$v_id.'"><span class="icon-pencil"></span>&nbsp;Daten ändern</a>
    <a class="list-group-item list-group-item-action list-group-item-redtext" href="testlist.php">Zurück zur Liste</a>
    <div class="FAIRsepdown"></div>';


    // ///////////////
    // List of files
    // ///////////////
    echo '</div>';
    echo '<div class="col-lg-6">';
    echo '<h4>Dokumente</h4>
    <table class="FAIR-data">
    <tbody>
    ';
    foreach($array_docs as $d) {

      echo '
      <tr>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">
      <a class="list-group-item list-group-item-action" href="download.php?db='.$d[0].'"><span class="icon-file"></span>&nbsp;'.$d[1].'</a>
      </td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">
      <span class="FAIR-text-med">'.$d[2].'</span>
      </td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">
      <a class="list-group-item list-group-item-action" href="'.$current_site.'.php?id='.$v_id.'&deldoc='.$d[0].'"><span class="icon-remove2"></span>&nbsp;Löschen</a>
      </td>
      </tr>';
    }
    // Aufklärungsbogen
    echo '
      <tr>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">
      <a class="list-group-item list-group-item-action" href="download.php?ab='.$d[0].'"><span class="icon-file"></span>&nbsp;Aufklärungsbogen</a>
      </td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">
      </td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">
      </td>
      </tr>';

    echo '</tbody></table>';

    // ///////////////
    // Formular upload
    // ///////////////
    echo '</div>';
    echo '<div class="col-lg-6">';
    echo '
    <form enctype="multipart/form-data" action="'.$current_site.'.php" method="POST">
    <div class="input-group">
    <input type="text" name="person_id" value="'.$v_id.'" style="display:none;"></div>
  <!-- MAX_FILE_SIZE muss vor dem Dateiupload Input Feld stehen -->
  <input type="hidden" name="MAX_FILE_SIZE" value="30000000" />
  <!-- Der Name des Input Felds bestimmt den Namen im $_FILES Array -->
  <div class="input-group">
  <span class="input-group-addon" id="basic-addon1">Neues Dokument hochladen</span>
  </div><div class="input-group">
  <span class="input-group-addon" id="basic-addon1">PDF wählen</span>
  <input name="userfile" type="file" class="form-control" />
  <span class="input-group-addon" id="basic-addon1">Dokument-Art</span>
  <select class="custom-select" id="select-state" placeholder="Wähle..." name="doc_class">
  <option value="" selected>Wähle...</option>
  ';
  echo '<option value="Impfbuch">Impfbuch</option>';
  echo '<option value="Impfersatzdokument">Impfersatzdokument</option>';
  echo '<option value="Digitaler Impfnachweis">Digitaler Impfnachweis</option>';
  echo '
  </select>
  </div><div class="input-group">
  <div class="FAIR-si-button">
  <input type="submit" class="btn btn-danger" value="Hochladen" name="upload_file" />
  </div>
  </div>
  </form>';
    
  echo $errorhtml0;
    
    echo '</div></div>';

  }
} else {
  // Print html header
  echo $GLOBALS['G_html_header'];

  // Print html menu
  echo $GLOBALS['G_html_menu'];
  echo $GLOBALS['G_html_menu2'];

  // Print html content part A
  echo $GLOBALS['G_html_main_right_a'];
  echo '<h1>KEINE BERECHTIGUNG</h1>';
}

// Print html content part C
echo $GLOBALS['G_html_main_right_c'];
// Print html footer
echo $GLOBALS['G_html_footer'];

?>