<?php
/**
 * SAPELZA Shop – Child-Theme für Astra
 */

if (!defined('ABSPATH')) exit;

/**
 * Eltern- und Kind-Stylesheet laden, dazu die Schriften der Homepage.
 */
add_action('wp_enqueue_scripts', function () {

    wp_enqueue_style(
        'astra-parent',
        get_template_directory_uri() . '/style.css',
        [],
        wp_get_theme(get_template())->get('Version')
    );

    wp_enqueue_style(
        'sapelza-fonts',
        'https://fonts.googleapis.com/css2'
            . '?family=DM+Serif+Display:ital@0;1'
            . '&family=Fira+Sans:wght@300;400;500;600'
            . '&family=JetBrains+Mono:wght@400;500'
            . '&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'sapelza-child',
        get_stylesheet_directory_uri() . '/style.css',
        ['astra-parent', 'sapelza-fonts'],
        wp_get_theme()->get('Version')
    );
}, 20);

/**
 * Vorverbindung zu Google Fonts – spart Ladezeit beim ersten Aufruf.
 */
add_filter('wp_resource_hints', function ($hints, $relation) {
    if ($relation === 'preconnect') {
        $hints[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous'];
    }
    return $hints;
}, 10, 2);

/**
 * Vier Produkte je Zeile statt drei.
 */
add_filter('loop_shop_columns', fn() => 4, 20);

/**
 * Auswahltext beim Gebinde verständlicher machen.
 */
add_filter('woocommerce_dropdown_variation_attribute_options_args', function ($args) {
    $args['show_option_none'] = 'Gebinde wählen';
    return $args;
});

/**
 * Was hier noch geladen wird, ist Gestaltung.
 *
 * Die Regeln des Betriebs, „Meine Artikel“ und der Wunschtermin liegen seit
 * der Trennung im Plugin „SAPELZA Shop“ — sie müssen einen Theme-Wechsel
 * überleben. Hier bleibt nur der Hell/Dunkel-Umschalter: der ist Rahmenwerk,
 * und ein neues Theme brächte seinen eigenen mit.
 */
foreach (['hell-dunkel', 'katalog', 'danke', 'kopf', 'shop'] as $teil) {
    $pfad = get_stylesheet_directory() . '/inc/' . $teil . '.php';
    if (file_exists($pfad)) require_once $pfad;
}

/**
 * Zugriff auf den Bereich, ohne sich auf das Plugin zu verlassen.
 *
 * sz_bereich() lebt im Plugin sapelza-shop, weil das Merken einer Wahl eine
 * Geschäftsentscheidung ist. Welche Bereiche es überhaupt gibt, liest das
 * Theme selbst aus dem Katalog — ist das Plugin abgeschaltet, zeigt die
 * Seite eben den größten Bereich, statt fatal zu enden.
 *
 * Theme-Code ruft ausschließlich diese Hülle auf, nie sz_bereich() direkt.
 */
function sz_theme_bereich(): string
{
    $bereiche = function_exists('sz_bereiche') ? sz_bereiche() : [];
    $erlaubt  = array_map(static fn($b) => $b->slug, $bereiche);
    if (!$erlaubt) return '';

    if (function_exists('sz_bereich')) {
        $wahl = sz_bereich();
        if (in_array($wahl, $erlaubt, true)) return $wahl;
    }

    /* Ohne Plugin gibt es kein Merken — dann zeigt die Seite den
       größten Bereich, statt gar nichts. */
    return $erlaubt[0];
}

/**
 * Die Startseite bringt eigene Stilregeln mit.
 *
 * Nur auf ihrer Vorlage geladen: der Shop braucht die Idiome der Startseite
 * nie, und jedes Kilobyte auf der Kategorieseite zahlt der Kunde mit
 * Ladezeit. Version aus dem Theme-Kopf, damit der Cache beim Update anspringt.
 */
add_action('wp_enqueue_scripts', function () {
    if (!is_page_template('page-startseite.php')) return;

    $fassung = wp_get_theme()->get('Version');
    $pfad    = get_stylesheet_directory_uri();

    wp_enqueue_style('sapelza-startseite', $pfad . '/css/startseite.css', ['sapelza-child'], $fassung);
    wp_enqueue_style('sapelza-abschnitte', $pfad . '/css/abschnitte.css', ['sapelza-startseite'], $fassung);

    /*
     * Im Fuss und ohne Abhaengigkeit: das Skript braucht weder jQuery noch
     * sonst etwas, und im Kopf wuerde es das Zeichnen der Seite aufhalten.
     */
    wp_enqueue_script('sapelza-startseite', $pfad . '/js/startseite.js', [], $fassung, true);
}, 30);

/**
 * Die veröffentlichten Rechtsseiten, als Titel => Adresse.
 *
 * Dieselbe Regel wie bei sz_theme_bereich(): die Liste lebt im Plugin,
 * das Theme fragt sie nur über diese Hülle ab. Ist das Plugin aus,
 * bleibt der Fuß trotzdem heil — dann kennt das Theme immerhin die
 * Datenschutzseite, die WordPress selbst bestimmt.
 *
 * Entwürfe erscheinen nicht. Der Fuß zeigt nur, was Besucher wirklich
 * lesen können.
 *
 * @return array<string, string>
 */
function sz_theme_rechtsseiten(): array
{
    $weg = [];

    if (function_exists('sz_rechtsseiten') && function_exists('sz_rechtsseite_adresse')) {
        foreach (sz_rechtsseiten() as $sz_s => $sz_d) {
            $sz_a = sz_rechtsseite_adresse($sz_s);
            if ($sz_a !== '') $weg[$sz_d['titel']] = $sz_a;
        }
        return $weg;
    }

    if (function_exists('get_privacy_policy_url')) {
        $sz_a = get_privacy_policy_url();
        if ($sz_a) $weg[__('Datenschutzerklärung', 'sapelza-shop')] = $sz_a;
    }

    return $weg;
}
