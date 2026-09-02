<?php
/**
 * 07 Die App — der Weg auf den Startbildschirm.
 *
 * Hier ist nichts herunterzuladen, und das steht auch so da.
 *
 * Eine Web-App kommt nicht aus einem App Store und ist keine Datei. Die
 * Seite legt sich als Symbol auf den Startbildschirm und öffnet danach
 * ohne Browserleisten. Ein Knopf mit „Herunterladen" wäre deshalb ein
 * Versprechen, das nichts einlöst: es käme keine Datei, und wer eine
 * erwartet, sucht sie hinterher im Download-Ordner.
 *
 * Was der Knopf wirklich tut, hängt vom Gerät ab:
 *
 * Auf Android meldet sich Chrome von selbst, sobald es installieren
 * kann — dann öffnet der Knopf die echte Abfrage des Systems.
 *
 * Auf dem iPhone gibt es dafür keine Schnittstelle. Apple verlangt den
 * Weg über Teilen → „Zum Home-Bildschirm", und kein Knopf der Welt kann
 * das auslösen. Dort klappt er deshalb die Anleitung auf. Das ist
 * weniger elegant, aber ehrlich — und es ist der einzige Weg, der
 * funktioniert.
 *
 * Läuft die Seite schon als App, verschwindet der Knopf: eine
 * Aufforderung zu etwas bereits Erledigtem liest sich wie ein Fehler.
 *
 * Ohne das Plugin gibt es kein Manifest und damit nichts abzulegen —
 * dann entfällt der ganze Abschnitt. Eine Überschrift über einem Knopf,
 * der nichts bewirkt, wäre schlechter als kein Abschnitt.
 */

if (!defined('ABSPATH')) exit;

if (!function_exists('sz_app_symbol')) return;

?>
<section class="sz-kapitel sz-appruf" id="app" aria-labelledby="sz-app-titel" data-sz-app>
    <div class="wrap">

        <p class="sz-kapitelmarke">
            <span class="sz-kapitelmarke__nr mono">07</span>
            <span class="hairline" aria-hidden="true"></span>
            <span class="sz-kapitelmarke__kicker mono"><?php echo esc_html__('Auf dem Telefon', 'sapelza-shop'); ?></span>
        </p>

        <div class="sz-appruf__innen">

            <div class="sz-appruf__zeichen">
                <?php
                /*
                 * Das Symbol so gezeigt, wie es später auf dem
                 * Startbildschirm liegt: mit runden Ecken und Schatten.
                 * Wer es hier sieht, erkennt es dort wieder.
                 */
                ?>
                <img class="sz-appruf__symbol"
                     src="<?php echo esc_url(sz_app_symbol(512)); ?>"
                     width="512" height="512" alt="" loading="lazy" decoding="async">
                <span class="sz-appruf__name mono"><?php echo esc_html__('Sapelza', 'sapelza-shop'); ?></span>
            </div>

            <div class="sz-appruf__text">
                <h2 id="sz-app-titel" class="sz-appruf__titel">
                    <?php echo esc_html__('Der Shop als App', 'sapelza-shop'); ?>
                </h2>

                <p class="sz-appruf__lead">
                    <?php echo esc_html__('Legen Sie den Shop auf den Startbildschirm: ein Symbol antippen, und Ihre Artikelliste ist da — ohne Browserleisten, ohne Suchen. Womit die App startet, bestimmen Sie in Ihrem Konto.', 'sapelza-shop'); ?>
                </p>

                <p class="sz-appruf__klar">
                    <?php echo esc_html__('Kein App Store, keine Installationsdatei. Es wird nichts heruntergeladen — die Seite selbst legt sich als Symbol ab.', 'sapelza-shop'); ?>
                </p>

                <div class="sz-appruf__tat">
                    <button type="button" class="sz-appruf__knopf"
                            data-sz-app-knopf
                            data-wort-installieren="<?php echo esc_attr__('App installieren', 'sapelza-shop'); ?>"
                            aria-expanded="false" aria-controls="sz-app-wege">
                        <span aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3v11"/><path d="m7.5 10.5 4.5 4.5 4.5-4.5"/>
                                <path d="M4.5 16.5v2a2.5 2.5 0 0 0 2.5 2.5h10a2.5 2.5 0 0 0 2.5-2.5v-2"/>
                            </svg>
                        </span>
                        <?php echo esc_html__('Auf den Startbildschirm legen', 'sapelza-shop'); ?>
                    </button>

                    <p class="sz-appruf__laeuft" data-sz-app-laeuft hidden>
                        <?php echo esc_html__('Sie benutzen den Shop bereits als App.', 'sapelza-shop'); ?>
                    </p>
                </div>

                <div class="sz-appruf__wege" id="sz-app-wege" data-sz-app-wege hidden>

                    <div class="sz-appruf__weg" data-sz-app-apfel>
                        <h3 class="sz-appruf__wegtitel mono"><?php echo esc_html__('iPhone und iPad', 'sapelza-shop'); ?></h3>
                        <ol class="sz-appruf__schritte">
                            <li><?php echo esc_html__('Diese Seite in Safari öffnen — nicht in Chrome, dort fehlt der Punkt.', 'sapelza-shop'); ?></li>
                            <li><?php echo esc_html__('Unten auf das Teilen-Zeichen tippen: das Quadrat mit dem Pfeil nach oben.', 'sapelza-shop'); ?></li>
                            <li><?php echo esc_html__('In der Liste „Zum Home-Bildschirm" wählen und bestätigen.', 'sapelza-shop'); ?></li>
                        </ol>
                    </div>

                    <div class="sz-appruf__weg" data-sz-app-android>
                        <h3 class="sz-appruf__wegtitel mono"><?php echo esc_html__('Android', 'sapelza-shop'); ?></h3>
                        <ol class="sz-appruf__schritte">
                            <li><?php echo esc_html__('Diese Seite in Chrome öffnen.', 'sapelza-shop'); ?></li>
                            <li><?php echo esc_html__('Oben rechts auf die drei Punkte tippen.', 'sapelza-shop'); ?></li>
                            <li><?php echo esc_html__('„App installieren" oder „Zum Startbildschirm hinzufügen" wählen.', 'sapelza-shop'); ?></li>
                        </ol>
                    </div>

                </div>

                <p class="sz-appruf__fein">
                    <?php echo esc_html__('Beim ersten Öffnen müssen Sie sich noch einmal anmelden: Das Fenster vom Startbildschirm hat einen eigenen Speicher, getrennt vom Browser. Danach bleibt es angemeldet.', 'sapelza-shop'); ?>
                </p>
            </div>

        </div>
    </div>
</section>
