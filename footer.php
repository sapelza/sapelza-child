<?php
/**
 * Die Fußzeile.
 *
 * Schließt die drei Hüllen, die header.php geöffnet hat: .ast-container,
 * #content und #page. Wer hier eine davon vergisst, zerlegt das Layout
 * jeder Seite — und der Fehler zeigt sich erst weit unten.
 *
 * Die Anschrift steht bewusst als Text im Theme und nicht in einem Widget:
 * sie ändert sich alle paar Jahre, und ein leerer Widget-Bereich auf einer
 * frischen Installation wäre schlimmer als ein fester Text.
 */

if (!defined('ABSPATH')) exit;

$sz_shop  = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
$sz_konto = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');

?>
        </div><!-- .ast-container -->
    </div><!-- #content -->

    <footer class="sz-fuss" role="contentinfo">
        <div class="sz-fuss__innen">

            <div class="sz-fuss__marke">
                <?php
                // Fest zusammengesetzt in sz_logo().
                echo sz_logo(); // phpcs:ignore WordPress.Security.EscapeOutput
                ?>
                <p class="sz-fuss__anschrift">
                    <?php echo esc_html__('Kaufhaus Sapelza', 'sapelza-shop'); ?><br>
                    <?php echo esc_html__('Graf-Künigl-Straße 2', 'sapelza-shop'); ?><br>
                    <?php echo esc_html__('39034 Toblach (BZ)', 'sapelza-shop'); ?>
                </p>
            </div>

            <div class="sz-fuss__spalte">
                <h2 class="sz-fuss__titel mono"><?php echo esc_html__('Sortiment', 'sapelza-shop'); ?></h2>
                <ul class="sz-fuss__liste">
                    <?php
                    $sz_bereiche = function_exists('sz_bereiche') ? sz_bereiche() : [];
                    if ($sz_bereiche) {
                        foreach ($sz_bereiche as $sz_b) {
                            printf(
                                '<li><a href="%s">%s</a></li>',
                                esc_url(get_term_link($sz_b)),
                                esc_html($sz_b->name)
                            );
                        }
                    }
                    ?>
                    <li><a href="<?php echo esc_url($sz_shop); ?>"><?php echo esc_html__('Alle Artikel', 'sapelza-shop'); ?></a></li>
                </ul>
            </div>

            <div class="sz-fuss__spalte">
                <h2 class="sz-fuss__titel mono"><?php echo esc_html__('Service', 'sapelza-shop'); ?></h2>
                <?php if (has_nav_menu('sz-fuss')) : ?>
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'sz-fuss',
                        'container'      => false,
                        'menu_class'     => 'sz-fuss__liste',
                        'depth'          => 1,
                    ]);
                    ?>
                <?php else : ?>
                    <ul class="sz-fuss__liste">
                        <li><a href="<?php echo esc_url(home_url('/meine-artikel/')); ?>"><?php echo esc_html__('Meine Artikel', 'sapelza-shop'); ?></a></li>
                        <li><a href="<?php echo esc_url($sz_konto); ?>"><?php echo esc_html__('B2B-Konto', 'sapelza-shop'); ?></a></li>
                        <li><span class="sz-fuss__hinweis"><?php echo esc_html__('Zustellung nur im Hochpustertal', 'sapelza-shop'); ?></span></li>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="sz-fuss__spalte">
                <h2 class="sz-fuss__titel mono"><?php echo esc_html__('Kontakt', 'sapelza-shop'); ?></h2>
                <ul class="sz-fuss__liste">
                    <li><a href="tel:+390474972205">+39 0474 972205</a></li>
                    <li><a href="mailto:info@sapelza.it">info@sapelza.it</a></li>
                </ul>
            </div>

        </div>

        <div class="sz-fuss__zeile">
            <span class="mono">
                <?php
                printf(
                    /* translators: %d ist das laufende Jahr. */
                    esc_html__('© %d Kaufhaus Sapelza · Toblach', 'sapelza-shop'),
                    (int) date_i18n('Y')
                );
                ?>
            </span>
        </div>
    </footer>

</div><!-- #page -->

<?php
/*
 * Die Bildlaufanzeige: der Porter faehrt am rechten Rand eine gestrichelte
 * Route hinunter, waehrend man scrollt. Reine Zier, deshalb aria-hidden und
 * unter 900px ausgeblendet — auf schmalen Fenstern nimmt sie nur Platz weg.
 */
?>
<?php
/*
 * Das Wasserzeichen.
 *
 * Die Konturform des Logos, sehr blass, auf jeder Seite. Sie loest die
 * wechselnden Riesenziffern ab, die uneinheitlich wirkten. Bewusst das
 * echte Logo und kein Nachbau — entsaettigt und stark zurueckgenommen.
 */
?>
<div class="sz-wasserzeichen" aria-hidden="true">
    <svg viewBox="0 0 219 141" xmlns="http://www.w3.org/2000/svg">
        <path d="M109 11 L216 70.5 L109 130 L2 70.5 Z" fill="none" stroke="currentColor" stroke-width="3.5"/>
        <path d="M109 17 L206 70.5 L109 124 L12 70.5 Z" fill="none" stroke="currentColor" stroke-width="1.6"/>
        <path d="M24 50 H44 V53 H174 V50 H194 V96 H174 V93 H44 V96 H24 Z" fill="none" stroke="currentColor" stroke-width="3"/>
        <path d="M28 54 H48 V57 H170 V54 H190 V92 H170 V89 H48 V92 H28 Z" fill="none" stroke="currentColor" stroke-width="1.4"/>
        <text x="109" y="80" text-anchor="middle" fill="currentColor"
              font-family="Georgia, 'Times New Roman', serif" font-size="27" letter-spacing="2.4">SAPELZA</text>
    </svg>
</div>

<div class="sz-lauf" aria-hidden="true">
    <span class="sz-lauf__route"></span>
    <img class="sz-lauf__porter" data-sz-lauf
         src="<?php echo esc_url(get_stylesheet_directory_uri() . '/bilder/porter-oben.webp'); ?>"
         width="672" height="1080" alt="" decoding="async">
</div>

<?php wp_footer(); ?>
</body>
</html>
