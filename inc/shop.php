<?php
/**
 * Katalogseiten — Kategorie und Produkt.
 *
 * Bewusst über Haken statt über eine ersetzte archive-product.php: diese
 * Vorlage ruft dutzende Haken auf, an denen auch B2BKing hängt
 * (Preisgruppen, Staffelpreise, Sichtbarkeit). Wer sie ersetzt, bricht das
 * still — und merkt es erst, wenn ein Kunde falsche Preise sieht.
 *
 * Ersetzt wird deshalb nur, was reine Darstellung ist: die Produktkachel
 * (woocommerce/content-product.php) und das, was hier eingehängt wird.
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
