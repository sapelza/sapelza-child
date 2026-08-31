<?php

/**
 * Die Kontouebersicht.
 *
 * WooCommerces eigene Vorlage begruesst mit "Hallo … In deiner
 * Konto-Uebersicht kannst du …" und zaehlt drei Links auf. Das steht
 * fest in der Vorlage, kein Haken davor und keiner dahinter.
 *
 * Unsere Uebersicht sagt dasselbe und mehr, in unserer Sprache. Also
 * bleibt hier nur der Haken, an dem sie haengt.
 *
 * @package sapelza-child
 */

defined('ABSPATH') || exit;

do_action('woocommerce_account_dashboard');
