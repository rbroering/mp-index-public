<?php error_reporting(0);

/**
 * This class represents a Card UI element used for providing a link
 * and brief description of a Masterportal project.
 */
class PortalCard {
    /** The pathname of the portal-info.json file. */
    private $file = "";
    /** The JSON object obtained from the portal-info file. */
    private $info = [];
    /** In case of an error with reading the portal-info.json, this variable will be set to true. */
    private $invalid = false;

    /**
     * To instantiate a new PortalCard, you have to provide a source
     * for gathering its information, typically the respective
     * portal-info.json file.
     * 
     * @param {string|object} $info The filepath of the portal-info.json file,
     * or a valid object retrieved from the centralized index.json file.
     */
    public function __construct(string|object $info) {
        if (is_object($info)) {
            $this->setInfoFromIndex($info);
        } else {
            $this->setInfoFromPortalInfo($info);
        }
    }

    private function setInfoFromPortalInfo(string $file) : bool {
        $this->file = $file;

        try {
            $Portalinfo = file_get_contents($file);
            $Portalinfo = json_decode($Portalinfo, true);
            $this->info = $Portalinfo;
        } catch(Exception $e) {
            $this->invalid = true;
            return false;
        }

        if (!array_key_exists('name', $Portalinfo) ||
            !is_string($Portalinfo['name'])) {
            $this->info['name'] = dirname($file);
        }

        return true;
    }

    private function setInfoFromIndex(object $info) : bool {
        $this->file = $info;


        return true;
    }
    
    /**
     * Returns the URL path of the Portal.
     * 
     * @return {string} URL path of the Portal.
     */
    private function getLink() : string {
        return substr($this->file, 0, strrpos($this->file, '/') + 1);
    }

    /**
     * Returns the image that has been provided in the portal-info.json
     * file. In case no image has been set, null is returned.
     * 
     * @return {string|null} Returns an image filepath if it has been provided, else null.
     */
    private function getImage() : string|null {
        if (!$Bild = $this->info['bild'] ?? null) return null;

        return (substr($Bild, 0, 1) == '/') ? $Bild : $this->getLink() . $Bild;
    }

    /**
     * Returns a boolean which indicates whether the portal-info.json
     * file could be read successfully.
     * 
     * @return {bool}
     */
    public function isValid() : bool {
        return !$this->invalid;
    }

    /**
     * Provides the HTML for the PortalCard.
     * 
     * @return {string}
     */
    public function getCard() : string {
        if ($this->invalid) return "";

        $Link = $this->getLink();
        $Name = $this->info['name'];
        $Bild = $this->getImage();
        $Beschreibung = $this->info['beschreibung'] ?? null;


        $Card = "<div class=\"card_wrapper\"><a href=\"$Link\" class=\"black\">".
                "<div class=\"card" . ($Bild ? " with-image" : '') . "\">".
                ($Bild ? "<div class=\"card__portalbild\" style=\"--img: url('$Bild');\"></div>" : '').
                "<span class=\"card__portalname\">$Name</span>".
                ($Beschreibung ? "<span class=\"card__beschreibung\">$Beschreibung</span>" : '').
                "</div></a></div>";

        return $Card;
    }
}

$Portale = glob('*/portal-info.json');

try {
    $IndexConfig = json_decode(file_get_contents('index.json'), true);
} catch(Exception $e) {
    $IndexConfig = [];
}

$Meldungen = $IndexConfig['meldungen'] ?? [];
$AktuellePortale = $IndexConfig['aktuelle-portale'] ?? [];

$ErrorDocument ??= $_SERVER["REDIRECT_STATUS"];

$ErrorDescriptionCommon = " Eine Liste mit allen aktuellen Masterportalen kannst du finden,".
                          " indem du diese Nachricht schließt.";

