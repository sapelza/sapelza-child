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
            <h2 id="sz-partner-titel" class="sz-partner__titel">
                <?php echo esc_html__('Danke, dass Sie seit Jahren bei uns kaufen', 'sapelza-shop'); ?>
            </h2>
            <p class="sz-partner__lead">
                <?php echo esc_html__('Die meisten unserer Kunden kennen das Geschäft in der Graf-Künigl-Straße persönlich — Handwerksbetriebe, Hotels und Gastwirte aus dem Tal, manche seit zwei Generationen. Dieser Shop ändert daran nichts. Er soll Ihnen nur den Weg sparen, wenn Sie ohnehin wissen, was Sie brauchen.', 'sapelza-shop'); ?>
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

        <?php
        /*
         * Das Zeichen am Ende des Abschnitts. Darunter kam bisher nichts
         * mehr — eine leere Bahn vor dem Fuss. Dieselbe gezeichnete Marke
         * wie das Wasserzeichen, hier aber als Schlusspunkt und deutlich
         * genug, dass man sie als Zeichen liest.
         */
        ?>
        <div class="sz-partner__zeichen" aria-hidden="true">
            <svg viewBox="0 0 219 141" xmlns="http://www.w3.org/2000/svg">
                <path d="M109 11 L216 70.5 L109 130 L2 70.5 Z" fill="none" stroke="currentColor" stroke-width="3.5"/>
                <path d="M109 17 L206 70.5 L109 124 L12 70.5 Z" fill="none" stroke="currentColor" stroke-width="1.6"/>
                <path d="M24 50 H44 V53 H174 V50 H194 V96 H174 V93 H44 V96 H24 Z" fill="none" stroke="currentColor" stroke-width="3"/>
                <path d="M28 54 H48 V57 H170 V54 H190 V92 H170 V89 H48 V92 H28 Z" fill="none" stroke="currentColor" stroke-width="1.4"/>
                <text x="109" y="80" text-anchor="middle" fill="currentColor"
                      font-family="Georgia, 'Times New Roman', serif" font-size="27" letter-spacing="2.4">SAPELZA</text>
            </svg>
        </div>
</section>
