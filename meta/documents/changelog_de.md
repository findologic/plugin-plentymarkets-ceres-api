# Release Notes für Findologic

## 3.9.1 (2022-03-22)

### Behoben
* [PLENTY-434] Ein Fehler wurde behoben, wodurch nicht auf eine konfigurierte Landinpage weitergeleitet wurde sondern auf die Produktdetailseite wenn es ein einziges Suchresultat gab.

### Geändert
* [PLENTY-428] Plentymarkets Review Version 3.9.0

## 3.9.0 (2022-03-17)

### Geändert
* [PLENTY-429] Verwendung von neuer Plentymarkets API für Suche von Varianten um Kompatibilität für plentyShop Version 5.0.47 sicherzustellen.

## 3.8.1 (2022-02-24)

### Behoben
* [PLENTY-426] Für Preisfilter mit Typ Text muss ein min und max Wert gegeben sein

### Geändert
* [PLENTY-428] Plentymarkets Review Version 3.8.0


## 3.8.0 (2022-02-14)

### Geändert

* [PLENTY-423] Die Drittanbieter Bibliothek "SVG-Injector" wird nicht länger von Cloudflare geladen, sondern stattdessen mit den Plugin-Assets ausgeliefert.
* [PLENTY-420] Die Weiterleitung zur Produktdetailseite wenn nur ein Produkt gefunden wird, verwendet nun eine ähnliche Logik wie die exportierte Item-URL.

## 3.7.6 (2022-01-11)

### Geändert

* [PLENTY-414] Logging wurde verbessert wenn die XML Antwort nicht verarbeitet werden kann.
* [PLENTY-366] Im Fehlerfall beinhalten Log Einträge mehr Daten für die weitere Analyse.


## 3.7.5 (2021-12-21)

### Geändert

* [PLENTY-403] Marketplace Name und Beschreibung wurden aktualisiert.
* [PLENTY-411] Wenn eine Anfrage an das Plentymarkets SDK scheitert, werden zwei weitere Anfragen geschickt, bevor ein Fallback passiert.

### Behoben

* [PLENTY-412] Ein Fehler wurde behoben, wodurch die Weiterleitung auf Landingpages nicht wie erwartet funktionierte.

## 3.7.4 (2021-11-30)

### Behoben

* [PLENTY-388] Ein Fehler wurde behoben, wodurch Assisted Suggest beim initialen Seiten-Aufruf automatisch geöffnet wurde, da das `autofocus` Attribut am Suchfeld gesetzt wurde.

## 3.7.3 (2021-10-27)

### Geändert

* [PLENTY-383] Die Weiterleitung zur Produktdetailseite parsed nun die ids, die von der Findologic-Antwort geliefert werden, um zu überprüfen, ob diese auch die id der Variante enthält.

### Behoben

* [PLENTY-373] Ein Fehler wurde behoben, wodurch beim Abschicken der Suche die Sprache nicht berücksichtigt wurde, was dazu führte, dass die Suchergebnisseite der Standardsprache des Shops entsprach.
* [PLENTY-371] Ein Fehler wurde behoben, wodurch die Weiterleitung auf die Produktdetailseite vor der Weiterleitung einer Landingpage durchgeführt wurde.

## 3.7.2 (2021-08-30)

### Geändert

* [PLENTY-365] Die Minimalanforderungen von Ceres und IO sind nun `5.0.35`.

### Behoben

* [PLENTY-362] Ein Fehler wurde behoben, wodurch die gewählten Filter nicht korrekt gerendert wurden,
wenn SSR aktiviert wurde.
* [PLENTY-363] Ein Fehler wurde behoben, wodurch ein falscher Minimalpreis ausgegeben wurde, wenn
der gesetzte Preis niedriger war, als durch den Bereichsslider möglich war.

## 3.7.1 (2021-08-16)

### Behoben

