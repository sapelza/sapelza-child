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
 * Der Kopf einer Katalogseite.
 *
 * Nach dem Entwurf: Brotkrumen, Kapitelmarke, grosse Ueberschrift mit
 * Slogan, dann drei Leisten quer ueber die Seite — Bereich, Kategorien,
 * Marken — und zuletzt die Trefferzeile.
 *
 * Auf /shop/ gab es vorher fast nichts: ohne Begriff war die Elternkette
 * leer, also fielen die Unterkategorien weg, und eine Beschreibung hat
 * die Seite auch nicht. Uebrig blieben Kicker, Titel und die Trefferzahl.
 * Deshalb steht hier ueberall ein Fall fuer die Wurzel daneben.
 *
 * Alle Zahlen kommen aus dem Katalog. Nichts ist fest verdrahtet — was
 * der Shop nicht fuehrt, wird nicht behauptet.
 */
add_action('woocommerce_before_shop_loop', function () {
    if (!function_exists('is_woocommerce')) return;

    $begriff = is_product_taxonomy() ? get_queried_object() : null;
    if (!$begriff instanceof WP_Term) $begriff = null;

    $titel = $begriff ? $begriff->name : __('Produkte', 'sapelza-shop');
    $text  = $begriff ? term_description($begriff) : '';

    /* --- Die Kette vom Shop bis hierher ------------------------------ */
    $pfad = [];
    $kette = [];
    if ($begriff) {
        $vorfahren = get_ancestors($begriff->term_id, $begriff->taxonomy, 'taxonomy');
        foreach (array_reverse($vorfahren) as $id) {
            $t = get_term($id);
            if ($t instanceof WP_Term) $pfad[] = $t;
        }
        $pfad[]  = $begriff;
        $kette   = array_merge([$begriff->term_id], $vorfahren);
    }

    /* --- In welchem Bereich stehen wir? ------------------------------- */
    $bereiche   = function_exists('sz_bereiche') ? sz_bereiche() : [];
    $bereich    = null;
    foreach ($bereiche as $b) {
        if (in_array((int) $b->term_id, $kette, true)) { $bereich = $b; break; }
    }

    /*
     * Der Kicker nennt den Bereich, in dem man steht — auf der Wurzel
     * den Katalog selbst. So weiss man auch auf einer tiefen Kategorie,
     * zu welcher Haelfte des Hauses sie gehoert.
     */
    $kicker = $bereich ? $bereich->name : __('Katalog', 'sapelza-shop');

    /* --- Der Slogan --------------------------------------------------- */
    $slogan = '';
    if ($text !== '') {
        /* Der erste Satz der Beschreibung, wenn er kurz genug ist. */
        $roh = trim(wp_strip_all_tags($text));
        $ende = strcspn($roh, '.!?');
        $erster = trim(substr($roh, 0, $ende + 1));
        if ($erster !== '' && mb_strlen($erster) <= 90) $slogan = $erster;
    }

    /*
     * Auf der Wurzel steht kein Slogan.
     *
     * Dort stand "Zehn Abteilungen, 290 Artikel, geliefert im
     * Hochpustertal" — gross gesetzt, in derselben Zeile, die auf jeder
     * Kategorie den ersten Satz ihrer Beschreibung traegt. Auf der
     * Uebersichtsseite war das eine Behauptung ueber die Groesse des
     * Hauses an einer Stelle, an der man einen Katalog aufschlaegt.
     *
     * Die Zahl selbst bleibt — klein, in der Trefferzeile darunter:
     * "1 – 24 von 288 Artikeln". Sie sagt dort mehr, weil sie mitzaehlt,
     * was ein Filter gerade uebrig laesst.
     */

    /* --- Die Kategorien der Leiste ------------------------------------ */
    $eltern = 0;
    if ($begriff) {
        $kinder = get_terms(['taxonomy' => 'product_cat', 'parent' => $begriff->term_id, 'hide_empty' => true]);
        $eltern = (!is_wp_error($kinder) && $kinder) ? (int) $begriff->term_id : (int) $begriff->parent;
    }

    $geschwister = get_terms([
        'taxonomy'   => 'product_cat',
        'parent'     => $eltern,
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ]);
    if (is_wp_error($geschwister)) $geschwister = [];

    /* Auf der Wurzel sind das die Bereiche selbst — dann waere die
       Leiste eine Wiederholung der Bereichsleiste darueber. */
    $bereich_ids = array_map(static fn($b) => (int) $b->term_id, $bereiche);
    if (!$begriff) {
        $unter = [];
        foreach ($bereiche as $b) {
            foreach (function_exists('sz_abteilungen') ? sz_abteilungen((int) $b->term_id) : [] as $a) {
                $unter[] = $a;
            }
        }
        usort($unter, static fn($x, $y) => $y->count <=> $x->count);
        $geschwister = array_slice($unter, 0, 14);
    }

    /*
     * Steht man in einer Kategorie, zeigen wir die Marken dieser
     * Kategorie mit ihren Zahlen — nicht die des ganzen Hauses. Wer in
     * Reinigungsmitteln steht, sucht keine Marke, die es nur bei den
     * Handschuhen gibt.
     */
    $marken = [];
    if ($begriff && function_exists('sz_marken_in')) {
        foreach (sz_marken_in((int) $begriff->term_id) as $eintrag) {
            $m = $eintrag['begriff'];
            $m->count = (int) $eintrag['anzahl'];
            $marken[] = $m;
        }
    }

    if (!$marken) {
        $marken = function_exists('sz_marken_liste') ? sz_marken_liste(12) : [];
    }
    $marken_name = __('Marke', 'sapelza-shop');
    if ($marken && function_exists('sz_marken_taxonomie')) {
        $tx = get_taxonomy(sz_marken_taxonomie());
        if ($tx && !empty($tx->labels->singular_name)) $marken_name = $tx->labels->singular_name;
    }

    ?>
    <header class="sz-katalog__kopf">
        <?php if ($pfad) : ?>
            <nav class="sz-krumen" aria-label="<?php echo esc_attr__('Brotkrumen', 'sapelza-shop'); ?>">
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"><?php echo esc_html__('Produkte', 'sapelza-shop'); ?></a>
                <?php foreach ($pfad as $i => $t) :
                    $letzter = ($i === count($pfad) - 1); ?>
                    <span class="sz-krumen__strich" aria-hidden="true">/</span>
                    <?php if ($letzter) : ?>
                        <span aria-current="page"><?php echo esc_html($t->name); ?></span>
                    <?php else : ?>
                        <a href="<?php echo esc_url(get_term_link($t)); ?>"><?php echo esc_html($t->name); ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <p class="sz-kapitelmarke">
            <span class="sz-kapitelmarke__nr mono">03</span>
            <span class="hairline"></span>
            <span class="sz-kapitelmarke__kicker mono"><?php echo esc_html($kicker); ?></span>
        </p>

        <h1 class="sz-katalog__titel"><?php echo esc_html($titel); ?></h1>

        <?php if ($slogan !== '') : ?>
            <p class="sz-katalog__slogan"><?php echo esc_html($slogan); ?></p>
        <?php endif; ?>

        <?php if ($text !== '' && $slogan !== wp_strip_all_tags($text)) : ?>
            <div class="sz-katalog__text"><?php echo wp_kses_post($text); ?></div>
        <?php endif; ?>
    </header>

    <?php if (count($bereiche) > 1) : ?>
        <div class="sz-band sz-band--bereich">
            <div class="sz-band__innen">
                <span class="sz-kicker-klein"><?php echo esc_html__('Bereich', 'sapelza-shop'); ?></span>
                <nav class="sz-schalter" aria-label="<?php echo esc_attr__('Bereich', 'sapelza-shop'); ?>">
                    <?php foreach ($bereiche as $b) :
                        $ist = ($bereich && (int) $b->term_id === (int) $bereich->term_id); ?>
                        <a class="sz-schalter__weg" href="<?php echo esc_url(get_term_link($b)); ?>"
                           <?php echo $ist ? 'aria-current="page"' : ''; ?>>
                            <?php echo esc_html($b->name); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <?php
                /*
                 * Der Favoritenfilter steht bei der Bereichswahl, nicht bei
                 * den Kategorien: er schneidet quer durch alle Kategorien,
                 * genau wie der Bereich.
                 */
                if (function_exists('sz_favoriten') && is_user_logged_in()) :
                    $sz_gemerkt = sz_favoriten();
                    $sz_an      = function_exists('sz_favoritenfilter') && sz_favoritenfilter();
                    if ($sz_gemerkt || $sz_an) : ?>
                        <a class="sz-chip sz-chip--stern"
                           href="<?php echo esc_url(sz_favoriten_adresse(!$sz_an)); ?>"
                           <?php echo $sz_an ? 'aria-current="page"' : ''; ?>>
                            <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 2.6l2.9 5.88 6.49.94-4.7 4.58 1.11 6.46L12 17.4l-5.8 3.06 1.1-6.46-4.69-4.58 6.49-.94z"></path>
                            </svg>
                            <?php echo esc_html__('Favoriten', 'sapelza-shop'); ?>
                            <span class="sz-chip__zahl mono"><?php echo esc_html(number_format_i18n(count($sz_gemerkt))); ?></span>
                        </a>
                    <?php endif;
                endif; ?>

                <?php if ($bereich) : ?>
                    <span class="sz-band__notiz">
                        <?php printf(
                            esc_html(_n('%s Artikel in diesem Bereich', '%s Artikel in diesem Bereich', (int) $bereich->count, 'sapelza-shop')),
                            esc_html(number_format_i18n((int) $bereich->count))
                        ); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($geschwister) : ?>
        <div class="sz-band sz-band--kategorien">
            <div class="sz-band__innen sz-band__innen--stapel">
                <p class="sz-kicker-klein"><?php echo esc_html__('Unterkategorien', 'sapelza-shop'); ?></p>
                <nav class="sz-chips" aria-label="<?php echo esc_attr__('Unterkategorien', 'sapelza-shop'); ?>">
                    <?php foreach ($geschwister as $g) :
                        $aktiv = ($begriff && (int) $g->term_id === (int) $begriff->term_id); ?>
                        <a class="sz-chip" href="<?php echo esc_url(get_term_link($g)); ?>"
                           <?php echo $aktiv ? 'aria-current="page"' : ''; ?>>
                            <?php echo esc_html($g->name); ?>
                            <span class="sz-chip__zahl mono"><?php echo esc_html(number_format_i18n((int) $g->count)); ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($marken) : ?>
        <div class="sz-band sz-band--marken">
            <div class="sz-band__innen sz-band__innen--stapel">
                <p class="sz-kicker-klein"><?php echo esc_html($marken_name); ?></p>
                <?php
                /*
                 * Der Chip filtert innerhalb der Kategorie, statt ins
                 * Markenarchiv zu springen. Wer in Reinigungsmitteln
                 * steht und "Sutter" waehlt, will die Sutter-Reiniger —
                 * nicht alles von Sutter im ganzen Haus.
                 */
                $sz_marke_jetzt = isset($_GET['marke']) ? sanitize_title(wp_unslash($_GET['marke'])) : '';
                ?>
                <nav class="sz-chips" aria-label="<?php echo esc_attr($marken_name); ?>">
                    <?php foreach ($marken as $m) :
                        $sz_ist = ($sz_marke_jetzt === $m->slug);
                        $sz_weg = $begriff
                            ? esc_url($sz_ist ? remove_query_arg('marke') : add_query_arg('marke', $m->slug, remove_query_arg('marke')))
                            : esc_url(get_term_link($m));
                        ?>
                        <a class="sz-chip" href="<?php echo $sz_weg; // phpcs:ignore ?>"
                           <?php echo $sz_ist ? 'aria-current="page"' : ''; ?>>
                            <?php echo esc_html($m->name); ?>
                            <span class="sz-chip__zahl mono"><?php echo esc_html(number_format_i18n((int) $m->count)); ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
    <?php endif; ?>

    <div class="sz-katalog__zeile">
        <?php
        /*
         * Die Trefferzeile selbst gesetzt: WooCommerces Satz ("Ergebnisse
         * 1 - 24 von 288 werden angezeigt") passt nicht in die Zeile, die
         * der Entwurf dort vorsieht.
         */
        global $wp_query;
        $gesamt = (int) $wp_query->found_posts;
        $pro    = (int) $wp_query->get('posts_per_page');
        $seite  = max(1, (int) $wp_query->get('paged'));
        $ab     = ($seite - 1) * $pro + 1;
        $bisher = min($gesamt, $seite * $pro);

        if ($gesamt > 0) : ?>
            <p class="sz-katalog__treffer mono">
                <?php printf(
                    /* translators: 1: erster Artikel, 2: letzter Artikel, 3: Gesamtzahl. */
                    esc_html__('%1$s – %2$s von %3$s Artikeln', 'sapelza-shop'),
                    esc_html(number_format_i18n($ab)),
                    esc_html(number_format_i18n($bisher)),
                    esc_html(number_format_i18n($gesamt))
                ); ?>
            </p>
        <?php endif;

        woocommerce_catalog_ordering();
        ?>
    </div>
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

    /*
     * Auch auf gewoehnlichen Seiten, die einen unserer Bausteine tragen —
     * die Schnellerfassung ist keine WooCommerce-Seite, braucht diese
     * Regeln aber. Ohne diese Zeile stuende sie voellig ungestaltet da.
     */
    $eigener_baustein = false;
    if (is_singular()) {
        $inhalt = get_post_field('post_content', get_queried_object_id());
        $eigener_baustein = is_string($inhalt) && (
            has_shortcode($inhalt, 'sz_schnellerfassung') ||
            has_shortcode($inhalt, 'sz_meine_artikel')
        );
    }

    if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page() && !$eigener_baustein) return;

    wp_enqueue_style(
        'sapelza-shop-seiten',
        get_stylesheet_directory_uri() . '/css/shop.css',
        ['sapelza-child'],
        wp_get_theme()->get('Version')
    );

    wp_enqueue_script(
        'sapelza-menge',
        get_stylesheet_directory_uri() . '/js/menge.js',
        [],
        wp_get_theme()->get('Version'),
        true
    );

    wp_enqueue_script(
        'sapelza-sortierung',
        get_stylesheet_directory_uri() . '/js/sortierung.js',
        [],
        wp_get_theme()->get('Version'),
        true
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

/* ===================================================================
   Klassische Kasse — Zahlung nach links
   =================================================================== */

/**
 * Die Zahlungsauswahl gehört unter die Adresse, nicht in die Bestellkarte.
 *
 * WooCommerce hängt sie standardmäßig an woocommerce_checkout_order_review
 * (Priorität 20) — also in die rechte Spalte, zusammen mit Summen und
 * Bestellknopf. Im Entwurf steht sie links unter der Adresse, und rechts
 * bleibt nur die Bestellung mit dem Knopf.
 *
 * Kein Vorlagen-Eingriff nötig: der Haken wird abgehängt und woanders
 * wieder eingehängt. So bleibt jede Erweiterung, die sich in das Formular
 * einklinkt, unangetastet.
 *
 * Wirkt nur auf der klassischen Kasse. Auf der Block-Fassung gibt es diese
 * Haken nicht — dort passiert schlicht nichts.
 */
add_action('wp', function () {
    if (!function_exists('is_checkout') || !is_checkout()) return;

    remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
    add_action('woocommerce_checkout_after_customer_details', 'woocommerce_checkout_payment', 20);
});

/* ===================================================================
   Die Kachel im Sortiment
   ===================================================================

   Der Entwurf setzt das Bild in einen cremefarbenen Kasten im Quadrat
   und darunter Marke, Name, Preis. Kein eigenes content-product.php:
   WooCommerces Kachel ruft woocommerce_after_shop_loop_item_title auf,
   und genau dort setzt B2BKing die Preise. Eine eigene Vorlage wuerde
   den Haken ueberspringen.

   Also nur zwei Klammern um das Bild und eine Zeile davor.
*/

add_action('woocommerce_before_shop_loop_item_title', function () {
    echo '<span class="sz-kachel__bild">';
}, 5);

add_action('woocommerce_before_shop_loop_item_title', function () {
    echo '</span>';
}, 20);

add_action('woocommerce_shop_loop_item_title', function () {
    if (!function_exists('sz_marken_taxonomie')) return;

    $taxonomie = sz_marken_taxonomie();
    if ($taxonomie === '') return;

    global $product;
    if (!$product) return;

    $marken = get_the_terms($product->get_id(), $taxonomie);
    if (!$marken || is_wp_error($marken)) return;

    printf('<span class="sz-kachel__marke">%s</span>', esc_html($marken[0]->name));
}, 5);

/*
 * Auf Seiten mit unseren Bausteinen traegt WordPress den Seitentitel
 * schon oben. Der Baustein bringt seine eigene Ueberschrift mit
 * Kapitelmarke und Vorspann mit, wie im Entwurf — sonst stuende
 * "Meine Artikel" zweimal da.
 */
add_filter('body_class', function ($klassen) {
    if (!is_singular()) return $klassen;

    $inhalt = get_post_field('post_content', get_queried_object_id());
    if (is_string($inhalt) && has_shortcode($inhalt, 'sz_meine_artikel')) {
        $klassen[] = 'sz-eigener-titel';
    }

    return $klassen;
});

/*
 * Vier Artikel je Reihe.
 *
 * WooCommerce haengt der Liste .columns-N an, und Astras Rasterdatei
 * richtet sich danach. Statt gegen diese Regel anzuschreiben, stellen
 * wir die Zahl selbst: dann heisst die Klasse .columns-4 und Astras
 * eigene Regel liefert vier Spalten.
 */
add_filter('loop_shop_columns', static fn() => 4, 20);

/*
 * "Weiterlesen" an nicht bestellbaren Artikeln.
 *
 * WooCommerce nimmt dort den Text der Blog-Ansicht. In einem Katalog
 * liest sich das falsch — man liest nichts weiter, man sieht sich den
 * Artikel an. Der Link bleibt: dort steht, ob es einen Nachfolger gibt.
 */
add_filter('woocommerce_product_add_to_cart_text', static function ($text, $produkt) {
    if (!$produkt instanceof WC_Product) return $text;
    if ($produkt->is_purchasable() && $produkt->is_in_stock()) return $text;

    return __('Artikel ansehen', 'sapelza-shop');
}, 20, 2);

/*
 * Kacheln ohne Bild.
 *
 * Solange die Artikel keine Bilder haben, zeigt jede Kachel denselben
 * grauen Platzhalter. Vierundzwanzig gleiche Platzhalter nebeneinander
 * sehen nach kaputtem Katalog aus — eine Liste ohne Bildflaeche sieht
 * nach Liste aus. Das ist ehrlicher, solange die Bilder fehlen.
 *
 * Die Klasse haengt am einzelnen Artikel: sobald einer ein Bild
 * bekommt, hat er wieder seinen Kasten, ohne dass hier etwas
 * umgestellt werden muss.
 */
add_filter('woocommerce_post_class', static function ($klassen, $produkt) {
    if (!$produkt instanceof WC_Product) return $klassen;

    $bild = $produkt->get_image_id();
    if (!$bild) $klassen[] = 'sz-ohne-bild';

    return $klassen;
}, 10, 2);
