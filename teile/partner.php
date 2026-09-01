<?php
/**
 * 05 Partnerbetriebe — die Wertschätzung für Stammkunden.
 *
 * Der Abschnitt ist bewusst sachlich und ohne Werbeton: die meisten Kunden
 * kennen das Geschäft persönlich. Der Shop tritt nicht an ihre Stelle, er
 * spart nur den Weg.
 */

if (!defined('ABSPATH')) exit;

$sz_punkte = [
    [
        't' => __('Ihre Konditionen', 'sapelza-shop'),
        'b' => __('Die Preise, die wir mit Ihnen vereinbart haben — online dieselben wie im Geschäft.', 'sapelza-shop'),
    ],
    [
        't' => __('Ein Ansprechpartner', 'sapelza-shop'),
        'b' => __('Dieselbe Nummer, dieselbe Person. Rückfragen klären wir am Telefon, nicht im Ticketsystem.', 'sapelza-shop'),
    ],
    [
        't' => __('Kurzer Weg', 'sapelza-shop'),
        'b' => __('Bestellt bis 12:00, am nächsten Werktag im Tal geliefert.', 'sapelza-shop'),
    ],
];

?>
<section class="sz-kapitel sz-partner" aria-labelledby="sz-partner-titel">
    <div class="wrap" style="position: relative; z-index: 1;">

        <p class="sz-kapitelmarke">
            <span class="sz-kapitelmarke__nr mono">05</span>
            <span class="hairline" aria-hidden="true"></span>
            <span class="sz-kapitelmarke__kicker mono"><?php echo esc_html__('Partnerbetriebe', 'sapelza-shop'); ?></span>
        </p>

        <div class="sz-partner__kopf">
            <?php
            /*
             * Ueberschrift und Logo stehen nebeneinander. Rechts neben dem
             * Satz blieb ein leeres Feld — die Ueberschrift ist auf 20
             * Zeichen Breite gesetzt, der Vorspann sitzt erst weiter
             * rechts. Dort gehoert das Zeichen hin, das der Satz meint.
             *
             * Das ganze Logo, nicht die blasse Kontur des Wasserzeichens:
             * an dieser Stelle ist es die Unterschrift unter einem Dank,
             * kein Hintergrund.
             */
            ?>
            <div class="sz-partner__marke">
                <h2 id="sz-partner-titel" class="sz-partner__titel">
                    <?php echo esc_html__('Danke, dass Sie seit Jahren bei uns kaufen', 'sapelza-shop'); ?>
                </h2>
                <?php
                /*
                 * Ein Siegel, kein Logo.
                 *
                 * Das Logo stand hier ein zweites Mal auf derselben Seite.
                 * Ein Siegel sagt etwas anderes: nicht "das sind wir",
                 * sondern "dafuer stehen wir gerade" — was der Satz
                 * daneben meint. Es nimmt die Raute des Logos auf und legt
                 * sie in einen Ring mit dem Ort.
                 *
                 * Kein Jahr darin: ich weiss nicht, seit wann es das Haus
                 * gibt, und eine erfundene Jahreszahl auf einem Siegel
                 * waere das Gegenteil dessen, wofuer ein Siegel steht.
                 * Wenn Sie mir das Gruendungsjahr sagen, setze ich es
                 * unten in den Ring.
                 */
                ?>
                <svg class="sz-partner__siegel" viewBox="0 0 200 200" role="img"
                     aria-label="<?php echo esc_attr__('Siegel: Kaufhaus Sapelza, Toblach im Hochpustertal', 'sapelza-shop'); ?>">
                    <defs>
                        <?php /* Oben im Uhrzeigersinn, unten dagegen — so steht beides aufrecht. */ ?>
                        <path id="sz-siegel-oben"  fill="none" d="M28 100 A72 72 0 0 1 172 100"/>
                        <path id="sz-siegel-unten" fill="none" d="M30 100 A70 70 0 0 0 170 100"/>
                    </defs>

                    <circle class="sz-siegel__ring" cx="100" cy="100" r="94" stroke-width="1"/>
                    <circle class="sz-siegel__ring" cx="100" cy="100" r="88" stroke-width="2.5"/>
                    <circle class="sz-siegel__ring" cx="100" cy="100" r="56" stroke-width="1"/>

                    <text font-size="13" letter-spacing="2.6">
                        <textPath href="#sz-siegel-oben" xlink:href="#sz-siegel-oben"
                                  startOffset="50%" text-anchor="middle">KAUFHAUS SAPELZA</textPath>
                    </text>

                    <text font-size="9.5" letter-spacing="1.8">
                        <textPath href="#sz-siegel-unten" xlink:href="#sz-siegel-unten"
                                  startOffset="50%" text-anchor="middle">TOBLACH · HOCHPUSTERTAL</textPath>
                    </text>

                    <?php /* Zwei kleine Rauten an den Seiten, wo die beiden Boegen sich treffen. */ ?>
                    <path class="sz-siegel__mark" d="M29 100 L33 96 L37 100 L33 104 Z"/>
                    <path class="sz-siegel__mark" d="M163 100 L167 96 L171 100 L167 104 Z"/>

                    <?php /* Die Raute des Logos, im selben Verhaeltnis 1,8 : 1. */ ?>
                    <path class="sz-siegel__ring" d="M100 74 L146 100 L100 126 L54 100 Z" stroke-width="2.4"/>
                    <path class="sz-siegel__ring" d="M100 80 L139 100 L100 120 L61 100 Z" stroke-width="1"/>
                    <text class="sz-siegel__wort" x="100" y="105" text-anchor="middle" font-size="13">SAPELZA</text>
                </svg>
            </div>
            <p class="sz-partner__lead">
                <?php echo esc_html__('Die meisten unserer Kunden kennen das Geschäft am Dorfplatz in Toblach persönlich — Handwerksbetriebe, Hotels und Gastwirte aus dem Tal, manche seit zwei Generationen. Dieser Shop ändert daran nichts. Er soll Ihnen nur den Weg sparen, wenn Sie ohnehin wissen, was Sie brauchen.', 'sapelza-shop'); ?>
            </p>
        </div>

        <ul class="sz-partner__punkte">
            <?php foreach ($sz_punkte as $sz_i => $sz_p) : ?>
                <li class="sz-partner__punkt">
                    <span class="sz-partner__nr mono"><?php echo esc_html(str_pad((string) ($sz_i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                    <h3 class="sz-partner__ptitel display"><?php echo esc_html($sz_p['t']); ?></h3>
                    <p class="sz-partner__ptext"><?php echo esc_html($sz_p['b']); ?></p>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>

</section>
