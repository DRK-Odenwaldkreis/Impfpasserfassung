<?php

/* **************

Websystem für das Impfpasszentrum
Author: Marc S. Duchene
June 2021

** ************** */


// Include functions
include_once 'admin01.php';
include_once 'menu.php';
//$GLOBALS['FLAG_SHUTDOWN_MAIN']=false;
if(!$GLOBALS['FLAG_SHUTDOWN_MAIN']) {
    include_once 'registration/auth.php';
    include_once 'registration/tools.php';
}

// Print html header
echo $GLOBALS['G_html_header'];

// Print html menu
echo $GLOBALS['G_html_menu'];
echo $GLOBALS['G_html_menu2'];

// Print html content part A
echo $GLOBALS['G_html_main_right_a'];

?>



<div class="row">

    <div class="col-sm-4">
    <a href="https://www.apotheken-in-erbach.de/" target="_blank">
    <img src="img/logo_apo_erb.png" style="display: block; margin-left: auto; margin-right: auto; width: 65%;"></img>
    </a>
    </div>

    <div class="col-sm-8">
        <div style="text-align: center;">
            <h2>Impfpasszentrum Odenwaldkreis</h2>
            <h3>Anmeldung für digitales Covid-Impfzertifikat</h3>
        </div>
    </div>
</div>

<div class="alert alert-info" role="alert">
    <h2>Coronavirus SARS-CoV-2 Impfzertifikat</h2>
    <h4>Wir bieten für Sie:</h4>

    <div class="row">
    <div class="col-sm-4 col-xs-12 main-link-page" onclick="window.location='#calendar'">
        <div class="header_icon">
        <img src="img/icon/cov_digital.svg" style="display: block; margin-left: auto; margin-right: auto; width: 40%;"></img>
        <div class="FAIRsep"></div>
        <div class="caption center_text">
        <h4>Digitaler Impfnachweis</h4>
        <h5><a href="https://www.apotheken-in-erbach.de/">Ein Service der Bären-Apotheke und Elefanten-Apotheke in Erbach</a></h5>
        </div>
        </div>
    </div>
    </div>

    <div class="FAIRsepdown"></div>
    <p>Bei Fragen können Sie sich an das Personal vor Ort wenden.</p>
    <div class="FAIRsepdown"></div>
    <div class="FAIRsep"></div>
</div>
<div class="FAIRsepdown" id="calendar"></div><div class="FAIRsepdown"></div>
<div class="row header_icon_main">

    <div class="col-sm-3 col-xs-6">
        <div class="header_icon">
        <img src="img/icon/cal_time.svg" style="display: block; margin-left: auto; margin-right: auto; width: 30%;"></img>
            
        <div class="caption center_text">
        <h5>Termin finden</h5>
        </div>
        </div>
    </div>
    <div class="col-sm-3 col-xs-6">
        <div class="header_icon">
        <img src="img/icon/mask.svg" style="display: block; margin-left: auto; margin-right: auto; width: 30%;"></img>
            
        <div class="caption center_text">
            <h5>Mit Maske erscheinen</h5>
            
        </div>
        </div>
    </div>
    <div class="col-sm-3 col-xs-6">
        <div class="header_icon">
        <img src="img/icon/qr_1.svg" style="display: block; margin-left: auto; margin-right: auto; width: 34%;"></img>
            
        <div class="caption center_text">
        <h5>Ihren eigenen Impfnachweis und Personalausweis o. ä. vorzeigen</h5>
        </div>
        </div>
    </div>
    <div class="col-sm-3 col-xs-6">
        <div class="header_icon">
        <img src="img/icon/cov_digital.svg" style="display: block; margin-left: auto; margin-right: auto; width: 46%;"></img>
            
        <div class="caption center_text">
        <h5>Ergebnis digital einlesen (CovPass oder Corona-Warn-App)</h5></h5>
        </div>
        </div>
    </div>

</div>


<div class="FAIRsepdown"></div>
<div class="FAIRsepdown"></div>


<div class="row">
    
    <div class="col-sm-12">
    <h2 style="text-align: center;">Termine und Orte im Odenwaldkreis</h2>
    </div>
    <div class="col-sm-12"><div class="card">
<?php
if(!$GLOBALS['FLAG_SHUTDOWN_MAIN']) {

    // Show table of available dates

        $calendar=H_build_table_testdates2('impfpass');
    
    
        //large display
        echo '<div class="calendar-large">';
        echo $calendar[0];
        echo '</div>';
        // small display
        echo '<div class="calendar-small">
        ';
        foreach($calendar[1] as $i) {
            echo $i[0].$i[1];
        }
        echo '</div>';



} else {
    echo '<div class="alert alert-danger" role="alert">
    <h3>Wartungsarbeiten</h3>
    <p>Derzeit finden Arbeiten an dieser Seite statt, der Kalender und die Terminbuchung stehen momentan nicht zur Verfügung. Bald geht es wieder weiter...wir bitten um etwas Geduld.</p>
    <div class="FAIRsepdown"></div>
    <div class="FAIRsep"></div>
</div>';
}

?>
    </div></div>

    </div>
</div>
<div class="FAIRsepdown"></div>
<div class="FAIRsepdown"></div>

<div class="FAIRsepdown"></div>
<div class="FAIRsepdown"></div>
<div class="row">
    <div class="col-sm-4">
        <div class="list-group">
            <h3>Für die Teams der Apotheken</h3>
            <a class="list-group-item list-group-item-action list-group-item-FAIR" id="module-r1" href="zentral/index.php">Impfpasserfassung (Intern)</a>
        </div>
    </div>
</div>

<?php
// Print html content part C
echo $GLOBALS['G_html_main_right_c'];
// Print html footer
echo $GLOBALS['G_html_footer'];

?>
