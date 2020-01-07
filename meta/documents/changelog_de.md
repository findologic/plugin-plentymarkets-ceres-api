# Release Notes für FINDOLOGIC

## 2.4.0 (2020-01-07)

### Added

* [PLENTY-179] Es gibt nun eine Konfigurationsoption, um CSS von FINDOLOGIC
 für das Filter-Styling, zu deaktivieren.

### Fixed

* [PLENTY-184] Ein Fehler wurde behoben welcher verursachte, dass das Abschicken
 einer Suche immer auf den Hauptshop verwies.
* [PLENTY-180] Das Plugin wird nicht länger versuchen eine nicht existierende
 CSS Datei zu laden.
* [PLENTY-176] Der "keine weiteren Filter" Text wird nun für alle Filter Typen angezeigt.
* [PLENTY-183] Nur Kategorien des aktuellen Kategoriebaums werden angezeigt.

## 2.3.0 (2019-11-29)

## Behoben

* Das Abschicken einer Suche mit mehreren Wörtern wird nicht länger
 Leerzeichen durch "+" Zeichen ersetzen.

## Geändert

* jQuery-UI wurde komplett entfernt und durch noUiSlider ersetzt, da es
 Fehler in der Browser Konsole verursachte. Falls es Anpassungen am Styling des
 Bereichsslider gibt, müssten diese überarbeitet werden.

## 2.2.0 (2019-11-21)

### Hinzugefügt

* Unterstützung für den "keine weitere Filter" text, sofern dieser bei FINDOLOGIC konfiguriert ist.

### Behoben

* Filter vom Typ Bereichsslider welche nicht Preisslider sind, funktionieren wieder.
* Es werden nicht länger Vue Fehler ausgegeben, sobald man mithilfe des ShopBuilders
 eine Checkout Seite erstellt.
* Bei Firefox werden nun drei anstatt zwei Filter pro Reihe ausgegeben.
* Wenn alle Filter als "Weitere Filter" kofiguriert sind, wird der "Weitere Filter"
 Knopf nicht länger über die gesamte Breite einer Reihe angezeigt.

## 2.1.0 (2019-09-23)

### Hinzugefügt

* Unterstützung für fixierte Filterwerte für den Filtertyp "Dropdown", sofern diese bei FINDOLOGIC konfiguriert sind.

### Behoben

* Der gesamte Kategoriebaum wird nun als selektiert makiert. Zuvor wurde nur der letzte
selektierte Kategoriefilter als selektiert makiert.
* Es werden nicht länger Vue Fehler ausgegeben, sobald das Ceres Plugin auf den Performance
level "Development" gesetzt wurde.

## Geändert

* Die Installationsanleitung leitet nun auf unsere Dokumentation unter https://docs.findologic.com weiter.

## 2.0.1 (2019-09-13)

### Behoben

* Deselektieren von bereits selektierten Kategoriefiltern funktioniert wie erwartet.

## 2.0.0 (2019-09-11)

### Hinzugefügt

* Unterstüzung für den Rückgabetyp `XML_2.1`. Bitte beachten Sie dass einige CSS-Style
Änderungen beinhaltet, welche das Styling von Filtern beeinflussen könnte. **Überprüfen Sie
Ihre Filter-Styles bevor Sie upgraden.**
* Unterstützung für den Filtertyp Dropdown, sofern diese bei FINDOLOGIC konfiguriert sind.

### Behoben

* Nur ein Kategoriefilter kann gleichzeitig ausgewählt werden.

## 1.2.3 (2019-08-21)

### Behoben

* Die Standard Container-Verknüpfungen in der Pluginkonfiguration sind nicht länger inaktiv.
* Filter vom Typ Bereichsslider erlauben nun die selben Min-/Maxwerte.

## 1.2.2 (2019-08-06)

### Behoben

* Leerzeichen im konfigurierten Shopkey werden entfernt, da sie sonst nicht valide sind.
* Filter vom Typ Bereichsslider erlauben Kommazahlen (kleiner 1) als Min-/Maxwerte.

