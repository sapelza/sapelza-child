<?php
/**
 * Katalogseiten — Kategorie und Produkt.
 *
 * Bewusst über Haken statt über eine ersetzte archive-product.php: diese
 * Vorlage ruft dutzende Haken auf, an denen auch B2BKing hängt
 * (Preisgruppen, Staffelpreise, Sichtbarkeit). Wer sie ersetzt, bricht das
 * still — und merkt es erst, wenn ein Kunde falsche Preise sieht.
 *
 * Es wird ueberhaupt keine WooCommerce-Vorlage ersetzt. Alles hier haengt
 * sich ein oder gestaltet — auch die Produktkachel, die zunaechst als
 * eigene content-product.php entstand und wieder verworfen wurde: sie haette
 * woocommerce_after_shop_loop_item_title uebersprungen, und genau dort setzt
 * B2BKing die Preise.
 */

if (!defined('ABSPATH')) exit;

/* Drei Spalten, wie im Entwurf. */
add_filter('loop_shop_columns', fn() => 3, 30);

/* Vierundzwanzig Artikel je Seite: acht Zeilen zu dreien. */
add_filter('loop_shop_per_page', fn() => 24, 30);

/**
 * Astras Seitenleiste hat auf den Katalogseiten nichts verloren —
 * das Raster braucht die volle Breite.
 */
add_filter('astra_page_layout', function ($layout) {
    if (function_exists('is_woocommerce') && (is_shop() || is_product_taxonomy())) {
        return 'no-sidebar';
    }
    return $layout;
}, 30);

/*
 * WooCommerce setzt über den Katalog eine Trefferzahl und eine
 * Sortierauswahl. Beides bleibt — aber unter unserem Kopf, nicht davor.
 */
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

/**
 * Der Kopf einer Kategorieseite.
 *
 * Grosse Überschrift, Beschreibung, dann die Unterkategorien und Marken
 * als Filterreihen. Alles aus echten Begriffen — nichts fest verdrahtet.
 */
