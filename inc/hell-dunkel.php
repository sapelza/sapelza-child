<?php
/**
 * Hell und dunkel.
 *
 * Hell ist der Standard — anders als auf sapelza.it. Die Wahl hängt an
 * [data-sz="dark"] auf <html> und in einem Cookie. Das Setzen passiert im
 * <head>, noch bevor der erste Pixel gezeichnet wird; sonst blitzt beim
 * Laden kurz die helle Seite auf, bevor sie dunkel wird.
 */

if (!defined('ABSPATH')) exit;

/**
 * Ganz früh im Kopf: Cookie lesen, Attribut setzen. Ohne Abhängigkeiten,
 * ohne Warten auf ein Skript am Seitenende.
 */
add_action('wp_head', function () {
    ?>
<script>
(function () {
    try {
        var m = document.cookie.match(/(?:^|; )sapelza_theme=([^;]+)/);
        var wahl = m ? decodeURIComponent(m[1]) : null;
        if (wahl !== "dark" && wahl !== "light") {
            wahl = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        document.documentElement.setAttribute("data-sz", wahl);
    } catch (e) { document.documentElement.setAttribute("data-sz", "light"); }
})();
</script>
    <?php
}, 1);

/**
 * Das Umschalten selbst — einmal für die ganze Seite, im Fuß.
 *
 * Bewusst über einen Klick auf dem Dokument statt über onclick am Knopf:
 * so wirkt es für jeden Knopf, egal wie viele es gibt und egal, wann sie
 * in die Seite kommen. Astra rendert die Kopfzeile zweimal — einmal für
 * den Bildschirm, einmal fürs Mobilgerät —, und beide sollen schalten.
 */
add_action('wp_footer', function () {
    ?>
<script>
document.addEventListener("click", function (e) {
    if (!e.target.closest(".sz-modus")) return;
    var w = document.documentElement.getAttribute("data-sz") === "dark" ? "light" : "dark";
    document.documentElement.setAttribute("data-sz", w);
    document.cookie = "sapelza_theme=" + w + "; path=/; max-age=" + (60 * 60 * 24 * 365) + "; SameSite=Lax";
});
</script>
    <?php
});

/**
 * Der Knopf als Bauteil — einmal geschrieben, dreifach verwendet.
 */
function sz_modus_knopf(): string
{
    /*
     * Zeichen statt Schriftzeichen: ☾ und ☀ werden je nach Schriftart
     * unterschiedlich gross und unterschiedlich hoch gesetzt — neben den
     * gezeichneten Symbolen daneben fiel das sofort auf. Als SVG haben
     * alle dieselbe Groesse und dieselbe Strichstaerke.
     */
    $gemein = 'width="14" height="14" viewBox="0 0 24 24" fill="none" '
            . 'stroke="currentColor" stroke-width="1.8" stroke-linecap="round" '
            . 'stroke-linejoin="round" aria-hidden="true" focusable="false"';

    $mond  = '<svg ' . $gemein . '><path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a7 7 0 1 0 10.5 10.5Z"/></svg>';
    $sonne = '<svg ' . $gemein . '><circle cx="12" cy="12" r="3.6"/>'
           . '<path d="M12 3.2v1.6M12 19.2v1.6M3.2 12h1.6M19.2 12h1.6'
           . 'M5.9 5.9 7 7M17 17l1.1 1.1M18.1 5.9 17 7M7 17l-1.1 1.1"/></svg>';

    return '<button type="button" class="sz-modus" aria-label="Hell oder dunkel">'
         . '<span class="sz-modus-hell" aria-hidden="true">' . $mond . '</span>'
         . '<span class="sz-modus-dunkel" aria-hidden="true">' . $sonne . '</span>'
         . '</button>';
}

/**
 * Als Kurzcode, wenn er von Hand gesetzt werden soll — etwa über ein
 * HTML-Element im Header-Builder von Astra.
 */
add_shortcode('sz_modus', fn() => sz_modus_knopf());

/*
 * Frueher haengte sich der Knopf selbst ans Menue — ueber wp_nav_menu_items
 * und wp_list_pages. Das war eine Kruecke, solange Astra die Kopfzeile
 * besass und wir keinen Platz dafuer hatten.
 *
 * Seit das Theme eine eigene header.php mitbringt, setzt sie ihn dort
 * bewusst neben Suche, Konto und Warenkorb. Die beiden Filter sind
 * deshalb entfallen: sonst stuende der Knopf zweimal da.
 */