## 1.2.1 (2019-07-31)

### Behoben

* Der Aufruf der zweiten Suchergebnisseite führte zu einem Fehler, da Plentymarkets keine Metainformationen über das FINDOLOGIC Plugin laden konnte.

### Geändert

* Die Schranke für die höchste unterstützte Version des Plugins Ceres wurde angehoben (4.0.0 - 4.x.x).

## 1.2.0 (2019-07-29)

### Hinzugefügt

* Unterstützung für Landingpages die bei FINDOLOGIC konfiguriert sind.
* Unterstützung für Promotionsbanner (inklusive Dataprovider).

## 1.1.2 (2019-07-19)

### Geändert

* Es wurde eine Schranke für die höchste unterstütze Version der Plugins Ceres (4.0.2) und IO (4.1.2) eingeführt.
* Beschreibungstext und Bilder wurden aktualisiert.
* Die aktuell verwendete Version des Plugins, die bei jeder Anfrage an FINDOLOGIC mitgeschickt wird, wird dynamisch über Plentymarkets ermittelt.
* Javascript Dateien von Drittanbietern werden von dem entsprechenden CDN geladen.
* Javascript Dateien des Plugins werden in einer minifizierten Version geladen.
* Das Plugin benutzt die aktuelle Version des FINDOLOGIC Snippets. Hierfür wurden weitere Einstellungsmöglichkeiten hinzugefügt.

## 1.1.1 (2019-07-03)

### Behoben

* Sortieroption "Meistverkaufte Artikel" wird unterstützt.

### Geändert

* Beschreibungstext für Voraussetzungen des Plugins wurde geändert.

## 1.1.0 (2019-06-27)

### Hinzugefügt

* Shops mit mehreren Sprachen werden unterstützt.

### Behoben

* Das Plugin verwendet explizit das richtige Ausgabeformat (XML).

## 1.0.3 (2019-06-04)

* Installationsanleitung hinzugefügt.
* Update der Plugin Beschreibung.

## 1.0.2 (2019-06-03)

* Eine Notiz bezüglich der Unterstützung von Mehrsprachigkeit wurde dem Beschreibungstext hinzugefügt.

## 1.0.0 (2019-06-03)

### Funktionen

* Mehrsprachigkeit

  Zur Zeit wird nur eine der verfügbaren Sprachen des Shops unterstützt. Welche das ist, kann per Absprache mit FINDOLOGIC konfiguriert werden.

* Personalisierung

  Mit FINDOLOGIC Personalisierung haben Sie ab sofort für jede Zielgruppe einen eigens optimierten Shop.
  
* Search

  Die Suchfunktion entspricht dem wichtigsten Verkaufstool in Ihrem Online-Shop. Unser seit über 10 Jahren geschärfter Algorithmus lässt Ihre Kunden 1:1 personalisiert wirklich das finden, wonach Sie suchen. Im Vorfeld hierzu bietet unsere Smart Suggest ein intelligentes Dropdown, um Ihren Usern eine schnellstmögliche Orientierung zu bieten. Vor allem Mobile.
  
* Navigation

  Ihren Usern werden global über Ihren Online-Shop zu jedem Zeitpunkt der Customer Journey die relevantesten Produkte angezeigt. Mit 1:1 Personalisierung und unserem raffinierten Merchandising. Bieten Sie ganzheitlich ein hochperformantes, konsistentes und individuelles Benutzererlebnis über alle Kategorieseiten hinweg.
  
* Merchandising

  Nutzen Sie das intuitive FINDOLOGIC Backend mit unseren speziell entwickelten Tools zur kompakten und effizienten Verkaufs- und Onsite-Marketing Steuerung.
  
* Shopping Guide

  Stellen Sie Ihren Usern im Rahmen einer Berater-Kampagne smarte Fragen, welche auch ein Verkäufer einer stationären Filiale stellen würde. Somit finden Ihre User einfach und schnell das passende Wunschprodukt.