* [PLENTY-357] Ein Fehler wurde behoben, wodurch der Filter Container nicht die gesamte breite verwendete,
wenn server-side-rendering verwendet wurde.
* [PLENTY-356] Ein Fehler wurde behoben, wodurch ein Fehler ausgegeben wurde, wenn die Findologic API
von Plentymarkets nicht erreichbar war.

## 3.7.0 (2021-07-26)

### Hinzugefügt

* [PLENTY-346] Es gibt nun eine Option, welche es erlaubt die minimale Suchwortlänge einzustellen.

### Geändert

* [PLENTY-318] Die Performance auf Navigationsseiten wurde verbessert, da nun ein anderer Endpunkt, welcher
  von Ceres zur Verfügung gestellt wurde, verwendet wird. Bitte beachten Sie, dass dadurch die minimale Ceres Version
  5.0.26 vorausgesetzt wird.

### Behoben

* [PLENTY-345] Ein Fehler wurde behoben, wodurch Suchbegriffe abgeschnitten wurden, wenn diese ein `&`
  Zeichen beinhalteten.
* [PLENTY-351] Mehrere Fehler in Bezug zum Ceres Server-Side-Rendering wurden behoben.


## 3.6.1 (2021-05-31)

### Behoben

* [PLENTY-340] Ein Fehler wurde behoben, wodurch Filterwerte hinter der Produktanzahl angezeigt wurden, wenn
 die Filterwerte zu viele Zeichen beinhalteten.
* [PLENTY-341] Ein Fehler wurde behoben, wodurch die Smart Suggest nur auf Suchseiten angezeigt wurde,
 wenn Findologic auf Kategorieseiten deaktiviert wurde.

## 3.6.0 (2021-05-18)

### Hinzugefügt

* [PLENTY-310] Die Anzahl der selektierten Filter kann nun neben dem Namen des Filters ausgegeben werden.
  * [PLENTY-337] Eine Konfigurationsoption wurde hinzugefügt um dieses Feature zu aktivieren/deaktivieren.
* [PLENTY-329] Eine `translation.json` wurde hinzugefügt, welche es erlaubt Storefront-Übersetzungen anzupassen.
* [PLENTY-330] Übersetzungen für alle von Findologic unterstützten Sprachen, wurden hinzugefügt.
* [PLENTY-327] Parameter `shopType` und `shopVersion` werden nun an die Findologic API gesendet.

### Behoben

* [PLENTY-309] Ein Fehler wurde behoben, wodurch bei manchen selektierten Filtern, eine `0` als Produktanzahl
  angezeigt wurde.

## 3.5.3 (2021-04-22)

### Behoben

* [PLENTY-332] Ein Fehler wurde behoben, wodurch das Findologic plugin nicht korrekt Such-/Navigations-Anfragen
bearbeitete, wenn das IO Plugin eine höhere Priorität als das Findologic Plugin gesetzt hat.

## 3.5.2 (2021-04-19)

### Behoben

* [PLENTY-332] Ein Fehler wurde behoben, wodurch sich das Findologic Plugin in einem inaktiven Zustand
 befand, wenn IO und Findologic derselben Priorität zugeordnet wurden.

## 3.5.1 (2021-04-19)

### Geändert

* [PLENTY-307] Filter erhalten nun eine `fl-active` CSS Klasse, wenn zumindest ein Filter gewählt wurde.

### Behoben

* [PLENTY-314/PLENTY-324] Ein Fehler wurde behoben, wodurch ein Fehler ausgegeben wurde, wenn ein Shop
 mehrere Sprachen angelegt hatte, jedoch manche davon keinen Shopkey hinterlegt hatten.

## 3.5.0 (2021-03-08)

### Geändert

* [PLENTY-305] Die Benutzerfreundlichkeit des Bereichssliders wurde optimiert
  * Die Währung/Einheit wird nun beim selektierten Filter ausgegeben.
  * Eingegebene Kommas (`,`) werden nun mit Punkten (`.`) ersetzt.
  * Nicht-Numerische Eingaben können nicht länger abgeschickt werden.
