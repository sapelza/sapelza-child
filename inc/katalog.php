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

/**
 * Die Listen fuer den Hero: Abteilungen und Marken mit Zahl und Weg.
 *
 * Bis 1.24.0 standen im Skript erfundene Namen. Hier kommen die echten
 * her — und was der Shop nicht fuehrt, bleibt leer statt behauptet.
 *
 * @return array{bereiche:array,marken:array}
 */
function sz_hero_listen(): array
{
    $abteilungen = [];

    foreach (sz_bereiche() as $bereich) {
        foreach (sz_abteilungen((int) $bereich->term_id) as $a) {
            $abteilungen[] = [
                'name' => $a->name,
                'zahl' => (int) $a->count,
                'weg'  => get_term_link($a),
            ];
        }
    }

    usort($abteilungen, static fn($x, $y) => $y['zahl'] <=> $x['zahl']);

    $marken = [];
    foreach (sz_marken_liste(8) as $m) {
        $marken[] = [
            'name' => $m->name,
            'zahl' => (int) $m->count,
            'weg'  => get_term_link($m),
        ];
    }

    return [
        'bereiche' => array_slice($abteilungen, 0, 8),
        'marken'   => $marken,
    ];
}

/**
 * Die Marken, die in einer Kategorie tatsächlich vorkommen.
 *
 * sz_marken_liste() nennt die Marken des ganzen Hauses. Steht man in
 * "Reinigungsmittel", hilft das wenig — dort will man die Marken sehen,
 * die es in Reinigungsmitteln gibt, mit den Zahlen dieser Kategorie.
 *
 * Ein Durchgang über die Artikel der Kategorie, dann gezählt. Bei
 * einigen hundert Artikeln ist das eine Abfrage; das Ergebnis liegt
 * eine Stunde im Zwischenspeicher.
 *
 * @return array<int, array{begriff: WP_Term, anzahl: int}>
 */
function sz_marken_in(int $kategorie): array
{
    $taxonomie = sz_marken_taxonomie();
    if ($taxonomie === '' || $kategorie <= 0) return [];

    $schluessel = 'sz_marken_' . $kategorie;
    $fertig = get_transient($schluessel);
    if (is_array($fertig)) return $fertig;

    $artikel = get_posts([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 600,
        'fields'         => 'ids',
        'tax_query'      => [[
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => $kategorie,
            'include_children' => true,
        ]],
    ]);

    if (!$artikel) return [];

    $begriffe = wp_get_object_terms($artikel, $taxonomie, ['fields' => 'all_with_object_id']);
    if (is_wp_error($begriffe) || !$begriffe) return [];

    $gezaehlt = [];
    foreach ($begriffe as $b) {
        if (!isset($gezaehlt[$b->term_id])) {
            $gezaehlt[$b->term_id] = ['begriff' => $b, 'anzahl' => 0];
        }
        $gezaehlt[$b->term_id]['anzahl']++;
    }

    usort($gezaehlt, static fn($x, $y) => $y['anzahl'] <=> $x['anzahl']);
    $gezaehlt = array_slice($gezaehlt, 0, 12);

    set_transient($schluessel, $gezaehlt, HOUR_IN_SECONDS);

    return $gezaehlt;
}

/**
 * Ein Symbol fuer eine Warengruppe.
 *
 * Gezeichnet, nicht aus einer Schrift: eine Symbolschrift waere eine
 * Anfrage mehr und laedt entweder zu spaet oder gar nicht. Umriss in der
 * Farbe der Zeile, damit es im dunklen Thema mitgeht.
 *
 * Die Zuordnung geht ueber Stichwoerter im Namen, nicht ueber die ID:
 * eine neue Kategorie "Reinigungstuecher" bekommt so von selbst das
 * Tuch. Was zu nichts passt, bekommt das Etikett — das ist ehrlicher
 * als ein Symbol, das etwas anderes behauptet.
 *
 * @param string $name Der Name der Kategorie.
 * @return string Die Pfade des Symbols, ohne umgebendes svg.
 */
