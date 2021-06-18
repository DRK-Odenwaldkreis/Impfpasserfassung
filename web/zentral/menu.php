<?php

/* **************

Websystem für das Impfpasszentrum
Author: Marc S. Duchene
June 2021

file with HTML elements
to construct website frame
and some global used values
** ************** */



// HTML header with complete <head> element
$G_html_header='<html lang="en">
  <head>
    <title>Impfpasszentrum Odenwaldkreis</title>
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
    
  </head>';


// Menu
$_module_array1=array(
  1=>array("text"=>'<h4 class="list-group-item-heading">Neue Kundenakte anlegen</h4><p class="list-group-item-text">Kunde ohne Voranmeldung eintragen</p>',"text_s"=>'<span class="icon-file-add"></span>&nbsp;Neuen Kunden',"link"=>"new_person.php","role"=>array(1,2,0,4,5),"role-disabled"=>array(0,0,0,0,0)),
  2=>array("text"=>'<h4 class="list-group-item-heading">Voranmeldungen</h4><p class="list-group-item-text">Liste der Voranmeldungen und Übernahme in Kundendaten</p>',"text_s"=>'<span class="icon-ticket"></span>&nbsp;Voranmeldungen',"link"=>"prereglist.php","role"=>array(1,2,0,4,5),"role-disabled"=>array(0,0,0,0,0)),
  10=>array("text"=>'<h4 class="list-group-item-heading">Kundenliste</h4><p class="list-group-item-text">Liste der übertragenen Kundendaten</p>',"link"=>"testlist.php","text_s"=>'<span class="icon-lab"></span>&nbsp;Kundenliste',"role"=>array(1,2,0,4,5),"role-disabled"=>array(0,0,0,0,0)),
  99=>array("text"=>'<h4 class="list-group-item-heading">Öffentliche Startseite Impfpasszentrum</h4><p class="list-group-item-text"></p>',"text_s"=>'',"link"=>"../index.php","role"=>array(1,2,3,4,5),"role-disabled"=>array(0,0,0,0,0))
);
$_module_array2=array(
  20=>array("text"=>'<h4 class="list-group-item-heading">Stationen</h4><p class="list-group-item-text">Stations-Management</p>',"text_s"=>'<span class="icon-office"></span>&nbsp;Stationen',"link"=>"station_admin.php","role"=>array(0,2,0,4,0),"role-disabled"=>array(0,0,0,0,0)),
  25=>array("text"=>'<h4 class="list-group-item-heading">Termine</h4><p class="list-group-item-text">Übersicht der angelegten Termine</p>',"text_s"=>'<span class="icon-calendar2"></span>&nbsp;Terminübersicht',"link"=>"terminlist.php","role"=>array(1,2,0,4,5),"role-disabled"=>array(0,0,0,0,0)),
  26=>array("text"=>'<h4 class="list-group-item-heading">Termine erstellen</h4><p class="list-group-item-text">Neue Termine für eine Passstation erstellen</p>',"text_s"=>'<span class="icon-cogs"></span>&nbsp;Termin-Verwaltung',"link"=>"terminerstellung.php","role"=>array(0,2,0,4,5),"role-disabled"=>array(1,0,0,0,0)),
  30=>array("text"=>'<h4 class="list-group-item-heading">Admin: Web user</h4><p class="list-group-item-text">User-Management</p>',"text_s"=>'<span class="icon-users"></span>&nbsp;User-Management',"link"=>"user_admin.php","role"=>array(0,0,0,4,0),"role-disabled"=>array(0,2,0,0,0)),
  33=>array("text"=>'<h4 class="list-group-item-heading">Admin: Files</h4><p class="list-group-item-text">Dateien</p>',"text_s"=>'',"link"=>"downloadlist.php","role"=>array(0,0,0,4,0),"role-disabled"=>array(0,0,0,0,0)),
  34=>array("text"=>'<h4 class="list-group-item-heading">Admin: Logs</h4><p class="list-group-item-text">Übersicht der Logs</p>',"text_s"=>'',"link"=>"log.php","role"=>array(0,0,0,4,0),"role-disabled"=>array(0,0,0,0,0)),
  98=>array("text"=>'<h4 class="list-group-item-heading">Support, Datenschutz, Impressum</h4><p class="list-group-item-text"></p>',"text_s"=>'',"link"=>"impressum.php","role"=>array(1,2,3,4,5),"role-disabled"=>array(0,0,0,0,0))
);



