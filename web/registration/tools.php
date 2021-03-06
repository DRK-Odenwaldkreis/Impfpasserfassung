<?php

/* **************

Websystem für das Impfpasszentrum
Author: Marc S. Duchene
June 2021

tools

** ************** */

/****************************************/
/* SQL functions */
/****************************************/

// Open DB connection
function S_open_db () {
	/* Database connection information */
	$gaSql=$GLOBALS['gaSql_server'];
	
	// Connect to DB
	$link=mysqli_connect($gaSql['server'],$gaSql['user'],$gaSql['password'],$gaSql['db']);
	if (!$link) {
		echo "<br>Fehler: konnte nicht mit MySQL verbinden.";
		echo "<br>Debug-Fehlernummer: " . mysqli_connect_errno();
		echo "<br>Debug-Fehlermeldung: " . mysqli_connect_error();
		echo "<br>";
		exit;
	}

	if (!$link) {
		header('Location: error.php?e=err80');
	}

	// Return the database object
	return $link;
}

// Close DB connection
function S_close_db ($Db) {
	mysqli_close($Db);
	return 0;
}


// Return query result from SQL database - first entry only
function S_get_entry ($Db,$sQuery) {
	$result = mysqli_query( $Db, $sQuery );
	$r = mysqli_fetch_all($result);

	// Return result of SQL query
	return $r[0][0];
}
// Only for login
function S_get_entry_login_username ($Db,$username) {
	$stmt=mysqli_prepare($Db,"SELECT id FROM li_user WHERE lower(username)=?;");
	mysqli_stmt_bind_param($stmt, "s", $username);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $id);
	mysqli_stmt_fetch($stmt);
	mysqli_stmt_close($stmt);

	// Return result of SQL query
	return $id;
}
// Only for login
function S_get_entry_login_firmencode ($Db,$firmencode) {
	$stmt=mysqli_prepare($Db,"SELECT id FROM Station WHERE Firmencode=?;");
	mysqli_stmt_bind_param($stmt, "s", $firmencode);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $id);
	mysqli_stmt_fetch($stmt);
	mysqli_stmt_close($stmt);

	// Return result of SQL query
	return $id;
}
// Return query result from SQL database - all entries
function S_get_multientry ($Db,$sQuery) {
	$result = mysqli_query( $Db, $sQuery );
	$r = mysqli_fetch_all($result);

	// Return result of SQL query
	return $r;
}
// Write data
function S_set_data ($Db,$sQuery) {
    $r = mysqli_query( $Db, $sQuery );
	
	// Return result of SQL query
	return $r;
}


function S_get_entry_vorgang ($Db,$scanevent) {
	$token=substr($scanevent, strrpos($scanevent, 'K' )+1);
	$stmt=mysqli_prepare($Db,"SELECT id, Used FROM Kartennummern WHERE id=?;");
	mysqli_stmt_bind_param($stmt, "i", $token);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $id_kartennummer, $used);
	mysqli_stmt_fetch($stmt);
	mysqli_stmt_close($stmt);
	// Check if number exists in table Kartennummern
	if($used==1) {
		// Karte bereits verwendet mit Ergebnis
		return "Used";
	} elseif($id_kartennummer>0) {
		$stmt=mysqli_prepare($Db,"SELECT id FROM Vorgang WHERE Token=?;");
		mysqli_stmt_bind_param($stmt, "s", $token);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $id);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);

		// Return result of SQL query
		return $id;
	} else {
		return "Not registered";
	}
}

