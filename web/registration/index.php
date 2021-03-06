<?php

/* **************

Websystem für das Impfpasszentrum
Author: Marc S. Duchene
June 2021

** ************** */

include_once 'preload.php';
if( isset($GLOBALS['G_sessionname']) ) { session_name ($GLOBALS['G_sessionname']); }
session_start();

// Include functions
include_once '../admin01.php';
//$GLOBALS['FLAG_SHUTDOWN_MAIN']=false;
include_once 'tools.php';
include_once 'auth.php';
include_once 'menu.php';

$current_site="index";


// Print html header
echo $GLOBALS['G_html_header'];

// Print html menu
echo $GLOBALS['G_html_menu'];
echo $GLOBALS['G_html_menu2'];

// Print html content part A
echo $GLOBALS['G_html_main_right_a'];





echo '<div class="row">';
echo '<div class="col-sm-12">
<h2>Anmeldung für einen Termin zum Erstellen des digitalen Covid-Impfpasses</h2>';

if(!$GLOBALS['FLAG_SHUTDOWN_MAIN']) {

    // Open database connection
    $Db=S_open_db();


    if(isset($_POST['submit_person'])) {
        // ///////////////
        // Registrierung speichern
        // ///////////////

        // save data
        $k_nname=$_POST['nname'];
        $k_vname=$_POST['vname'];
        $gebdatum_d = $_POST['gebdatum_d'];
        $gebdatum_m = $_POST['gebdatum_m'];
        $gebdatum_y = $_POST['gebdatum_y'];
        $k_geb=sprintf('%04d',$gebdatum_y).'-'.sprintf('%02d',$gebdatum_m).'-'.sprintf('%02d',$gebdatum_d);
        $k_adresse=$_POST['adresse'];
        $k_ort=$_POST['ort'];
        $k_vactype1=$_POST['vaccine_type1'];
        $k_vactype2=$_POST['vaccine_type2'];
        $k_vacdate1=$_POST['vac_date1'];
        $k_vacdate2=$_POST['vac_date2'];
        $k_email=$_POST['email'];
        $k_slot_id=$_POST['termin_id'];
        $k_date=$_POST['date'];
        $k_int_date=$_POST['int_date'];
        $k_int_time1=$_POST['int_time1'];
        $k_int_time2=$_POST['int_time1'];
        $k_int_location=$_POST['int_location'];
        
        if (filter_var($k_email, FILTER_VALIDATE_EMAIL)) {
            $prereg_id=S_set_entry_voranmeldung($Db,array($k_vname,$k_nname,$k_geb,$k_adresse,$k_ort,$k_vactype1,$k_vactype2,$k_vacdate1,$k_vacdate2,$k_email,$k_slot_id,$k_date));
            if($prereg_id=='DOUBLE_ENTRY') {
                echo '<div class="alert alert-danger" role="alert">
                <h3>Ungültiger Vorgang</h3>
                <p>Sie haben bereits einen Termin für diesen Tag gewählt.</p>
                </div>';
                echo '<div class="list-group">';
                echo '<a class="list-group-item list-group-item-action list-group-item-FAIR" href="../index.php">Zur Startseite</a>';
                echo '</div>';
            } elseif($prereg_id>0) {
                // Generate verification via email
                $token_ver=A_generate_token(16);
                S_set_data($Db,'INSERT INTO Voranmeldung_Verif (Token,id_preregistration) VALUES (\''.$token_ver.'\','.$prereg_id.');');
                // Send email for verification
                $header = "From: no-reply@testzentrum-odenwald.de\r\n";
                $header .= "Content-Type: text/plain; charset=UTF-8\nContent-Transfer-Encoding: 8bit";
                $content="Guten Tag,\n
Sie wurden soeben für einen Termin im Impfpasszentrum Odenwaldkreis eingetragen. Falls diese Anfrage von Ihnen nicht initiiert wurde, können Sie diese Nachricht ignorieren.\n
Bitte mit diesem Link den Termin bestätigen:\n";
                $content.=$FLAG_http.'://'.$hostname.($path == '/' ? '' : $path)."/index.php?confirm=confirm&t=$token_ver&i=$prereg_id";
                $content.="\n\n
Mit freundlichen Grüßen\n
Das Team vom Impfpasszentrum Odenwaldkreis";
                $title='Impfpasszentrum Odenwaldkreis - Termin bestätigen';
                $res=mail($k_email, $title, $content, $header, "-r no-reply@testzentrum-odenwald.de");

                echo '<div class="alert alert-success" role="alert">
                <h3>Ihre Daten wurden gespeichert</h3>
                <p>Sie erhalten jetzt eine E-Mail, die Sie bestätigen müssen. Hierfür haben Sie 20 Minuten Zeit, andernfalls wird Ihr Termin wieder freigegeben und Ihre Daten gelöscht.</p>
                <p><i>Schauen Sie auch in Ihrem Spam-Ordner, falls die E-Mail nicht ankommt.</i></p>
                </div>';

                echo '<div class="alert alert-info" role="alert">
                <h3>Ablauf</h3>
                <p>Bitte wählen Sie einen freien Termin für jede Person, die einen digitalen Impfpass benötigt.</p>
                <p>Bitte tragen Sie Ihre Daten ein. Sie erhalten anschließend eine E-Mail, die Sie bestätigen müssen.</p>
                <p>Nach Abschluss des Registrierungsprozesses erhalten Sie auf Ihre E-Mail-Adresse eine Bestätigung. Bitte halten Sie vor Ort auch einen Lichtbildausweis bereit.</p>
                </div>';
                
            } else {
                echo '<div class="alert alert-danger" role="alert">
                <h3>Termin bereits gebucht</h3>
                <p>Ihr gewählter Termin ist in der Zwischenzeit vergeben worden. Bitte wählen Sie einen neuen Termin aus.</p>
                </div>';
                echo '<div class="list-group">';
                echo '<a class="list-group-item list-group-item-action list-group-item-FAIR" href="../index.php">Neue Registrierung starten</a>';
                echo '</div>';
            }
        } else {
             // ///////////////////////////
            // Email invalid !!!


            echo '
            <div class="alert alert-danger" role="alert">
            <h3>E-Mail ungültig</h3>
            <p>Die eingetragene E-Mail-Adresse entspricht keinem gültigen Format.</p>
            <p>Bitte tragen Sie die Daten korrekt ein.</p>
            </div></div></div>';

            echo '<div class="row">
            <div class="col-sm-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                <b>Gewählter Termin</b>
                </div>
                <div class="panel-body">
                <div class="row">
                <div class="col-sm-4 calendar-col"><b>Datum</b> <span class="calendarblue">'.$k_int_date.'</span></div>
                <div class="col-sm-4 calendar-col"><b>Uhrzeit</b> <span class="calendarblue">'.$k_int_time1.' - '.$k_int_time2.' Uhr</span></div>
                <div class="col-sm-4 calendar-col"><b>Ort</b> <span class="calendarblue">'.$k_int_location.'</span></div>
                </div>
                </div>
                </div>';

                echo '<h3>Registrierung</h3>
                <form action="'.$current_site.'.php" method="post">
                    <input type="text" value="'.$k_date.'" name="date" style="display:none;">
                    <input type="text" value="'.$k_slot_id.'" name="termin_id" style="display:none;">
                    <input type="text" value="'.$k_int_date.'" name="int_date" style="display:none;">
                    <input type="text" value="'.$k_int_time1.'" name="int_time1" style="display:none;">
                    <input type="text" value="'.$k_int_time2.'" name="int_time2" style="display:none;">
                    <input type="text" value="'.$k_int_location.'" name="int_location" style="display:none;">

                    <div class="input-group"><span class="input-group-addon" id="basic-addon1">Vorname</span><input type="text" name="vname" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$k_vname.'" required></div>
                    <div class="input-group"><span class="input-group-addon" id="basic-addon1">Nachname</span><input type="text" name="nname" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$k_nname.'" required></div>

                    <div class="input-group"><span class="input-group-addon" id="basic-addon1">Geburtsdatum</span>
                    <input type="number" min="1" max="31" placeholder="TT" class="form-control" name="gebdatum_d" value="'.$gebdatum_d.'" required>
                    <input type="number" min="1" max="12" placeholder="MM" class="form-control" name="gebdatum_m" value="'.$gebdatum_m.'" required>
                    <input type="number" min="1900" max="2999" placeholder="JJJJ" class="form-control" name="gebdatum_y" value="'.$gebdatum_y.'" required>
                    </div>

                    <div class="input-group"><span class="input-group-addon" id="basic-addon1">Wohnadresse</span><input type="text" name="adresse" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$k_adresse.'" required></div>
                    <div class="input-group"><span class="input-group-addon" id="basic-addon1">Wohnort</span><input type="text" name="ort" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$k_ort.'" required></div>
                    <div class="input-group"><span class="input-group-addon" id="basic-addon1">E-Mail</span><input type="text" name="email" class="form-control" placeholder="" aria-describedby="basic-addon1" value="'.$k_email.'" required></div>
                    ';

                    $vaccine_array=S_get_multientry($Db,'SELECT id, Name FROM Impfstoff;');
                    echo '<div class="FAIRsepdown"></div>
                    <div class="alert alert-warning" role="alert">
                        <h4>Ihre Impfstoff-Daten</h4>
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
                            <option value="0">keiner</option>
                                ';
                                foreach($vaccine_array as $i) {
                                    $display=$i[1];
                                    echo '<option value="'.$i[0].'">'.$display.'</option>';
                                }
                                echo '
                            </select></div>
                            <div class="input-group"><span class="input-group-addon" id="basic-addon1">Datum der 2. Impfung</span><input type="date" name="vac_date2" class="form-control" placeholder="" aria-describedby="basic-addon1"></div>
                            <p>Das Datum der 2. Impfung nicht ausfüllen, falls es keine 2. Impfung bei Ihnen gab.</p>
                    </div>
                    <div class="FAIRsepdown"></div>';


                    echo '
                    <div class="cb_drk">
                    <input type="checkbox" id="cb2" name="cb2" required/>
                    <label for="cb2">Ich bestätige die wahrheitsgemäße Angabe der angegebenen Daten.</label>
                    </div>
                    <div class="FAIRsepdown"></div><div class="cb_drk">
                    <input type="checkbox" id="cb3" name="cb3" required/>
                    <label for="cb3">Ich bin mit dem oben genannten Ablauf einverstanden und akzeptiere die Erklärung zum Datenschutz 
                    (<a href="../impressum.php#datenschutz" target="_blank">Datenschutzerklärung in neuem Fenster öffnen</a>).</label>
                    </div>
                    <div class="FAIRsepdown"></div>
                    <span class="input-group-btn">
                    <input type="submit" class="btn btn-lg btn-primary" value="Jetzt Registrieren" name="submit_person" />
                    </span>
                    </form>
                    <div class="FAIRsepdown"></div>
                    ';

                echo '</div>';
                echo '</div>';

        }

        

    } elseif(isset($_GET['confirm'])) {
        // ///////////////
        // Registrierung abschließen mit E-Mail Code
        // ///////////////

        $prereg_id=$_GET['i'];
        $token_ver=$_GET['t'];
        $id_check=S_get_entry_voranmeldung($Db,array($prereg_id,$token_ver));

        if($id_check>0) {
            // Generate unique token for QR code
            $token=A_generate_token(8);
            while( (S_get_entry($Db,'SELECT id FROM Voranmeldung WHERE Token=\''.$token.'\'')>0) ) {
                $token=A_generate_token(5,'alphacapitalsnum');
            }
            S_set_data($Db,'UPDATE Voranmeldung SET Token=\'P'.$token.'\' WHERE id=CAST('.$id_check.' AS int)');
            S_set_data($Db,'DELETE From Voranmeldung_Verif WHERE id_preregistration=CAST('.$id_check.' AS int)');
            // Send mail with QR code will be done from different process of server - not from this Web UI

            echo '<div class="alert alert-success" role="alert">
            <h3>Ihr Termin wurde bestätigt</h3>
            <p>Sie erhalten jetzt eine E-Mail mit den Termindaten.</p>
            <p>Der Versand dieser E-Mail kann ein paar Minuten in Anspruch nehmen - bitte haben Sie etwas Geduld.</p>
            </div>';
        } else {
            $token_check=S_get_entry_voranmeldung_debug($Db,$prereg_id);
            if($token_check==null) {
                echo '<div class="alert alert-warning" role="alert">
                <h3>Ungültiger Code</h3>
                <p>Der Link ist bereits abgelaufen. Sie müssen sich neu registrieren und einen neuen Termin auswählen.</p>
                </div>';
                echo '<div class="list-group">';
                echo '<a class="list-group-item list-group-item-action list-group-item-FAIR" href="../index.php">Neue Registrierung starten</a>';
                echo '</div>';
            } else {
                echo '<div class="alert alert-success" role="alert">
                <h3>Ihr Termin wurde bereits bestätigt</h3>
                <p>Sie sollten eine E-Mail mit den Termindaten bereits erhalten haben.</p>
                <p>Der Versand dieser E-Mail kann ein paar Minuten in Anspruch nehmen - bitte haben Sie etwas Geduld.</p>
                </div>';
                echo '<div class="list-group">';
                echo '<a class="list-group-item list-group-item-action list-group-item-FAIR" href="../index.php">Neue Registrierung starten</a>';
                echo '</div>';
            }
            
        }
        
    } elseif(isset($_GET['cancel'])) {
        // ///////////////
        // Termin löschen - Frage
        // ///////////////

        // check pre registration data
        $k_prereg_id=$_GET['i'];
        $k_token=$_GET['t'];
        $stmt=mysqli_prepare($Db,"SELECT Termin_id, Nachname, Vorname FROM Voranmeldung WHERE id=? AND Token=? AND Used!=1;");
        mysqli_stmt_bind_param($stmt, "is", $k_prereg_id, $k_token);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $k_termin_id, $k_name, $k_vorname);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if($k_termin_id>0) {
            // get Termin data
            $array_appointment=S_get_multientry($Db,'SELECT id, Tag, Startzeit, Endzeit, Slot, Stunde FROM Termine WHERE id=CAST('.$k_termin_id.' as int);');
            if($array_appointment[0][0]>0) {
                $date=date("d.m.Y",strtotime($array_appointment[0][1]));
                if($array_appointment[0][4]>=1) {
                    $time1=sprintf('%02d', $array_appointment[0][5]).':'.sprintf('%02d', ( $array_appointment[0][4]*15-15 ) );
                    $time2=(date("H:i",strtotime($time1) + 60 * 15));
                } else {
                    $time1=date("H:i",strtotime($array_appointment[0][2]));
                    $time2=date("H:i",strtotime($array_appointment[0][3]));
                }
                $valid_appointment=true;
            } else {
                $valid_appointment=false;
            }
        } else {
            $valid_appointment=false;
        }

        if($valid_appointment) {
            echo '<div class="panel panel-primary">
            <div class="panel-heading">
            <b>Termin stornieren / Voranmeldung löschen</b>
            </div>
            <div class="panel-body">
            
            <div class="row calendar_selection">
            <div class="col-sm-4 calendar-col"><b>Datum</b> <span class="calendarblue">'.$date.'</span></div>
            <div class="col-sm-4 calendar-col"><b>Uhrzeit</b> <span class="calendarblue">'.$time1.' - '.$time2.' Uhr</span></div>
            <div class="col-sm-4 calendar-col"><b>Name</b> <span class="calendarblue">'.$k_name.', '.$k_vorname.'</span></div>
            </div>

            <p>Sie möchten Ihren Termin stornieren bzw. die Voranmeldung löschen?</p>
            <form action="'.$current_site.'.php" method="post">
            <input type="text" value="'.$k_prereg_id.'" name="prereg_id" style="display:none;">
            <input type="text" value="'.$k_termin_id.'" name="termin_id" style="display:none;">
            <span class="input-group-btn">
            <input type="submit" class="btn btn-danger" value="Jetzt stornieren" name="cancel_slot" />
            </span>
            </form>

            </div></div>';
        } else {
            echo '<div class="alert alert-warning" role="alert">
            <h3>Fehler</h3>
            <p>Ihr Link ist fehlerhaft. Vielleicht wurde der Termin bereits von Ihnen storniert oder der Termin wurde bereits wahrgenommen.</p>
            </div>';

            echo '</div>';
            echo '</div>';
        }


        echo '<div class="list-group">';
        echo '<a class="list-group-item list-group-item-action list-group-item-FAIR" href="../index.php">Zur Startseite</a>';
        echo '</div>';
        
    } elseif(isset($_POST['cancel_slot'])) {
        // ///////////////
        // Termin löschen - Bestätigt
        // ///////////////

        // check pre registration data
        $k_prereg_id=$_POST['prereg_id'];
        $k_termin_id=$_POST['termin_id'];
        $stmt=mysqli_prepare($Db,"SELECT id, Termin_id FROM Voranmeldung WHERE id=? AND Termin_id=? AND Used!=1;");
        mysqli_stmt_bind_param($stmt, "ii", $k_prereg_id, $k_termin_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $k_prereg_id_check, $k_termin_id_check);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        if($k_termin_id_check==$k_termin_id) {
            // Delete data
            S_set_data($Db,'DELETE From Voranmeldung WHERE id=CAST('.$k_prereg_id_check.' AS int)');
            S_set_data($Db,'UPDATE Termine SET Used=Null WHERE id=CAST('.$k_termin_id_check.' AS int)');

            echo '<div class="alert alert-success" role="alert">
            <h3>Termin stornieren / Voranmeldung löschen</h3>
            <p>Ihr Termin wurde storniert und Ihre Voranmeldungsdaten gelöscht. Vielen Dank für Ihre Mithilfe.</p>
            </div>';
        } else {
            echo '<div class="alert alert-warning" role="alert">
            <h3>Fehler</h3>
            <p>Ihr Link ist fehlerhaft. Vielleicht wurde der Termin bereits von Ihnen storniert oder der Termin wurde bereits wahrgenommen.</p>
            </div>';
        }
        

        echo '<div class="list-group">';
        echo '<a class="list-group-item list-group-item-action list-group-item-FAIR" href="../index.php">Neuen Termin auswählen</a>';
        echo '</div>';
        
    } elseif( isset($_GET['appointment']) || isset($_GET['appointment_more']) ) {
        $display_single_termin=false;
        $display_slot_termin=false;

        // Termin selected from slot booking
        if( isset($_GET['slot']) ) {
            $display_single_termin=true;
        }

        if( isset($_GET['appointment']) ) {
            $val_termin_id=$_GET['appointment'];

            $stmt=mysqli_prepare($Db,"SELECT id, Tag, Startzeit, Endzeit, Slot, 0, 0 , id_station, Stunde FROM Termine WHERE id=?;");
            mysqli_stmt_bind_param($stmt, "i", $val_termin_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $array_appointment[0], $array_appointment[1], $array_appointment[2], $array_appointment[3], $array_appointment[4], $array_appointment[5], $array_appointment[6], $array_appointment[7], $array_appointment[8]);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);



            // Slot booking
            $date=date("d.m.Y",strtotime($array_appointment[1]));
            $date_sql=date("Y-m-d",strtotime($array_appointment[1]));
            if($array_appointment[4]>0 && !$display_single_termin) {
                $display_slot_termin=true;
                $array_termine_slot=S_get_multientry($Db,'SELECT id,Stunde,Slot,count(id),count(Used) FROM Termine WHERE Slot>0 AND id_station='.$array_appointment[7].' AND Date(Tag)=\''.$array_appointment[1].'\' GROUP BY Stunde,Slot;');
            } elseif(isset($_GET['slot'])) {
                $time1=sprintf('%02d', $array_appointment[8]).':'.sprintf('%02d', ( $array_appointment[4]*15-15 ) );
                $time2=(date("H:i",strtotime($time1) + 60 * 15));
            }

            // Adresse
            $stations_array=S_get_multientry($Db,'SELECT id, Ort, Adresse FROM Station WHERE id="'.$array_appointment[7].'";');

            $location=$stations_array[0][1].', '.$stations_array[0][2];

        } else {
            $val_station_id=$_GET['appointment_more'];
        }

        if(true) {
            // ///////////////
            // Registrierungsformular
            // ///////////////

            

            echo '<div class="alert alert-info" role="alert">
            <h3>Ablauf</h3>';

            echo '<p>Bitte wählen Sie einen freien Termin für jede Person, die einen digitalen Impfpass benötigt.</p>';

            echo '<p>Bitte tragen Sie Ihre Daten ein. Sie erhalten anschließend eine E-Mail, die Sie bestätigen müssen.</p>
            <p>Nach Abschluss des Registrierungsprozesses erhalten Sie auf Ihre E-Mail-Adresse die Terminbestätigung. Bitte halten Sie im Impfpasszentrum auch einen Lichtbildausweis zusätzlich zum Impfnachweis bereit.</p>
            </div>';

            if($display_single_termin) {
                echo '<div class="panel panel-primary">
                <div class="panel-heading">
                <b>Gewählter Termin</b>
                </div>
                <div class="panel-body">
                <div class="row">
                <div class="col-sm-4 calendar-col"><b>Datum</b> <span class="calendarblue">'.$date.'</span></div>
                <div class="col-sm-4 calendar-col"><b>Uhrzeit</b> <span class="calendarblue">'.$time1.' - '.$time2.' Uhr</span></div>
                <div class="col-sm-4 calendar-col"><b>Ort</b> <span class="calendarblue">'.$location.'</span></div>
                </div>
                </div>
                </div>';

                echo '<h3>Registrierung</h3>
                <form action="'.$current_site.'.php" method="post">
                    <input type="text" value="'.$date_sql.'" name="date" style="display:none;">
                    <input type="text" value="'.$val_termin_id.'" name="termin_id" style="display:none;">
                    <input type="text" value="'.$date.'" name="int_date" style="display:none;">
                    <input type="text" value="'.$time1.'" name="int_time1" style="display:none;">
                    <input type="text" value="'.$time2.'" name="int_time2" style="display:none;">
                    <input type="text" value="'.$location.'" name="int_location" style="display:none;">

                    <div class="input-group"><span class="input-group-addon" id="basic-addon1">Vorname</span><input type="text" name="vname" class="form-control" placeholder="" aria-describedby="basic-addon1" required></div>
                    <div class="input-group"><span class="input-group-addon" id="basic-addon1">Nachname</span><input type="text" name="nname" class="form-control" placeholder="" aria-describedby="basic-addon1" required></div>

                    <div class="input-group"><span class="input-group-addon" id="basic-addon1">Geburtsdatum</span>
                    <input type="number" min="1" max="31" placeholder="TT" class="form-control" name="gebdatum_d" required>
                    <input type="number" min="1" max="12" placeholder="MM" class="form-control" name="gebdatum_m" required>
                    <input type="number" min="1900" max="2999" placeholder="JJJJ" class="form-control" name="gebdatum_y" required>
                    </div>

                    <div class="input-group"><span class="input-group-addon" id="basic-addon1">Wohnadresse</span><input type="text" name="adresse" class="form-control" placeholder="" aria-describedby="basic-addon1" required></div>
                    <div class="input-group"><span class="input-group-addon" id="basic-addon1">Wohnort</span><input type="text" name="ort" class="form-control" placeholder="" aria-describedby="basic-addon1" required></div>
                    <div class="input-group"><span class="input-group-addon" id="basic-addon1">E-Mail</span><input type="text" name="email" class="form-control" placeholder="" aria-describedby="basic-addon1" required></div>
                    ';

                    $vaccine_array=S_get_multientry($Db,'SELECT id, Name FROM Impfstoff;');
                    echo '<div class="FAIRsepdown"></div>
                    <div class="alert alert-warning" role="alert">
                        <h4>Ihre Impfstoff-Daten</h4>
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
                            <option value="0">keiner</option>
                                ';
                                foreach($vaccine_array as $i) {
                                    $display=$i[1];
                                    echo '<option value="'.$i[0].'">'.$display.'</option>';
                                }
                                echo '
                            </select></div>
                            <div class="input-group"><span class="input-group-addon" id="basic-addon1">Datum der 2. Impfung</span><input type="date" name="vac_date2" class="form-control" placeholder="" aria-describedby="basic-addon1"></div>
                            <p>Das Datum der 2. Impfung nicht ausfüllen, falls es keine 2. Impfung bei Ihnen gab.</p>
                    </div>
                    <div class="FAIRsepdown"></div>';


                    echo '
                    <div class="cb_drk">
                    <input type="checkbox" id="cb2" name="cb2" required/>
                    <label for="cb2">Ich bestätige die wahrheitsgemäße Angabe der angegebenen Daten.</label>
                    </div>
                    <div class="FAIRsepdown"></div><div class="cb_drk">
                    <input type="checkbox" id="cb3" name="cb3" required/>
                    <label for="cb3">Ich bin mit dem oben genannten Ablauf einverstanden und akzeptiere die Erklärung zum Datenschutz 
                    (<a href="../impressum.php#datenschutz" target="_blank">Datenschutzerklärung in neuem Fenster öffnen</a>).</label>
                    </div>
                    <div class="FAIRsepdown"></div>
                    <span class="input-group-btn">
                    <input type="submit" class="btn btn-lg btn-primary" value="Jetzt Registrieren" name="submit_person" />
                    </span>
                    </form>
                    <div class="FAIRsepdown"></div>
                    ';
                echo '</div>';
                echo '</div>';
            } elseif($display_slot_termin) {
                // Show available slots
                $current_time=time();
                echo '<div class="panel panel-primary">
                <div class="panel-heading">
                <b>Gewähltes Impfpasszentrum</b>
                </div>
                <div class="panel-body">
                <div class="row">
                <div class="col-sm-6 calendar-col"><b>Datum</b> <span class="calendarblue">'.$date.'</span></div>
                <div class="col-sm-6 calendar-col"><b>Ort</b> <span class="calendarblue">'.$location.'</span></div>
                </div>
                </div>
                </div>';
                echo '<h3>Termin auswählen</h3>
                <div class="row"><div class="col-sm-12 calendar_selection">';
                $at_least_one=false;
                foreach($array_termine_slot as $k) {
                    if( $date==date('d.m.Y') && $current_time > strtotime(sprintf('%02d', $k[1]).':'.sprintf('%02d', ( $k[2]*15-15 )).':00') ) {
                        // time over
                        
                    } elseif(($k[3]<=$k[4])) {
                        $display_slot=sprintf('%02d', $k[1]).':'.sprintf('%02d', ( $k[2]*15-15 ) );
                        $display_slot.='&nbsp;-&nbsp;'.(date("H:i",strtotime($display_slot) + 60 * 15));
                        if(($k[3]-$k[4])>2) {
                            $display_free='<span class="label label-success">'.($k[3]-$k[4]).'</span>';
                        } else {
                            $display_free='<span class="label label-warning">'.($k[3]-$k[4]).'</span>';
                        }
                        echo '<div style="float: left;"><a class="calendaryellow-dis">'.$display_slot.' ausgebucht</a></div>';
                        $at_least_one=true;
                    } else {
                        $display_slot=sprintf('%02d', $k[1]).':'.sprintf('%02d', ( $k[2]*15-15 ) );
                        $display_slot.='&nbsp;-&nbsp;'.(date("H:i",strtotime($display_slot) + 60 * 15));
                        if(($k[3]-$k[4])>2) {
                            $display_free='<span class="label label-success">'.($k[3]-$k[4]).'</span>';
                        } else {
                            $display_free='<span class="label label-warning">'.($k[3]-$k[4]).'</span>';
                        }
                        echo '<div style="float: left;"><a class="calendaryellow" href="?appointment='.($k[0]).'&slot=100">'.$display_slot.'
                        '.$display_free.'</a></div>';
                        $at_least_one=true;
                    }
                }
                if(!$at_least_one) {
                    echo '<div class="alert alert-warning" role="alert">
                <p>Dieses Zentrum hat heute keine Termine mehr</p>
                </div>';
                }
                echo '</div>';
                echo '</div>';
            } else {
                // ///////////////
                // Kein Ort/Termin ausgewählt
                // ///////////////
                echo '<div class="alert alert-warning" role="alert">
                <h3>Warnung</h3>
                <p>Sie haben keinen Ort/Termin ausgewählt!</p>
                <p>Bitte wählen Sie im <a href="../index.php">Kalender</a> einen Tag und einen Ort aus.</p>
                </div>';

                echo '</div>';
                echo '</div>';
            }
        }
    } else {
        // ///////////////
        // Kein Termin ausgewählt
        // ///////////////
        echo '<div class="alert alert-warning" role="alert">
        <h3>Warnung</h3>
        <p>Sie haben keinen Termin ausgewählt!</p>
        <p>Bitte wählen Sie im <a href="../index.php">Kalender</a> einen Tag und einen Ort aus.</p>
        </div>';

        echo '</div>';
        echo '</div>';
    }
    // Close connection to database
    S_close_db($Db);

} else {

    echo '<div class="alert alert-danger" role="alert">
    <h3>Wartungsarbeiten</h3>
    <p>Derzeit finden Arbeiten an dieser Seite statt, die Terminbuchung und alle Services damit stehen momentan nicht zur Verfügung. Bald geht es wieder weiter...wir bitten um etwas Geduld.</p>
    <div class="FAIRsepdown"></div>
    <div class="FAIRsep"></div>
</div>';
    echo '</div>';
    echo '</div>';
}


// Print html content part C
echo $GLOBALS['G_html_main_right_c'];
// Print html footer
echo $GLOBALS['G_html_footer'];

?>