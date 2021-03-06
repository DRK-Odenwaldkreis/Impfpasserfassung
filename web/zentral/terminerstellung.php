<?php

/* **************

Websystem für das Impfpasszentrum
Author: Marc S. Duchene
June 2021

** ************** */

include_once 'preload.php';
if( isset($GLOBALS['G_sessionname']) ) { session_name ($GLOBALS['G_sessionname']); }
session_start();
$sec_level=1;
$current_site="terminerstellung";

// Include functions
include_once 'tools.php';
include_once 'auth.php';
include_once 'menu.php';

// role check
if( A_checkpermission(array(0,2,0,4,5)) ) {

    $errorhtml0 ='';
    $errorhtml1 ='';
    $errorhtml2 ='';
    $display_creating_termin=false;

    // Create termine
    $val_report_display=0;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        if(isset($_POST['create_termine2'])) {
            // Termine eintragen - mit Terminbuchung
            $t_station=$_POST['station_id'];
            $t_datum=$_POST['date'];
            $t_start_slot=$_POST['startzeit_slot'];
            $t_ende_slot=$_POST['endzeit_slot'];
            $t_number_slot=$_POST['terminzahl_slot'];
            // check values
            if( ($t_station!='') && ($t_datum!='') && ($t_start_slot!='') && ($t_ende_slot!='') && ($t_number_slot>0) ) {
                // number of appointed times
                $t_number_between_start_end= 1 + ( strtotime($t_ende_slot) - strtotime($t_start_slot) ) / 15 / 60;
                // start slot and start hour
                $t_slot=(substr($t_start_slot,3,2) / 15 ) + 1;
                $t_stunde=substr($t_start_slot,0,2);
                // write values
                $Db=S_open_db();
                for($j=0;$j<$t_number_between_start_end;$j++) {
                    for($k=0;$k<$t_number_slot;$k++) {
                        S_set_data($Db,'INSERT Termine (id_station,Tag,Stunde,Slot) VALUES (cast(\''.$t_station.'\' AS int),\''.$t_datum.'\',\''.$t_stunde.'\',\''.$t_slot.'\');');
                    }
                    if($t_slot>=4) {$t_slot=1; $t_stunde++;} else {$t_slot++;}
                }
                S_close_db($Db);

                $errorhtml0 = H_build_boxinfo( 0, "Termine wurden erstellt.", 'green' );
                $display_creating_termin=true;
            } else {
                $errorhtml2 = H_build_boxinfo( 0, "Fehler bei der Eingabe.", 'red' );
            }
            
            
        }
    }

    // Print html header
    echo $GLOBALS['G_html_header'];

    // Print html menu
    echo $GLOBALS['G_html_menu'];
    echo $GLOBALS['G_html_menu2'];

    // Print html content part A
    echo $GLOBALS['G_html_main_right_a'];

    echo '<h1>Termine für Impfpassstation erstellen</h1>';


    echo '<div class="row">';

    if($display_creating_termin) {
      echo '<div class="card">
      <div class="col-md-6">
      <h3>Neue Termine erstellt</h3>
      <p></p>';
      echo $errorhtml0;
      echo '<a class="list-group-item list-group-item-action list-group-item-redtext" href="'.$current_site.'.php">Weitere Termine erstellen</a>';
      echo '</div></div>';

    } else {

        // Get available stations
        $Db=S_open_db();
        $stations_array=S_get_multientry($Db,'SELECT id, Ort FROM Station;');
        S_close_db($Db);

        

        echo '
        <div class="col-lg-12"><div class="card">
        <h3>Mit Terminbuchung und verpflichtender Voranmeldung</h3>
        <p class="list-group-item-text">Zum Erstellen bitte alle Felder ausfüllen.</p><p></p>';
        echo '<form action="'.$current_site.'.php" method="post">
        <div class="input-group">
            <span class="input-group-addon" id="basic-addon1">Station</span>
            <select id="select-state-2" placeholder="Wähle eine Station..." class="custom-select" style="margin-top:0px;" name="station_id">
            <option value="" selected>Wähle Station...</option>
                ';
                foreach($stations_array as $i) {
                    $display=$i[1].' / '.$i[0];
                    echo '<option value="'.$i[0].'">'.$display.'</option>';
                }
                echo '
            </select>
            </div>
            <div class="input-group">
            <span class="input-group-addon" id="basic-addon4">Datum</span>
            <input type="date" class="form-control" placeholder="JJJJ-MM-DD" aria-describedby="basic-addon4" value="" name="date">
            <span class="input-group-addon" id="basic-addon4">Termine pro Slot (15 min.)</span>
            <input type="number" min="1" max="99" class="form-control" aria-describedby="basic-addon4" name="terminzahl_slot">
            </div>

            <div class="input-group">
            <span class="input-group-addon" id="basic-addon4">Erster Termin</span>
            <input type="time" step="900" class="form-control" aria-describedby="basic-addon4" name="startzeit_slot">
            <span class="input-group-addon" id="basic-addon4">Letzter Termin (inklusiv)</span>
            <input type="time" step="900" class="form-control" aria-describedby="basic-addon4" name="endzeit_slot">

            
            </div>

            
            <div class="FAIR-si-button">
            <input type="submit" class="btn btn-danger" value="Buchbare Termine erstellen" name="create_termine2" />
            </div>';
            echo H_build_boxinfo( 0, "Information:<br>Beispielsweise ist der erste Termin von 8:00 bis 8:15 Uhr und der letzte Termin von 16:45 bis 17:00 Uhr, muss auch 08:00 und 16:45 eingetragen werden.", 'yellow' );
            echo '</div>
            </form>';
            echo $errorhtml2;
        echo '</div></div>';


    }

    echo '</div>';
      
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