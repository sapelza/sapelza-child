<?php
/**
 * 01 Zugang — Anmeldung links, Weg zur Freischaltung rechts.
 *
 * Das Anmeldeformular baut WordPress selbst (wp_login_form). Ein selbst
 * geschriebenes Formular müsste Nonce, Weiterleitung und Fehlerfälle
 * nachbilden — daran scheitert man leise und mit Sicherheitsfolgen.
 * Gestaltet wird deshalb nur die Hülle.
 */

if (!defined('ABSPATH')) exit;

$sz_konto = function_exists('wc_get_page_permalink')
    ? wc_get_page_permalink('myaccount')
    : home_url('/my-account/');

$sz_schritte = [
    [
        't' => __('Betrieb melden', 'sapelza-shop'),
        'b' => __('Firmenname, MwSt.-Nummer und die Lieferadresse im Hochpustertal.', 'sapelza-shop'),
    ],
    [
        't' => __('Wir prüfen', 'sapelza-shop'),
        'b' => __('Abgleich mit unserer Kundenkartei, in der Regel innerhalb eines Werktags.', 'sapelza-shop'),
    ],
    [
        't' => __('Freischaltung', 'sapelza-shop'),
        'b' => __('Ihre Nettopreise, die Staffeln und der Kauf auf Rechnung sind hinterlegt.', 'sapelza-shop'),
    ],
];

?>
<section class="sz-kapitel sz-zugang" aria-labelledby="sz-zugang-titel">
    <span class="geisterziffer" aria-hidden="true" style="left: -2vw; top: -4vh;">01</span>

    <div class="wrap" style="position: relative; z-index: 1;">

        <p class="sz-kapitelmarke">
            <span class="sz-kapitelmarke__nr mono">01</span>
            <span class="hairline" aria-hidden="true"></span>
            <span class="sz-kapitelmarke__kicker mono"><?php echo esc_html__('Zugang', 'sapelza-shop'); ?></span>
        </p>

        <div class="sz-zugang__raster">

            <div>
                <h2 id="sz-zugang-titel" class="sz-zugang__titel">
                    <?php
                    echo is_user_logged_in()
                        ? esc_html__('Willkommen zurück', 'sapelza-shop')
                        : esc_html__('Anmelden', 'sapelza-shop');
                    ?>
                </h2>
                <p class="sz-zugang__lead">
                    <?php echo esc_html__('Ihre Preise, Ihr Bestellverlauf, Ihre Adressen.', 'sapelza-shop'); ?>
                </p>

                <div class="sz-zugang__karte">
                    <span class="sz-zugang__kante" aria-hidden="true"></span>

                    <?php if (is_user_logged_in()) : ?>
                        <p class="sz-zugang__angemeldet">
                            <?php
                            $sz_nutzer = wp_get_current_user();
                            printf(
                                /* translators: %s ist der Anzeigename des angemeldeten Betriebs. */
                                esc_html__('Angemeldet als %s.', 'sapelza-shop'),
                                '<strong>' . esc_html($sz_nutzer->display_name) . '</strong>'
                            );
                            ?>
                        </p>
                        <a class="sz-knopf sz-knopf--voll" href="<?php echo esc_url($sz_konto); ?>">
                            <?php echo esc_html__('Zum B2B-Konto', 'sapelza-shop'); ?>
                        </a>
                    <?php else : ?>
                        <?php
                        wp_login_form([
                            'redirect'       => $sz_konto,
                            'label_username' => __('E-Mail', 'sapelza-shop'),
                            'label_password' => __('Passwort', 'sapelza-shop'),
                            'label_log_in'   => __('Anmelden', 'sapelza-shop'),
                            'remember'       => true,
                            'label_remember' => __('Angemeldet bleiben', 'sapelza-shop'),
                        ]);
                        ?>
                        <div class="sz-zugang__fuss">
                            <a href="<?php echo esc_url(wp_lostpassword_url($sz_konto)); ?>">
                                <?php echo esc_html__('Passwort vergessen', 'sapelza-shop'); ?>
                            </a>
                            <span class="mono sz-zugang__sicher">
                                <?php echo esc_html__('Verschlüsselte Verbindung', 'sapelza-shop'); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php /* Rechts kein zweites Formular, sondern der Weg zur Freischaltung. */ ?>
            <div>
                <p class="kicker"><?php echo esc_html__('Noch kein Konto?', 'sapelza-shop'); ?></p>
                <p class="sz-zugang__neulead">
                    <?php echo esc_html__('Betriebe erhalten nach der Freischaltung ihre Nettopreise, Staffelpreise und den Kauf auf Rechnung. Wir brauchen dafür nur Ihre MwSt.-Nummer.', 'sapelza-shop'); ?>
                </p>

                <ol class="sz-schritte">
                    <?php foreach ($sz_schritte as $sz_i => $sz_s) : ?>
                        <li class="sz-schritt">
                            <span class="sz-schritt__nr mono"><?php echo esc_html(str_pad((string) ($sz_i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                            <div>
                                <h3 class="sz-schritt__titel display"><?php echo esc_html($sz_s['t']); ?></h3>
                                <p class="sz-schritt__text"><?php echo esc_html($sz_s['b']); ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>

                <div class="sz-zugang__abschluss">
                    <a class="sz-knopf sz-knopf--umriss" href="<?php echo esc_url($sz_konto); ?>">
                        <?php echo esc_html__('Betrieb registrieren', 'sapelza-shop'); ?>
                    </a>
                    <p class="sz-zugang__fein">
                        <?php echo esc_html__('Freischaltung in der Regel innerhalb eines Werktags. Mehrere Personen im Betrieb? Jede bekommt einen eigenen Zugang.', 'sapelza-shop'); ?>
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>