* [PLENTY-313] Die Performance bei Seiten, die Herstellerbilder, oder Farbbilder Filter verwenden, wurde verbessert.

### Behoben

* [PLENTY-315] Ein Fehler wurde behoben, wodurch auf Suchseiten ohne Ergebnisse, eine lange Ladezeit entstand.
* [PLENTY-316] Ein Fehler wurde behoben, wodurch die Annzahl der Proukte auf Kategorieseiten nicht
 der tatsächlichen Anzahl der Produkte entsprach.

## 3.4.0 (2021-02-15)

### Hinzugefügt

* [PLENTY-245] Die selektierten Filter enthalten nun auch den Namen des Filters.
* [PLENTY-273] Der Kategoriefilter wird nun als Dropdown dargestellt, wenn der Filter in der Filterkonfiguration als
  Dropdown konfiguriert wurde.
* [PLENTY-308] Beim Halten des Mauszeigers über eine Farbfilter-Kachel wird nun der Name der Farbe als Titel angezeigt.

### Geändert

* [PLENTY-287] Anstatt der Container-Verknüpfung wird nun die Komponente für das Suchfeld global überschrieben.

### Behoben

* [PLENTY-311] Ein Fehler wurde behoben, welcher es erlaubte XSS auszuführen, wenn die Smart Did-You-Mean
  Container-Verknüpfung verwendet wurde.
* [PLENTY-299] Ein Fehler wurde behoben, wodurch Fehler in der Konsole ausgegeben wurden, wenn sowohl die
  TopBar als auch Filter-Widgets auf der selben Seite verwendet wurden.

## 3.3.0 (2021-01-12)

### Hinzugefügt

* [PLENTY-266] ShopBuilder Filter Widgets werden nun unterstützt.

### Geändert

* [PLENTY-242] Die Direct Integration container Konfiguration wurde entfernt.

### Behoben

* [PLENTY-280] Ein Fehler wurde behoben, wodurch Lücken im Produktlisting entstanden,
wenn eine Ceres version kleiner gleich 5.0.2 verwendet wurde.

## 3.2.2 (2020-12-17)

### Behoben

* [PLENTY-294] Ein Fehler wurde behoben, wodurch man auf eine 404 Seite weitergeleitet wurde, wenn die
 Hauptvariation eines Produkts inaktiv war.

## 3.2.1 (2020-11-24)

### Behoben

* [PLENTY-286] Ein Fehler wurde behoben, wodurch die Pagination nicht korrekt funktionierte.

## 3.2.0 (2020-11-19)

### Geändert

* [PLENTY-257] Die Weiterleitung zu Produktdetailseite verwendet nun die Ceres URL anstatt der Calisto URL.
* [PLENTY-159] Findologic wird nicht länger eine interne Plentymarkets Suche durchführen um sicherzustellen, dass alle
 Produkte die von Findologic zurückgegeben werden, auch tatsächlich angezeigt werden können. Dies gilt nur für Ceres > 5.0.2.

### Behoben

* [PLENTY-276] Ein Fehler wurde behoben, welcher dazu führte, dass die Weiterleitung auf die Produktdetailseite nicht die
 aktuelle Sprache berücksichtigte. Dies führte dazu, dass Kunden unter Umständen auf eine andere Sprache weitergeleitet wurden.

## 3.1.5 (2020-11-02)

### Behoben

* [PLENTY-277] Ein Fehler wurde behoben, wodurch Findologic nicht aktiv war, wenn keine explizite Sprache beim Shopkey
 gesetzt wurde.

## 3.1.4 (2020-10-07)

### Behoben

* [PLENTY-274] Ein Fehler wurde behoben, wodurch Kategorie/Hersteller Klicks in der Smart Suggest nicht
 korrekt funktionierten.
