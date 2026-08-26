<?php
/**
 * 02 Bestellwege — die drei Wege in den Warenkorb.
 *
 * Gleichrangige Kacheln, keine Textblöcke mit Symbol. Die Kachel trägt das
 * Bordeaux-Licht, das beim Anfahren von unten steigt — dieselbe Tiefe wie
 * im Sortiment, damit die Seite einen Rhythmus hat und nicht drei.
 *
 * Zwei der drei Ziele gibt es noch nicht (Schnellerfassung, Scannen). Sie
 * verweisen deshalb auf das Konto statt ins Leere; sobald die Seiten
 * existieren, wird hier ein Ziel eingetragen — nicht der Weg erfunden.
 */

if (!defined('ABSPATH')) exit;

$sz_konto = function_exists('wc_get_page_permalink')
    ? wc_get_page_permalink('myaccount')
    : home_url('/my-account/');

$sz_wege = [
    [
        'symbol' => 'tastatur',
        't' => __('Schnellerfassung', 'sapelza-shop'),
        'b' => __('Artikelnummer oder EAN eintippen; Bezeichnung, Bestand und Preis ergänzen sich von selbst, die Eingabe springt weiter in die nächste Zeile. Eine lange Bestellung entsteht in einem Zug, ohne einen einzigen Klick im Katalog.', 'sapelza-shop'),
        'm' => __('Zur Erfassung', 'sapelza-shop'),
        'href' => $sz_konto,
    ],
    [
        'symbol' => 'scan',
        't' => __('Scannen', 'sapelza-shop'),
        'b' => __('Barcode mit der Kamera Ihres Mobilgeräts erfassen, ebenso über die QR-Etiketten an Ihrem Lagerplatz. Sie bestellen dort, wo Ihnen der Mangel auffällt — im Lager, nicht Stunden später am Schreibtisch.', 'sapelza-shop'),
        'm' => __('Kamera öffnen', 'sapelza-shop'),
        'href' => $sz_konto,
    ],
    [
        'symbol' => 'wiederholen',
        't' => __('Meine Artikel', 'sapelza-shop'),
        'b' => __('Ihr eigener Katalog aus allem, was Sie bisher bezogen haben, auf Wunsch mit Ihren internen Bezeichnungen statt unseren. Was regelmäßig gebraucht wird, liegt in zwei Klicks wieder im Warenkorb.', 'sapelza-shop'),
        'm' => __('Liste öffnen', 'sapelza-shop'),
        'href' => home_url('/meine-artikel/'),
    ],
];

/**
 * Die drei Sinnbilder als Inline-SVG.
 *
 * Inline, weil sie die Textfarbe erben müssen und weil drei zusätzliche
 * Anfragen für drei Striche keinen Sinn ergeben.
 */
function sz_weg_symbol(string $name): string
{
    $gemein = 'viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" '
            . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"';

    $pfade = [
        'tastatur'    => '<rect x="4" y="14" width="40" height="22" rx="2"/><path d="M11 21h2M18 21h2M25 21h2M32 21h2M11 28h2M18 28h12M36 28h2"/>',
        'scan'        => '<path d="M6 16V8h8M42 16V8h-8M6 32v8h8M42 32v8h-8"/><path d="M13 24h22"/><path d="M17 18v12M23 18v12M29 18v12"/>',
        'wiederholen' => '<path d="M40 20a16 16 0 1 0-3 12"/><path d="M40 8v12H28"/><path d="M20 24h10"/>',
    ];

    return '<svg class="sz-weg__symbol" ' . $gemein . '>' . ($pfade[$name] ?? '') . '</svg>';
}

?>
<section class="sz-kapitel sz-wege" aria-labelledby="sz-wege-titel">
    <span class="geisterziffer" aria-hidden="true" style="right: -3vw; top: -6vh;">02</span>

    <div class="wrap" style="position: relative; z-index: 1;">
        <p class="sz-kapitelmarke">
            <span class="sz-kapitelmarke__nr mono">02</span>
            <span class="hairline" aria-hidden="true"></span>
            <span class="sz-kapitelmarke__kicker mono"><?php echo esc_html__('Bestellen', 'sapelza-shop'); ?></span>
        </p>

        <div class="sz-wege__kopf">
            <h2 id="sz-wege-titel" class="sz-wege__titel">
                <?php echo esc_html__('Ihr Weg zur Bestellung', 'sapelza-shop'); ?>
            </h2>
            <p class="sz-wege__lead">
                <?php echo esc_html__('Sie sollen nicht suchen, sondern erfassen. Alle drei Wege führen ohne Umweg über den Katalog in den Warenkorb — eine Bestellung über vierzig Positionen ist damit in wenigen Minuten fertig statt in einer halben Stunde.', 'sapelza-shop'); ?>
            </p>
        </div>
    </div>

    <div class="wrap" style="position: relative; z-index: 1;">
        <ul class="sz-wege__raster">
            <?php foreach ($sz_wege as $sz_i => $sz_w) : ?>
                <li class="sz-wege__zelle">
                    <a class="sz-weg" href="<?php echo esc_url($sz_w['href']); ?>">
                        <span class="sz-weg__licht" aria-hidden="true"></span>

                        <span class="sz-weg__kopf">
                            <span class="sz-weg__index mono">
                                <?php echo esc_html__('Bestellen', 'sapelza-shop'); ?> / <?php echo esc_html(str_pad((string) ($sz_i + 1), 2, '0', STR_PAD_LEFT)); ?>
                            </span>
                            <span class="sz-weg__punkt" aria-hidden="true"></span>
                        </span>

                        <h3 class="sz-weg__titel display"><?php echo esc_html($sz_w['t']); ?></h3>
                        <p class="sz-weg__text"><?php echo esc_html($sz_w['b']); ?></p>

                        <span class="sz-weg__buehne">
                            <span class="sz-weg__halo" aria-hidden="true"></span>
                            <?php
                            // Fest zusammengesetztes SVG aus sz_weg_symbol(), keine Nutzereingabe.
                            echo sz_weg_symbol($sz_w['symbol']); // phpcs:ignore WordPress.Security.EscapeOutput
                            ?>
                        </span>

                        <span class="sz-weg__strich" aria-hidden="true"></span>
                        <span class="sz-weg__mehr mono"><?php echo esc_html($sz_w['m']); ?> &rarr;</span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
