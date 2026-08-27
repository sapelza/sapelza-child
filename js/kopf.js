/*
 * Kopfzeile — die zwei Schalter.
 *
 * Astras Kopf brachte sein eigenes Menü-Skript mit. Da wir den Kopf
 * ersetzt haben, ist auch das weg; diese Datei tritt an seine Stelle.
 * Bewusst klein: aufklappen, zuklappen, Suchfeld zeigen.
 */
(function () {
    'use strict';

    function umschalten(knopf, ziel, klasse) {
        if (!knopf || !ziel) return;
        knopf.addEventListener('click', function () {
            var offen = knopf.getAttribute('aria-expanded') === 'true';
            knopf.setAttribute('aria-expanded', String(!offen));

            if (klasse) {
                ziel.classList.toggle(klasse, !offen);
            } else {
                /* hidden statt display:none, damit auch Vorlesegeräte
                   das Feld erst dann finden, wenn es geöffnet ist. */
                if (offen) ziel.setAttribute('hidden', '');
                else ziel.removeAttribute('hidden');
            }

            if (!offen) {
                var feld = ziel.querySelector('input[type="search"]');
                if (feld) feld.focus();
            }
        });
    }

    /*
     * Der Kopf wird beim Scrollen flacher.
     *
     * Die Schwelle liegt bei 40px und hat eine Hysterese: erst ab 40 klein,
     * erst unter 20 wieder gross. Ohne den Abstand flackert die Leiste,
     * sobald jemand genau auf der Schwelle stehen bleibt.
     */
    function schrumpfenAufsetzen() {
        var kopf = document.querySelector(".sz-kopf");
        if (!kopf) return;

        var klein = false;
        var wartet = false;

        function pruefen() {
            var y = window.scrollY || document.documentElement.scrollTop || 0;
            if (!klein && y > 40) { klein = true; kopf.classList.add("ist-klein"); }
            else if (klein && y < 20) { klein = false; kopf.classList.remove("ist-klein"); }
        }

        window.addEventListener("scroll", function () {
            if (wartet) return;
            wartet = true;
            window.requestAnimationFrame(function () { wartet = false; pruefen(); });
        }, { passive: true });

        pruefen();
    }

    function los() {
        schrumpfenAufsetzen();
        umschalten(
            document.querySelector('.sz-kopf__auf'),
            document.getElementById('sz-nav'),
            'ist-offen'
        );

        umschalten(
            document.querySelector('.sz-suche__auf'),
            document.getElementById('sz-suchfeld'),
            null
        );

        /* Escape schließt beides — sonst sitzt man auf schmalen Fenstern
           im aufgeklappten Menü fest. */
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            var nav = document.getElementById('sz-nav');
            var suche = document.getElementById('sz-suchfeld');
            var navKnopf = document.querySelector('.sz-kopf__auf');
            var sucheKnopf = document.querySelector('.sz-suche__auf');

            if (nav && nav.classList.contains('ist-offen')) {
                nav.classList.remove('ist-offen');
                if (navKnopf) { navKnopf.setAttribute('aria-expanded', 'false'); navKnopf.focus(); }
            }
            if (suche && !suche.hasAttribute('hidden')) {
                suche.setAttribute('hidden', '');
                if (sucheKnopf) { sucheKnopf.setAttribute('aria-expanded', 'false'); sucheKnopf.focus(); }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', los);
    } else {
        los();
    }
})();
