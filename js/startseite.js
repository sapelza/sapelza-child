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

            var hoehe = (y1 - y0) * faktor + 2 * rand;

            return {
                hoehe: hoehe,
                punkte: punkte.map(function (p) {
                    return {
                        name: p.name,
                        x: rand + (p.x - x0) * faktor,
                        y: rand + (p.y - y0) * faktor
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

                var kreis = document.createElementNS(ns, 'circle');
                kreis.setAttribute('cx', p.x.toFixed(1));
                kreis.setAttribute('cy', p.y.toFixed(1));
                kreis.setAttribute('r', '3.5');
                g.appendChild(kreis);

                var text = document.createElementNS(ns, 'text');
                text.setAttribute('x', (p.x + 10).toFixed(1));
                text.setAttribute('y', (p.y + 4).toFixed(1));
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
                zeile.className = 'sz-beiwerk__zeile';
                zeile.style.setProperty('--verzug', (i * 60) + 'ms');
                if (weg) zeile.href = weg;

                var wort = document.createElement('span');
                wort.textContent = name;
                zeile.appendChild(wort);

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
                else zeichneListe(TAL.concat([SEXTEN]).map(function (o) { return o.name; }));
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