function S_set_entry_voranmeldung ($Db,$array_data) {
	// First, check if Termin_id is already used by same person (  to fix a bug found by N.B. <3  )
	$stmt=mysqli_prepare($Db,"SELECT id FROM Voranmeldung WHERE Vorname=? AND Nachname=? AND Geburtsdatum=? AND Adresse=? AND Wohnort=?  AND Erstimpfstoff_id=? AND Zweitimpfstoff_id=NULLIF(?,0) AND Erstimpfung=? AND Zweitimpfung=? AND Mailadresse=? AND Tag=?;");
	mysqli_stmt_bind_param($stmt, "sssssiissss", $array_data[0], $array_data[1], $array_data[2], $array_data[3], $array_data[4], $array_data[5], $array_data[6], $array_data[7], $array_data[8], $array_data[9], $array_data[11]);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $double_entry_id);
	mysqli_stmt_fetch($stmt);
	mysqli_stmt_close($stmt);

	if($double_entry_id>0) {
		return 'DOUBLE_ENTRY';
	}

	// Second, check if Termin_id is already used by other person
	$stmt=mysqli_prepare($Db,"SELECT id, Slot, id_station, Tag, Stunde FROM Termine WHERE id=?;");
	mysqli_stmt_bind_param($stmt, "i", $array_data[10]);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $termin_id, $termin_slot, $termin_station, $termin_tag, $termin_stunde);
	mysqli_stmt_fetch($stmt);
	mysqli_stmt_close($stmt);
	if($termin_slot>0) {
		$check_termin_id=S_get_entry($Db,'SELECT id FROM Voranmeldung WHERE Termin_id=CAST('.$termin_id.' as int);');
	} else {
		$check_termin_id=0;
	}
	if($check_termin_id>0) {
		// Selected Termin is used, select another in same slot if available
		$new_termin_id=S_get_entry($Db,'SELECT id FROM Termine WHERE id_station='.$termin_station.' AND Tag="'.$termin_tag.'" AND Stunde='.$termin_stunde.' AND Slot='.$termin_slot.' AND Used is null;');
		if($new_termin_id>0) {
			// is available - use new termin_id
			$termin_id=$new_termin_id;
		} else {
			// is not available
			return 0;
		}
	} 

	// Write data because Termin_id is not used or Termin_id has no slots
	if($termin_slot>0) {
		S_set_data($Db,'UPDATE Termine SET Used=1 WHERE id=CAST('.$termin_id.' as int);');
	}
	$stmt=mysqli_prepare($Db,"INSERT INTO Voranmeldung (Vorname, Nachname, Geburtsdatum, Adresse, Wohnort, Erstimpfstoff_id, Zweitimpfstoff_id, Erstimpfung, Zweitimpfung, Mailadresse, Termin_id, Tag) VALUES (?,?,?,?,?,?,NULLIF(?,0),?,NULLIF(?,''),?,?,?);");
	mysqli_stmt_bind_param($stmt, "sssssiisssis", $array_data[0], $array_data[1], $array_data[2], $array_data[3], $array_data[4], $array_data[5], $array_data[6], $array_data[7], $array_data[8],$array_data[9], $termin_id, $array_data[11]);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $result);
	mysqli_stmt_fetch($stmt);
	mysqli_stmt_close($stmt);

	$stmt=mysqli_prepare($Db,"SELECT id FROM Voranmeldung WHERE Vorname=? AND Nachname=? AND Geburtsdatum=? AND Adresse=? AND Wohnort=? AND Erstimpfstoff_id=? AND  Erstimpfung=? AND Mailadresse=? AND Termin_id=? AND Tag=? ORDER BY id DESC;");
	mysqli_stmt_bind_param($stmt, "sssssissis", $array_data[0], $array_data[1], $array_data[2], $array_data[3], $array_data[4], $array_data[5], $array_data[7], $array_data[9], $termin_id, $array_data[11]);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $result2);
	mysqli_stmt_fetch($stmt);
	mysqli_stmt_close($stmt);
	return $result2; // need id as a return value
	
}

function S_get_entry_voranmeldung ($Db,$array_data) {
	$stmt=mysqli_prepare($Db,"SELECT id_preregistration FROM Voranmeldung_Verif WHERE id_preregistration=? AND Token=?;");
	mysqli_stmt_bind_param($stmt, "is", $array_data[0], $array_data[1]);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $id);
	mysqli_stmt_fetch($stmt);
	mysqli_stmt_close($stmt);
	return $id;
}
function S_get_entry_voranmeldung_debug ($Db,$data) {
	$stmt=mysqli_prepare($Db,"SELECT Token FROM Voranmeldung WHERE id=?;");
	mysqli_stmt_bind_param($stmt, "i", $data);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $id);
	mysqli_stmt_fetch($stmt);
	mysqli_stmt_close($stmt);
	return $id;
}


