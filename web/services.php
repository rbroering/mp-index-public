<?php

// Alles nach zwei Schrägstrichen ist ein Kommentar bis zum Zeilenende.

// PHP-Dokumentation hier: https://www.php.net/manual/de/funcref.php
// Funktionen lassen sich am besten über das Suchfeld finden.

// Wahrheitswert: Wird gerade die Info abgerufen?
$ViewInfo = isset($_GET['info']);

// Markiere die Ausgabe dieses Skripts als JSON-Inhalt
if (!$ViewInfo)
header('Content-Type: application/json');

// Ermögliche Nutzung von Sitzungen
session_start();

// Einstellung für den Ordner mit services-Dateien
$ServicesPath = "services/";

// Empfange eine Liste an Dateinamen aus dem URL-Parameter ?src=
if (!empty($_GET['src'])) $FetchFrom = $_GET['src'];
elseif (!empty($_SESSION['src'])) $FetchFrom = $_SESSION['src'];
elseif (!empty($_COOKIE['src'])) $FetchFrom = $_COOKIE['src'];
else $FetchFrom = 'default';

switch($_GET['store']) {
    case 'session':
        $_SESSION['src'] = $FetchFrom;
    break;
    case 'delete-session':
        session_destroy();
    break;
    case 'cookie':
        $days = $_GET['cookie-days'] ?? 7;
        if ($days > 365) $days = 365;

        setcookie('src', $FetchFrom, time() + 60*60*24*$days);
    break;
    case 'delete-cookie':
        setcookie('src', null, -1);
        unset($_COOKIE['src']);
    break;
}

$Files = explode(',', $FetchFrom);

$Output = []; // Initialisierung des Ausgabe-Arrays
$LayerNames = []; // Array aller in den eingebundenen Dateien enthaltener Layer

// Sollte die Info-Seite abgerufen werden, überspringe das Laden der Dateien
if (!$ViewInfo) {

    // Gehe die Liste der Dateien durch, $File ist aktuelle Datei
    foreach($Files as $i => $File) {
        // Vervollständige Dateinamen
        $File = $ServicesPath.$File.".json";

        // Initialisiere Array von zum $Output hinzuzufügenden Arrays aus $File
        $Content = [];

        // Fahre fort, wenn die Datei existiert, sonst nächste Datei
        if (file_exists($File)) {
            // Wandel JSON-Inhalt der Datei in ein PHP-Array um, bei Fehler nächste Datei
            try {
                $Content = json_decode(file_get_contents($File), true);
            } catch(Exception $e) { continue; }
            
            // Überprüfe $Content auf bereits in $Output enthaltene Layer und entferne diese,
            // um doppelte Layer zu verhindern. Im Fall von Dopplung wird der zuerst eingebundene
            // Layer verwendet.
            foreach($Content as $i => $Layer) {
                if (in_array($Layer, $LayerNames)) {
                    unset($Content[$i]);
                } else {
                    array_push($LayerNames, $Layer);
                }
            }
            
            // Kopiere $Content der aktuellen Datei in $Output
            $Output = array_merge($Output, $Content);
        }
    }

}

// Wandel $Output nach JSON um und gebe dieses auf der Seite aus
if (!$ViewInfo) {
    echo json_encode($Output);
    exit(0);
}

