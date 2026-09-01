/*
 * Die Sortierung.
 *
 * WooCommerce gibt ein natives <select> aus. Geschlossen laesst es sich
 * gestalten, die aufgeklappte Liste aber nicht — die zeichnet das
 * Betriebssystem, mit blauem Balken und Systemschrift. Auf einer Seite,
 * die sonst durchgehend in Mono und Bordeaux gesetzt ist, faellt das
 * heraus wie ein Fremdkoerper.
 *
 * Also eine eigene Liste daneben. Das <select> bleibt im Dokument und
 * traegt weiterhin den Wert: ohne Skript funktioniert die Sortierung
 * wie zuvor, und das Formular schickt dasselbe wie immer ab.
 *
 * ------------------------------------------------------------------
 * Warum das hier neu geschrieben ist
 * ------------------------------------------------------------------
 *
 * Die erste Fassung schloss die Liste, wenn man daneben tippte — ueber
 * einen Lauscher am Dokument. Das ist zweimal schiefgegangen:
 *
 * Erstens schickt iOS Safari keinen click an document, wenn man auf
 * eine Flaeche ohne eigenen Zweck tippt. pointerdown half, aber nur
 * halb.
 *
 * Zweitens — und das war der eigentliche Fehler — hing die Liste
 * trotzdem: sie stand als absolut gesetztes Kind im Seitenfluss, und
 * auf dem Telefon lag sie ueber den Waren, ohne dass irgendetwas sie
 * sicher wieder wegnahm.
 *
 * Jetzt liegt ein echter Grund darunter: eine Flaeche ueber dem ganzen
 * Fenster, die jeden Druck abfaengt. Kein Ereignis kann daran vorbei.
 * Und auf schmalen Schirmen wird die Liste ein Blatt von unten, wie
 * das Menue — dieselbe Geste, dasselbe Verhalten.
 *
 * Beide, Grund und Blatt, haengen im Koerper und nicht in der Leiste:
 * so kann kein Vorfahr mit eigener Stapelebene sie unter etwas
 * anderes schieben. Genau daran ist die erste Fassung gescheitert.
 */
