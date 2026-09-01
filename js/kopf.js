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

    /*
     * Der Porter als Bildlaufanzeige.
     *
     * Er faehrt die gestrichelte Route am rechten Rand hinunter, im
     * Verhaeltnis zum Scrollfortschritt. Kein eigener Takt: der
     * Scrollbalken ist die Zeitachse, sonst laeuft er der Seite hinterher.
     */
    function laufAufsetzen() {
        var bahn = document.querySelector(".sz-lauf");
        var porter = document.querySelector("[data-sz-lauf]");
        if (!bahn || !porter) return;

        var wartet = false;
        var ruht = null;

        function setzen() {
            var hoehe = document.documentElement.scrollHeight - window.innerHeight;
            var y = window.scrollY || document.documentElement.scrollTop || 0;
            var p = hoehe > 0 ? y / hoehe : 0;
            if (p < 0) p = 0; else if (p > 1) p = 1;
            porter.style.top = (p * 100) + "%";
        }

        /*
         * Auf dem Telefon zeigt sich die Anzeige nur, waehrend gescrollt
         * wird — die Gestaltung blendet sie an ist-faehrt ein. Am
         * Schreibtisch aendert die Klasse nichts, dort steht sie ohnehin.
         *
         * Eine knappe Sekunde Nachlauf: kuerzer, und sie flackert
         * zwischen zwei Wischern; laenger, und sie steht im Weg.
         */
        function faehrt() {
            bahn.classList.add("ist-faehrt");
            if (ruht) window.clearTimeout(ruht);
            ruht = window.setTimeout(function () {
                bahn.classList.remove("ist-faehrt");
            }, 900);
        }

        window.addEventListener("scroll", function () {
            faehrt();
            if (wartet) return;
            wartet = true;
            window.requestAnimationFrame(function () { wartet = false; setzen(); });
        }, { passive: true });

        window.addEventListener("resize", setzen);
        setzen();
    }

    /*
     * Daneben tippen schliesst, Escape auch.
     *
     * Das Menue ist auf dem Telefon ein Blatt, das ueber der Seite liegt.
     * Wer daneben tippt, meint damit "weg" — vorher blieb es stehen und
     * verdeckte, wonach man greifen wollte.
     *
     * pointerdown zusaetzlich zu click: iOS Safari schickt keinen click
     * an document, wenn man auf eine Flaeche ohne eigenen Zweck tippt.
     * Denselben Fehler hatte die Sortierliste.
     */
    function danebenSchliessen(knopf, ziel, klasse) {
        if (!knopf || !ziel) return;

        function zu() {
            if (knopf.getAttribute("aria-expanded") !== "true") return false;
            knopf.setAttribute("aria-expanded", "false");
            ziel.classList.remove(klasse);
            return true;
        }

        ["pointerdown", "click"].forEach(function (art) {
            document.addEventListener(art, function (e) {
                if (ziel.contains(e.target) || knopf.contains(e.target)) return;
                zu();
            });
        });

        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" && zu()) knopf.focus();
        });

        /* Ein angetippter Menuepunkt fuehrt fort — das Blatt darf nicht
           offen zurueckbleiben, wenn die naechste Seite dieselbe ist. */
        ziel.addEventListener("click", function (e) {
            if (e.target.closest("a")) zu();
        });
    }

    function los() {
        schrumpfenAufsetzen();
        laufAufsetzen();
        danebenSchliessen(
            document.querySelector('.sz-kopf__auf'),
            document.getElementById('sz-nav'),
            'ist-offen'
        );

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
