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
 */
( function () {
    'use strict';

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

        var liste = document.createElement( 'div' );
        liste.className = 'sz-sortierung__liste';
        liste.setAttribute( 'role', 'listbox' );
        liste.hidden = true;

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

        function auf( ja ) {
            liste.hidden = ! ja;
            knopf.setAttribute( 'aria-expanded', String( ja ) );
            huelle.classList.toggle( 'ist-offen', ja );
        }

        knopf.addEventListener( 'click', function () {
            auf( liste.hidden );
        } );

        /*
         * Tippen daneben schliesst.
         *
         * click allein genuegt nicht: iOS Safari schickt keinen click an
         * document, wenn man auf eine Flaeche ohne eigenen Zweck tippt.
         * Auf dem Telefon blieb die Liste dadurch offen stehen, und man
         * kam nicht mehr an sie heran. pointerdown kommt in beiden
         * Faellen.
         */
        [ 'pointerdown', 'click' ].forEach( function ( art ) {
            document.addEventListener( art, function ( e ) {
                if ( ! huelle.contains( e.target ) ) auf( false );
            } );
        } );

        /* Tastatur: Escape schliesst, Pfeile wandern. */
        huelle.addEventListener( 'keydown', function ( e ) {
            if ( e.key === 'Escape' ) { auf( false ); knopf.focus(); return; }
            if ( e.key !== 'ArrowDown' && e.key !== 'ArrowUp' ) return;

            e.preventDefault();
            if ( liste.hidden ) { auf( true ); eintraege[ 0 ].focus(); return; }

            var i = eintraege.indexOf( document.activeElement );
            var weiter = e.key === 'ArrowDown' ? i + 1 : i - 1;
            if ( weiter < 0 ) weiter = eintraege.length - 1;
            if ( weiter >= eintraege.length ) weiter = 0;
            eintraege[ weiter ].focus();
        } );

        feld.parentNode.insertBefore( huelle, feld );
        huelle.appendChild( knopf );
        huelle.appendChild( liste );
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
