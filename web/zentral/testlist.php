<?php

/* **************

Websystem für das Impfpasszentrum
Author: Marc S. Duchene
June 2021


_- **************
DataTables is available under the MIT license. In short, this means that you are free to use DataTables as you wish, including modifying and redistributing the code, as long as the original copyright notice is retained.
MIT license

Copyright (C) 2008-2021, SpryMedia Ltd.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
_- **************



** ************** */

include_once 'preload.php';
if( isset($GLOBALS['G_sessionname']) ) { session_name ($GLOBALS['G_sessionname']); }
session_start();
$sec_level=1;
$current_site="testlist";

// Include functions
include_once 'tools.php';
include_once 'auth.php';
include_once 'menu.php';

// role check
if( A_checkpermission(array(1,2,0,4,5)) ) {





  $today=date("Y-m-d",time());

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // show different date
    if(isset($_POST['show_times'])) {
      $u_date=($_POST['date']);
      $today=$u_date; // for overwriting default search value in box
    } elseif(isset($_POST['show_times_minus1'])){
      $u_date=($_POST['date']);
      $today=date("Y-m-d",strtotime($u_date.' - 1 days'));
    } elseif(isset($_POST['show_times_plus1'])){
      $u_date=($_POST['date']);
      $today=date("Y-m-d",strtotime($u_date.' + 1 days'));
    } elseif(isset($_POST['show_times_today'])){
      $today=$today;
    } elseif(isset($_POST['show_sensitive_data'])) {
      $today=$_POST['date'];
      $_SESSION['display_sensitive']=1;
    } elseif(isset($_POST['unshow_sensitive_data'])) {
      $today=$_POST['date'];
      $_SESSION['display_sensitive']=0;
    }
  }

  // Create CSV export file
  $val_report_display=0;
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      if(isset($_POST['create_export_csv'])) {
          $date=($_POST['date']);
          $uid=$_SESSION['uid'];
          $dir="/home/webservice/Testerfassung/CSVExport/";
          chdir($dir);
          if( A_checkpermission(array(0,0,0,0,5)) && !A_checkpermission(array(0,2,0,4,0)) ) {
            $job="python3 job.py $date $uid";
          } else {
            $job="python3 job.py $date";
          }
          exec($job,$script_output);
          $file=$script_output[0];
          if( file_exists("/home/webservice/Reports/$file") ) {
              header('Content-Type: application/octet-stream');
              header('Content-Disposition: attachment; filename="'.basename($file).'"');
              header('Pragma: no-cache');
              header('Expires: 0');
              readfile("/home/webservice/Reports/$file");
              exit;
          }
          
      }
    }



    // pre settings for DataTables
    $local_dt_language="'language': {
			'info': 'Zeige _START_ bis _END_ von _TOTAL_ Einträgen',
			'infoFiltered': '(gefiltert aus _MAX_ Einträgen)',
			'search': 'Suchen:',
			select: {
				rows: {
					_: '%d Zeilen ausgewählt',
					0: '',
					1: '1 Zeile ausgewählt'
				}
			}
        },";



    // Print html header
    //echo $GLOBALS['G_html_header'];
    echo '<head>
    <title>Kundenliste</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-ico; charset=binary" />
    <link rel="icon" href="img/favicon.ico" type="image/x-ico; charset=binary" />

    <link href="css/bootstrap.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="css/dashboard.css" rel="stylesheet">
    <link href="css/symbols-fair.css" rel="stylesheet">
    
    <script type="text/javascript" src="lib/datatables/jQuery-3.3.1/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="lib/datatables/Bootstrap-3.3.7/js/bootstrap.min.js"></script>
    
    <link rel="stylesheet" type="text/css" href="lib/datatables/datatables.min.css"/>
    <script type="text/javascript" src="lib/datatables/datatables.min.js"></script>

    </head>';

    // Print html menu
    echo $GLOBALS['G_html_menu'];
    echo $GLOBALS['G_html_menu2'];
  
    // Print html content part A
    echo $GLOBALS['G_html_main_right_a'];

  // Open database connection
  $Db=S_open_db();

  // Get all data for today or another day
  if( A_checkpermission(array(1,0,0,0,0)) && !A_checkpermission(array(0,2,0,4,5)) ) {
    // only for own station and today
    $today=date("Y-m-d",time());
    $array_tests=S_get_multientry($Db,'SELECT Vorgang.id, Vorgang.Teststation, Vorgang.Reg_type, Vorgang.Registrierungszeitpunkt, 0, Vorgang.Nachname, Vorgang.Vorname, Vorgang.Adresse, Vorgang.Wohnort, 0, Vorgang.Mailadresse, Vorgang.Geburtsdatum, Impfstoff1.Name, Vorgang.Erstimpfung, Impfstoff2.Name, Vorgang.Zweitimpfung FROM Vorgang LEFT OUTER JOIN Impfstoff AS Impfstoff1 ON Impfstoff1.id=Vorgang.Erstimpfstoff_id LEFT OUTER JOIN Impfstoff AS Impfstoff2 ON Impfstoff2.id=Vorgang.Zweitimpfstoff_id WHERE Date(Vorgang.Registrierungszeitpunkt)="'.$today.'" AND Vorgang.Teststation=CAST('.$_SESSION['station_id'].' AS int) ORDER BY Vorgang.Registrierungszeitpunkt DESC;');
  } elseif( A_checkpermission(array(0,0,0,0,5)) && !A_checkpermission(array(0,2,0,4,0)) ) {
    // only for own station
    $array_tests=S_get_multientry($Db,'SELECT Vorgang.id, Vorgang.Teststation, Vorgang.Reg_type, Vorgang.Registrierungszeitpunkt, 0, Vorgang.Nachname, Vorgang.Vorname, Vorgang.Adresse, Vorgang.Wohnort, 0, Vorgang.Mailadresse, Vorgang.Geburtsdatum, Impfstoff1.Name, Vorgang.Erstimpfung, Impfstoff2.Name, Vorgang.Zweitimpfung FROM Vorgang LEFT OUTER JOIN Impfstoff AS Impfstoff1 ON Impfstoff1.id=Vorgang.Erstimpfstoff_id LEFT OUTER JOIN Impfstoff AS Impfstoff2 ON Impfstoff2.id=Vorgang.Zweitimpfstoff_id WHERE Date(Vorgang.Registrierungszeitpunkt)="'.$today.'" AND Vorgang.Teststation=CAST('.$_SESSION['station_id'].' AS int)  ORDER BY Vorgang.Registrierungszeitpunkt DESC;');
  } else {
    // for all stations
    $array_tests=S_get_multientry($Db,'SELECT Vorgang.id, Vorgang.Teststation, Vorgang.Reg_type, Vorgang.Registrierungszeitpunkt, 0, Vorgang.Nachname, Vorgang.Vorname, Vorgang.Adresse, Vorgang.Wohnort, 0, Vorgang.Mailadresse, Vorgang.Geburtsdatum, Impfstoff1.Name, Vorgang.Erstimpfung, Impfstoff2.Name, Vorgang.Zweitimpfung FROM Vorgang LEFT OUTER JOIN Impfstoff AS Impfstoff1 ON Impfstoff1.id=Vorgang.Erstimpfstoff_id LEFT OUTER JOIN Impfstoff AS Impfstoff2 ON Impfstoff2.id=Vorgang.Zweitimpfstoff_id WHERE Date(Vorgang.Registrierungszeitpunkt)="'.$today.'"  ORDER BY Vorgang.Registrierungszeitpunkt DESC;');

  }


  echo '<h1>Ansicht der registrierten Impfdaten / Kundenliste</h1>';

  echo '<div class="card">
  <div class="row">';

  echo '<div class="col-sm-4">';
  echo '<p></p>';
  if( A_checkpermission(array(1,0,0,0,0)) && !A_checkpermission(array(0,2,0,4,5)) ) {
    // only today
    echo '<input type="date" class="form-control" placeholder="Tag wählen" aria-describedby="basic-addonA2" value="'.$today.'" name="date" disabled>';
  } else {
    // choose a day
    echo'<form action="'.$current_site.'.php" method="post">
    <div class="input-group">
    <span class="input-group-addon" id="basic-addonA2">Tag auswählen</span>
    <input type="date" class="form-control" placeholder="Tag wählen" aria-describedby="basic-addonA2" value="'.$today.'" name="date">
    <span class="input-group-btn">
    <input type="submit" class="btn btn-primary" value="Liste anzeigen" name="show_times" />
    <input type="submit" class="btn btn-default" value="- 1 Tag" name="show_times_minus1" />
    <input type="submit" class="btn btn-default" value="Heute" name="show_times_today" />
    <input type="submit" class="btn btn-default" value="+ 1 Tag" name="show_times_plus1" />
    </span>
    </div></form>';
  }

  // Button to switch between sensitive data to display
  echo'<form action="'.$current_site.'.php" method="post">
  <input type="date" value="'.$today.'" name="date" style="display: none;">
  <div class="FAIR-si-button">';
  if($_SESSION['display_sensitive']==0) {
    echo '<button type="submit" class="btn btn-primary" name="show_sensitive_data"><span class="icon-eye"></span>&nbsp;Zeige personenbezogene Daten</button>';
  } else {
    echo '<button type="submit" class="btn btn-primary active" name="unshow_sensitive_data"><span class="icon-eye-blocked"></span>&nbsp;Blende personenbezogene Daten aus</button>';
  }
  echo'
    </div></form>
  </div>';

  echo '</div>';

  echo '
  <div class="col-sm-12">
  <table class="FAIR-data" id="maintable" data-order=\'[[ 0, "desc" ]]\' data-page-length=\'10000\'>';
  
  
    echo '<thead>
    <tr>
    <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"><h4>Lfd. ID</h4></td>
    <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"><h4>Stations-ID</h4></td>
    <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"><h4>Registr.</h4></td>
    <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"><h4>Name</h4></td>
    <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"><h4>Geburtsdatum</h4></td>
    <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"><h4>Adresse</h4></td>
    <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"><h4>E-Mail</h4></td>
    <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"><h4>1. Impfung</h4></td>
    <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"><h4>2. Impfung</h4></td>
    <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"></td>
    </tr>
    </thead><tbody>';

  //Get list of times
  foreach($array_tests as $i) {




    

    // Color code for station
    if($i[1]<15) {
      $color_st_code=(255-5*$i[1]).','.(255-7*$i[1]).','.(255-11*$i[1]);
    } elseif($i[1]<30) {
      $color_st_code=(255-3*$i[1]).','.(255-2*$i[1]).','.(255-1*$i[1]);
    } elseif($i[1]<60) {
      $color_st_code=(255-2*$i[1]).','.(255-1*$i[1]).','.(255-2*$i[1]);
    } else {
      $color_st_code='255,255,255';
    }


    echo '
    
    <tr>
    <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">#'.$i[0].'</td>
    
    ';
    if($_SESSION['display_sensitive']==0) {
      echo '<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top" style="background-color:rgb('.$color_st_code.');"><abbr title="'.$i[24].'">S'.$i[1].'</abbr></td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">'.$i[3].'<br><span class="text-sm">'.$i[2].'</span></td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"><span class="FAIR-sep-l-black"></span></td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"><span class="FAIR-sep-l-black"></span></td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"><span class="FAIR-sep-l-black"></span></td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"><span class="FAIR-sep-l-black"></span></td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"><span class="FAIR-sep-l-black"></span></td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"><span class="FAIR-sep-l-black"></span></td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top"></td>
      ';
    } else {
      echo '<td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top" style="background-color:rgb('.$color_st_code.');"><abbr title="'.$i[24].'">S'.$i[1].'</abbr></td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">'.$i[3].'<br><span class="text-sm">'.$i[2].'</span></td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">'.$i[5].', '.$i[6].'</td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">'.(date("d.m.Y",strtotime($i[11]))).'</td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">'.$i[7].'<br>'.$i[8].'</td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">'.$i[10].'</td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">'.$i[12].'<br>'.$i[13].'</td>
      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">'.$i[14].'<br>'.$i[15].'</td>

      <td class="FAIR-data-height2 FAIR-data-right FAIR-data-left FAIR-data-bottom FAIR-data-top">
      <a class="list-group-item list-group-item-action list-group-item-redtext" href="edit_person.php?id='.$i[0].'"><span class="icon-pencil"></span>&nbsp;Ändern</a>
      <a class="list-group-item list-group-item-action list-group-item-redtext" href="docs_person.php?id='.$i[0].'"><span class="icon-folder3"></span>&nbsp;Akte</a>
      </td>
      ';
    }
    echo '</tr>';
  }
  echo '</tbody></table></div></div>';


  // Initialize DataTables JavaScript
  echo "
  <script>
  $(document).ready( function () {
    $('#maintable').DataTable( {
      dom: \"frti\",
      $local_dt_language
    });
  } );
  </script>
  ";

  if(A_checkpermission(array(0,2,0,4,5))) {
    // Get XLSX file
    echo '<div class="card">
        <div class="col-sm-4">
        <h3>Export</h3>
        <p></p>';
        echo '<form action="'.$current_site.'.php" method="post">
        <div class="input-group">
          <span class="input-group-addon" id="basic-addonA2">Tag auswählen</span>
          <input type="date" class="form-control" placeholder="Tag wählen" aria-describedby="basic-addonA2" value="'.$today.'" name="date">
          </div>
            <input type="submit" class="btn btn-danger" value="Export Excel-Datei" name="create_export_csv" />
            </span>
            </div>
            </form>';
        echo $errorhtml0;
      
        echo '</div></div>';

    echo '</div>';
  }


  // Close connection to database
  S_close_db($Db);

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
