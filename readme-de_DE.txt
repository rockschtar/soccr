=== Soccr ===
Contributors: rockschtar
Donate link: https://github.com/rockschtar/soccr
Tags: football, soccer, fussball, bundesliga, openligadb
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 8.4
Stable tag: develop
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Zeigt Spielergebnisse, Tabellen und kommende Spiele aus OpenLigaDB als Gutenberg-Blöcke an.

== Description ==

**Soccr** bindet Live-Fußballdaten aus [OpenLigaDB](https://www.openligadb.de) in den WordPress-Block-Editor ein. Das Plugin liefert drei vollständig konfigurierbare Gutenberg-Blöcke für Spielergebnisse, Ligatabellen und mannschaftsbezogene Spielinformationen — alle serverseitig gerendert und für eine bessere Performance gecached.

= Blöcke =

**Tabelle (Standings)**
Zeigt eine vollständige Ligatabelle für eine ausgewählte Liga und Saison an. Die Tabelle enthält Position, Mannschaftsname, gespielte Spiele, Siege, Unentschieden, Niederlagen, Tore, Tordifferenz und Punkte. Liga und Saison lassen sich direkt im Block-Inspector auswählen.

**Spieltag (Matchday)**
Zeigt alle Spiele eines bestimmten Spieltags an. Standardmäßig wird automatisch der aktuelle Spieltag dargestellt. Eine optionale Blätterfunktion erlaubt das Durchstöbern aller Spieltage einer Saison. Spiele werden nach Datum gruppiert und zeigen Anstoßzeiten für anstehende Partien sowie Endstände für abgeschlossene Spiele.

**Team-Spiel (Team Match)**
Zeigt ein Spiel einer bestimmten Mannschaft an. Drei Anzeigemodi stehen zur Verfügung: aktuelles Spiel (nächstes anstehendes oder zuletzt beendetes), nächstes Spiel oder letztes Spiel. Optional werden Mannschaftswappen angezeigt. Die Mannschaft kann aus einer Liste aller Teams der gewählten Liga ausgewählt werden.

= Unterstützte Ligen =

Standardmäßig sind folgende Ligen und Wettbewerbe aus OpenLigaDB verfügbar:

* 1\. Bundesliga (bl1)
* 2\. Bundesliga (bl2)
* 3\. Liga (bl3)
* 1\. Frauen Bundesliga (fbl1)
* 2\. Frauen Bundesliga (fbl2)
* UEFA Champions League (ucl)
* UEFA Europa League (uel)
* UEFA Conference League (uecl)
* DFB-Pokal (dfb)

Weitere Ligen lassen sich über den Filter `soccr_league_shortcuts` ergänzen — ein Beispiel steht im Abschnitt *Für Entwickler*.

= Funktionen =

* Drei sofort einsatzbereite Gutenberg-Blöcke
* Serverseitiges Rendering — kein JavaScript im Frontend erforderlich
* Eingebautes Caching (1 Stunde für Spiel- und Tabellendaten, bis zu 24 Stunden für Mannschaften, Ligen und Wappen)
* Ausrichtungsunterstützung (links, zentriert, rechts, breit, voll) für alle Blöcke
* Optionaler eigener Block-Titel je Block
* Mannschaftswappen mit eingebautem Bild-Proxy und Caching
* Blätterfunktion für Spieltage

= Datenquelle =

Alle Daten stammen von **OpenLigaDB** und stehen unter der [Open Database License (ODbL) v1.0](https://opendatacommons.org/licenses/odbl/). Jeder Block zeigt automatisch den vorgeschriebenen Quellenhinweis an.

Bei der Nutzung des Plugins sendet deine WordPress-Seite Anfragen an `https://api.openligadb.de`. Es werden keine personenbezogenen Nutzerdaten an OpenLigaDB übertragen.

= Für Entwickler =

Verfügbare Filter:

* `soccr_league_shortcuts` — Anpassen, welche Ligen im Block-Inspector verfügbar sind (Standard: `['bl1', 'bl2', 'bl3', 'fbl1', 'fbl2', 'ucl', 'uel', 'uecl', 'dfb']`)
* `soccr_team_match_html` — HTML-Ausgabe des Team-Spiel-Blocks anpassen
* `soccr_group_matches_html` — HTML-Ausgabe des Spieltag-Blocks anpassen
* `soccr_group_matches_headline` — Überschrift des Spieltag-Blocks anpassen
* `soccr_team_icon_url` — URL des Mannschaftswappens vor dem Proxying anpassen
* `soccr_league_season_display` — Darstellung der Saison neben dem Liganamen anpassen (Standard: `2025/2026`)

Beispiel — weitere Ligen über `soccr_league_shortcuts` ergänzen. Es kann jeder bei OpenLigaDB verfügbare Shortcut verwendet werden:

`add_filter('soccr_league_shortcuts', static function (array $shortcuts): array {
    $shortcuts[] = 'uefaeuro2024'; // UEFA Euro 2024
    $shortcuts[] = 'wm2022';       // FIFA-WM 2022
    return $shortcuts;
});`

Verfügbare Actions:

* `soccr_exception` — Wird bei API- oder Rendering-Fehlern ausgelöst; nutzbar für eigenes Error-Logging

== Installation ==

1. Installiere das Plugin über *Plugins → Installieren* im WordPress-Admin und aktiviere es.
2. Füge einen der drei Soccr-Blöcke über den Block-Editor ein. Die Blöcke sind im Block-Inserter unter der Kategorie **Soccr** zu finden.
3. Wähle Liga und Saison im Block-Inspector auf der rechten Seite aus.

== Frequently Asked Questions ==

= Welche Ligen werden unterstützt? =

Standardmäßig sind die Bundesligen der Männer und Frauen, die 3. Liga, die UEFA-Klubwettbewerbe und der DFB-Pokal verfügbar — die vollständige Liste steht in der Beschreibung. Jede weitere bei OpenLigaDB verfügbare Liga lässt sich über den Filter `soccr_league_shortcuts` einbinden.

= Wird ein API-Key benötigt? =

Nein. OpenLigaDB ist ein kostenloser, offener Dienst und erfordert weder eine Registrierung noch einen API-Key.

= Warum zeigt der Block veraltete Daten? =

Das Plugin cached API-Antworten als Transients. Spiel- und Tabellendaten werden 1 Stunde lang zwischengespeichert. Wenn du Daten sofort aktualisieren möchtest, lösche die zwischengespeicherten Daten mit `wp transient delete --all` auf der Kommandozeile. Nutzt deine Seite einen persistenten Object-Cache, leere stattdessen diesen mit `wp cache flush`.

= Kann ich eigene Styles ergänzen? =

Ja. Alle Blöcke verwenden BEM-CSS-Klassen mit dem Präfix `wp-block-soccr-` (z. B. `.wp-block-soccr-standings`, `.wp-block-soccr-team-match`, `.wp-block-soccr-group-matches`). Diese Klassen lassen sich im Stylesheet deines Themes ansprechen.

= Werden Mannschaftswappen angezeigt? =

Der Team-Spiel-Block zeigt optional Mannschaftswappen aus OpenLigaDB und Wikimedia Commons an. Die Anzeige der Wappen lässt sich im Block-Inspector ein- und ausschalten. Bilder werden über WordPress geproxyt, um Mixed-Content-Probleme zu vermeiden, und 24 Stunden lang gecached.
