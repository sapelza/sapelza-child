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
            /*
             * Eine Runde, keine Durchfahrt.
             *
             * Vorher fuhr er vom Lager nach Westen und dann in einem Zug
             * bis Winnebach — auf dem Schirm las sich das als eine einzige
             * Fahrt nach rechts, und am Ende stand der Wagen irgendwo im
             * Osten herum.
             *
             * So faehrt niemand aus. Eine Runde geht vom Lager weg und
             * kommt zum Lager zurueck: erst das westliche Tal, dann quer
             * hinueber ans oestliche Ende, dann heim. Zwei Kehren, und am
             * Schluss steht er wieder in Toblach.
             */
            var ORTE = {
                lager: 0.5,
                west:  0.04,
                ost:   0.96
            };

            /*
             * Die Abschnitte der Runde.
             *
             * Die Anteile stehen im Verhaeltnis der Wegstrecken, sonst
             * raste er quer durchs Tal und schliche die Zubringer: das
             * Querstueck ist doppelt so lang wie jeder Zubringer, also
             * bekommt es auch doppelt so viel Weg.
             *
             * Der letzte Abschnitt endet auf 1.0, nicht darueber — sonst
             * kaeme der Wagen am Ende nicht ganz heim.
             */
            var FAHRT = [
                { bis: 0.15, von: ORTE.lager, nach: ORTE.lager, blick: 0 },  /* steht noch */
                { bis: 0.33, von: ORTE.lager, nach: ORTE.west,  blick: 1 },  /* westwaerts */
                { bis: 0.39, von: ORTE.west,  nach: ORTE.west,  blick: 0 },  /* Kehre, schaut schon nach Osten */
                { bis: 0.76, von: ORTE.west,  nach: ORTE.ost,   blick: 0 },  /* quer nach Ost */
                { bis: 0.82, von: ORTE.ost,   nach: ORTE.ost,   blick: 1 },  /* Kehre, schaut schon heim */
                { bis: 1.00, von: ORTE.ost,   nach: ORTE.lager, blick: 1 }   /* heim */
            ];

            var t = ORTE.lager;
            var westwaerts = false;
            var anfang = 0;

            for (var s = 0; s < FAHRT.length; s++) {
                var teil = FAHRT[s];

                if (p < teil.bis) {
                    var spanne = teil.bis - anfang;
                    var q = spanne > 0 ? (p - anfang) / spanne : 0;
                    t = teil.von + (teil.nach - teil.von) * q;

                    /*
                     * Der Blick steht am Abschnitt, nicht an der Bewegung.
                     * In den Kehren steht der Wagen still — wer nur die
                     * Bewegung liest, laesst ihn dort in die alte Richtung
                     * schauen und erst beim Anfahren umspringen. Er dreht
                     * sich aber waehrend der Kehre, nicht danach.
                     */
                    westwaerts = teil.blick === 1;
                    break;
                }

                anfang = teil.bis;
                t = teil.nach;
                westwaerts = teil.blick === 1;
            }

            if (t < 0) t = 0;
            if (t > 1) t = 1;

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
                /* Ueber die Fahrt verteilt, nicht alle am Anfang. */
                var schwelle = 0.22 + i * 0.2;
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
       Hero: der freie Raum rechts neben der Ueberschrift
       ---------------------------------------------------------------

       Vorher stand hier eine Mindmap, die vom angefahrenen Wort aus
       nach unten streute. Die Ueberschrift laeuft aber ueber vier
       Zeilen und nimmt die halbe Breite — die Namen landeten im Text.

       Jetzt: fuer "im Hochpustertal" faehrt der Porter die Talstrasse
       ab, die Orte leuchten der Reihe nach auf. Fuer die beiden anderen
       Woerter eine ruhige Liste an derselben Stelle.

       Die Orte stehen mit echten Koordinaten da, nicht als gefaellige
       Kurve: das Pustertal laeuft von West nach Ost, und Sexten liegt
       nicht daran, sondern zweigt bei Innichen nach Sueden ab. Eine
       erfundene Linie haette das verwischt.
       --------------------------------------------------------------- */

    function heroBeiwerkAufsetzen() {
        var hero = document.querySelector('.sz-hero');
        if (!hero) return;

        var woerter = hero.querySelectorAll('[data-sz-wort]');
        if (!woerter.length) return;

        var buehne = document.createElement('div');
        buehne.className = 'sz-beiwerk';
        buehne.setAttribute('aria-hidden', 'true');
        hero.appendChild(buehne);

        var porterBild = hero.getAttribute('data-sz-porter') || '';

        /* Hochpustertal, West nach Ost. Sexten zweigt bei Innichen ab. */
        var TAL = [
            { name: 'Welsberg',   lat: 46.7519, lon: 12.1122 },
            { name: 'Niederdorf', lat: 46.7369, lon: 12.1697 },
            { name: 'Toblach',    lat: 46.7358, lon: 12.2214 },
            { name: 'Innichen',   lat: 46.7325, lon: 12.2806 },
            { name: 'Winnebach',  lat: 46.7414, lon: 12.3903 }
        ];
        var SEXTEN = { name: 'Sexten', lat: 46.7005, lon: 12.3500, ab: 3 };

        /*
         * Die Listen kamen bis 1.24.0 aus dem Skript und waren erfunden:
         * "Kueche, Reinigung, Hygiene ..." steht so in keinem Katalog.
         * Jetzt reicht der Hero die echten Abteilungen und Marken durch,
         * mit ihren Artikelzahlen und ihren Adressen. Was der Shop nicht
         * fuehrt, erscheint nicht.
         */
        /* --- Die Zeichnung von Toblach ------------------------------
         *
         * Zwei Haeuser im Umriss, gezeichnet nach den Fotos vom Ort: die
         * Pfarrkirche mit Turm, Uhr und Zwiebelhaube, daneben das Haus am
         * Dorfplatz — flaches Satteldach mit weitem Vorsprung, drei
         * Reihen Balkone, unten der Laubengang mit dem Schriftband.
         *
         * Alles in einem Koordinatenfeld 120 x 80, Boden bei y = 76. So
         * laesst sich die ganze Gruppe mit einem Faktor skalieren, ohne
         * dass etwas verrutscht.
         * ------------------------------------------------------------ */

        var NS = 'http://www.w3.org/2000/svg';

        function teil(name, attr) {
            var e = document.createElementNS(NS, name);
            for (var k in attr) e.setAttribute(k, attr[k]);
            return e;
        }

        function kirche() {
            var g = teil('g', { fill: 'none', stroke: 'currentColor',
                                'stroke-width': 1.4, 'stroke-linejoin': 'round' });

            /* Das Schiff: erst der Kasten, dann der Giebel obendrauf. Als
               eine einzige geschwungene Form gezeichnet wurde daraus ein
               Schluesselloch — der Barockgiebel ist breit und flach. */
            g.appendChild(teil('path', { d: 'M19.5 76 V46 H41 V76' }));
            g.appendChild(teil('path', { d: 'M18.6 46 H41.9', 'stroke-width': 1 }));

            /* Der Schweifgiebel: zwei Voluten, dazwischen der Aufsatz */
            g.appendChild(teil('path', { d:
                'M20.6 46 V43.6 Q20.6 41.4 23.4 41 Q26 40.6 26.6 38.6 '
              + 'Q27.6 35.8 30.3 35.8 Q33 35.8 34 38.6 '
              + 'Q34.6 40.6 37.2 41 Q40 41.4 40 43.6 V46' }));

            /* Die Nische mit der Figur im Giebel */
            g.appendChild(teil('path', { d: 'M29 45.6 V41.4 Q29 39.6 30.3 39.6 Q31.6 39.6 31.6 41.4 V45.6',
                                         'stroke-width': 0.7 }));

            g.appendChild(teil('circle', { cx: 30.3, cy: 56, r: 3.1 }));
            g.appendChild(teil('path', { d: 'M27.3 76 V69.5 Q27.3 66.2 30.3 66.2 Q33.3 66.2 33.3 69.5 V76' }));
            g.appendChild(teil('path', { d: 'M23.4 76 V46 M37.2 76 V46', 'stroke-width': 0.7 }));

            /* Der Turm: Schaft, Gesims, Uhr, Glockengeschoss */
            g.appendChild(teil('path', { d: 'M8.5 76 V36 M19.5 76 V36' }));
            g.appendChild(teil('path', { d: 'M6.6 36 H21.4' }));
            g.appendChild(teil('circle', { cx: 14, cy: 31.5, r: 2.6 }));
            g.appendChild(teil('path', { d: 'M14 31.5 V29.7 M14 31.5 L15.6 32.4', 'stroke-width': 0.7 }));
            g.appendChild(teil('path', { d: 'M9.4 36 V27 H18.6 V36' }));
            g.appendChild(teil('path', { d: 'M11.6 27 V23.4 Q11.6 21.4 14 21.4 Q16.4 21.4 16.4 23.4 V27',
                                         'stroke-width': 0.9 }));
            g.appendChild(teil('path', { d: 'M7.2 21.4 H20.8' }));

            /* Die Zwiebelhaube — daran erkennt man sie von weitem */
            g.appendChild(teil('path', { d:
                'M8 21.4 Q4.8 16.4 10.6 13.2 Q13.4 11.8 14 10.2 '
              + 'Q14.6 11.8 17.4 13.2 Q23.2 16.4 20 21.4 Z' }));

            /* Laterne, kleine Haube, Kugel, Kreuz */
            g.appendChild(teil('path', { d: 'M12.2 10.2 V7.4 H15.8 V10.2', 'stroke-width': 0.9 }));
            g.appendChild(teil('path', { d: 'M12.2 7.4 Q12.2 4.8 14 4.4 Q15.8 4.8 15.8 7.4 Z',
                                         'stroke-width': 0.9 }));
            g.appendChild(teil('circle', { cx: 14, cy: 3.4, r: 0.9, fill: 'currentColor', stroke: 'none' }));
            g.appendChild(teil('path', { d: 'M14 2.5 V0.4 M12.9 1.3 H15.1', 'stroke-width': 0.8 }));

            return g;
        }

        function haus() {
            var g = teil('g', { fill: 'none', stroke: 'currentColor',
                                'stroke-width': 1.4, 'stroke-linejoin': 'round' });

            /* Das Dach: zwei Linien, damit der weite Vorsprung als Balken
               liest und nicht als Strich. */
            g.appendChild(teil('path', { d: 'M43 47.5 L80 28.5 L117 47.5' }));
            g.appendChild(teil('path', { d: 'M46.6 49.6 L80 32.5 L113.4 49.6', 'stroke-width': 0.9 }));
            g.appendChild(teil('path', { d: 'M51 46.6 V49.6 M62 41 V44 M98 41 V44 M109 46.6 V49.6',
                                         'stroke-width': 0.8 }));

            g.appendChild(teil('path', { d: 'M51 48.6 V76 H109 V48.6' }));

            /* Drei Balkonbaender. Die Bruestung als Linie mit Staeben —
               bei kleiner Groesse verschwimmt sie zu einem Band, und das
               ist richtig so. */
            [55.5, 63.5].forEach(function (y) {
                g.appendChild(teil('path', { d: 'M52.5 ' + y + ' H107.5', 'stroke-width': 1 }));
                var d = '';
                for (var x = 54.5; x < 107; x += 4) d += 'M' + x + ' ' + (y - 2.6) + ' V' + y;
                g.appendChild(teil('path', { d: d, 'stroke-width': 0.5 }));
            });

            /* Fenster mit Laden */
            [51.6, 59.6].forEach(function (y) {
                var d = '';
                for (var x = 56; x < 105; x += 8) d += 'M' + x + ' ' + y + ' h3.4 v3.2 h-3.4 Z';
                g.appendChild(teil('path', { d: d, 'stroke-width': 0.6 }));
            });

            /* Der Laubengang: das Schriftband auf den Saeulen */
            g.appendChild(teil('rect', { x: 51, y: 66.4, width: 58, height: 4.4,
                                         fill: 'currentColor', stroke: 'none' }));

            var saeulen = '';
            for (var x = 54; x <= 106; x += 6.5) saeulen += 'M' + x + ' 70.8 V76 ';
            g.appendChild(teil('path', { d: saeulen, 'stroke-width': 1.1 }));

            return g;
        }

        /* Die Raute des Logos, frei setzbar. */
        function raute(cx, cy, halb) {
            var h = halb / 1.8;
            return teil('path', {
                d: 'M' + cx + ' ' + (cy - h) + ' L' + (cx + halb) + ' ' + cy
                 + ' L' + cx + ' ' + (cy + h) + ' L' + (cx - halb) + ' ' + cy + ' Z',
                fill: 'none', stroke: 'currentColor', 'stroke-width': 1.2
            });
        }

        /*
         * Die ganze Ortsmarke: die beiden Haeuser, darunter Raute und
         * Name, darunter die Zeile, die sagt, was hier steht.
         * Feld 130 x 106.
         */
        function ortsmarke() {
            var g = teil('g', { class: 'sz-haupt' });

            var v = teil('g', { transform: 'translate(5 0)' });
            v.appendChild(kirche());
            v.appendChild(haus());
            v.appendChild(teil('path', { d: 'M2 76.6 H118', stroke: 'currentColor',
                                         'stroke-width': 0.8, opacity: 0.45, fill: 'none' }));
            g.appendChild(v);

            /* Raute und Name in einer Zeile, die Erklaerung darunter —
               links buendig mit der Raute. Rechts neben dem Namen lief
               sie sonst ueber den Rand des Feldes hinaus. */
            g.appendChild(raute(15, 90, 7));

            var t = teil('text', { class: 'sz-haupt__name', x: 28, y: 95 });
            t.textContent = 'Toblach';
            g.appendChild(t);

            var u = teil('text', { class: 'sz-haupt__unter', x: 8, y: 104.5 });
            u.textContent = 'UNSER HAUS AM DORFPLATZ';
            g.appendChild(u);

            return g;
        }

        var LISTEN = {};
        try { LISTEN = JSON.parse(hero.getAttribute('data-sz-listen') || '{}'); }
        catch (x) { LISTEN = {}; }

        var offen = null;
        var schliessZeit = null;
        var lauf = null;

        /* --- Wieviel Platz ist rechts frei? ------------------------- */
        function freiraum() {
            var hb = hero.getBoundingClientRect();
            var titel = hero.querySelector('h1');
            if (!titel) return null;

            var tb = titel.getBoundingClientRect();
            var links = tb.right - hb.left + 48;
            var breite = hb.width - links - 40;

            /* Unter 150px passt nicht einmal mehr ein Ortsname nebeneinander.
               Dann lieber nichts als ein Gedraenge. */
            if (breite < 150) return null;

            return { links: links, oben: hb.height * 0.12, breite: breite, hoehe: hb.height * 0.76 };
        }

        /* --- Die Orte in den Rahmen rechnen --------------------------
         *
         * Massstabsgetreu: derselbe Faktor fuer beide Achsen. Verzerrte
         * man die Breite, stuende die Abzweigung nach Sexten falsch.
         *
         * Der Rahmen richtet sich deshalb nach dem Tal, nicht umgekehrt:
         * das Pustertal ist breit und flach, also wird die Buehne ein
         * breites niedriges Band und sitzt mittig im freien Raum. Vorher
         * fuellte sie die ganze Hoehe — die Strasse lag dann als duenner
         * Faden in einem hohen leeren Kasten.
         *
         * Rechts bleibt Platz fuer die laengste Ortsbeschriftung, sonst
         * liefe "Winnebach" aus dem Bild.
         */
        function abbilden(raum) {
            var mittelBreite = Math.cos(46.735 * Math.PI / 180);
            var punkte = TAL.concat([SEXTEN]).map(function (o) {
                return { name: o.name, x: o.lon * mittelBreite, y: -o.lat };
            });

            var xs = punkte.map(function (p) { return p.x; });
            var ys = punkte.map(function (p) { return p.y; });
            var x0 = Math.min.apply(null, xs), x1 = Math.max.apply(null, xs);
            var y0 = Math.min.apply(null, ys), y1 = Math.max.apply(null, ys);

            var rand = 26;
            var schrift = 96;
            var faktor = (raum.breite - 2 * rand - schrift) / (x1 - x0 || 1);

            /*
             * Platz ueber der Strasse fuer die Ortsmarke von Toblach.
             *
             * Sie ist rund 88px hoch und steht ueber ihrem Punkt. Das Tal
             * selbst ist flach — ohne diesen Kopfraum stiesse sie oben an,
             * und der Turm waere abgeschnitten.
             */
            var kopfraum = 118;
            var hoehe = (y1 - y0) * faktor + 2 * rand + kopfraum;

            return {
                hoehe: hoehe,
                punkte: punkte.map(function (p) {
                    return {
                        name: p.name,
                        x: rand + (p.x - x0) * faktor,
                        y: rand + kopfraum + (p.y - y0) * faktor
                    };
                })
            };
        }

        /* Weiche Strasse durch die Punkte (Catmull-Rom als Bezier). */
        function strasse(pfad) {
            var d = 'M' + pfad[0].x.toFixed(1) + ' ' + pfad[0].y.toFixed(1);
            for (var i = 0; i < pfad.length - 1; i++) {
                var p0 = pfad[i - 1] || pfad[i];
                var p1 = pfad[i], p2 = pfad[i + 1];
                var p3 = pfad[i + 2] || p2;
                var c1x = p1.x + (p2.x - p0.x) / 6, c1y = p1.y + (p2.y - p0.y) / 6;
                var c2x = p2.x - (p3.x - p1.x) / 6, c2y = p2.y - (p3.y - p1.y) / 6;
                d += ' C' + c1x.toFixed(1) + ' ' + c1y.toFixed(1) +
                     ' ' + c2x.toFixed(1) + ' ' + c2y.toFixed(1) +
                     ' ' + p2.x.toFixed(1) + ' ' + p2.y.toFixed(1);
            }
            return d;
        }

        function stelle(raum) {
            buehne.style.left   = Math.round(raum.links) + 'px';
            buehne.style.top    = Math.round(raum.oben) + 'px';
            buehne.style.width  = Math.round(raum.breite) + 'px';
            buehne.style.height = Math.round(raum.hoehe) + 'px';
        }

        /* --- Die Route ---------------------------------------------- */
        function zeichneRoute(raum) {
            var bild = abbilden(raum);
            var punkte = bild.punkte;

            /* Die Buehne auf die natuerliche Hoehe des Tals stellen und
               mittig in den freien Raum setzen. */
            buehne.style.height = Math.round(bild.hoehe) + 'px';
            buehne.style.top = Math.round(raum.oben + (raum.hoehe - bild.hoehe) / 2) + 'px';

            var tal = punkte.slice(0, TAL.length);
            var sexten = punkte[punkte.length - 1];
            var abzweig = tal[SEXTEN.ab];

            var ns = 'http://www.w3.org/2000/svg';
            var svg = document.createElementNS(ns, 'svg');
            svg.setAttribute('class', 'sz-route');
            svg.setAttribute('viewBox', '0 0 ' + Math.round(raum.breite) + ' ' + Math.round(bild.hoehe));

            var ast = document.createElementNS(ns, 'path');
            ast.setAttribute('class', 'sz-route__ast');
            ast.setAttribute('d', 'M' + abzweig.x.toFixed(1) + ' ' + abzweig.y.toFixed(1) +
                                  ' Q' + ((abzweig.x + sexten.x) / 2).toFixed(1) + ' ' + abzweig.y.toFixed(1) +
                                  ' ' + sexten.x.toFixed(1) + ' ' + sexten.y.toFixed(1));
            svg.appendChild(ast);

            /* Zwei Linien uebereinander: eine breite blasse als Bett,
               darauf die schmale kraeftige. So liest sich der Strich als
               Strasse und nicht als Haarlinie. */
            var d = strasse(tal);

            var bett = document.createElementNS(ns, 'path');
            bett.setAttribute('class', 'sz-route__bett');
            bett.setAttribute('d', d);
            svg.appendChild(bett);

            var weg = document.createElementNS(ns, 'path');
            weg.setAttribute('class', 'sz-route__weg');
            weg.setAttribute('d', d);
            svg.appendChild(weg);

            var marken = punkte.map(function (p) {
                var g = document.createElementNS(ns, 'g');
                g.setAttribute('class', 'sz-route__ort');

                /*
                 * Toblach ist nicht einer von sechs Punkten.
                 *
                 * Hier steht das Haus. Bisher sah man das der Karte nicht
                 * an — sechs gleiche Punkte, sechs gleiche Namen, und der
                 * Hauptort ging darin unter. Er bekommt deshalb die
                 * Zeichnung ueber der Strasse, mit Raute und Name; sein
                 * gewoehnlicher Namenszug entfaellt dafuer, sonst stuende
                 * er zweimal da.
                 */
                if (p.name === 'Toblach') {
                    g.setAttribute('class', 'sz-route__ort sz-route__haupt');

                    /*
                     * Die Marke waechst mit der Karte, aber nur bis zu
                     * einem Punkt. Fest bei 112px war sie in einer breiten
                     * Karte ein Beiwerk; ohne Grenze wuerde sie in einer
                     * schmalen alles zudecken.
                     */
                    var breit = Math.max(124, Math.min(172, raum.breite * 0.25));
                    var mass = breit / 130;
                    var luft = 24;

                    var marke = ortsmarke();
                    marke.setAttribute('transform',
                        'translate(' + (p.x - breit / 2).toFixed(1) + ' '
                                     + (p.y - luft - 106 * mass).toFixed(1) + ') '
                      + 'scale(' + mass.toFixed(4) + ')');
                    g.appendChild(marke);

                    /* Der Strich von der Strasse hinauf zur Marke, damit
                       die Zeichnung nicht ueber dem Tal schwebt. */
                    g.appendChild(teil('path', {
                        class: 'sz-haupt__strich',
                        d: 'M' + p.x.toFixed(1) + ' ' + (p.y - 4).toFixed(1)
                         + ' V' + (p.y - luft).toFixed(1)
                    }));

                    /* Der Ortspunkt selbst als Raute, nicht als Kreis:
                       die Regel .sz-route__ort circle setzt r fest und
                       wuerde ihn den anderen gleichmachen. Gefuellt, damit
                       er auf der Strasse als Ziel liest und nicht als
                       weiterer Halt. */
                    var punkt = raute(p.x, p.y, 6);
                    punkt.setAttribute('fill', 'currentColor');
                    punkt.setAttribute('class', 'sz-haupt__punkt');
                    g.appendChild(punkt);

                    svg.appendChild(g);
                    return g;
                }

                var kreis = document.createElementNS(ns, 'circle');
                kreis.setAttribute('cx', p.x.toFixed(1));
                kreis.setAttribute('cy', p.y.toFixed(1));
                kreis.setAttribute('r', '3.5');
                g.appendChild(kreis);

                /*
                 * Die Namen stehen unter der Strasse, nicht daneben.
                 *
                 * Auf der Hoehe der Strasse lief "Niederdorf" waagrecht in
                 * die Ortsmarke von Toblach hinein — die Abstaende der
                 * Orte sind kleiner als die Namen lang sind. Unterhalb
                 * kreuzt nichts mehr: Toblach steht als einziges darueber.
                 */
                var text = document.createElementNS(ns, 'text');
                text.setAttribute('x', (p.x + 8).toFixed(1));
                text.setAttribute('y', (p.y + 17).toFixed(1));
                text.textContent = p.name;
                g.appendChild(text);

                svg.appendChild(g);
                return g;
            });

            var wagen = null;
            if (porterBild) {
                wagen = document.createElementNS(ns, 'image');
                wagen.setAttribute('class', 'sz-route__porter');
                wagen.setAttribute('href', porterBild);
                wagen.setAttribute('width', '30');
                wagen.setAttribute('height', '48');
                svg.appendChild(wagen);
            }

            buehne.innerHTML = '';
            buehne.appendChild(svg);

            var laenge = weg.getTotalLength();
            weg.style.strokeDasharray = laenge;
            bett.style.strokeDasharray = laenge;

            /* Wo liegt jeder Ort auf der Strasse? Grob abgetastet — auf
               den Meter kommt es hier nicht an, nur auf die Reihenfolge. */
            var beiLaenge = tal.map(function (p) {
                var beste = 0, abstand = Infinity;
                for (var s = 0; s <= laenge; s += laenge / 160) {
                    var q = weg.getPointAtLength(s);
                    var d = (q.x - p.x) * (q.x - p.x) + (q.y - p.y) * (q.y - p.y);
                    if (d < abstand) { abstand = d; beste = s; }
                }
                return beste;
            });

            function setzen(anteil) {
                var s = laenge * anteil;
                weg.style.strokeDashoffset = laenge - s;
                bett.style.strokeDashoffset = laenge - s;

                for (var i = 0; i < tal.length; i++) {
                    marken[i].classList.toggle('ist-da', s >= beiLaenge[i] - 2);
                }
                /* Sexten kommt mit Innichen, es zweigt dort ab. */
                marken[marken.length - 1].classList.toggle('ist-da', s >= beiLaenge[SEXTEN.ab] - 2);
                ast.classList.toggle('ist-da', s >= beiLaenge[SEXTEN.ab] - 2);

                if (wagen) stelleWagen(s);
            }

            /* Der Wagen steht auf der Strasse und schaut in Fahrtrichtung.
               Die Drehung muss um seinen eigenen Punkt gehen — dreht man
               um einen anderen, wandert er aus dem Bild. */
            function stelleWagen(s) {
                var q = weg.getPointAtLength(s);
                var vorn = weg.getPointAtLength(Math.min(laenge, s + 6));
                /*
                 * Das Bild zeigt nach unten — als Bildlaufanzeige im Fuss
                 * faehrt der Wagen nach unten, dort braucht es keine
                 * Drehung. Also minus 90 Grad, nicht plus: mit plus stand
                 * er genau verkehrt und fuhr rueckwaerts durchs Tal.
                 */
                var winkel = Math.atan2(vorn.y - q.y, vorn.x - q.x) * 180 / Math.PI - 90;
                wagen.setAttribute('x', (q.x - 15).toFixed(1));
                wagen.setAttribute('y', (q.y - 24).toFixed(1));
                wagen.setAttribute('transform', 'rotate(' + winkel.toFixed(1) + ' ' + q.x.toFixed(1) + ' ' + q.y.toFixed(1) + ')');
            }

            if (sanft.matches) {
                /* Nicht bewegen heisst nicht unsichtbar: die ganze Strasse
                   steht da, alle Orte sind benannt, der Wagen parkt in
                   Toblach — dort, wo das Haus steht. */
                setzen(1);
                if (wagen) stelleWagen(beiLaenge[2]);
                return;
            }

            setzen(0);
            var beginn = null;
            var dauer = 1700;

            function schritt(zeit) {
                if (beginn === null) beginn = zeit;
                var t = Math.min(1, (zeit - beginn) / dauer);
                /* sanft anfahren und ausrollen */
                var e = t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2;
                setzen(e);
                if (t < 1) lauf = window.requestAnimationFrame(schritt);
            }

            lauf = window.requestAnimationFrame(schritt);
        }

        /* --- Die Liste ---------------------------------------------- */
        function zeichneListe(eintraege) {
            var kasten = document.createElement('div');
            kasten.className = 'sz-beiwerk__liste';

            eintraege.forEach(function (e, i) {
                /* Ein Eintrag ist entweder nur ein Name (die Orte) oder
                   ein Gegenstand mit Zahl und Adresse (Katalog). */
                var name = typeof e === 'string' ? e : e.name;
                var zahl = typeof e === 'string' ? null : e.zahl;
                var weg  = typeof e === 'string' ? null : e.weg;

                var zeile = document.createElement(weg ? 'a' : 'span');
                zeile.className = 'sz-beiwerk__zeile'
                    + (typeof e === 'object' && e && e.haupt ? ' ist-haupt' : '');
                zeile.style.setProperty('--verzug', (i * 60) + 'ms');
                if (weg) zeile.href = weg;

                var wort = document.createElement('span');
                wort.textContent = name;
                zeile.appendChild(wort);

                if (typeof e === 'object' && e && e.haupt) {
                    var hier = document.createElement('span');
                    hier.className = 'sz-beiwerk__hier';
                    hier.textContent = 'unser Haus';
                    zeile.appendChild(hier);
                }

                if (zahl !== null && zahl !== undefined) {
                    var z = document.createElement('span');
                    z.className = 'sz-beiwerk__zahl';
                    z.textContent = zahl;
                    zeile.appendChild(z);
                }

                kasten.appendChild(zeile);
            });

            buehne.innerHTML = '';
            buehne.appendChild(kasten);
            /* Erst im naechsten Bild einblenden, sonst springt es. */
            window.requestAnimationFrame(function () { kasten.classList.add('ist-da'); });
        }

        function zeichne(schluessel) {
            var raum = freiraum();
            if (!raum) { schliesse(); return; }

            if (lauf) { window.cancelAnimationFrame(lauf); lauf = null; }
            stelle(raum);
            buehne.classList.add('ist-offen');

            /*
             * Die Route braucht Platz: fuenf Orte nebeneinander plus die
             * laengste Beschriftung. Unter 380px wuerde sie sich stapeln.
             * Dann treten dieselben Orte als Liste an ihre Stelle —
             * dieselbe Auskunft, nur ohne Gedraenge. Das faengt Fenster
             * ab, in denen die Ueberschrift fast alles einnimmt.
             */
            buehne.style.pointerEvents = (schluessel === 'karte') ? 'none' : 'auto';

            if (schluessel === 'karte') {
                if (raum.breite >= 380) zeichneRoute(raum);
                else zeichneListe(TAL.concat([SEXTEN]).map(function (o) {
                    /* Auch ohne Platz fuer die Karte bleibt Toblach der
                       Hauptort — dieselbe Auskunft, nur als Zeile. */
                    return { name: o.name, haupt: o.name === 'Toblach' };
                }));
                return;
            }

            zeichneListe(LISTEN[schluessel] || []);
        }

        function schliesse() {
            if (lauf) { window.cancelAnimationFrame(lauf); lauf = null; }
            buehne.classList.remove('ist-offen');
            buehne.innerHTML = '';
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
                zeichne(schluessel);
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

        /* Beim Groessenwechsel stimmt der Rahmen nicht mehr. */
        window.addEventListener('resize', function () {
            if (offen) schliesse();
        });
    }


    /* --------------------------------------------------------------- */

    function los() {
        tourAufsetzen();
        auftauchenAufsetzen();
        heroBeiwerkAufsetzen();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', los);
    } else {
        los();
    }
})();
