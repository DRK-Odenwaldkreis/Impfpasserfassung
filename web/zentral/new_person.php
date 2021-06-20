<?php

/* **************

Websystem für das Impfpasszentrum
Author: Marc S. Duchene
June 2021

Edit data of registered person

** ************** */

include_once 'preload.php';
if( isset($GLOBALS['G_sessionname']) ) { session_name ($GLOBALS['G_sessionname']); }
session_start();
$sec_level=1;
$current_site="new_person";

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


  if( isset($_POST['submit_person']) ){
    // ///////////////
    // Registrierung speichern
    // ///////////////
      $k_id=$_POST['id'];
      $k_nname=$_POST['nname'];
      $k_vname=$_POST['vname'];
      $k_geb=$_POST['geburtsdatum'];
      $k_adresse=$_POST['adresse'];
      $k_ort=$_POST['ort'];
      $k_tel=$_POST['telefon'];
      $k_email=$_POST['email'];
      $k_vactype1=$_POST['vaccine_type1'];
      $k_vactype2=$_POST['vaccine_type2'];
      $k_vacdate1=$_POST['vac_date1'];
      $k_vacdate2=$_POST['vac_date2'];
      $k_station=$_SESSION['station_id'];



      if (filter_var($k_email, FILTER_VALIDATE_EMAIL)) {
        // New entry
        S_set_data($Db,'INSERT INTO Vorgang (Teststation,reg_Type,Vorname,Nachname,Geburtsdatum,Adresse,Wohnort,Mailadresse,Erstimpfung,Zweitimpfung,Erstimpfstoff_id,Zweitimpfstoff_id)
        VALUES ('.$k_station.',\'POCREG\',\''.$k_vname.'\',\''.$k_nname.'\',\''.$k_geb.'\',\''.$k_adresse.'\',\''.$k_ort.'\',\''.$k_email.'\',\''.$k_vacdate1.'\',NULLIF(\''.$k_vacdate2.'\',""),'.$k_vactype1.',NULLIF('.$k_vactype2.',0));');

        // check saving
        $check_id=S_get_entry($Db,'SELECT id FROM Vorgang
        WHERE Vorname=\''.$k_vname.'\'AND
        Nachname=\''.$k_nname.'\' AND
        Geburtsdatum=\''.$k_geb.'\' AND
        Adresse=\''.$k_adresse.'\' AND
        Wohnort=\''.$k_ort.'\' AND
        Erstimpfung=\''.$k_vacdate1.'\' AND
        Erstimpfstoff_id='.$k_vactype1.';');

        if($check_id>0) {
          echo '<div class="row">';
          echo '<div class="col-sm-12">
          <div class="alert alert-success" role="alert">
          <h3>Eintrag gespeichert</h3>
          </div>';
          echo '<div class="list-group">';
          
          echo '
          <a class="list-group-item list-group-item-action list-group-item-redtext" href="docs_person.php?id='.$check_id.'"><span class="icon-folder3"></span>&nbsp;Akte</a>
          <a class="list-group-item list-group-item-action list-group-item-redtext" href="edit_person.php?id='.$check_id.'"><span class="icon-pencil"></span>&nbsp;Daten ändern</a>
          <a class="list-group-item list-group-item-action list-group-item-FAIR" href="testlist.php">Zurück zur Liste</a>
          <div class="FAIRsepdown"></div>';
          echo '</div></div>';
        } else {
          echo '<div class="row">';
          echo '<div class="col-sm-12">
          <div class="alert alert-danger" role="alert">
          <h3>Eintrag nicht gespeichert - Fehler</h3>
          </div>';
          echo '<div class="list-group">';
          
          echo '</div></div>';
        }
        
      } else {
        // New entry
        S_set_data($Db,'INSERT INTO Vorgang (Teststation,reg_Type,Vorname,Nachname,Geburtsdatum,Adresse,Wohnort,Erstimpfung,Zweitimpfung,Erstimpfstoff_id,Zweitimpfstoff_id)
        VALUES ('.$k_station.',\'POCREG\',\''.$k_vname.'\',\''.$k_nname.'\',\''.$k_geb.'\',\''.$k_adresse.'\',\''.$k_ort.'\',\''.$k_vacdate1.'\',NULLIF(\''.$k_vacdate2.'\',""),'.$k_vactype1.',NULLIF('.$k_vactype2.',0));');
        // check saving
        $check_id=S_get_entry($Db,'SELECT id FROM Vorgang 
        WHERE Vorname=\''.$k_vname.'\' AND
        Nachname=\''.$k_nname.'\' AND
        Geburtsdatum=\''.$k_geb.'\' AND
        Adresse=\''.$k_adresse.'\' AND
        Wohnort=\''.$k_ort.'\' AND
        Erstimpfung=\''.$k_vacdate1.'\' AND
        Erstimpfstoff_id='.$k_vactype1.';');

        if($check_id>0) {
          echo '<div class="row">';
          echo '<div class="col-sm-12">
          <div class="alert alert-warning" role="alert">
          <h3>E-Mail-Adresse ungültiges Format. Eintrag ohne E-Mail wurde gespeichert.</h3>
          <div class="FAIRsepdown"></div>
          </div>';
          echo '<div class="list-group">';
          
          echo '
          <a class="list-group-item list-group-item-action list-group-item-redtext" href="docs_person.php?id='.$check_id.'"><span class="icon-folder3"></span>&nbsp;Akte</a>
          <a class="list-group-item list-group-item-action list-group-item-redtext" href="edit_person.php?id='.$check_id.'"><span class="icon-pencil"></span>&nbsp;Daten ändern</a>
          <a class="list-group-item list-group-item-action list-group-item-FAIR" href="testlist.php">Zurück zur Liste</a>
          <div class="FAIRsepdown"></div>';
          echo '</div></div>';
        } else {
          echo '<div class="row">';
          echo '<div class="col-sm-12">
          <div class="alert alert-danger" role="alert">
          <h3>Eintrag nicht gespeichert - Fehler</h3>
          </div>';
          echo '<div class="list-group">';
          
          echo '</div></div>';
        }
      }
  
      
  
  
    }  else {
      
    // ///////////////
    // Registrierung anlegen / Formular
    // ///////////////

      // Get data
      $vaccine_array=S_get_multientry($Db,'SELECT id, Name FROM Impfstoff;');

      echo '<div class="row">';
      echo '<div class="col-sm-12">
      <h3>Kundendaten neu anlegen</h3>';
      echo '
      <form action="'.$current_site.'.php" method="post">
      <div class="input-group"><span class="input-group-addon" id="basic-addon1">ID</span><input type="text" class="form-control" placeholder="" aria-describedby="basic-addon1" value="NEU" disabled>
      <span class="input-group-addon" id="basic-addon1">S</span><input type="text" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$_SESSION['station_id'].'" disabled></div>
      ';

      echo '<div class="input-group"><span class="input-group-addon" id="basic-addon1">Vorname</span><input type="text" name="vname" class="form-control" placeholder="" aria-describedby="basic-addon1" required></div>
      <div class="input-group"><span class="input-group-addon" id="basic-addon1">Nachname</span><input type="text" name="nname" class="form-control" placeholder="" aria-describedby="basic-addon1" required></div>
      <div class="input-group"><span class="input-group-addon" id="basic-addon1">Geburtsdatum</span><input type="date" name="geburtsdatum" class="form-control" placeholder="" aria-describedby="basic-addon1" required></div>';

      echo '<div class="input-group"><span class="input-group-addon" id="basic-addon1">Wohnadresse</span><input type="text" name="adresse" class="form-control" placeholder="" aria-describedby="basic-addon1" required></div>
      <div class="input-group"><span class="input-group-addon" id="basic-addon1">Wohnort</span><input type="text" name="ort" class="form-control" placeholder="" aria-describedby="basic-addon1" required></div>

      <div class="input-group"><span class="input-group-addon" id="basic-addon1">E-Mail *</span><input type="text" name="email" class="form-control" placeholder="" aria-describedby="basic-addon1"></div>
      
      <div class="FAIRsepdown"></div>
      <div class="alert alert-warning" role="alert">
          <h4>Impfstoff-Daten</h4>
          <h5>1. Impfung</h5>
              <div class="input-group"><span class="input-group-addon" id="basic-addon1">Impfstoff</span><select id="select-vac" class="custom-select" style="margin-top:0px;" placeholder="Bitte wählen..." name="vaccine_type1" required>
              <option value="" selected>Bitte wählen...</option>
                  ';
                  foreach($vaccine_array as $i) {
                      $display=$i[1];
                      echo '<option value="'.$i[0].'">'.$display.'</option>';
                  }
                  echo '
              </select></div>
              <div class="input-group"><span class="input-group-addon" id="basic-addon1">Datum der 1. Impfung</span><input type="date" name="vac_date1" class="form-control" placeholder="" aria-describedby="basic-addon1" required></div>
              <div class="FAIRsepdown"></div>
          <h5>2. Impfung</h5>
              <div class="input-group"><span class="input-group-addon" id="basic-addon1">Impfstoff</span><select id="select-vac" class="custom-select" style="margin-top:0px;" placeholder="Bitte wählen..." name="vaccine_type2" required>
              <option value="" selected>Bitte wählen...</option>
              ';
              echo'
              <option value="0">keiner</option>
                  ';
                  foreach($vaccine_array as $i) {
                      $display=$i[1];
                      echo '<option value="'.$i[0].'">'.$display.'</option>';
                  }
                  echo '
              </select></div>
              <div class="input-group"><span class="input-group-addon" id="basic-addon1">Datum der 2. Impfung</span><input type="date" name="vac_date2" class="form-control" placeholder="" aria-describedby="basic-addon1"></div>
              <p>Das Datum der 2. Impfung nicht ausfüllen, falls es keine 2. Impfung bei der Person gab.</p>
      </div>';



      echo '<div class="FAIRsepdown"></div>
      <span class="input-group-btn">
        <input type="submit" class="btn btn-lg btn-danger" value="Daten speichern" name="submit_person" />
        </span>
      </form>
      <p>* optional</p>';
      
      
      
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