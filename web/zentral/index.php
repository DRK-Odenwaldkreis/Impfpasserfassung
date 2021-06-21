<?php

/* **************

Websystem für das Testzentrum DRK Odenwaldkreis
Author: Marc S. Duchene
March 2021

** ************** */

include_once 'preload.php';
if( isset($GLOBALS['G_sessionname']) ) { session_name ($GLOBALS['G_sessionname']); }
session_start();
$sec_level=1;
$current_site="index";


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

// Select station for statistics
if($_SESSION['station_id']>0) {
    $station=$_SESSION['station_id'];
} else {
    $station=1;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['select_station'])) {
        $station=$_POST['station_id'];
    }
}




echo '<div class="row">';
echo '<div class="col-sm-6">
<h3>Vor Ort</h3>
<div class="list-group">';
foreach($_module_array1 as $key=>$a) {
    $show_entry=false;
    $show_entry_disabled=false;
    foreach($a["role"] as $b) {
        if($b>0 && $_SESSION['roles'][$b]==1) { 
            $show_entry=true;
        }
    }
    foreach($a["role-disabled"] as $b) {
        if($b>0 && $_SESSION['roles'][$b]==1) { 
            $show_entry_disabled=true;
        }
    }
    if($show_entry) { 
        echo '<a class="list-group-item list-group-item-action list-group-item-FAIR" id="module-'.$key.'" href="'.$a["link"].'">'.$a["text"].'</a>';
    } elseif($show_entry_disabled) {
        echo '<a class="list-group-item list-group-item-action list-group-item-FAIR disabled" id="module-'.$key.'" >'.$a["text"].'</a>';
    }
}
echo '</div></div>';
echo '<div class="col-sm-6">
<h3>Verwaltung</h3>
<div class="list-group">';
foreach($_module_array2 as $key=>$a) {
    $show_entry=false;
    $show_entry_disabled=false;
    foreach($a["role"] as $b) {
        if($b>0 && $_SESSION['roles'][$b]==1) { 
            $show_entry=true;
        }
    }
    foreach($a["role-disabled"] as $b) {
        if($b>0 && $_SESSION['roles'][$b]==1) { 
            $show_entry_disabled=true;
        }
    }
    if($show_entry) { 
        echo '<a class="list-group-item list-group-item-action list-group-item-FAIR" id="module-'.$key.'" href="'.$a["link"].'">'.$a["text"].'</a>';
    } elseif($show_entry_disabled) {
        echo '<a class="list-group-item list-group-item-action list-group-item-FAIR disabled" id="module-'.$key.'" >'.$a["text"].'</a>';
    }
}
echo '</div></div>';
echo '</div>';


// ////////////////////////////
// Test statistics

// Open database connection
$Db=S_open_db();
$stations_array=S_get_multientry($Db,'SELECT id, Ort FROM Station;');
$today=date("Y-m-d",time());
$yesterday=date("Y-m-d",time() - 60 * 60 * 24);
$beforetwodays=date("Y-m-d",time() - 2 * 60 * 60 * 24);


$stat_val_total=S_get_entry($Db,'SELECT count(id) From Vorgang;');
$stat_val_total_day=S_get_entry($Db,'SELECT count(id) From Vorgang WHERE Date(Registrierungszeitpunkt)=\''.$today.'\';');
$stat_val_total_yday=S_get_entry($Db,'SELECT count(id) From Vorgang WHERE Date(Registrierungszeitpunkt)=\''.$yesterday.'\';');
$stat_val_total_2day=S_get_entry($Db,'SELECT count(id) From Vorgang WHERE Date(Registrierungszeitpunkt)=\''.$beforetwodays.'\';');


// Close connection to database
S_close_db($Db); 

echo '<div class="row">';
echo '<div class="col-md-4">
<div class="alert alert-info" role="alert">
<p>Erfasste Personen</p>
<h3><span class="FAIR-text-sm">heute</span> '.$stat_val_total_day.'</h3>
<h3><span class="FAIR-text-sm">gestern</span> '.$stat_val_total_yday.'</h3>
<h3><span class="FAIR-text-sm">vorgestern</span> '.$stat_val_total_2day.'</h3>
<h3><span class="FAIR-text-sm">insgesamt</span> '.$stat_val_total.'</h3>
</div>';


echo '</div>';
echo '</div>';
echo '</div>';

// Test statistics
// ////////////////////////////


echo '</div></div>';



// Print html content part C
echo $GLOBALS['G_html_main_right_c'];
// Print html footer
echo $GLOBALS['G_html_footer'];

?>