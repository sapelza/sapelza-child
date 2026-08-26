<?php
/**
 * 06 Marken — nur wenn es Marken gibt.
 *
 * Der Entwurf trägt fest „110 Marken im Haus". Ob dieser Shop überhaupt
 * eine Markentaxonomie führt, ist damit nicht gesagt. Findet sz_marken_*()
 * keine, entfällt der Abschnitt vollständig — eine Überschrift über einer
 * leeren Fläche ist schlechter als kein Abschnitt.
 */

if (!defined('ABSPATH')) exit;

$sz_marken = sz_marken_liste(24);

if (!$sz_marken) return;

$sz_gesamt = sz_marken_anzahl();

?>
<section class="sz-kapitel sz-marken" aria-labelledby="sz-marken-titel">
    <div class="wrap" style="position: relative; z-index: 1;">

        <p class="sz-kapitelmarke">
            <span class="sz-kapitelmarke__nr mono">06</span>
            <span class="hairline" aria-hidden="true"></span>
            <span class="sz-kapitelmarke__kicker mono"><?php echo esc_html__('Marken', 'sapelza-shop'); ?></span>
        </p>

        <h2 id="sz-marken-titel" class="sz-marken__titel">
            <?php
            printf(
                /* translators: %d ist die Zahl der geführten Marken. */
                esc_html(_n('%d Marke im Haus', '%d Marken im Haus', $sz_gesamt, 'sapelza-shop')),
                (int) $sz_gesamt
            );
            ?>
        </h2>

        <ul class="sz-marken__raster">
            <?php foreach ($sz_marken as $sz_m) : ?>
                <li class="sz-marken__zelle">
                    <a class="sz-marke" href="<?php echo esc_url(get_term_link($sz_m)); ?>">
                        <span class="sz-marke__name"><?php echo esc_html($sz_m->name); ?></span>
                        <span class="sz-marke__zahl mono"><?php echo esc_html((string) $sz_m->count); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>
</section>