/****************************************/
/* Auxilliary functions */
/****************************************/

// Generate random token
function A_generate_token($length = 8,$mode='alphanum') {
	if($mode=='num') {
		$characters = '0123456789';
	} elseif($mode=='alphacapitalsnum') {
		$characters = '123456789ABCDEFGHKLMNPQRSTUVWXYZ';
	} else {
		// without 0, O, o, z, Z, y, Y, l
		$characters = '123456789abcdefghijkmnpqrstuvwxABCDEFGHIJKLMNPQRSTUVWX';
	}
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Login for user with $uid
function A_login_firmencode($Db,$sid) {
    
	$_SESSION['b2b_id'] = $sid;
	if($_SESSION['b2b_id']=='') { die("Error in database. (Err:102)"); }
	
	$_SESSION['b2b_signedin'] = true;
	$_SESSION['b2b_username'] = S_get_entry($Db,'SELECT Ort FROM Station WHERE id='.$sid.';');

    return true;
}

function A_get_day_name($number_of_week) {
	$days=array('Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag');
	return $days[$number_of_week];
}
function A_get_day_name_2($number_of_week) {
	$days=array('So','Mo','Di','Mi','Do','Fr','Sa');
	return $days[$number_of_week];
}

function A_sanitize_input($input) {
	// strips any HTML and PHP tags
	strip_tags($input);

	// validate white listed chars in input (alphanumeric)
	$validated = "";
	if(preg_match("/^[a-zA-Z0-9\-]+$/", $input)) {
		$validated = $input;
	}
	
	return $validated;
}


/****************************************/
/* HTML code snippets */
/****************************************/

function H_build_boxhead( $w, $id, $title, $j=0 ) {
	if($w>0) {
		$w_string='width: '.$w.'px;';
		$w_string50='width: '.($w-50).'px;';
		$w_string10='width: '.($w-10).'px;';
	}
	$class_add='';
	$margin_add='';
	$margin_add='margin-right:15px;';
	$html_result = '<div style="float:none; '.$margin_add.'"><div class="FAIR-box-head'.$class_add.'" style="'.$w_string.' position: relative; z-index:'.(50-$j).';">
  <div class="FAIR-foldbox-head-left" style="display: inline; '. $w_string50.'">
  '.$title.'
  </div>
  <div class="FAIR-foldbox-head-right" style="display: inline; width: 50px;">&nbsp;
  </div></div>
  <div class="FAIR-foldbox-static '.$class_add.'" id="'.$id.'" style="display: block; '.$w_string.' position: relative; z-index: '.(49-$j).';">';
	return $html_result;
}
// return html code for info card after foldbox header
function H_build_boxinfo( $w, $text, $c='red' ) {
	if($c=='red') { $class='alert alert-danger'; }
	elseif($c=='blue') { $class='alert alert-info'; }
	elseif($c=='green') { $class='alert alert-success'; }
	else { $class='alert alert-warning'; }
	if($w==0) {
		$html_result = '<div class="'.$class.'" style="top: -5px">';
	} else {
		$html_result = '<div class="'.$class.'" style="width: '. ($w-20) .'px; left: -10px; top: -5px">';
	}
	$html_result .= '<p style="margin-right: 4px;">'.$text.'</p>';
	$html_result .= '</div>';
	return $html_result;
}
// return html code for box style
function H_build_boxheadinner( $w, $id, $title, $j=0 ) {
	$html_result = '<div style="float: none; margin-right:15px;"><div class="FAIR-box-head-inner" style="width: '.$w.'px; position: relative; z-index:'.(50-$j).';">
  <div class="FAIR-foldbox-head-left" style="display: inline; width: '. ($w-50) .'px;">
  '.$title.'
  </div>
  <div class="FAIR-foldbox-head-right" style="display: inline; width: 50px;">
  </div></div>
  <div class="FAIR-foldbox-static-inner" id="'.$id.'" style="display: block; width: '. ($w-10) .'px; position: relative; z-index: '.(49-$j).';">';
	return $html_result;
}

function H_build_boxfoot( ) {
	$html_result = '</div></div>';
	return $html_result;
}

function H_build_table_testdates( ) {
	$res='';
	$Db=S_open_db();
	$flag_prereg=S_get_entry($Db,'SELECT value FROM website_settings WHERE name="FLAG_Pre_registration";');
	$stations_array=S_get_multientry($Db,'SELECT id, Ort, Adresse FROM Station WHERE Firmencode is null OR Firmencode="";');
	// X ist Anzahl an Tagen für Vorschau in Tabelle
	$X=14;
	// Ohne Terminbuchung für nächste X Tage
	$today=date('Y-m-d');
	$in_x_days=date('Y-m-d', strtotime($today. ' + '.$X.' days'));
	

	// Table
	$res.='
	<table class="FAIR-data">
	<tr>
    <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-gray"><h4>Ort</h4></td>';
	for($j=0;$j<$X;$j++) {
		$string_date=date('d.m.', strtotime($today. ' + '.$j.' days'));
		$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-center1 FAIR-data-gray"><h4>'.$string_date.'</h4></td>';
	}
	$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-gray"></td></tr>';

	$res.='<tr>
    <td class="FAIR-data-height1 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-blue1" colspan="'.($X+2).'"><b><i>';
	if($flag_prereg!=0) {
		//$res.='Für folgende Teststationen ist keine Terminbuchung möglich, eine Voranmeldung kann gerne gemacht werden';
		$res.='Für folgende Teststationen ist keine Voranmeldung notwendig';
	} else {
		$res.='Für folgende Teststationen ist keine Voranmeldung notwendig';
	}
	$res.='</i></b></td>
	</tr>';
	foreach($stations_array as $st) {
		// check if station has appointed times
		if( S_get_entry($Db,'SELECT id_station FROM Termine WHERE Slot is null AND Date(Tag)>="'.$today.'" AND Date(Tag)<="'.$in_x_days.'" AND id_station='.$st[0].';')==$st[0]) {
			/* $location_thirdline_val=S_get_entry($Db,'SELECT Oeffnungszeiten FROM Station WHERE id='.$st[0].';');
			if($location_thirdline_val!='') {
				$display_location_thirdline='<br><span class="text-sm">'.$location_thirdline_val.'</span>';
			} else {
				$display_location_thirdline='';
			} */$display_location_thirdline='';
			$res.='<tr>';
			$string_location='<b>'.$st[1].'</b><br>'.$st[2].'';
			$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-blue2">'.$string_location.$display_location_thirdline.'</td>';
			for($j=0;$j<$X;$j++) {
				$in_j_days=date('Y-m-d', strtotime($today. ' + '.$j.' days'));
				$array_termine_open=S_get_multientry($Db,'SELECT Startzeit, Endzeit, opt_station, opt_station_adresse FROM Termine WHERE Slot is null AND id_station='.$st[0].' AND Date(Tag)="'.$in_j_days.'" ORDER BY Startzeit ASC;');
				$string_times='';
				$string_location_opt='';
				foreach($array_termine_open as $te) {
					if($te[2]!='') {
						$string_times.='<span class="text-sm">'.$te[2].',<br>'.$te[3].'</span><br>';
					}
					$string_times.=date('H:i',strtotime($te[0])).' - '.date('H:i',strtotime($te[1])).'<br>';
				}
				if($string_times!='') {
					$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-center1 FAIR-data-blue2">'.$string_times.'</td>';
				} else {
					$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-center1 FAIR-data-blue3"></td>';
				}
			}
			$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-center1 FAIR-data-blue3"></td>';
			$res.='</tr>';
		}
		
	}

	
	$res.='<tr>
    <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-gray"><h4>Ort</h4></td>';
	for($j=0;$j<$X;$j++) {
		$string_date=date('d.m.', strtotime($today. ' + '.$j.' days'));
		$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-center1 FAIR-data-gray"><h4>'.$string_date.'</h4></td>';
	}
	$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-gray"></td></tr>';
    
	$res.='</table>
	';

	S_close_db($Db);
	return $res;
}

function H_build_table_testdates2( $mode ) {
	if($mode=='impfpass') {
		$path_to_reg='registration/';
	}
	$res='';
	$res_s_array=array(); // for small displays - array with one element per day
	$Db=S_open_db();
	$flag_prereg=S_get_entry($Db,'SELECT value FROM website_settings WHERE name="FLAG_Pre_registration";');
	$stations_array=S_get_multientry($Db,'SELECT Station.id, Station.Ort, Station.Adresse FROM Station ;');
	// X ist Anzahl an Tagen für Vorschau in Tabelle
	$X=10;
	// Ohne Terminbuchung für nächste X Tage
	$today=date('Y-m-d');
	$in_x_days=date('Y-m-d', strtotime($today. ' + '.$X.' days'));
	
	$bool_valid_appointments_found=false;

	// Table
	$res.='
	<table class="FAIR-data">
	<tr>
    <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-gray"><h4>Ort</h4></td>';
	for($j=0;$j<$X;$j++) {
		$string_date=A_get_day_name_2(date('w', strtotime($today. ' + '.$j.' days'))).' '.date('d.m.', strtotime($today. ' + '.$j.' days'));
		$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-center1 FAIR-data-gray"><h4>'.$string_date.'</h4></td>';
		$string_date=A_get_day_name(date('w', strtotime($today. ' + '.$j.' days'))).' '.date('d.m.', strtotime($today. ' + '.$j.' days'));
		$res_s_array[$j][0]='<div class="cal-day-head">'.$string_date.'</div>';
	}
	$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-gray"></td></tr>';

	foreach($stations_array as $st) {
		if($st[3]==1) {
			$cal_color='red';
		} else {
			$cal_color='blue';
		}
		// check if station has appointed times
		if( S_get_entry($Db,'SELECT id_station FROM Termine WHERE Slot is null AND Date(Tag)>="'.$today.'" AND Date(Tag)<="'.$in_x_days.'" AND id_station='.$st[0].';')==$st[0]) {
			$display_location_thirdline='';
			$res.='<tr>';
			$string_location='<b>'.$st[1].'</b><br>'.$st[2].'';
			$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-'.$cal_color.'2">'.$string_location.$display_location_thirdline.'</td>';
			for($j=0;$j<$X;$j++) {
				$in_j_days=date('Y-m-d', strtotime($today. ' + '.$j.' days'));
				$array_termine_open=S_get_multientry($Db,'SELECT id,Startzeit, Endzeit FROM Termine WHERE Slot is null AND id_station='.$st[0].' AND Date(Tag)="'.$in_j_days.'" ORDER BY Startzeit ASC;');
				$string_times='';
				$string_times_small='';
				foreach($array_termine_open as $te) {

					$string_location_small=$string_location;

					$string_times.=date('H:i',strtotime($te[1])).' - '.date('H:i',strtotime($te[2])).'<br>';
					$string_times_small.=date('H:i',strtotime($te[1])).' - '.date('H:i',strtotime($te[2])).'<br>';

					$bool_valid_appointments_found=true;
				}
				
				if($string_times!='') {
					if($flag_prereg==0) {
						$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-center1 FAIR-data-'.$cal_color.'2">
						'.$string_times.'</td>';

						$res_s_array[$j][1].='<div class="cal-element calendar'.$cal_color.'">'.$string_location_small.$display_location_thirdline.'<br>'.$string_times_small.'</div>';

					} else {
						$res.='<td onclick="window.location=\''.$path_to_reg.'index.php?appointment='.$te[0].'\'" class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-center1 FAIR-data-'.$cal_color.'2 calendar'.$cal_color.'">'.$string_times.'</td>';
						$res_s_array[$j][1].='<div class="cal-element calendar'.$cal_color.'" onclick="window.location=\''.$path_to_reg.'index.php?appointment='.$te[0].'\'">'.$string_location_small.$display_location_thirdline.'<br>'.$string_times_small.'</div>';
					}
					
				} else {
					$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-center1 FAIR-data-blue3"></td>';
				}
			}
			$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-center1 FAIR-data-blue3"></td>';
			$res.='</tr>';
		}
		
	}

	if($flag_prereg!=0) {
		
		foreach($stations_array as $st) {
			if($st[3]==1) {
				$cal_color='red';
			} else {
				$cal_color='yellow';
			}
			// check if station has appointed times
			if( S_get_entry($Db,'SELECT id_station FROM Termine WHERE Slot>0 AND Date(Tag)>="'.$today.'" AND Date(Tag)<="'.$in_x_days.'" AND id_station='.$st[0].';')==$st[0]) {
				$location_thirdline_val=S_get_entry($Db,'SELECT Oeffnungszeiten FROM Station WHERE id='.$st[0].';');
				if($location_thirdline_val!='') {
					$display_location_thirdline='<br><span class="text-sm">Öffnungszeiten '.$location_thirdline_val.'</span>';
				} else {
					$display_location_thirdline='';
				}
				$res.='<tr>';
				$string_location='<b>'.$st[1].'</b><br>'.$st[2].'';
				$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-'.$cal_color.'2">'.$string_location.$display_location_thirdline.'</td>';
				for($j=0;$j<$X;$j++) {
					$in_j_days=date('Y-m-d', strtotime($today. ' + '.$j.' days'));
					if($j==0) {
						// TODAY do not show past entries
						$current_hour=date('G');
						$array_termine_open=S_get_multientry($Db,'SELECT count(id), count(Used) FROM Termine WHERE Slot>0 AND id_station='.$st[0].' AND Date(Tag)="'.$in_j_days.'" AND Stunde>='.$current_hour.';');
					} else {
						$array_termine_open=S_get_multientry($Db,'SELECT count(id), count(Used) FROM Termine WHERE Slot>0 AND id_station='.$st[0].' AND Date(Tag)="'.$in_j_days.'";');
					}
					

					$count_free=$array_termine_open[0][0]-$array_termine_open[0][1];
					$display_termine='<div style="display: block; margin-top: 5px;"><span class="label label-success">'.($count_free).'</span></div><span class="text-sm"><div style="display: block; margin-top: 5px;">freie&nbsp;Termine</div></span>';
					if($count_free>0) {
						$string_times='';

						// get times
						$value_termine_times1=S_get_entry($Db,'SELECT Stunde FROM Termine WHERE Slot>0 AND id_station='.$st[0].' AND Date(Tag)="'.$in_j_days.'" ORDER BY Stunde ASC;');
						$value_termine_times2=S_get_entry($Db,'SELECT Stunde FROM Termine WHERE Slot>0 AND id_station='.$st[0].' AND Date(Tag)="'.$in_j_days.'" ORDER BY Stunde DESC;');
						$value_termine_id=S_get_entry($Db,'SELECT id FROM Termine WHERE Slot>0 AND id_station='.$st[0].' AND Date(Tag)="'.$in_j_days.'" ORDER BY Stunde ASC;');

						$res.='<td onclick="window.location=\''.$path_to_reg.'index.php?appointment='.($value_termine_id).'\'" class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-center1 FAIR-data-'.$cal_color.'2 calendar'.$cal_color.'">'.$string_times.$display_termine.'</td>';
						$res_s_array[$j][1].='<div class="cal-element calendar'.$cal_color.'" onclick="window.location=\''.$path_to_reg.'index.php?appointment='.($value_termine_id).'\'">'.$string_location.$display_location_thirdline.'<br>'.$string_times.$display_termine.'</div>';

						$bool_valid_appointments_found=true;
					} else {
						$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-center1 FAIR-data-yellow3"></td>';
					}
				}

				$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-center1 FAIR-data-yellow3"></td>';
				$res.='</tr>';
			}
			
		}
	}

	if(!$bool_valid_appointments_found) {
		$res.='<tr><td class="FAIR-data-height1 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-center1" colspan="'.($X+2).'"><b>Keine Termine gefunden<b></td></tr>';
	}
	
	$res.='<tr>
    <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-gray"><h4>Ort</h4></td>';
	for($j=0;$j<$X;$j++) {
		$string_date=A_get_day_name_2(date('w', strtotime($today. ' + '.$j.' days'))).' '.date('d.m.', strtotime($today. ' + '.$j.' days'));
		$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-center1 FAIR-data-gray"><h4>'.$string_date.'</h4></td>';
	}
	$res.='<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top FAIR-data-gray"></td></tr>';
    
	$res.='</table>
	';

	S_close_db($Db);
	return array($res,$res_s_array);
}

?>