* [PLENTY-275] Ein Fehler wurde behoben, wodurch Findologic bei Sprachen aktiviert wurde, obwohl diese keinen
 Shopkey konfiguriert hatten.

## 3.1.3 (2020-10-05)

### Behoben

* [PLENTY-267] Ein Fehler wurde behoben, welcher bei mehreren Suchformularen dazu geführt hatte, dass immer das erste
 abgeschickt wurde.
* [PLENTY-272] Ein Fehler wurde behoben, welcher auf Kategorieseiten dazu geführt hatte, dass die Standardsortierung
 nicht funktionierte, wenn eine Sortieroption in Ceres konfiguriert war, welche durch Findologic nicht unterstützt
 wird.
* Dieser Release enthält Änderungen des letzten Releases, da es einen Fehler beim Erstellen des letzten Releases gab.
 Wir entschuldigen uns für etwaige Komplikationen und werden in Zukunft darauf achten, dass dies nicht erneut geschieht.

## 3.1.2 (2020-09-07)

### Behoben

* [PLENTY-260] Ein Fehler wurde behoben, wodurch keine Ergebnisse ausgespielt wurden, wenn eine Kategorieseite als
 Suchergebnisseite im IO Plugin konfiguriert wurde.
* [PLENTY-262] Ein Fehler wurde behoben, wodurch man auf die Produktdetailseite weitergeleitet wurde, wenn die letzte
 Paginationsseite nur ein Resultat enthielt.

## 3.1.1 (2020-08-03)

### Behoben

* [PLENTY-254] Beim Neuladen der Suchresultat-Seite wurde ein Leerzeichen im Suchbegriff als Plus-Zeichen interpretiert.
* [PLENTY-225] Intern wurden Filterwerte doppelt gesendet. Dies wurde behoben.

### Geändert

* [PLENTY-256] Text im Marketplace wurde aktualisisert damit klar hervorgeht das eine Integration mit Ceres < 5 möglich ist.
* [PLENTY-259] Der Name des Plugins wurde aktualisiert.

## 3.1.0 (2020-06-10)

### Hinzugefügt

* [PLENTY-187] Guided Shopping wird nun unterstützt.

### Geändert

* [PLENTY-244] Die Plugin Icons für den marketplace wurden überarbeitet.

## 3.0.1 (2020-05-06)

### Geändert

* [PLENTY-232] Das Bild welches angezeigt wird, wenn kein Farbbild/Farbton konfiguriert ist, wurde geändert.
* [PLENTY-241] Alle Vorkommnisse von FINDOLOGIC wurden durch Findologic ersetzt.
* [PLENTY-243] & [PLENTY-239] Die Bilder für den Plenty Marketplace wurden aktualisiert.

## 3.0.0 (2020-04-14)

### Support Ceres 5

<p align="center"><a href="https://marketplace.plentymarkets.com/plugins/sales/online-shops/ceres_4697" target="_blank"><img height="150" alt="Ceres 5" src="https://plentymarkets-assistant.s3.eu-central-1.amazonaws.com/ceres-5.svg"></a></p>

### Behoben

* [PLENTY-227] Ein Fehler wurde behoben, welcher immer das erste Suchformular,
 anstatt das abgeschickte Formular abschickte.

## 2.7.0 (2020-04-06)

### Hinzugefügt

* [PLENTY-193] Die Anzahl der Filter pro Spalte, kann nun konfiguriert werden.
* [PLENTY-196] Filter haben nun ein no-follow Attribut, wodurch diese
 nicht länger gecrawlt werden.

### Geändert

* [PLENTY-229] Die benutzerfreundliche Suchbegriff-Nachricht wurde angepasst, damit
 diese sich dem Stil der Direct Integration anpasst.
* [PLENTY-230] Die Smart Did-You-Mean Nachricht wird nun unter der Suchbegriff-Nachricht
 angezeigt.
