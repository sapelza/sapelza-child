# Medien

## danke.webm / danke.mp4

Die Dankes-Animation auf der Seite „Bestellung erhalten".

Legen Sie die Datei **hier** ab, unter genau diesem Namen:

- `danke.webm` — bevorzugt. Kleiner, und mit Alphakanal transparent.
- `danke.mp4` — Rückfall für ältere Safari-Fassungen.

Beide dürfen gleichzeitig da sein; der Browser nimmt die erste, die er
kann. Liegt **keine** von beiden hier, erscheint der Dank als Text —
die Seite bleibt vollständig funktionsfähig.

## Worauf zu achten ist

- **Kurz.** Zwei bis vier Sekunden. Sie läuft einmal, nicht in Schleife.
- **Ohne Ton.** Sie wird stumm abgespielt; eine Tonspur wäre nur Ballast.
- **Klein.** Unter 1 MB. Der Kunde wartet sonst auf eine Feier, während
  er nur seine Bankdaten sehen will.
- **Hochformat oder quadratisch** passt besser als Breitbild: sie steht
  rechts neben dem Text und ist auf 15 rem begrenzt.
- Bei WebM mit Alphakanal sitzt sie ohne Kasten auf der Fläche — dieselbe
  Technik wie beim Porter auf der B2B-Seite.

## Nach dem Ablegen

Datei committen, Version im Theme-Kopf erhöhen, Tag setzen, hochladen.
Ohne Tag sieht Git Updater die neue Fassung nicht.
