<?php
/**
 * Die Kopfzeile.
 *
 * Ersetzt Astras Kopf vollständig. Die Hüllen #page, #content und
 * .ast-container bleiben bewusst erhalten: Astras WooCommerce-Vorlagen
 * und unsere eigenen Regeln hängen daran. Wer sie hier umbenennt, bricht
 * Kategorie, Warenkorb und Kasse auf einen Schlag.
 *
 * footer.php schließt dieselben drei Hüllen wieder.
 */

if (!defined('ABSPATH')) exit;

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="sz-sprung" href="#content"><?php echo esc_html__('Zum Inhalt springen', 'sapelza-shop'); ?></a>

<div id="page" class="hfeed site">

    <header class="sz-kopf" role="banner">
        <div class="sz-kopf__innen">

            <?php
            // Fest zusammengesetzt in sz_logo(), keine Nutzereingabe.
            echo sz_logo(); // phpcs:ignore WordPress.Security.EscapeOutput
            ?>

            <?php
            /*
             * Der Menueknopf.
             *
             * Auf schmalen Fenstern steht er nicht mehr in der Leiste,
             * sondern fest unten links — dort, wo der Daumen ohnehin
             * liegt. Oben rechts muss man auf einem Telefon umgreifen.
             *
             * Mit Wort, nicht nur mit drei Strichen: ein blosses Zeichen
             * an einer neuen Stelle liest sich als Zierrat. Das Wort
             * wechselt beim Oeffnen, damit klar ist, was der zweite
             * Druck tut — beide stehen im Text, die Gestaltung zeigt
             * jeweils eines.
             */
            ?>
            <button type="button" class="sz-kopf__auf" aria-expanded="false" aria-controls="sz-nav">
                <span class="sz-kopf__strich" aria-hidden="true"></span>
                <span class="sz-kopf__wort sz-kopf__wort--auf"><?php echo esc_html__('Menü', 'sapelza-shop'); ?></span>
                <span class="sz-kopf__wort sz-kopf__wort--zu"><?php echo esc_html__('Schließen', 'sapelza-shop'); ?></span>
            </button>

            <nav id="sz-nav" class="sz-nav" role="navigation"
                 aria-label="<?php echo esc_attr__('Hauptnavigation', 'sapelza-shop'); ?>">
                <?php
                wp_nav_menu([
                    'theme_location' => 'sz-haupt',
                    'container'      => false,
                    'menu_class'     => 'sz-nav__liste',
                    'fallback_cb'    => 'sz_kopf_menue_rueckfall',
                    'depth'          => 2,
                ]);
                ?>
            </nav>

            <div class="sz-werkzeuge">

                <button type="button" class="sz-werkzeug sz-suche__auf" aria-expanded="false"
                        aria-controls="sz-suchfeld"
                        aria-label="<?php echo esc_attr__('Suchen', 'sapelza-shop'); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                </button>

                <a class="sz-werkzeug" href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/')); ?>"
                   aria-label="<?php echo esc_attr__('Konto', 'sapelza-shop'); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a8 8 0 0 1 16 0v1"/></svg>
                </a>

                <?php $sz_anzahl = sz_warenkorb_anzahl(); ?>
                <?php if ($sz_anzahl >= 0) : ?>
                    <a class="sz-werkzeug sz-warenkorb" href="<?php echo esc_url(wc_get_cart_url()); ?>"
                       aria-label="<?php echo esc_attr__('Warenkorb', 'sapelza-shop'); ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.4 11h9.6l2-8H6"/><circle cx="9" cy="19" r="1.4"/><circle cx="17" cy="19" r="1.4"/></svg>
                        <?php if ($sz_anzahl > 0) : ?>
                            <span class="sz-warenkorb__zahl"><?php echo esc_html((string) $sz_anzahl); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <?php
                if (function_exists('sz_modus_knopf')) {
                    // Fest zusammengesetzte Auszeichnung aus dem Theme.
                    echo sz_modus_knopf(); // phpcs:ignore WordPress.Security.EscapeOutput
                }
                ?>
            </div>
        </div>

        <?php
        /*
         * Das Suchfeld liegt unter der Leiste und wird eingeblendet. Es geht
         * an WooCommerce, nicht an die WordPress-Suche: post_type=product
         * hält Beiträge und Seiten aus den Treffern heraus.
         */
        ?>
        <form id="sz-suchfeld" class="sz-suchfeld" role="search" method="get"
              action="<?php echo esc_url(home_url('/')); ?>" hidden>
            <input type="hidden" name="post_type" value="product">
            <label class="screen-reader-text" for="sz-suchfeld-eingabe">
                <?php echo esc_html__('Artikel, Marke oder Artikelnummer', 'sapelza-shop'); ?>
            </label>
            <input type="search" id="sz-suchfeld-eingabe" name="s"
                   placeholder="<?php echo esc_attr__('Artikel, Marke oder Artikelnummer …', 'sapelza-shop'); ?>"
                   value="<?php echo esc_attr(get_search_query()); ?>">
            <button type="submit"><?php echo esc_html__('Suchen', 'sapelza-shop'); ?></button>
        </form>
    </header>

    <div id="content" class="site-content">
        <div class="ast-container">