// HTML body with menu
// contains start of <body> element
$G_html_menu='<body>';
$G_html_menu_login='<body style="background-color:#ccc;">';
$G_html_menu2='<nav class="navbar navbar-inverse navbar-fixed-top FAIR-navbar">
      <div class="container-fluid">
        <div class="navbar-header">
          <a class="navbar-brand" href="index.php"><span class="shorten">Impfpasszentrum Odenwaldkreis </span><span style="color:#eee;">Start</span></a>';
if($_SESSION['uid']>0) {
	$G_html_menu2.='<ul class="nav navbar-nav navbar-left">';

  $G_html_menu2.='
  <li class="dropdown">
  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Menü <b class="caret"></b></a>
  <ul class="dropdown-menu">
  ';
  foreach($_module_array1 as $key=>$a) {
    $show_entry=false;
    foreach($a["role"] as $b) {
        if($b>0 && $_SESSION['roles'][$b]==1) { 
            $show_entry=true;
        }
    }
    if($show_entry && $a["text_s"]!='') { 
      $G_html_menu2.= '<li><a href="'.$a["link"].'">'.$a["text_s"].'</a></li>';
    }
  }
  foreach($_module_array2 as $key=>$a) {
    $show_entry=false;
    foreach($a["role"] as $b) {
        if($b>0 && $_SESSION['roles'][$b]==1) { 
            $show_entry=true;
        }
    }
    if($show_entry && $a["text_s"]!='') { 
      $G_html_menu2.= '<li><a href="'.$a["link"].'">'.$a["text_s"].'</a></li>';
    }
  }
    
    $G_html_menu2.='</ul>
</li>';

	if($_SESSION['station_id']>0) {
    $display_station='S'.$_SESSION['station_id'].'/'.$_SESSION['station_name'];
  } else {
    $display_station=$_SESSION['username'];
  }
	$G_html_menu2.='<li title="Station"><a style="color:#fff; font-size:85%;">'.$display_station.'</a></li>';

	$G_html_menu2.='</div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">';

	// Logged in / expiration of cookie
	$cookievalue=json_decode($_COOKIE['drk-cookie']);
	$expiry=$cookievalue->expiry;
	$expiry_diff=($expiry-time())/60; // in minutes
	if($expiry_diff<20) {$expiry_diff=20;}
	if( floor($expiry_diff / 60) < 2 ) { $expiry_text=ceil($expiry_diff).' Min.'; } // ceil = round up
	else { $expiry_text=ceil($expiry_diff / 60).' Std.'; } // ceil = round up
	$G_html_menu2.='<li title="Eingeloggt für '.$expiry_text.'" data-toggle="tooltip" data-placement="bottom" class="shorten"><a style="color:#fff; font-size:85%;">Eingeloggt für '.$expiry_text.'</a></li>';
	
	$G_html_menu2.='<li><a href="logout.php" style="color: #fff; background-color: #9f0000;">Logout</a></li>';
} else {
	$G_html_menu2.='</div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
			<li><a href="impressum.php">Impressum</a></li>
			<li><a href="login.php" style="color: #fff; background-color: #419f00;">Login</a></li>';
}
$G_html_menu2.='</ul>
        
          </ul>
        </div>
      </div>
    </nav>
';

// HTML element for content
$G_html_main_right_a='<main role="main" class="FAIR-main-col">';

// HTML section for database table and its content
// Content is produced with JS after initialisation of site
$G_html_main_right_b='
		  <div class="table-responsive">
		  <table id="main-tab" class="table table-striped display" width="100%"></table>
		  </div>
		  
		  <div class="table-responsive" style="visibility: hidden; position: fixed;">
		  <table id="comment-tab" class="table table-striped display" width="100%"></table>
		  </div>
';

// HTML closure elements before footer
$G_html_main_right_c='
        </main>
      </div>
    </div>';

// HTML footer section with closure of <body> and <html> elements
$G_html_footer='
  </body>
</html>';


// HTML closure elements before footer
$G_html_no_permission='
        <div style="padding-top:8px;"><h2 class="FAIR-redgrey">Keine Berechtigung</h2></div>';
	
?>