// Info-Bereich
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <title>Services - Info</title>
        <style type="text/css">
            body {
                background: #efefef;
                font-family: Helvetica, Arial, sans-serif;
            }

            #wrapper {
                width: 95%;
                margin-left: auto;
                margin-right: auto;
                max-width: 1400px;
            }

            input[type="text"],
            input[type="number"] {
                margin: 20px 0 0;
                background: #f0f0f0;
                border: 1px solid #666;
                border-width: 0 0 2px 0;
                border-radius: 3px;
                padding: 6px 10px;
                font-family: inherit;
                font-size: inherit;
                transition: .1s;
            }
            input[type="text"]:hover,
            input[type="number"]:hover {
                border-color: #2fb46b;
            }
            input[type="text"]:focus,
            input[type="number"]:focus {
                background: #e2faed;
                border-color: #2fb46b;
            }
            input[type="submit"] {
                background: #efefef;
                border: 1px solid #666;
                border-width: 0 0 2px 0;
                border-radius: 3px;
                padding: 6px 10px;
                font-family: inherit;
                font-size: inherit;
                font-weight: bold;
                color: inherit;
                cursor: pointer;
                transition: .1s;
            }
            input[type="submit"]:hover {
                background: #a8fdce;
                border-color: #2fb46b;
            }

            main {
                margin: 0 auto 40px;
            }

            .box {
                padding: 20px;
                background: white;
                border: 0;
                box-shadow: 0 0 10px #bbb;
                border-radius: 10px;
            }
            .box.success {
                margin-bottom: 40px;
                background: #eafff1;
                box-shadow: 0 0 10px #2fb46b;
                color: #004408;
                font-weight: bold;
            }

            h2 {
                margin: 0 0 20px;
                padding: 0;
            }

            main {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: repeat(4, 1fr);
                grid-column-gap: 30px;
                grid-row-gap: 30px;
            }

            .box:nth-of-type(1) { grid-area: 1 / 1 / 2 / 3; }
            .box:nth-of-type(2) { grid-area: 2 / 1 / 3 / 3; }
            .box:nth-of-type(3) { grid-area: 3 / 1 / 4 / 2; }
            .box:nth-of-type(4) { grid-area: 3 / 2 / 4 / 3; }
            .box:nth-of-type(5) { grid-area: 4 / 1 / 5 / 2; }
            .box:nth-of-type(6) { grid-area: 4 / 2 / 5 / 3; }
        </style>
    </head>
    <body>
        <div id="wrapper">
            <h1>Services - Info</h1>
            <?php
                if (!empty($_GET['store'])) {
                    ?>
                    <div class="box success">
                        Änderungen gespeichert.
                    </div>
                    <?php
                }
            ?>
            <main>
                <div class="box">
                    <h2>Eingebundene Dateien</h2>
                    <p>
                        Die Liste der einzubindenden Dateien kann über Sitzungen und Cookies geändert
                        und gespeichert werden.
                    </p>

                    <p>
                        Eine ungespeicherte und damit für das Masterportal ungeeignete Änderung
                        der Liste kann über den URL-Parameter ?src erreicht werden.
                    </p>

                    <p>
                        Dabei wird die Einstellung der Liste wiefolgt priorisiert:
                        <ol>
                            <li>URL-Parameter ?src</li>
                            <li>Session (Sitzung)</li>
                            <li>Cookie</li>
                        </ol>
                    </p>

                    <p>
                        Die aktuell verwendete Datei-Liste ist hier gezeigt.
                        Sollten Layer-IDs doppelt vorkommen, wird der erste gefundene Layer behalten und die
                        anderen gelöscht. Dabei ist die Reihenfolge, in denen die Dateien eingebunden werden,
                        ausschlaggebend.
                        <ol>
                        <?php
                            if (empty($Files)) echo "<li>default</li>";

                            foreach($Files as $File) {
                                echo "<li>$File</li>\n";
                            }
                        ?>
                        </ol>
                    </p>
                </div>
                <div class="box">
                    <h2>URL-Parameter ?src einstellen</h2>
                    Die einzelnen Dateinamen bitte durch Kommata trennen.<br>
                    Bsp.: default,meinedatei1,meinedatei2
                    <form method="get">
                        <input type="text" name="src" placeholder="Dateinamen"/>
                        <input type="submit" value="OK"/>
                    </form>
                </div>
                <div class="box">
                    <h2>Session starten</h2>
                    Die einzelnen Dateinamen bitte durch Kommata trennen.<br>
                    Bsp.: default,meinedatei1,meinedatei2
                    <form method="get">
                        <input type="hidden" name="info"/>
                        <input type="hidden" name="store" value="session"/>
                        <input type="text" name="src" placeholder="Dateinamen"/>
                        <input type="submit" value="OK"/>
                    </form>
                </div>
                <div class="box">
                    <h2>Session löschen</h2>
                    <form method="get">
                        <input type="hidden" name="info"/>
                        <input type="hidden" name="store" value="delete-session"/>
                        <input type="submit" value="OK"/>
                    </form>
                </div>
                <div class="box">
                    <h2>Cookie erstellen</h2>
                    <p>
                        Die einzelnen Dateinamen bitte durch Kommata trennen.<br>
                        Bsp.: default,meinedatei1,meinedatei2
                    </p>

                    <p>
                        Die Einstellung einer Speicherdauer ist optional.
                        Wert in Tagen.
                    </p>
                    <form method="get">
                        <input type="hidden" name="info"/>
                        <input type="hidden" name="store" value="cookie"/>
                        <input type="text" name="src" placeholder="Dateinamen"/>
                        <input type="submit" value="OK"/><br>
                        <input type="number" name="cookie-days" placeholder="Speicherdauer"/>
                    </form>
                </div>
                <div class="box">
                    <h2>Cookie löschen</h2>
                    <form method="get">
                        <input type="hidden" name="info"/>
                        <input type="hidden" name="store" value="delete-cookie"/>
                        <input type="submit" value="OK"/>
                    </form>
                </div>
            </main>
        </div>
    </body>
</html>
<?php
