<?php
/**
 * Bausteine für Kopf- und Fußzeile.
 *
 * Die Vorlagen header.php und footer.php halten nur die Auszeichnung; was
 * gerechnet werden muss, steht hier — sonst wird die Vorlage zum Programm.
 */

if (!defined('ABSPATH')) exit;

/**
 * Eigene Menüplätze.
 *
 * Astras Plätze bleiben bestehen, werden von unseren Vorlagen aber nicht
 * mehr gelesen. Solange dem Platz kein Menü zugewiesen ist, greift der
 * Rückfall weiter unten — der Kopf steht also sofort richtig da und lässt
 * sich später unter Design → Menüs verfeinern.
 */
add_action('after_setup_theme', function () {
    register_nav_menus([
        'sz-haupt' => __('SAPELZA — Hauptnavigation', 'sapelza-shop'),
        'sz-fuss'  => __('SAPELZA — Fußzeile Service', 'sapelza-shop'),
    ]);
});

/**
 * Rückfall-Navigation, solange kein Menü zugewiesen ist.
 *
 * Bewusst keine Seitenliste: die würde AGB, Sample Page und Checkout in
 * den Kopf holen. Stattdessen die Wege, die ein Betrieb wirklich braucht.
 */
function sz_kopf_menue_rueckfall(): void
{
    $shop  = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
    $konto = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');

    $punkte = [
        ['t' => __('Produkte', 'sapelza-shop'), 'u' => $shop],
    ];

    /* Nur zeigen, wenn die Seite auch existiert — ein Menuepunkt ins Leere
       ist schlimmer als ein fehlender. */
    $erfassung = function_exists('sz_erfassung_url') ? sz_erfassung_url() : '';
    if ($erfassung) {
        $punkte[] = ['t' => __('Schnellerfassung', 'sapelza-shop'), 'u' => $erfassung];
    }

    $punkte = array_merge($punkte, [
        ['t' => __('Meine Artikel', 'sapelza-shop'),   'u' => home_url('/meine-artikel/')],
        ['t' => __('Partnerbetriebe', 'sapelza-shop'), 'u' => home_url('/#sz-partner-titel')],
        ['t' => __('Konto', 'sapelza-shop'),           'u' => $konto],
    ]);

    echo '<ul class="sz-nav__liste">';
    foreach ($punkte as $p) {
        printf(
            '<li class="sz-nav__punkt"><a href="%s">%s</a></li>',
            esc_url($p['u']),
            esc_html($p['t'])
        );
    }
    echo '</ul>';
}

/**
 * Wie viele Artikel im Warenkorb liegen.
 *
 * Gibt -1 zurück, wenn es keinen Warenkorb gibt — dann zeigt der Kopf gar
 * keine Zahl, statt eine falsche Null zu behaupten.
 */
function sz_warenkorb_anzahl(): int
{
    if (!function_exists('WC') || !WC()->cart) return -1;
    return (int) WC()->cart->get_cart_contents_count();
}

/**
 * Das Logo, oder der Schriftzug als Rückfall.
 *
 * Vorrang hat immer das in WordPress hinterlegte Website-Logo
 * (Design → Customizer → Website-Informationen). Erst wenn keines gesetzt
 * ist, nimmt das Theme die mitgelieferte Datei. Das Original wurde
 * freigestellt übernommen und ausdrücklich nicht nachgebaut.
 */
function sz_logo(): string
{
    if (function_exists('has_custom_logo') && has_custom_logo()) {
        return get_custom_logo();
    }

    $datei = get_stylesheet_directory() . '/bilder/logo.png';
    if (file_exists($datei)) {
        return sprintf(
            '<a class="sz-logo" href="%s" rel="home"><img src="%s" width="219" height="141" alt="%s"></a>',
            esc_url(home_url('/')),
            esc_url(get_stylesheet_directory_uri() . '/bilder/logo.png'),
            esc_attr(get_bloginfo('name'))
        );
    }

    return sprintf(
        '<a class="sz-logo sz-logo--text" href="%s" rel="home">%s</a>',
        esc_url(home_url('/')),
        esc_html(get_bloginfo('name'))
    );
}

/**
 * Kopf- und Fußzeile brauchen ihre Regeln auf jeder Seite, nicht nur auf
 * der Startseite — deshalb getrennt von startseite.css eingebunden.
 */
add_action('wp_enqueue_scripts', function () {
    $fassung = wp_get_theme()->get('Version');
    $pfad    = get_stylesheet_directory_uri();

    wp_enqueue_style('sapelza-kopf', $pfad . '/css/kopf.css', ['sapelza-child'], $fassung);
    wp_enqueue_script('sapelza-kopf', $pfad . '/js/kopf.js', [], $fassung, true);
}, 25);
