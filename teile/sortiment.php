<?php
/**
 * Sortiment — die Abteilungen des gewählten Bereichs.
 *
 * Liest ausschließlich echte Kategorien (product_cat): Ebene 1 ist der
 * Bereich, Ebene 2 die Abteilung. Nichts ist fest verdrahtet — kommt ein
 * Handwerk-Sortiment als weitere Oberkategorie dazu, erscheint es hier von
 * selbst.
 *
 * Der Umschalter sind Links mit ?bereich=, keine Schaltflächen mit
 * JavaScript: so funktioniert er ohne Skript, und sz_bereich() im Plugin
 * liest genau diesen Parameter und merkt ihn am Benutzer.
 */

if (!defined('ABSPATH')) exit;

$sz_bereiche = sz_bereiche();
$sz_aktiv    = sz_theme_bereich();

/* Ohne gemerkte Wahl gilt der größte Bereich — sonst stünde hier eine
   Überschrift über einer leeren Fläche. */
if ($sz_aktiv === '' && $sz_bereiche) {
    $sz_aktiv = $sz_bereiche[0]->slug;
}

$sz_aktiver_begriff = null;
foreach ($sz_bereiche as $sz_b) {
    if ($sz_b->slug === $sz_aktiv) { $sz_aktiver_begriff = $sz_b; break; }
}

$sz_abteilungen = $sz_aktiver_begriff
    ? sz_abteilungen((int) $sz_aktiver_begriff->term_id)
    : [];

?>
<section class="sz-sortiment" aria-labelledby="sz-sortiment-titel">
    <span class="geisterziffer" aria-hidden="true" style="right: -2vw; bottom: -6vh;">01</span>

    <div class="wrap" style="position: relative; z-index: 1;">

        <p class="kicker"><?php echo esc_html__('Produkte', 'sapelza-shop'); ?></p>

        <div class="sz-sortiment__kopf">
            <h2 id="sz-sortiment-titel" class="sz-sortiment__titel">
                <?php echo esc_html__('Was Sie bei uns bestellen.', 'sapelza-shop'); ?>
            </h2>
            <p style="color: var(--sz-ink-soft); max-width: 42ch;">
                <?php echo esc_html__('Bestellen statt zusammensuchen. Nicht gefunden? Fragen Sie uns — wir beschaffen es.', 'sapelza-shop'); ?>
            </p>
        </div>

        <?php if (count($sz_bereiche) > 1) : ?>
            <nav class="sz-bereiche" aria-label="<?php echo esc_attr__('Bereich wählen', 'sapelza-shop'); ?>">
                <?php foreach ($sz_bereiche as $sz_b) :
                    $sz_ist_aktiv = ($sz_b->slug === $sz_aktiv);
                    ?>
                    <a class="sz-bereiche__knopf"
                       href="<?php echo esc_url(add_query_arg('bereich', $sz_b->slug, get_permalink())); ?>#sz-sortiment-titel"
                       <?php echo $sz_ist_aktiv ? 'aria-current="true"' : ''; ?>>
                        <?php echo esc_html($sz_b->name); ?>
                        <span class="sz-bereiche__zahl"><?php echo esc_html((string) $sz_b->count); ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if ($sz_abteilungen) : ?>
            <div class="sz-abteilungen">
                <?php foreach ($sz_abteilungen as $sz_i => $sz_abt) : ?>
                    <a class="sz-abteilung" href="<?php echo esc_url(get_term_link($sz_abt)); ?>">
                        <span class="sz-abteilung__index"><?php echo esc_html(str_pad((string) ($sz_i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                        <?php
                        /*
                         * Dasselbe Symbol wie an den Chips im Katalog — hier
                         * ist die Stelle, an der man es zuerst sieht. Wer sich
                         * die Rolle einmal gemerkt hat, findet sie drinnen
                         * schneller als das Wort.
                         *
                         * Fest zusammengesetzt im Theme, keine Nutzereingabe.
                         */
                        if (function_exists('sz_kategorie_symbol_svg')) {
                            echo sz_kategorie_symbol_svg($sz_abt->name, 'sz-abteilung__symbol'); // phpcs:ignore WordPress.Security.EscapeOutput
                        }
                        ?>
                        <h3 class="sz-abteilung__name"><?php echo esc_html($sz_abt->name); ?></h3>
                        <p class="sz-abteilung__zahl">
                            <?php
                            printf(
                                /* translators: %d ist die Zahl der Artikel in einer Abteilung. */
                                esc_html(_n('%d Artikel', '%d Artikel', (int) $sz_abt->count, 'sapelza-shop')),
                                (int) $sz_abt->count
                            );
                            ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="sz-sortiment__leer">
                <?php echo esc_html__('Für diesen Bereich sind noch keine Abteilungen angelegt.', 'sapelza-shop'); ?>
            </p>
        <?php endif; ?>

    </div>
</section>
