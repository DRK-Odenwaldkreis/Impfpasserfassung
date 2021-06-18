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
$current_site="edit_person";

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
    // Registrierungsänderung speichern
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



      if (filter_var($k_email, FILTER_VALIDATE_EMAIL)) {
  
        S_set_data($Db,'UPDATE Vorgang SET
        Vorname=\''.$k_vname.'\',
        Nachname=\''.$k_nname.'\',
        Geburtsdatum=\''.$k_geb.'\',
        Adresse=\''.$k_adresse.'\',
        Wohnort=\''.$k_ort.'\',
        Mailadresse=\''.$k_email.'\',
        Erstimpfung=\''.$k_vacdate1.'\',
        Zweitimpfung=NULLIF(\''.$k_vacdate2.'\',""),
        Erstimpfstoff_id='.$k_vactype1.',
        Zweitimpfstoff_id=NULLIF('.$k_vactype2.',0)
        WHERE id=CAST('.$k_id.' AS int);');
        echo '<div class="row">';
        echo '<div class="col-sm-12">
        <div class="alert alert-success" role="alert">
        <h3>Änderung gespeichert</h3>
        </div>';
        echo '<div class="list-group">';
        
        echo '
        <a class="list-group-item list-group-item-action list-group-item-redtext" href="docs_person.php?id='.$k_id.'"><span class="icon-folder3"></span>&nbsp;Akte</a>
        <a class="list-group-item list-group-item-action list-group-item-redtext" href="edit_person.php?id='.$k_id.'"><span class="icon-pencil"></span>&nbsp;Daten ändern</a>
        <a class="list-group-item list-group-item-action list-group-item-FAIR" href="testlist.php">Zurück zur Liste</a>
        <div class="FAIRsepdown"></div>';
        echo '</div></div>';
      } else {
        S_set_data($Db,'UPDATE Vorgang SET
        Vorname=\''.$k_vname.'\',
        Nachname=\''.$k_nname.'\',
        Geburtsdatum=\''.$k_geb.'\',
        Adresse=\''.$k_adresse.'\',
        Wohnort=\''.$k_ort.'\',
        Erstimpfung=\''.$k_vacdate1.'\',
        Zweitimpfung=\''.$k_vacdate2.'\',
        Erstimpfstoff_id='.$k_vactype1.',
        Zweitimpfstoff_id=NULLIF('.$k_vactype2.',0)
        WHERE id=CAST('.$k_id.' AS int);');
        echo '<div class="row">';
        echo '<div class="col-sm-12">
        <div class="alert alert-warning" role="alert">
        <h3>E-Mail-Adresse ungültiges Format. Änderungen ohne E-Mail wurden gespeichert.</h3>
        <div class="FAIRsepdown"></div>
        <p><span class="anweisung"><span class="icon-notification"></span> ANWEISUNG:</span> E-Mail nochmal ändern? <a href="?id='.$k_id.'">Dazu hier klicken</a>.</p>
        </div>';
        echo '<div class="list-group">';
        
        echo '
        <a class="list-group-item list-group-item-action list-group-item-redtext" href="docs_person.php?id='.$k_id.'"><span class="icon-folder3"></span>&nbsp;Akte</a>
        <a class="list-group-item list-group-item-action list-group-item-redtext" href="edit_person.php?id='.$k_id.'"><span class="icon-pencil"></span>&nbsp;Daten ändern</a>
        <a class="list-group-item list-group-item-action list-group-item-FAIR" href="testlist.php">Zurück zur Liste</a>
        <div class="FAIRsepdown"></div>';
        echo '</div></div>';
      }
  
      
  
  
    } elseif( isset($_GET['id']) && isset($_GET['reset']) && $_GET['reset']=='mail' ) {
  // ///////////////
  // Reset Mailsend=NULL
  // ///////////////
      
      S_set_data($Db,'UPDATE Vorgang SET
      privateMail_lock=NULL
      WHERE id=CAST('.$_GET['id'].' AS int);');

      echo '<div class="row">';
      echo '<div class="col-sm-12">
      <h3>Kunden-Daten geändert</h3>
      <p>Benachrichtigungs-E-Mail wird nochmal verschickt</p>';      
      echo '<a class="list-group-item list-group-item-action list-group-item-redtext" href="testlist.php">Zurück zur Liste</a>';
      
      echo '</div></div>';

    } elseif( isset($_GET['id']) && isset($_GET['reset']) && $_GET['reset']=='lock' ) {
      // ///////////////
      // Reset customer_lock=NULL
      // ///////////////
          
          S_set_data($Db,'UPDATE Vorgang SET
          customer_lock=NULL
          WHERE id=CAST('.$_GET['id'].' AS int);');
    
          echo '<div class="row">';
          echo '<div class="col-sm-12">
          <h3>Kunden-Daten geändert</h3>
          <p>Reset der Sperre (Abholung des Ergebnisses wieder möglich</p>';      
          echo '<a class="list-group-item list-group-item-action list-group-item-redtext" href="testlist.php">Zurück zur Liste</a>';
          
          echo '</div></div>';
    
    } elseif( isset($_GET['id']) || isset($_GET['transfer']) ) {
      $errorhtml1 ='';
      // Transfer data from pre registration first
      if(isset($_GET['transfer'])) {
        $token=A_sanitize_input($_GET['transfer']);
        $v_id=S_set_transfer_person_prereg($Db,$token,$_SESSION['station_id']);
        if($v_id=='err') {
          $errorhtml1=H_build_boxinfo( 400, '<span class="icon-warning"></span> Keine Voranmeldung gefunden bzw. Daten bereits übertragen.', 'red' );
        }
      } else {
        $v_id=A_sanitize_input($_GET['id']);
      }

    // ///////////////
    // Registrierung ändern / Formular
    // ///////////////

      // Get data
      $array_vorgang=S_get_multientry($Db,'SELECT id,Teststation,0, Registrierungszeitpunkt,Vorname,Nachname,Geburtsdatum,Adresse,Wohnort,0,Mailadresse,Erstimpfstoff_id, Zweitimpfstoff_id, Erstimpfung, Zweitimpfung FROM Vorgang WHERE id=CAST('.$v_id.' AS int);');

      $vaccine_array=S_get_multientry($Db,'SELECT id, Name FROM Impfstoff;');

      echo '<div class="row">';
      echo '<div class="col-sm-12">
      <h3>Kundendaten ändern</h3>';
      echo $errorhtml1;
      echo '
      <a class="list-group-item list-group-item-action list-group-item-redtext" href="docs_person.php?id='.$v_id.'"><span class="icon-folder3"></span>&nbsp;Akte</a>
      <a class="list-group-item list-group-item-action list-group-item-redtext" href="testlist.php">Zurück zur Liste</a>
      <div class="FAIRsepdown"></div>';
      echo '
      <form action="'.$current_site.'.php" method="post">
      <div class="input-group"><span class="input-group-addon" id="basic-addon1">ID</span><input type="text" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$array_vorgang[0][0].'" disabled>
      <span class="input-group-addon" id="basic-addon1">S</span><input type="text" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$array_vorgang[0][1].'" disabled></div>
      
      <div class="input-group"><span class="input-group-addon" id="basic-addon1">Reg</span><input type="text" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$array_vorgang[0][3].'" disabled>
      <input type="text" name="id" value="'.$array_vorgang[0][0].'" style="display:none;"></div>';

      echo '<div class="input-group"><span class="input-group-addon" id="basic-addon1">Vorname</span><input type="text" name="vname" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$array_vorgang[0][4].'" required></div>
      <div class="input-group"><span class="input-group-addon" id="basic-addon1">Nachname</span><input type="text" name="nname" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$array_vorgang[0][5].'" required></div>
      <div class="input-group"><span class="input-group-addon" id="basic-addon1">Geburtsdatum</span><input type="date" name="geburtsdatum" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$array_vorgang[0][6].'" required></div>';

      echo '<div class="input-group"><span class="input-group-addon" id="basic-addon1">Wohnadresse</span><input type="text" name="adresse" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$array_vorgang[0][7].'" required></div>
      <div class="input-group"><span class="input-group-addon" id="basic-addon1">Wohnort</span><input type="text" name="ort" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$array_vorgang[0][8].'" required></div>

      <div class="input-group"><span class="input-group-addon" id="basic-addon1">E-Mail *</span><input type="text" name="email" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$array_vorgang[0][10].'"></div>
      
      <div class="FAIRsepdown"></div>
      <div class="alert alert-warning" role="alert">
          <h4>Impfstoff-Daten</h4>
          <h5>1. Impfung</h5>
              <div class="input-group"><span class="input-group-addon" id="basic-addon1">Impfstoff</span><select id="select-vac" class="custom-select" style="margin-top:0px;" placeholder="Bitte wählen..." name="vaccine_type1" required>
              <option value="" selected>Bitte wählen...</option>
                  ';
                  foreach($vaccine_array as $i) {
                      $display=$i[1];
                      if($array_vorgang[0][11]==$i[0]) {$selected='selected';} else {$selected='';}
                      echo '<option value="'.$i[0].'" '.$selected.'>'.$display.'</option>';
                  }
                  echo '
              </select></div>
              <div class="input-group"><span class="input-group-addon" id="basic-addon1">Datum der 1. Impfung</span><input type="date" name="vac_date1" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$array_vorgang[0][13].'" required></div>
              <div class="FAIRsepdown"></div>
          <h5>2. Impfung</h5>
              <div class="input-group"><span class="input-group-addon" id="basic-addon1">Impfstoff</span><select id="select-vac" class="custom-select" style="margin-top:0px;" placeholder="Bitte wählen..." name="vaccine_type2" required>
              <option value="" selected>Bitte wählen...</option>
              ';
              if($array_vorgang[0][12]==0) {$selected='selected';} else {$selected='';}
              echo'
              <option value="0" '.$selected.'>keiner</option>
                  ';
                  foreach($vaccine_array as $i) {
                      $display=$i[1];
                      if($array_vorgang[0][12]==$i[0]) {$selected='selected';} else {$selected='';}
                      echo '<option value="'.$i[0].'" '.$selected.'>'.$display.'</option>';
                  }
                  echo '
              </select></div>
              <div class="input-group"><span class="input-group-addon" id="basic-addon1">Datum der 2. Impfung</span><input type="date" name="vac_date2" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$array_vorgang[0][14].'"></div>
              <p>Das Datum der 2. Impfung nicht ausfüllen, falls es keine 2. Impfung bei der Person gab.</p>
      </div>';



      echo '<div class="FAIRsepdown"></div>
      <span class="input-group-btn">
        <input type="submit" class="btn btn-lg btn-danger" value="Änderung speichern" name="submit_person" />
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