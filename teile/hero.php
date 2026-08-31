<?php
/**
 * Hero — der Satz, der sich öffnet.
 *
 * Drei Wörter im Satz sind anfahrbar und blenden eine Mindmap ein. Die
 * Einblendung selbst kommt später; die Auszeichnung steht schon, damit sie
 * sich ohne Umbau des Satzes nachrüsten lässt.
 *
 * Die Zahlen im Satz stammen aus dem Katalog, nicht aus dem Entwurf —
 * siehe inc/katalog.php.
 */

if (!defined('ABSPATH')) exit;

$sz_abteilungen = sz_abteilungen_gesamt();
$sz_marken      = sz_marken_anzahl();

?>
<?php
/*
 * Die Bildadresse des Porters haengt am Abschnitt, damit das Skript sie
 * nicht aus dem Fussbereich klauben muss. Der Fuss traegt denselben
 * Wagen als Bildlaufanzeige — verlaesst man sich darauf, bricht das
 * Beiwerk, sobald dort etwas anderes steht.
 */
?>
<section class="sz-hero" aria-labelledby="sz-hero-satz"
         data-sz-porter="<?php echo esc_url(get_stylesheet_directory_uri() . '/bilder/porter-oben.webp'); ?>"
         data-sz-listen="<?php echo esc_attr(wp_json_encode(sz_hero_listen())); ?>">
    <div class="wrap">

        <p class="kicker">
            <span class="kicker__punkt" aria-hidden="true"></span>
            <?php echo esc_html__('Kaufhaus Sapelza · Toblach', 'sapelza-shop'); ?>
        </p>

        <h1 id="sz-hero-satz" class="statement">
            <?php if ($sz_abteilungen > 0) : ?>
                <button type="button" class="sz-hero__wort" data-sz-wort="bereiche" aria-expanded="false">
                    <?php
                    printf(
                        /* translators: %s ist die Zahl der Abteilungen, als Wort oder Ziffer. */
                        esc_html__('%s Abteilungen', 'sapelza-shop'),
                        esc_html(sz_als_wort($sz_abteilungen))
                    );
                    ?>
                </button>
                <?php echo esc_html__('für Handwerk und Gastronomie,', 'sapelza-shop'); ?>
            <?php else : ?>
                <?php echo esc_html__('Alles für Handwerk und Gastronomie,', 'sapelza-shop'); ?>
            <?php endif; ?>

            <?php if ($sz_marken > 0) : ?>
                <button type="button" class="sz-hero__wort" data-sz-wort="marken" aria-expanded="false">
                    <?php
                    printf(
                        /* translators: %d ist die Zahl der Marken im Sortiment. */
                        esc_html__('%d Marken', 'sapelza-shop'),
                        (int) $sz_marken
                    );
                    ?>
                </button>
            <?php endif; ?>

            <?php echo esc_html__('geliefert', 'sapelza-shop'); ?>
            <button type="button" class="sz-hero__wort" data-sz-wort="karte" aria-expanded="false">
                <?php echo esc_html__('im Hochpustertal', 'sapelza-shop'); ?>
            </button>
            <?php echo esc_html__('— an dem Tag, den Sie bestimmen.', 'sapelza-shop'); ?>
        </h1>

        <?php
        /*
         * Die Suche geht bewusst an WooCommerce, nicht an die WordPress-Suche:
         * post_type=product hält Beiträge und Seiten aus den Treffern heraus.
         */
        ?>
        <form class="sz-hero__suche" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
            <input type="hidden" name="post_type" value="product">
            <div class="sz-hero__feld">
                <svg class="sz-hero__lupe" width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m20 20-3.5-3.5"></path>
                </svg>
                <label class="screen-reader-text" for="sz-hero-suchfeld">
                    <?php echo esc_html__('Artikel, Marke oder Artikelnummer', 'sapelza-shop'); ?>
                </label>
                <input type="search" id="sz-hero-suchfeld" name="s"
                       placeholder="<?php echo esc_attr__('Artikel, Marke oder Artikelnummer …', 'sapelza-shop'); ?>"
                       value="<?php echo esc_attr(get_search_query()); ?>">
            </div>
            <button type="submit" class="sz-hero__knopf">
                <?php echo esc_html__('Suchen', 'sapelza-shop'); ?>
            </button>
        </form>

        <p class="sz-hero__hinweis">
            <?php echo esc_html__('Angemeldet?', 'sapelza-shop'); ?>
            <a href="<?php echo esc_url(home_url('/meine-artikel/')); ?>">
                <?php echo esc_html__('Zu Ihren bereits gekauften Artikeln', 'sapelza-shop'); ?>
            </a>
        </p>

    </div>
</section>