* [PLENTY-231] Farbfilter Bilder werden nun als Hintergrund anstatt als separates
 `<img>` Element gesetzt.

### Behoben

* [PLENTY-220] Ein Fehler wurde behoben, wodurch der Filter Button auf Kategorieseiten
 an eine falsche Stelle gesetzt wurde, wenn der ShopBuilder verwendet wurde.

## 2.6.0 (2020-03-13)

### Hinzugefügt

* [PLENTY-192] Plentymarkets Tags werden nun unterstützt. Findologic wird nun automatisch nach Tags auf Tagseiten
 filtern.

### Geändert

* [PLENTY-199] Dropdown Filter haben nun eine fixierte Höhe. Das vermeidet, dass Dropdowns größer als die gesamte
 Seite sind, wenn ein Filter viele Filtermöglichkeiten bietet.
* [PLENTY-209] Die weiterleitungs URL zu der Produktdetailseite ist nun die Selbe, die auch exportiert wird.
* [PLENTY-204] Links zu unserer Dokumentation wurden aktualisiert.
* [PLENTY-216] Ceres und IO 4.5 sind nun als Minimalanforderungen im Plugin versehen.

### Behoben

* [PLENTY-210] Ein Fehler wurde behoben welcher einen Konsolenfehler verursachte, wenn alle Filter als
 "Weitere Filter" konfiguriert waren.
* [PLENTY-200] Ein Fehler wurde behoben welcher Sortieroptionen verschwinden lies, obwohl Findologic noch keine
 Resultate ausgespielt hatte. Einige Sortiermöglichkeiten werden dennoch verschwinden, wenn ein Filter selektiert wird,
 da manche Sortiermöglichkeiten nicht mit Findologic kompatibel sind.

## 2.5.1 (2020-02-27)

### Behoben

* [PLENTY-203] Ein Fehler wurde behoben durch den Benutzer auf die Produktdetailseite
 weitergeleitet wurden, nachdem ein Filter gewählt wurde, der nur ein Resultat liefert.

## 2.5.0 (2020-02-24)

### Hinzugefügt

