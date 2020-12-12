<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Gemeinschaftspraxis M. M. Nickertz & Dr. med. K. König</title>
    <link rel="stylesheet" href="assets/css/libs/normalize.min.css" media="all"/>
    <link rel="stylesheet" href="assets/css/libs/foundation.min.css" media="all"/>
    <link rel="stylesheet" href="assets/css/libs/foundation.float.min.css" media="all"/>
    <link rel="stylesheet" href="assets/css/app.css" media="all"/>
    <link rel="stylesheet" href="assets/css/print.css" media="print"/>
</head>
<body>
    <div class="wrapperOuter row">
        <div class="small-12 column no-print-spacing">
<?php

$serverName = "praxis-app01\\PRAXISTIMER";
$connectionInfo = array("Database" => "PRAXISTIMER", "UID" => "PRAXISTIMER", "PWD" => "PRAXISTIMER");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if( $conn ) {
    if( isset($_GET['id']) && $_GET['id'] != "" ) {
        $sql = "
        DECLARE @StartTime DATETIME = '1899/30/12 07:00:00',
                @Datum DATETIME = '1899/30/12' 
        SELECT 
            FORMAT(DATEADD(day,[datum], @Datum), 'dd.MM.yyyy') as datum,
            FORMAT(DATEADD(minute, (([von] - 85) * 5), @StartTime), 'HH:mm') as von,
            FORMAT(DATEADD(minute, (([bis] - 85) * 5) + 5, @StartTime), 'HH:mm') as bis,
            CASE WHEN namelang IS NULL THEN 'Not-Patient' ELSE [namelang] END as leistung,
            CASE WHEN behandler IS NULL THEN 'Not-Patient' WHEN behandler = '7' THEN 'T. Bannasch' WHEN behandler = '8' THEN 'C. Lobitz' WHEN behandler = '9' THEN 'Labor' WHEN behandler = '10' THEN 'Technik/EKG' ELSE p.name END as behandler,
            [patientname],
            [patientvorname] 
        FROM [PRAXISTIMER].[dbo].[termine] AS t 
        LEFT JOIN [PRAXISTIMER].dbo.leistung AS l ON t.leistungsnr = l.leistungsnr 
        LEFT JOIN [PRAXISTIMER].dbo.person AS p ON p.id = t.behandler + 1 
        WHERE 
            patientnr = ".$_GET['id']."1 
        AND 
            CAST(datum as datetime) > DATEADD(day, 2, getdate()) 
        AND 
            CAST(datum as datetime) < DATEADD(day, 365, getdate())     
        ORDER BY CAST(datum as datetime), von";


        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        if( sqlsrv_has_rows($stmt) === false ) {
            die('<p>Patient unbekannt oder keine Termine gefunden.</p>
            <p><a href="index.php" class="button">Neue Suche</a></p>');

        }
        else {

            $i = 0;
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                if ($i == 0) {
                    ?>
                    <fieldset class="controls hide-for-print">
                        <div class="row">
                            <div class="small-12 column">
                                <label for="chbxCovidHint" class="control">Aktuelle Hinweise anzeigen?</label>
                                <input type="checkbox" id="chbxCovidHint" checked/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="small-12 column">
                                <label for="chbxServiceHint" class="control">Leistung anzeigen?</label>
                                <input type="checkbox" id="chbxServiceHint" checked/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="small-12 column">
                                <label for="chbxRxHint" class="control">Hinweis Rezeptmailbox anzeigen?</label>
                                <input type="checkbox" id="chbxRxHint" checked/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="small-12 column">
                                <label for="chbxDiabHint" class="control">Hinweis Diabetes Kontakt anzeigen?</label>
                                <input type="checkbox" id="chbxDiabHint"/>
                            </div>
                        </div>
                    </fieldset>
                    <div class="row">
                        <div class="small-6 column"><a href="index.php" class="button hide-for-print">Neue Suche</a></div>
                        <div class="small-6 column"><button class="button print alert hide-for-print">Drucken</button></div>
                    </div>
                    <hr class="hide-for-print">
                    <div class="logo">
                        <img src="assets/img/gfx-logo.gif" alt="Logo"/>
                    </div>
                    <div class="row">
                        <div class="small-12 column text-center no-print-spacing">
                            <p class="covidHint"><b>+++ Aktueller Hinweis +++</b><br/>Aufgrund der aktuellen Corona-Pandemie, informieren<br/>Sie sich bitte vor <u>jedem</u> Besuch im MKK über etwaige geänderte Abläufe auf unserer Internetseite unter<br/><b>www.mkk-kamen.de</b><br/>Bleiben Sie gesund!</p>
                        </div>
                    </div>
                    <h1>Termine für<em><?php echo $row['patientname'] . ", " . $row['patientvorname']; ?></em></h1>
                    <table>
                    <thead>
                    <tr>
                        <th class="th_Date">Datum</th>
                        <th class="th_Time">Uhrzeit</th>
                        <th class="th_Person">Behandler</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php

                }
                ?>
                <tr class="v_<?php echo $i;?>">
                    <td class="td_Date"><?php echo $row['datum'] ?></td>
                    <td class="td_Time"><?php echo $row['von'] ?> Uhr</td>
                    <td class="td_Person"><?php echo $row['behandler'] ?>
                        <fieldset class="hide-for-print controls">
                            <div class="row">
                                <div class="small-2 column">
                                    <label for="n_<?php echo $i;?>" title="Nüchtern" class="control">nü</label>
                                    <input type="checkbox" class="n" id="n_<?php echo $i;?>"/>
                                </div>
                                <div class="small-2 column">
                                    <label for="m_<?php echo $i;?>" title="ohne Medikamenteneinnahme" class="control">Medi</label>
                                    <input type="checkbox" class="m" id="m_<?php echo $i;?>"/>
                                </div>
                                <div class="small-2 column">
                                    <label for="no_<?php echo $i;?>" title="Notiz" class="control">Notiz</label>
                                    <input type="checkbox" class="no" id="no_<?php echo $i;?>"/>
                                </div>
                                <div class="small-3 end column">
                                    <label for="v_<?php echo $i;?>" title="Ausblenden" class="control">Ausblenden</label>
                                    <input type="checkbox" class="v" id="v_<?php echo $i;?>"/>
                                </div>
                            </div>
                        </fieldset>
                    </td>
                </tr>
                <tr class="tr_Leistung v_<?php echo $i;?>">
                    <td class="td_Leistung" colspan="3">
                        <span class="service"><?php echo $row['leistung'] ?></span>
                        <span class="hide n_<?php echo $i;?>"><i>Bitte erscheinen Sie nüchtern</i></span>
                        <span class="hide m_<?php echo $i;?>"><i>Bitte nehmen Sie keine Medikamente ein</i></span>
                        <span class="hide no_<?php echo $i;?>">
                            <span class="show-for-print"><i>Notiz:</i></span>
                            <textarea class="notiz" placeholder="Ergänzende Notizen eingeben..."></textarea>
                        </span>
                    </td>
                </tr>
                <?php
                $i++;
            }

        }
        ?>
        </tbody>
        </table>
        <div class="row">
            <div class="small-12 column text-center no-print-spacing">
                <p class="rxHint">Schon gewusst? Sie können Ihre Rezepte und<br/>Überweisungen ganz bequem per Telefon vorbestellen,<br/>nutzen Sie dazu unsere Rezept Mailbox unter<br/><b>02307 - 97372 - 22</b>.</p>
            </div>
        </div>
        <div class="row">
            <div class="small-12 column text-center no-print-spacing">
                <p class="rxDiab hide">Sie möchten vom Diabetes-Team<br/>zurückgerufen werden?<br/>Nutzen Sie unsere Mailbox unter<br/><b>02307 - 97372 - 23</b><br/>oder schreiben Sie eine E-Mail an<br/><b>diabetes@mkk-kamen.de</b>.</p>
            </div>
        </div>
        <div class="row">
            <div class="small-12 column text-center no-print-spacing">
                <p class="imprint"><b>Medizinisches Kompetenzkollegium Kamen</b><br/>Gemeinschaftspraxis M. M. Nickertz & Dr. med. K. König<br/>Westicker Str. 1<br/>59174 Kamen<br/><br/>Telefon: 02307 - 97372 - 0<br/>Telefax: 02307 - 97372 - 20<br/><br>kontakt@mkk-kamen.de<br/>www.mkk-kamen.de</p>
            </div>
        </div>
        <div class="row">
            <div class="small-12 column text-center no-print-spacing">
                <p class="cancelHint"><b>Sie können einen Termin nicht einhalten?<br/>Bitte sagen Sie Ihren Termin rechtzeitig ab.</b></p>
            </div>
        </div>
        <div class="row">
            <div class="small-12 column text-center no-print-spacing">
                <p class="disclamerHint">Ausgedruckt am <?php echo date("d.m.Y"); ?> &middot; Änderungen vorbehalten.</p>
            </div>
        </div>
        <div class="print-spacing-bottom">.</div>
        <?php
    }
    else {
        ?>
        <form method="get" action="index.php">
            <fieldset>
                <div class="row">
                    <div class="small-12 columns">
                        <label for="id">Patientennummer</label>
                        <input type="text" id="id" name="id" autocomplete="off"/>
                    </div>
                </div>
                <div class="row">
                    <div class="small-12 columns">
                        <input type="submit" class="button" value="Termine suchen"/>
                    </div>
                </div>
            </fieldset>
        </form>
        <?php
    }

} else {
    echo "Connection could not be established.<br />";
    die(print_r(sqlsrv_errors(), true));
}
?>
        </div>
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
