/*
 * Zwei Pfeile ans Mengenfeld.
 *
 * WooCommerce und das Mengen-Snippet geben ein nacktes Zahlenfeld aus.
 * Die kleinen Pfeile, die der Browser selbst anbietet, sind winzig und
 * auf dem Telefon gar nicht da. Ueberall sonst im Shop — in Meine
 * Artikel, in der Schnellerfassung — steht ein Minus, die Zahl, ein
 * Plus. Hier nun auch.
 *
 * Von aussen angebaut, nicht in die Vorlage geschrieben: die Vorlage
 * gehoert WooCommerce und dem Snippet. Wir fassen das Feld nur ein.
 */
( function () {
    'use strict';

    function schritt( feld, richtung ) {
        var jetzt = parseFloat( feld.value );
        if ( isNaN( jetzt ) ) jetzt = 0;

        var stufe = parseFloat( feld.getAttribute( 'step' ) ) || 1;
        var klein = parseFloat( feld.getAttribute( 'min' ) );
        var gross = parseFloat( feld.getAttribute( 'max' ) );

        var neu = jetzt + richtung * stufe;

        if ( ! isNaN( klein ) && neu < klein ) neu = klein;
        if ( ! isNaN( gross ) && neu > gross ) neu = gross;

        /* Nachkommastellen der Stufe behalten — manche Gebinde gehen in
           halben Kisten. */
        var stellen = ( String( stufe ).split( '.' )[ 1 ] || '' ).length;
        feld.value = stellen ? neu.toFixed( stellen ) : String( neu );

        /* WooCommerce hoert auf change, das Snippet oft auf input. */
        feld.dispatchEvent( new Event( 'input', { bubbles: true } ) );
        feld.dispatchEvent( new Event( 'change', { bubbles: true } ) );
    }

    function einfassen( feld ) {
        if ( feld.dataset.szGefasst === '1' ) return;
        feld.dataset.szGefasst = '1';

        var huelle = document.createElement( 'span' );
        huelle.className = 'sz-menge sz-menge--kachel';

        var minus = document.createElement( 'button' );
        minus.type = 'button';
        minus.textContent = '\u2212';
        minus.setAttribute( 'aria-label', 'weniger' );

        var plus = document.createElement( 'button' );
        plus.type = 'button';
        plus.textContent = '+';
        plus.setAttribute( 'aria-label', 'mehr' );

        feld.parentNode.insertBefore( huelle, feld );
        huelle.appendChild( minus );
        huelle.appendChild( feld );
        huelle.appendChild( plus );

        minus.addEventListener( 'click', function ( e ) { e.preventDefault(); schritt( feld, -1 ); } );
        plus.addEventListener( 'click', function ( e ) { e.preventDefault(); schritt( feld, 1 ); } );
    }

    function alle() {
        var felder = document.querySelectorAll(
            'ul.products li.product input.qty, ul.products li.product input[type="number"],'
            + ' .single-product div.product input.qty'
        );

        Array.prototype.forEach.call( felder, einfassen );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', alle );
    } else {
        alle();
    }

    /* Nach einem Variantenwechsel baut WooCommerce das Feld neu. */
    document.addEventListener( 'found_variation', alle );
    document.body && document.addEventListener( 'wc_fragments_refreshed', alle );
} )();