$ErrorDescription = !$ErrorDocument ?: match((int) $ErrorDocument) {
    401 => "Der Zugriff auf die angeforderte Seite ist eingeschränkt" .$ErrorDescriptionCommon,
    404 => "Die angeforderte Seite existiert nicht oder wurde gelöscht." .$ErrorDescriptionCommon,
    500 => "Ein interner Konfigurationsfehler liegt vor." .$ErrorDescriptionCommon,
    default => "Ein unbekannter Fehler trat auf. " .$ErrorDescriptionCommon,
};
?>
<!DOCTYPE html>
<html lang="de-DE">
    <head>
        <meta charset="utf-8"/>
        <title>
            Masterportal
        </title>
        <base href="/"></base>
        <link rel="stylesheet" type="text/css" href="index.css"/>
        <script type="application/javascript" src="index.js" defer></script>
    </head>
    <body <?php
        $bodyClasses = [];

        if ($ErrorDocument) $bodyClasses[] = "scrolling-disabled";

        if (!empty($bodyClasses)) echo 'class="' . implode(' ', $bodyClasses) . '"';
    ?>>
        <header>
            <span>Masterportal-Index</span>
            <nav class="jump-to-section-nav normalwidth">
                <ul>
                    <li>
                        <a class="black" href="#aktuelle-portale" title="Abschnitt: Aktuelle Portale" alt="Aktuelle Portale">
                            Aktuelle Portale
                        </a>
                    </li>
                    <li>
                        <a class="black" href="#unsere-portale" title="Abschnitt: Alle Portale" alt="Alle Portale">
                            Alle Portale
                        </a>
                    </li>
                </ul>
            </nav>
            <nav class="smallwidth">
                <div class="nav-toggle nav-action--dropdown">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </nav>
        </header>
        <div class="smallwidth">
            <div class="nav-dropdown_wrapper">
                <nav class="nav-dropdown">
                    <ul>
                        <!--
                        <a class="black nav-action--dropdown" href="#neue-portale" title="Abschnitt: Neue Portale" alt="Neue Portale">
                            <li>
                                Neue Portale
                            </li>
                        </a>
                        -->
                        <a class="black nav-action--dropdown" href="#unsere-portale" title="Abschnitt: Alle Portale" alt="Alle Portale">
                            <li>
                                Alle Portale
                            </li>
                        </a>
                    </ul>
                </nav>
            </div>
        </div>
        <header class="relative"></header>
        <!-- <div class="banner" style="background-image: url('assets/img/Banner.jpg');"></div> -->
        <?php
            if (!empty($Meldungen)) {
                echo '<div class="notifications">';

                foreach ($Meldungen as $Meldung) {
                    ?>
                    <div><?= $Meldung ?></div>
                    <?php
                }

                echo '</div>';
            }
        ?>
        <main>
            <a name="aktuelle-portale"></a>
            <h1>Aktuelle Portale</h1>
            <div class="cards">
                <?php
                foreach (array_intersect($Portale, $AktuellePortale) as $Portal) {
                    try {
                        $Card = new PortalCard($Portal);

                        if ($Card->isValid()) {
                            echo $Card->getCard();
                        }
                    } catch(Exception $e) {
                        continue;
                    }
                }
                ?>
            </div>
            <a name="unsere-portale"></a>
            <h1>Alle Portale</h1>
            <div class="cards">
                <?php
                foreach ($Portale as $Portal) {
                    try {
                        $Card = new PortalCard($Portal);

                        if ($Card->isValid()) {
                            echo $Card->getCard();
                        }
                    } catch(Exception $e) {
                        continue;
                    }
                }
                ?>
            </div>
        </main>
        
        <?php
        if ($ErrorDocument) {
        ?>
        <div class="popup-notification active">
            <div class="popup">
                <div class="errordocument">
                    <div class="popup__header">
                        <h1>Fehler</h1>
                        <div class="nav-toggle nav-action--popup nav-toggle--close">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                    <div class="popup__body">
                        <div style="text-align: center;">
                            <span class="errordocument__number">
                                <?= $ErrorDocument ?>
                            </span>
                        </div>
                        <p>
                            <?= $ErrorDescription ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        }
        ?>
    </body>
</html>