function sz_kategorie_symbol(string $name): string
{
    $n = function_exists('mb_strtolower') ? mb_strtolower($name) : strtolower($name);

    $zeichen = [
        /* Papierrolle: Koerper, Stirnseite, Kern. */
        'papier' => '<path d="M6.5 4.5h9a2.5 2.5 0 0 1 2.5 2.5v13H6.5z"/>'
                  . '<ellipse cx="6.5" cy="12.5" rx="2.8" ry="8"/><circle cx="6.5" cy="12.5" r="1.3"/>',

        /* Spruehflasche. */
        'reinigungsmittel' => '<path d="M9.5 9.5h4.6a1.8 1.8 0 0 1 1.8 1.8v7.4a1.8 1.8 0 0 1-1.8 1.8H9.5a1.8 1.8 0 0 1-1.8-1.8v-7.4a1.8 1.8 0 0 1 1.8-1.8z"/>'
                            . '<path d="M10.4 9.5V6.2h3v3.3M13.4 6.2h3.4l1.4 1.8"/><path d="M19.5 6.6h1.6M19.2 9h1.6M19.9 4.4l1.3-.7"/>',

        /* Spender mit Pumpe. */
        'kosmetik' => '<path d="M9.5 9.6h5v9.6a1.4 1.4 0 0 1-1.4 1.4h-2.2a1.4 1.4 0 0 1-1.4-1.4z"/>'
                    . '<path d="M10.8 9.6V6.4h2.4v3.2M13.2 6.4h3.2v2.2"/>',

        /* Tuch mit Wellenkante. Der Mittelstrich der ersten Fassung
           machte daraus ein aufgeschlagenes Buch — jetzt eine zweite
           Welle quer, das liest sich als Stoff. */
        'textil' => '<path d="M4.4 7c2.5-2 5 1.6 7.6 0s5.1-2 7.6 0v9.4c-2.5 2-5-1.6-7.6 0s-5.1 2-7.6 0z"/>'
                  . '<path d="M4.4 10.8c2.5-2 5 1.6 7.6 0s5.1-2 7.6 0" stroke-width="0.9" opacity="0.65"/>',

        /* Teller. */
        'geschirr' => '<circle cx="12" cy="12" r="8.2"/><circle cx="12" cy="12" r="4.2"/>',

        /* Schild mit Tropfen. */
        'desinfekt' => '<path d="M12 3.2l7 2.8v5.6c0 4.2-2.9 7.2-7 9.2-4.1-2-7-5-7-9.2V6z"/>'
                     . '<path d="M12 9.4c1.6 1.9 2.4 3.1 2.4 4a2.4 2.4 0 0 1-4.8 0c0-.9.8-2.1 2.4-4z"/>',

        /* Tonne. */
        'abfall' => '<path d="M4.6 7h14.8M9.4 7V4.8h5.2V7M6.4 7l1 13.2h9.2L17.6 7"/><path d="M10.2 10.6v6.4M13.8 10.6v6.4"/>',

        /* Hand im Handschuh. */
        'handschuh' => '<path d="M7.4 20.8v-7.6c0-1.1 1-1.7 1.8-1.1l1 .8V5.4a1.5 1.5 0 0 1 3 0v4.4"/>'
                     . '<path d="M13.2 9.8V4.6a1.5 1.5 0 0 1 3 0v5.2"/>'
                     . '<path d="M16.2 9.8V6.8a1.5 1.5 0 0 1 3 0v7.4c0 3.8-2.4 6.6-5.4 6.6H7.4"/>',

        /* Waschmaschine. */
        'wäsche' => '<rect x="4.2" y="3.2" width="15.6" height="17.6" rx="2"/>'
                  . '<circle cx="12" cy="14" r="4.4"/><circle cx="12" cy="14" r="1.6"/>'
                  . '<path d="M7.4 6.6h.02M10 6.6h.02"/>',

        /* Kaefer. */
        'schädling' => '<ellipse cx="12" cy="13.4" rx="4.4" ry="6"/><path d="M12 7.4V5.6"/>'
                     . '<path d="M9.8 5.4 8.2 3.6M14.2 5.4 15.8 3.6"/>'
                     . '<path d="M7.6 10.4 4.2 8.6M7.6 13.4H4M7.6 16.4 4.2 18.2M16.4 10.4 19.8 8.6M16.4 13.4H20M16.4 16.4 19.8 18.2"/>',

        /* Bett. */
        'hotel' => '<path d="M3 20V8M3 13.6h18V20M21 20v-6.4"/>'
                 . '<path d="M6.6 13.6v-2.2a2 2 0 0 1 2-2h3.2a2 2 0 0 1 2 2v2.2"/>',

        /* Haus mit Fenstern. */
        'betrieb' => '<path d="M4.4 20.6V6.8L12 3.4l7.6 3.4v13.8"/><path d="M9.4 20.6v-5.4h5.2v5.4"/>'
                   . '<path d="M8.4 10.4h.02M12 10.4h.02M15.6 10.4h.02"/>',

        /* Topf mit Dampf. */
        'küche' => '<path d="M4 9.8h16v5.4a4.4 4.4 0 0 1-4.4 4.4H8.4A4.4 4.4 0 0 1 4 15.2z"/>'
                 . '<path d="M4 11.8H2.4M20 11.8h1.6"/><path d="M9 7.4V5.2M12 7.4V4.2M15 7.4V5.2"/>',

        /* Karton. */
        'verpackung' => '<path d="M3.4 8 12 3.8 20.6 8v8.6L12 20.8 3.4 16.6z"/><path d="M3.4 8 12 12.2 20.6 8M12 12.2v8.6"/>',
    ];

    /* Die Reihenfolge zaehlt: "Reinigungstextilien" traegt beide
       Stichwoerter und soll das Tuch bekommen, nicht die Flasche. */
    $wege = [
        'textil'           => 'textil',
        'tuch'             => 'textil',
        'wisch'            => 'textil',
        'papier'           => 'papier',
        'reinigungsmittel' => 'reinigungsmittel',
        'reiniger'         => 'reinigungsmittel',
        'kosmetik'         => 'kosmetik',
        'gäste'            => 'kosmetik',
        'seife'            => 'kosmetik',
        'geschirr'         => 'geschirr',
        'spülmaschine'     => 'geschirr',
        'spülen'           => 'geschirr',
        'desinfekt'        => 'desinfekt',
        'hygiene'          => 'desinfekt',
        'abfall'           => 'abfall',
        'beutel'           => 'abfall',
        'müll'             => 'abfall',
        'sack'             => 'abfall',
        'handschuh'        => 'handschuh',
        'schutz'           => 'handschuh',
        'wäsche'           => 'wäsche',
        'schädling'        => 'schädling',
        'hotel'            => 'hotel',
        'zimmer'           => 'hotel',
        'bett'             => 'hotel',
        'betrieb'          => 'betrieb',
        'ausstattung'      => 'betrieb',
        'küche'            => 'küche',
        'koch'             => 'küche',
        'gastro'           => 'küche',
        'verpackung'       => 'verpackung',
        'karton'           => 'verpackung',
    ];

    foreach ($wege as $wort => $schluessel) {
        if (mb_strpos($n, $wort) !== false) return $zeichen[$schluessel];
    }

    /* Etikett: sagt "eine Warengruppe" und behauptet nichts weiter. */
    return '<path d="M3.4 12.4V3.8h8.6l8.6 8.6-8.6 8.6z"/><circle cx="7.6" cy="7.8" r="1.4"/>';
}

/**
 * Das ganze Symbol, fertig zum Ausgeben.
 *
 * Bewusst ohne wp_kses_post: das raeumt Attribute in Kleinschreibung um
 * und macht aus viewBox ein viewbox, das kein Browser liest. Die
 * Auszeichnung stammt hier vollstaendig aus dem Theme.
 */
function sz_kategorie_symbol_svg(string $name, string $klasse = 'sz-chip__symbol'): string
{
    return '<svg class="' . esc_attr($klasse) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
         . ' stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
         . sz_kategorie_symbol($name) . '</svg>';
}
