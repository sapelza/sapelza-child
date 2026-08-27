<?php
/**
 * Der Dank nach der Bestellung.
 *
 * Sitzt oben auf der Seite „Bestellung erhalten", über den Bestelldaten —
 * bewusst im Seitenfluss und nicht als Einblendung darüber. Auf dieser
 * Seite stehen bei Vorkasse die Bankdaten; was sie verdeckt oder erst
 * weggeklickt werden muss, kostet Überweisungen.
 *
 * Das ist Gestaltung, deshalb Theme und nicht Plugin. Wechselt das Theme,
 * darf der Dank mitgehen — anders als der Wunschtermin, an dem Bestelldaten
 * hängen.
 */

if (!defined('ABSPATH')) exit;

/** Merker an der Bestellung: der Dank lief bereits. */
const SZ_DANKE_META = '_sz_danke_gezeigt';

/**
 * Welche Videofassungen vorliegen.
 *
 * WebM zuerst: kleiner, und mit Alphakanal auch transparent — dieselbe
 * Technik wie beim Porter. MP4 als Rückfall für ältere Safari-Fassungen.
 *
 * @return array<string,string> MIME-Typ => URL
 */
function sz_danke_quellen(): array
{
    $ordner  = get_stylesheet_directory() . '/medien/';
    $adresse = get_stylesheet_directory_uri() . '/medien/';
    $gefunden = [];

    foreach (['danke.webm' => 'video/webm', 'danke.mp4' => 'video/mp4'] as $datei => $typ) {
        if (file_exists($ordner . $datei)) $gefunden[$typ] = $adresse . $datei;
    }

    return $gefunden;
}

/**
 * Wurde der Dank für diese Bestellung schon gezeigt?
 *
 * Die Bestätigungsseite steht in der Bestätigungsmail und wird wieder
 * aufgerufen. Beim dritten Mal ist eine Feier kein Dank mehr, sondern
 * Lärm. Gemerkt wird an der Bestellung, nicht im Browser: sonst liefe sie
 * auf jedem Gerät erneut.
 */
function sz_danke_schon_gezeigt(WC_Order $bestellung): bool
{
    return (bool) $bestellung->get_meta(SZ_DANKE_META);
}

add_action('woocommerce_before_thankyou', function ($bestell_id) {
    $bestellung = wc_get_order($bestell_id);
    if (!$bestellung instanceof WC_Order) return;

    /* Bei fehlgeschlagener Zahlung wäre ein Dank verfrüht. */
    if ($bestellung->has_status(['failed', 'cancelled'])) return;

    if (sz_danke_schon_gezeigt($bestellung)) return;

    $quellen = sz_danke_quellen();
    $name    = $bestellung->get_billing_company() ?: $bestellung->get_billing_first_name();

    ?>
    <section class="sz-danke<?php echo $quellen ? ' hat-video' : ''; ?>" aria-labelledby="sz-danke-titel">

        <?php if ($quellen) : ?>
            <?php
            /*
             * muted und playsinline sind Pflicht, sonst verweigern Browser
             * das automatische Abspielen. Kein loop und keine Bedienleiste:
             * der Dank läuft einmal und tritt dann zurück.
             */
            ?>
            <video class="sz-danke__video" autoplay muted playsinline preload="auto"
                   aria-hidden="true" tabindex="-1">
                <?php foreach ($quellen as $sz_typ => $sz_url) : ?>
                    <source src="<?php echo esc_url($sz_url); ?>" type="<?php echo esc_attr($sz_typ); ?>">
                <?php endforeach; ?>
            </video>
            <script>
            /*
             * autoplay startet auch, wenn CSS das Video versteckt. Wer
             * reduzierte Bewegung eingestellt hat, soll es gar nicht erst
             * laufen sehen — also anhalten und die Quellen loesen, damit
             * auch nichts nachgeladen wird.
             */
            ( function () {
                if ( ! window.matchMedia || ! window.matchMedia( "(prefers-reduced-motion: reduce)" ).matches ) return;
                var v = document.currentScript && document.currentScript.previousElementSibling;
                if ( ! v || v.tagName !== "VIDEO" ) return;
                v.pause();
                v.removeAttribute( "autoplay" );
                while ( v.firstChild ) v.removeChild( v.firstChild );
                v.load();
            } )();
            </script>
        <?php endif; ?>

        <div class="sz-danke__text">
            <p class="sz-danke__kicker mono"><?php echo esc_html__('Bestellung eingegangen', 'sapelza-shop'); ?></p>

            <h2 id="sz-danke-titel" class="sz-danke__titel">
                <?php
                if ($name) {
                    printf(
                        /* translators: %s ist der Firmenname oder Vorname des Bestellers. */
                        esc_html__('Danke für Ihren Einkauf, %s.', 'sapelza-shop'),
                        esc_html($name)
                    );
                } else {
                    echo esc_html__('Danke für Ihren Einkauf.', 'sapelza-shop');
                }
                ?>
            </h2>

            <p class="sz-danke__lead">
                <?php echo esc_html__('Wir stellen Ihre Bestellung zusammen. Alles Weitere steht unten — und im Zweifel rufen Sie einfach an.', 'sapelza-shop'); ?>
            </p>
        </div>
    </section>
    <?php

    /*
     * Erst nach der Ausgabe merken: bricht das Rendern vorher ab, soll der
     * Dank beim nächsten Aufruf noch kommen.
     */
    $bestellung->update_meta_data(SZ_DANKE_META, current_time('mysql'));
    $bestellung->save();
}, 5);

/**
 * Die Stilregeln nur auf der Bestätigungsseite laden.
 */
add_action('wp_enqueue_scripts', function () {
    if (!function_exists('is_order_received_page') || !is_order_received_page()) return;

    wp_enqueue_style(
        'sapelza-danke',
        get_stylesheet_directory_uri() . '/css/danke.css',
        ['sapelza-child'],
        wp_get_theme()->get('Version')
    );
}, 30);
