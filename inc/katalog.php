<?php
/**
 * Lesende Auskünfte über den Katalog.
 *
 * Nur Abfragen, keine Entscheidungen — deshalb darf das im Theme liegen und
 * nicht im Plugin. Wer hier Geschäftsregeln unterbringt, hat die Grenze
 * falsch gezogen.
 *
 * Der Grund für diese Datei: die Entwurfsvorschau trägt feste Zahlen
 * („Zwölf Abteilungen", „110 Marken"). Die stimmten schon bei der
 * Erstinstallation nicht — es sind zehn Unterkategorien. Feste Zahlen auf
 * einer Seite, die jeder nachzählen kann, sind ein Versprechen, das bricht.
 */

if (!defined('ABSPATH')) exit;

/**
 * Die Bereiche: Oberkategorien des Katalogs (product_cat, Ebene 1).
 *
 * Heute sind das „Spülen, Reinigung & Hygiene" und „Hotel &
 * Betriebsausstattung". Kommt ein Handwerk-Sortiment als weitere
 * Oberkategorie dazu, erscheint es ohne Codeänderung.
 *
 * @return WP_Term[]
 */
function sz_bereiche(): array
{
    if (!taxonomy_exists('product_cat')) return [];

    $begriffe = get_terms([
        'taxonomy'   => 'product_cat',
        'parent'     => 0,
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ]);

    return is_wp_error($begriffe) ? [] : $begriffe;
}

/**
 * Die Abteilungen eines Bereichs (product_cat, Ebene 2).
 *
 * @return WP_Term[]
 */
function sz_abteilungen(int $bereich_id): array
{
    if (!taxonomy_exists('product_cat') || $bereich_id <= 0) return [];

    $begriffe = get_terms([
        'taxonomy'   => 'product_cat',
        'parent'     => $bereich_id,
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ]);

    return is_wp_error($begriffe) ? [] : $begriffe;
}

/**
 * Wie viele Abteilungen der Katalog insgesamt führt.
 *
 * Zählt alle Unterkategorien über alle Bereiche hinweg — das ist die Zahl,
 * die im Kopfsatz steht.
 */
function sz_abteilungen_gesamt(): int
{
    $summe = 0;
    foreach (sz_bereiche() as $bereich) {
        $summe += count(sz_abteilungen((int) $bereich->term_id));
    }
    return $summe;
}

/**
 * Wie viele Marken der Katalog führt, oder 0.
 *
 * WooCommerce bringt seit 9.6 die Taxonomie product_brand mit. Existiert
 * sie hier nicht oder ist sie leer, liefert das 0 — der Kopfsatz lässt die
 * Marken dann weg, statt eine erfundene Zahl zu behaupten.
 */
function sz_marken_anzahl(): int
{
    $taxonomie = sz_marken_taxonomie();
    if ($taxonomie === '') return 0;

    $anzahl = wp_count_terms(['taxonomy' => $taxonomie, 'hide_empty' => true]);
    return is_wp_error($anzahl) ? 0 : (int) $anzahl;
}

/**
 * Welche Markentaxonomie dieser Shop führt, oder ''.
 *
 * Drei Schreibweisen sind im Umlauf: WooCommerce ab 9.6 bringt
 * product_brand mit, ältere Installationen nutzen ein Attribut pa_marke,
 * das Plugin „Perfect Brands“ setzt pwb-brand. Geprüft wird in dieser
 * Reihenfolge; die erste gefüllte gewinnt.
 */
function sz_marken_taxonomie(): string
{
    foreach (['product_brand', 'pa_marke', 'pwb-brand'] as $taxonomie) {
        if (!taxonomy_exists($taxonomie)) continue;
        $anzahl = wp_count_terms(['taxonomy' => $taxonomie, 'hide_empty' => true]);
        if (!is_wp_error($anzahl) && (int) $anzahl > 0) return $taxonomie;
    }
    return '';
}

/**
 * Die meistgeführten Marken, höchstens $grenze Stück.
 *
 * @return WP_Term[] Leer, wenn dieser Shop keine Marken führt.
 */
function sz_marken_liste(int $grenze = 24): array
{
    $taxonomie = sz_marken_taxonomie();
    if ($taxonomie === '') return [];

    $begriffe = get_terms([
        'taxonomy'   => $taxonomie,
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => max(1, $grenze),
    ]);

    return is_wp_error($begriffe) ? [] : $begriffe;
}

/**
 * Zahlen als Wort, solange sie klein sind.
 *
 * „Zehn Abteilungen" liest sich wie ein Satz, „10 Abteilungen" wie eine
 * Tabelle. Ab dreizehn wird die Ziffer wieder ehrlicher.
 */
function sz_als_wort(int $n): string
{
    $woerter = [
        1 => 'Eine', 2 => 'Zwei', 3 => 'Drei', 4 => 'Vier', 5 => 'Fünf', 6 => 'Sechs',
        7 => 'Sieben', 8 => 'Acht', 9 => 'Neun', 10 => 'Zehn', 11 => 'Elf', 12 => 'Zwölf',
    ];
    return $woerter[$n] ?? (string) $n;
}