add_action('woocommerce_before_shop_loop', function () {
    if (!function_exists('is_woocommerce')) return;

    $begriff = is_product_taxonomy() ? get_queried_object() : null;
    $titel   = $begriff instanceof WP_Term ? $begriff->name : __('Sortiment', 'sapelza-shop');
    $text    = $begriff instanceof WP_Term ? term_description($begriff) : '';

    ?>
    <header class="sz-katalog__kopf">
        <p class="sz-katalog__kicker mono"><?php echo esc_html__('Sortiment', 'sapelza-shop'); ?></p>
        <h1 class="sz-katalog__titel"><?php echo esc_html($titel); ?></h1>
        <?php if ($text) : ?>
            <div class="sz-katalog__text"><?php echo wp_kses_post($text); ?></div>
        <?php endif; ?>

        <?php
        /*
         * Unterkategorien. Auf einer Oberkategorie sind das die
         * Abteilungen, auf einer Abteilung die Geschwister — so bleibt
         * man beim Wechseln auf derselben Ebene statt hinauszuspringen.
         */
        $eltern = 0;
        if ($begriff instanceof WP_Term) {
            $kinder = get_terms(['taxonomy' => 'product_cat', 'parent' => $begriff->term_id, 'hide_empty' => true]);
            $eltern = (!is_wp_error($kinder) && $kinder) ? $begriff->term_id : (int) $begriff->parent;
        }

        $geschwister = $eltern
            ? get_terms(['taxonomy' => 'product_cat', 'parent' => $eltern, 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC'])
            : [];

        if (!is_wp_error($geschwister) && $geschwister) :
            ?>
            <nav class="sz-chips" aria-label="<?php echo esc_attr__('Unterkategorien', 'sapelza-shop'); ?>">
                <?php foreach ($geschwister as $g) :
                    $aktiv = ($begriff instanceof WP_Term && $g->term_id === $begriff->term_id);
                    ?>
                    <a class="sz-chip" href="<?php echo esc_url(get_term_link($g)); ?>"
                       <?php echo $aktiv ? 'aria-current="true"' : ''; ?>>
                        <?php echo esc_html($g->name); ?>
                        <span class="sz-chip__zahl"><?php echo esc_html((string) $g->count); ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php
        /* Marken, nur wenn dieser Shop welche führt. */
        $marken = function_exists('sz_marken_liste') ? sz_marken_liste(10) : [];
        if ($marken) :
            ?>
            <nav class="sz-chips sz-chips--marken" aria-label="<?php echo esc_attr__('Marken', 'sapelza-shop'); ?>">
                <span class="sz-chips__label mono"><?php echo esc_html__('Marke', 'sapelza-shop'); ?></span>
                <?php foreach ($marken as $m) : ?>
                    <a class="sz-chip" href="<?php echo esc_url(get_term_link($m)); ?>">
                        <?php echo esc_html($m->name); ?>
                        <span class="sz-chip__zahl"><?php echo esc_html((string) $m->count); ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <div class="sz-katalog__zeile">
            <?php
            // Trefferzahl und Sortierung stehen jetzt hier, unter dem Kopf.
            woocommerce_result_count();
            woocommerce_catalog_ordering();
            ?>
        </div>
    </header>
    <?php
}, 5);

/**
 * Die Stilregeln der Katalogseiten.
 *
 * Nur dort geladen, wo sie gebraucht werden — der Kunde auf der
 * Startseite zahlt sie nicht mit.
 */
add_action('wp_enqueue_scripts', function () {
    if (!function_exists('is_woocommerce')) return;
    if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page()) return;

    wp_enqueue_style(
        'sapelza-shop-seiten',
        get_stylesheet_directory_uri() . '/css/shop.css',
        ['sapelza-child'],
        wp_get_theme()->get('Version')
    );
}, 30);

/* ===================================================================
   Produktseite
   ===================================================================

   Wieder ueber Haken, nicht ueber ersetzte Vorlagen — aber NICHT ueber
   Prioritaeten von woocommerce_single_product_summary: Astra ordnet diese
   Spalte selbst um, dadurch landeten Einschuebe nicht dort, wo die Zahl
   es vermuten liesse.

   Verlaesslich sind benannte Haken:

     woocommerce_single_product_summary, 4   Markenzeile vor dem Titel
     woocommerce_after_add_to_cart_form      Hinweis auf den Wunschtermin

   Artikelnummer und Steuerangabe kommen von WooCommerce selbst und werden
   nur gestaltet — siehe die Begruendungen weiter unten.
*/

/**
 * Die Marke über dem Titel — wie im Entwurf, mit Punkt davor.
 */
add_action('woocommerce_single_product_summary', function () {
    global $product;
    if (!$product instanceof WC_Product) return;
    if (!function_exists('sz_marken_taxonomie')) return;

    $taxonomie = sz_marken_taxonomie();
    if ($taxonomie === '') return;

    $begriffe = get_the_terms($product->get_id(), $taxonomie);
    if (!$begriffe || is_wp_error($begriffe)) return;

    printf(
        '<p class="sz-produkt__marke mono"><span class="sz-punkt sz-punkt--da" aria-hidden="true"></span>%s</p>',
        esc_html($begriffe[0]->name)
    );
}, 4);

/*
 * Keine eigene Artikelnummer-Zeile.
 *
 * Sie stand doppelt: unsere zeigte die Nummer des Elternprodukts, die von
 * WooCommerce weiter unten die der gewaehlten Variante — zwei
 * verschiedene Nummern fuer dasselbe Produkt. WooCommerces eigene ist die
 * richtige, weil sie mit dem Gebinde wechselt. Sie wird nur gestaltet.
 */

/*
 * Und kein eigener Steuerhinweis.
 *
 * Er behauptete "inkl. MwSt.", waehrend der Shop die Preise "excl. IVA"
 * ausweist. Eine falsche Steuerangabe ist auf einem Rechnungsshop kein
 * Schoenheitsfehler. Was gilt, sagt WooCommerce selbst — wir erfinden es
 * nicht dazu.
 */

/**
 * Der Hinweis auf den Wunschtermin.
 *
 * Der Liefertag ist das, was diesen Shop von einem Versandhaus
 * unterscheidet — er gehört auf die Produktseite, nicht erst in die Kasse.
 */
add_action('woocommerce_after_add_to_cart_form', function () {
    ?>
    <p class="sz-produkt__termin">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.7" stroke-linecap="round" aria-hidden="true" focusable="false">
            <rect x="3.5" y="5" width="17" height="16" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/>
        </svg>
        <?php echo esc_html__('Den Liefertermin bestimmen Sie beim Bestellen', 'sapelza-shop'); ?>
    </p>
    <?php
});

/* ===================================================================
   Kasse — Kopf und Schrittanzeige
   =================================================================== */

/**
 * Kapitelmarke, Titel und die drei Schritte über der Kasse.
 *
 * Über den Seiteninhalt eingefügt, nicht über einen WooCommerce-Haken:
 * die gängigen Kassen-Haken gehören zur klassischen Fassung und laufen
 * auf der Block-Kasse nie.
 *
 * Die Schrittanzeige ist bewusst nicht klickbar. Sie zeigt, wo man steht
 * — sie ist kein Menü. Zurück geht es über den Warenkorb.
 */
add_filter('the_content', function ($inhalt) {
    if (!function_exists('is_checkout') || !is_checkout()) return $inhalt;
    if (function_exists('is_order_received_page') && is_order_received_page()) return $inhalt;
    if (!in_the_loop() || !is_main_query()) return $inhalt;
    if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) return $inhalt;

    $schritte = [
        ['nr' => '01', 'name' => __('Warenkorb', 'sapelza-shop'),        'stand' => 'fertig'],
        ['nr' => '02', 'name' => __('Adresse & Zahlung', 'sapelza-shop'), 'stand' => 'jetzt'],
        ['nr' => '03', 'name' => __('Bestätigung', 'sapelza-shop'),       'stand' => 'offen'],
    ];

    ob_start();
    ?>
    <header class="sz-kasse__kopf">
        <p class="sz-kapitelmarke">
            <span class="sz-kapitelmarke__nr mono">02</span>
            <span class="hairline" aria-hidden="true"></span>
            <span class="sz-kapitelmarke__kicker mono">
                <?php echo esc_html__('Schritt 02 von 03', 'sapelza-shop'); ?>
            </span>
        </p>

        <h1 class="sz-kasse__titel"><?php echo esc_html__('Kasse', 'sapelza-shop'); ?></h1>

        <ol class="sz-schritte-leiste">
            <?php foreach ($schritte as $s) : ?>
                <li class="sz-schritt-feld ist-<?php echo esc_attr($s['stand']); ?>">
                    <span class="sz-schritt-feld__zeichen mono" aria-hidden="true">
                        <?php echo $s['stand'] === 'fertig' ? '&check;' : esc_html($s['nr']); ?>
                    </span>
                    <span class="sz-schritt-feld__name"><?php echo esc_html($s['name']); ?></span>
                </li>
            <?php endforeach; ?>
        </ol>
    </header>
    <?php
    return (string) ob_get_clean() . $inhalt;
}, 18);
