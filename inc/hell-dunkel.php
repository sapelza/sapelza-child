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
    return '<button type="button" class="sz-modus" aria-label="Hell oder dunkel">'
         . '<span class="sz-modus-hell" aria-hidden="true">&#9790;</span>'
         . '<span class="sz-modus-dunkel" aria-hidden="true">&#9728;</span>'
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
