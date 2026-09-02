<?php
/**
 * Template Name: SAPELZA Startseite
 *
 * Der Rahmen der Startseite. Die Abschnitte liegen je in einer eigenen
 * Datei unter teile/ — sonst wächst diese hier zu einer Datei, die niemand
 * mehr überblickt, und jeder Abschnitt lässt sich einzeln prüfen.
 *
 * Die Reihenfolge ist die Nummerierung der Kapitelmarken. „app" steht am
 * Ende, weil es das einzige ist, was nichts über das Haus erzählt,
 * sondern etwas vom Leser will.
 *
 * Astras eigenen Seitentitel und Container lassen wir bewusst weg: die
 * Abschnitte bringen ihre volle Breite selbst mit.
 */

if (!defined('ABSPATH')) exit;

get_header();

?>
<main id="primary" class="sz-start" role="main">
    <?php
    foreach (['hero', 'zugang', 'bestellwege', 'sortiment', 'tour', 'partner', 'marken', 'app'] as $teil) {
        $pfad = get_stylesheet_directory() . '/teile/' . $teil . '.php';
        if (file_exists($pfad)) include $pfad;
    }
    ?>
</main>
<?php

get_footer();
