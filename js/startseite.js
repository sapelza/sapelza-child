/*
 * Startseite — Bewegung.
 *
 * Kein React, keine Bibliothek. Alles hier ist scrollgekoppelt und
 * umkehrbar: vorwärts scrollen bewegt vorwärts, rückwärts rückwärts,
 * Stillstand hält an. Opacity allein wäre kein Bewegungssystem.
 *
 * Wer prefers-reduced-motion gesetzt hat, bekommt den Endzustand sofort
 * und keine Fahrt.
 */
(function () {
    'use strict';

    var sanft = window.matchMedia('(prefers-reduced-motion: reduce)');

    /* ---------------------------------------------------------------
       Kleine Helfer
       --------------------------------------------------------------- */

    function klemme(n) { return n < 0 ? 0 : n > 1 ? 1 : n; }

    /*
     * Fortschritt eines Elements durch das Fenster: 0 sobald seine
     * Oberkante unten hereinkommt, 1 wenn seine Unterkante oben
     * hinausgeht. Bewusst ohne Trägheit — der Scrollbalken ist die
     * Zeitachse, nicht eine Animation mit eigenem Willen.
     */
    function fortschritt(el) {
        var r = el.getBoundingClientRect();
        var h = window.innerHeight || 800;
        var gesamt = r.height + h;
        if (gesamt <= 0) return 0;
        return klemme((h - r.top) / gesamt);
    }

    /* ---------------------------------------------------------------
       Die Tour: der Porter fährt die Talachse ab
       --------------------------------------------------------------- */

    function tourAufsetzen() {
        var karte = document.querySelector('[data-sz-tour]');
        if (!karte) return;

        var porter = karte.querySelector('[data-sz-porter]');
        var strecke = karte.querySelector('[data-sz-befahren]');
        var zusagen = document.querySelectorAll('[data-sz-zusage]');
        if (!porter || !strecke) return;

        var laenge = strecke.getTotalLength();
        strecke.style.strokeDasharray = laenge;

        function zeichne() {
            var p = fortschritt(karte);

            /*
             * Die Runde geht vom Lager aus: Toblach liegt in der Mitte der
             * Achse. Erst nach Westen, dann die Kehre, dann nach Osten.
             * Deshalb zwei Abschnitte statt einer geraden Fahrt.
             */
            var t;      /* Position auf der Achse, 0 = West, 1 = Ost */
            var westwaerts;

            if (p < 0.42) {
                t = 0.5 - (p / 0.42) * 0.5;     /* Mitte -> West */
                westwaerts = true;
            } else if (p < 0.5) {
                t = 0;                           /* Kehre */
                westwaerts = false;
            } else {
                t = ((p - 0.5) / 0.5);           /* West -> Ost */
                westwaerts = false;
            }

            var punkt = strecke.getPointAtLength(t * laenge);

            /* Der Wagen sitzt auf der Straße, nicht darüber. */
            var kb = karte.getBoundingClientRect();
            var vb = 1000;
            var faktor = kb.width / vb;
            porter.style.transform =
                'translate(' + (punkt.x * faktor) + 'px, ' + (punkt.y * faktor) + 'px)' +
                ' translate(-50%, -78%)' +
                (westwaerts ? ' scaleX(-1)' : '');

            /* Befahrener Abschnitt: was hinter dem Wagen liegt. */
            strecke.style.strokeDashoffset = laenge * (1 - t);

            /* Die drei Zusagen erscheinen, während der Wagen fährt. */
            for (var i = 0; i < zusagen.length; i++) {
                var schwelle = 0.28 + i * 0.16;
                zusagen[i].classList.toggle('ist-da', p >= schwelle);
            }
        }

        if (sanft.matches) {
            /*
             * Endzustand ohne Fahrt. Der Porter wird NICHT versteckt: reduzierte
             * Bewegung heisst „nicht animieren“, nicht „weglassen“. Er parkt am
             * Lager in Toblach — das Bild bleibt vollstaendig, nur still.
             */
            strecke.style.strokeDashoffset = 0;
            var kb0 = karte.getBoundingClientRect();
            var lager = strecke.getPointAtLength(laenge * 0.5);
            porter.style.transform =
                'translate(' + (lager.x * (kb0.width / 1000)) + 'px, '
                + (lager.y * (kb0.width / 1000)) + 'px) translate(-50%, -78%)';
            for (var i = 0; i < zusagen.length; i++) zusagen[i].classList.add('ist-da');
            return;
        }

        var wartet = false;
        function anstossen() {
            if (wartet) return;
            wartet = true;
            window.requestAnimationFrame(function () { wartet = false; zeichne(); });
        }

        window.addEventListener('scroll', anstossen, { passive: true });
        window.addEventListener('resize', anstossen);
        zeichne();
    }

    /* ---------------------------------------------------------------
       Abschnitte, die beim Hereinscrollen auftauchen
       --------------------------------------------------------------- */

    function auftauchenAufsetzen() {
        var kandidaten = document.querySelectorAll(
            '.sz-kapitelmarke, .sz-zugang__raster > *, .sz-wege__kopf, .sz-wege__zelle,' +
            ' .sz-sortiment__kopf, .sz-abteilung, .sz-partner__punkt, .sz-marken__zelle'
        );
        if (!kandidaten.length) return;

        if (sanft.matches || !('IntersectionObserver' in window)) {
            for (var i = 0; i < kandidaten.length; i++) kandidaten[i].classList.add('ist-da');
            return;
        }

        for (var j = 0; j < kandidaten.length; j++) {
            kandidaten[j].classList.add('taucht-auf');
            /* Innerhalb einer Reihe versetzt, damit nicht alles zugleich
               kippt — dieselbe Staffelung wie in der Vorschau. */
            kandidaten[j].style.transitionDelay = ((j % 3) * 45) + 'ms';
        }

        var beobachter = new IntersectionObserver(function (eintraege) {
            eintraege.forEach(function (e) {
                if (!e.isIntersecting) return;
                e.target.classList.add('ist-da');
                beobachter.unobserve(e.target);
            });
        }, { rootMargin: '0px 0px -12% 0px', threshold: 0.12 });

        for (var k = 0; k < kandidaten.length; k++) beobachter.observe(kandidaten[k]);
    }

    /* ---------------------------------------------------------------
       Hero: das angefahrene Wort öffnet eine Mindmap
       --------------------------------------------------------------- */

    function mindmapAufsetzen() {
        var hero = document.querySelector('.sz-hero');
        if (!hero || sanft.matches) return;

        var woerter = hero.querySelectorAll('[data-sz-wort]');
        if (!woerter.length) return;

        var buehne = document.createElement('div');
        buehne.className = 'sz-mindmap';
        buehne.setAttribute('aria-hidden', 'true');
        hero.appendChild(buehne);

        var zweige = {
            bereiche: ['Küche', 'Reinigung', 'Hygiene', 'Hotelbedarf', 'Verbrauch', 'Wäsche'],
            marken: ['Vileda', 'Papernet', 'Sutter', 'Fasana', 'Deiss'],
            karte: ['Welsberg', 'Niederdorf', 'Toblach', 'Innichen', 'Sexten', 'Winnebach']
        };

        var offen = null;
        var schliessZeit = null;

        function zeichne(schluessel, quelle) {
            var hb = hero.getBoundingClientRect();
            var qb = quelle.getBoundingClientRect();
            var ox = qb.left - hb.left + qb.width / 2;
            var oy = qb.top - hb.top + qb.height / 2;

            var namen = zweige[schluessel] || [];
            var teile = ['<svg class="sz-mindmap__svg" viewBox="0 0 ' + Math.round(hb.width) + ' ' + Math.round(hb.height) + '">'];

            /*
             * Die Richtung wird nach freiem Raum gewählt, nicht nach einem
             * festen Winkelraster: die Überschrift ist breiter als jeder
             * sinnvolle Radius, ein genormtes Spinnennetz läge quer darüber.
             * Deshalb streuen die Äste nach unten und zur freien Seite.
             */
            var nachRechts = ox < hb.width * 0.55;
            for (var i = 0; i < namen.length; i++) {
                var t = (i + 1) / (namen.length + 1);
                var weite = (nachRechts ? 1 : -1) * (120 + t * 280);
                var hoehe = 60 + i * 44 + (i % 2 ? 18 : -8);
                var zx = ox + weite;
                var zy = oy + hoehe;
                var kx = ox + weite * 0.45;
                var ky = oy + hoehe * 0.35;

                teile.push('<path class="sz-mindmap__ast" style="--verzug:' + (i * 55) + 'ms" d="M' + ox + ' ' + oy + ' Q' + kx + ' ' + ky + ' ' + zx + ' ' + zy + '"/>');
                teile.push('<circle class="sz-mindmap__knoten" style="--verzug:' + (i * 55 + 120) + 'ms" cx="' + zx + '" cy="' + zy + '" r="3"/>');
                teile.push('<text class="sz-mindmap__wort" style="--verzug:' + (i * 55 + 180) + 'ms" x="' + (zx + (nachRechts ? 10 : -10)) + '" y="' + (zy + 4) + '" text-anchor="' + (nachRechts ? 'start' : 'end') + '">' + namen[i] + '</text>');
            }

            teile.push('</svg>');
            buehne.innerHTML = teile.join('');
            buehne.classList.add('ist-offen');
            hero.classList.add('ist-verschleiert');
        }

        function schliesse() {
            buehne.classList.remove('ist-offen');
            hero.classList.remove('ist-verschleiert');
            offen = null;
        }

        function baldSchliessen() {
            window.clearTimeout(schliessZeit);
            schliessZeit = window.setTimeout(schliesse, 450);
        }

        woerter.forEach(function (wort) {
            function oeffne() {
                window.clearTimeout(schliessZeit);
                var schluessel = wort.getAttribute('data-sz-wort');
                if (offen === schluessel) return;
                offen = schluessel;
                woerter.forEach(function (w) { w.setAttribute('aria-expanded', String(w === wort)); });
                zeichne(schluessel, wort);
            }

            wort.addEventListener('mouseenter', oeffne);
            wort.addEventListener('focus', oeffne);
            wort.addEventListener('mouseleave', baldSchliessen);
            wort.addEventListener('blur', baldSchliessen);
            wort.addEventListener('click', function (e) {
                e.preventDefault();
                if (offen === wort.getAttribute('data-sz-wort')) {
                    woerter.forEach(function (w) { w.setAttribute('aria-expanded', 'false'); });
                    schliesse();
                } else {
                    oeffne();
                }
            });
        });
    }

    /* --------------------------------------------------------------- */

    function los() {
        tourAufsetzen();
        auftauchenAufsetzen();
        mindmapAufsetzen();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', los);
    } else {
        los();
    }
})();
