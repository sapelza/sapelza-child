# SAPELZA Shop — Child-Theme

Astra-Child-Theme für [sapelzashop.com](https://sapelzashop.com). Enthält
**nur Gestaltung**: Stylesheet, Vorlagen und den Hell/Dunkel-Umschalter.

Das Verhalten — Geschäftsregeln, „Meine Artikel", Wunschtermin — liegt im
Plugin [sapelza-shop](https://github.com/sapelza/sapelza-shop). Diese Trennung
ist Absicht: das Plugin überlebt einen Theme-Wechsel, das Theme nicht. Wären
die Bestellregeln hier, gingen bei einem Redesign stillschweigend Bestellungen
ohne Liefertermin durch.

## Installation

Erstinstallation als ZIP über Design → Themes → Installieren → Theme hochladen.
Danach übernimmt [Git Updater](https://git-updater.com) die Aktualisierung.

## Aktualisieren

Git Updater erkennt neue Versionen an **Git-Tags**, nicht am Datei-Kopf. Damit
ein Update in WordPress erscheint, müssen beide zusammenpassen:

    git add -A && git commit -m "..." && git tag 1.5.1 && git push && git push origin 1.5.1

Ohne Tag erscheint nie ein Update. Weicht der Tag von der Version in
`style.css` ab, meldet WordPress dauerhaft ein Update, das nichts ändert.

**Immer zuerst auf Staging einspielen, dann Live.**

## Token

Der Block zwischen `SZ:TOKEN-ANFANG` und seinem Ende in `style.css` wird
maschinell aus der Vorschau-App erzeugt. Farben werden dort entschieden, hier
stehen sie nur noch — von Hand geänderte Werte überschreibt der nächste Lauf.

## Abhängigkeit zum Plugin

Theme-Code fragt den Bereich (Gastro/Handwerk) ausschließlich über
`sz_theme_bereich()` in `functions.php` ab, nie direkt über `sz_bereich()`.
Die Hülle prüft mit `function_exists` und fällt auf Gastro zurück, damit die
Seite auch bei abgeschaltetem Plugin lädt.