* [PLENTY-125] Smart Did-You-Mean wird nun untertützt. Weitere Informationen
 gibt es [in unserer Dokumentation](https://docs.findologic.com/doku.php?id=integration_documentation:plentymarkets_ceres:ceres_sdym).
* [PLENTY-185] Falls Smart Did-You-Mean verwendet wird,
 werden Klicks auf Kategorien oder Herstellern in der Smart Suggest
 nun einen Benutzerfreundlichen Text des tatsächlich gewählten Filters anzeigen.
* [PLENTY-188] Suchergebnisse die nur ein einziges Resultat liefern, werden nun
 direkt auf die Produktdetailseite weitergeleitet.

### Geändert

* Der "page" Parameter wird nicht länger nach dem Deselektieren eines Filters
 angezeigt.
* [PLENTY-197] Dokumentation für Smart Did-You-Mean wurde hinzugefügt.
* Intern: JavaScript wird nun automatisch vor einem commit kompiliert.

## 2.4.2 (2020-02-03)

### Behoben

* [PLENTY-190] Klicks auf Vorschläge in der Mobilen Smart Suggest, werden nicht länger
 die mobile Smart Suggest ausblenden, ohne die Suche auszuführen. Dies inkludiert
 auch Klicks auf "Alle Ergebnisse anzeigen" in der normalen Smart Suggest.

## 2.4.1 (2020-01-17)

### Behoben

* [PLENTY-186] Kompatibilität mit der Ceres Version v4.5.0 wurde wiederhergestellt.

## 2.4.0 (2020-01-07)

### Hinzugefügt

* [PLENTY-179] Es gibt nun eine Konfigurationsoption, um CSS von Findologic
 für das Filter-Styling, zu deaktivieren.

### Behoben

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

* Unterstützung für den "keine weitere Filter" text, sofern dieser bei Findologic konfiguriert ist.

### Behoben

* Filter vom Typ Bereichsslider welche nicht Preisslider sind, funktionieren wieder.
* Es werden nicht länger Vue Fehler ausgegeben, sobald man mithilfe des ShopBuilders
 eine Checkout Seite erstellt.
* Bei Firefox werden nun drei anstatt zwei Filter pro Reihe ausgegeben.
* Wenn alle Filter als "Weitere Filter" kofiguriert sind, wird der "Weitere Filter"
 Knopf nicht länger über die gesamte Breite einer Reihe angezeigt.

## 2.1.0 (2019-09-23)

### Hinzugefügt

* Unterstützung für fixierte Filterwerte für den Filtertyp "Dropdown", sofern diese bei Findologic konfiguriert sind.

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
* Unterstützung für den Filtertyp Dropdown, sofern diese bei Findologic konfiguriert sind.

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

* Der Aufruf der zweiten Suchergebnisseite führte zu einem Fehler, da Plentymarkets keine Metainformationen über das Findologic Plugin laden konnte.

### Geändert

* Die Schranke für die höchste unterstützte Version des Plugins Ceres wurde angehoben (4.0.0 - 4.x.x).

## 1.2.0 (2019-07-29)

### Hinzugefügt

* Unterstützung für Landingpages die bei Findologic konfiguriert sind.
* Unterstützung für Promotionsbanner (inklusive Dataprovider).

## 1.1.2 (2019-07-19)

### Geändert

* Es wurde eine Schranke für die höchste unterstütze Version der Plugins Ceres (4.0.2) und IO (4.1.2) eingeführt.
* Beschreibungstext und Bilder wurden aktualisiert.
* Die aktuell verwendete Version des Plugins, die bei jeder Anfrage an Findologic mitgeschickt wird, wird dynamisch über Plentymarkets ermittelt.
* Javascript Dateien von Drittanbietern werden von dem entsprechenden CDN geladen.
* Javascript Dateien des Plugins werden in einer minifizierten Version geladen.
* Das Plugin benutzt die aktuelle Version des Findologic Snippets. Hierfür wurden weitere Einstellungsmöglichkeiten hinzugefügt.

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

  Zur Zeit wird nur eine der verfügbaren Sprachen des Shops unterstützt. Welche das ist, kann per Absprache mit Findologic konfiguriert werden.

* Personalisierung

  Mit Findologic Personalisierung haben Sie ab sofort für jede Zielgruppe einen eigens optimierten Shop.
  
* Search

  Die Suchfunktion entspricht dem wichtigsten Verkaufstool in Ihrem Online-Shop. Unser seit über 10 Jahren geschärfter Algorithmus lässt Ihre Kunden 1:1 personalisiert wirklich das finden, wonach Sie suchen. Im Vorfeld hierzu bietet unsere Smart Suggest ein intelligentes Dropdown, um Ihren Usern eine schnellstmögliche Orientierung zu bieten. Vor allem Mobile.
  
* Navigation

  Ihren Usern werden global über Ihren Online-Shop zu jedem Zeitpunkt der Customer Journey die relevantesten Produkte angezeigt. Mit 1:1 Personalisierung und unserem raffinierten Merchandising. Bieten Sie ganzheitlich ein hochperformantes, konsistentes und individuelles Benutzererlebnis über alle Kategorieseiten hinweg.
  
* Merchandising

  Nutzen Sie das intuitive Findologic Backend mit unseren speziell entwickelten Tools zur kompakten und effizienten Verkaufs- und Onsite-Marketing Steuerung.
  
* Shopping Guide

  Stellen Sie Ihren Usern im Rahmen einer Berater-Kampagne smarte Fragen, welche auch ein Verkäufer einer stationären Filiale stellen würde. Somit finden Ihre User einfach und schnell das passende Wunschprodukt.