( function () {
    'use strict';

    var SCHMAL = 900;

    function schmal() {
        return window.matchMedia( '(max-width: ' + ( SCHMAL - 1 ) + 'px)' ).matches;
    }

    function aufsetzen( feld ) {
        if ( feld.dataset.szGefasst === '1' ) return;
        feld.dataset.szGefasst = '1';

        var form = feld.closest( 'form' );
        var huelle = document.createElement( 'div' );
        huelle.className = 'sz-sortierung';

        var knopf = document.createElement( 'button' );
        knopf.type = 'button';
        knopf.className = 'sz-sortierung__knopf';
        knopf.setAttribute( 'aria-haspopup', 'listbox' );
        knopf.setAttribute( 'aria-expanded', 'false' );

        var wort = document.createElement( 'span' );
        knopf.appendChild( wort );

        var pfeil = document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' );
        pfeil.setAttribute( 'viewBox', '0 0 24 24' );
        pfeil.setAttribute( 'aria-hidden', 'true' );
        pfeil.innerHTML = '<path d="M6 9l6 6 6-6" fill="none" stroke="currentColor"'
                        + ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
        knopf.appendChild( pfeil );

        /* Der Grund: eine Flaeche ueber dem ganzen Fenster. Sie faengt
           jeden Druck ab, der nicht der Liste gilt. */
        var grund = document.createElement( 'div' );
        grund.className = 'sz-sortierung__grund';
        grund.hidden = true;

        var liste = document.createElement( 'div' );
        liste.className = 'sz-sortierung__liste';
        liste.setAttribute( 'role', 'listbox' );
        liste.hidden = true;

        /* Auf dem Blatt steht eine Ueberschrift: aus dem Zusammenhang
           gerissen — unten am Bildschirm, weit weg vom Knopf — waere
           sonst nicht klar, worueber man da entscheidet. */
        var titel = document.createElement( 'p' );
        titel.className = 'sz-sortierung__titel';
        titel.textContent = 'Sortieren nach';
        liste.appendChild( titel );

        var eintraege = [];

        Array.prototype.forEach.call( feld.options, function ( wahl ) {
            var zeile = document.createElement( 'button' );
            zeile.type = 'button';
            zeile.className = 'sz-sortierung__wahl';
            zeile.setAttribute( 'role', 'option' );
            zeile.dataset.wert = wahl.value;
            zeile.textContent = wahl.textContent;
            liste.appendChild( zeile );
            eintraege.push( zeile );

            zeile.addEventListener( 'click', function () {
                feld.value = wahl.value;
                auf( false );

                /* WooCommerce haengt am change des Feldes und schickt selbst
                   ab; wo das nicht greift, schicken wir das Formular. */
                feld.dispatchEvent( new Event( 'change', { bubbles: true } ) );
                if ( form && ! form.dataset.szAbgeschickt ) {
                    form.dataset.szAbgeschickt = '1';
                    form.submit();
                }
            } );
        } );

        function zeigen() {
            wort.textContent = feld.options[ feld.selectedIndex ]
                ? feld.options[ feld.selectedIndex ].textContent : '';

            eintraege.forEach( function ( z ) {
                var ist = z.dataset.wert === feld.value;
                z.setAttribute( 'aria-selected', String( ist ) );
                z.classList.toggle( 'ist-gewaehlt', ist );
            } );
        }

        var offen = false;

        function auf( ja ) {
            if ( ja === offen ) return;
            offen = ja;

            knopf.setAttribute( 'aria-expanded', String( ja ) );
            huelle.classList.toggle( 'ist-offen', ja );

            if ( ja ) {
                /* Grund und Liste wandern in den Koerper. Dort kann kein
                   Vorfahr mit eigener Stapelebene sie unter die Waren
                   schieben — daran ist die erste Fassung gescheitert. */
                document.body.appendChild( grund );
                document.body.appendChild( liste );

                liste.classList.toggle( 'ist-blatt', schmal() );
                if ( ! schmal() ) stelle();

                grund.hidden = false;
                liste.hidden = false;
                return;
            }

            grund.hidden = true;
            liste.hidden = true;
            liste.classList.remove( 'ist-blatt' );

            /* Zurueck an ihren Platz, damit beim naechsten Mal nichts
               doppelt im Koerper haengt. */
            if ( grund.parentNode ) grund.parentNode.removeChild( grund );
            if ( liste.parentNode ) liste.parentNode.removeChild( liste );
        }

        /* Am Schreibtisch haengt die Liste unter dem Knopf. Weil sie im
           Koerper sitzt, muss die Stelle gerechnet werden. */
        function stelle() {
            var r = knopf.getBoundingClientRect();
            liste.style.top = ( r.bottom + 6 ) + 'px';
            liste.style.left = 'auto';
            liste.style.right = Math.max( 12, window.innerWidth - r.right ) + 'px';
        }

        knopf.addEventListener( 'click', function () {
            auf( ! offen );
        } );

        [ 'pointerdown', 'click' ].forEach( function ( art ) {
            grund.addEventListener( art, function ( e ) {
                e.preventDefault();
                auf( false );
            } );
        } );

        /* Beim Rollen und beim Drehen des Geraets stimmt die gerechnete
           Stelle nicht mehr. Am Schreibtisch schliessen wir deshalb;
           auf dem Blatt ist nichts zu rechnen, es klebt unten. */
        window.addEventListener( 'scroll', function () {
            if ( offen && ! schmal() ) auf( false );
        }, { passive: true } );

        window.addEventListener( 'resize', function () {
            if ( offen ) auf( false );
        } );

        document.addEventListener( 'keydown', function ( e ) {
            if ( e.key === 'Escape' && offen ) { auf( false ); knopf.focus(); }
        } );

        /* Tastatur: Pfeile wandern durch die Liste. */
        function pfeile( e ) {
            if ( e.key !== 'ArrowDown' && e.key !== 'ArrowUp' ) return;

            e.preventDefault();
            if ( ! offen ) { auf( true ); eintraege[ 0 ].focus(); return; }

            var i = eintraege.indexOf( document.activeElement );
            var weiter = e.key === 'ArrowDown' ? i + 1 : i - 1;
            if ( weiter < 0 ) weiter = eintraege.length - 1;
            if ( weiter >= eintraege.length ) weiter = 0;
            eintraege[ weiter ].focus();
        }

        knopf.addEventListener( 'keydown', pfeile );
        liste.addEventListener( 'keydown', pfeile );

        feld.parentNode.insertBefore( huelle, feld );
        huelle.appendChild( knopf );
        huelle.appendChild( feld );

        zeigen();
    }

    function alle() {
        var felder = document.querySelectorAll( 'form.woocommerce-ordering select.orderby, form.woocommerce-ordering select[name="orderby"]' );
        Array.prototype.forEach.call( felder, aufsetzen );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', alle );
    } else {
        alle();
    }
} )();
