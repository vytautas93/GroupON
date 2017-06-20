# Groupon Marketplace Plugin
 
zunächst einmal, was ist Groupon?
 
Antwort: Ein Marktplatz ;-)
 
Groupon ist ein sehr interessanter Marktplatz für Deals und hat eine sehr gute Reichweite im Ausland.
 
,...Sprechen Sie mit Ihren Produkten eine riesige Zielgruppe an. Groupon Goods ist ein One-Stop-Shop für den täglichen Bedarf mit Markenprodukten und einmaligen, unschlagbaren Angeboten.
Nutzen Sie Online-, E-Mail- und Telefonwerbung. Mit Groupon Goods werden Ihre Produkte für mehr als 15 Millionen aktive Kunden zugänglich.
 
Wie melde ich mich dort an?
https://www.groupon.de/merchant/marketing-solutions/goods -> **Los geht´s**
 
WICHTIG: Da es ab uns zu mal etwas länger dauert mit der kompletten Anmeldung, installiert das Plugin (wegen der 30 Tage Trail) erst dann, **wenn Ihr die Zugangsdaten bekommen habt und euren Ersten Auftrag verkauft habt!**
So könnt Ihr diese am besten ausnutzen.
 
## Was muss ich machen, dass alles sauber läuft und ich im Forum keine Beiträge schreiben muss?
 
Ganz eifach!
1. Du registrierst dich bei Groupon (wie oben schon beschrieben)
2. Du bekommst von Groupon die folgenden Accounts:
-   https://www.groupon.de/merchant/ <- Dieses ist das Merchant Center, für Bewertungen und Abrechnungen
-   https://deal-centre.groupon.de/ <- Hier tragt Ihr eure Deals ein!
-   https://scm.commerceinterface.com/accounts/ <- Da die SupplierID, die benötigt wird leider nicht im Account angezeigt wird, musst du folgendes machen.
- Einloggen in https://scm.commerceinterface.com/dashboard/ und dann mit der rechten Maustaste den Seitenquelltext anzeigen lassen. Hier wird dir dann wenn du die Suche nutzt und nach id_supplier suchst die Supplier ID unter value angezeigt.
<img src = "http://i.imgur.com/WdCr0nn.png" alt="image 1">
- Einen Token generierst Du unter: [https://scm.commerceinterface.com/access_tokens/](https://scm.commerceinterface.com/access_tokens/)
> Diese trägst Du dann in dem Plugin unter Einstellungen unter dem Marktplatz z.B. DE ein
 
OK, damit der Versand bei Groupon auch bestätigt wird, musst du jetzt noch,...
5. Erstelle eine Ereignisaktion unter "Einstellungen > Aufträge > Ereignisaktionen" für deine Versendeten Bestellungen!
z.B. Statuswechsel auf die 7.0, Filter Herkunft "Groupon" und Aktionen > Plugins "Versandbestätigung an Groupon senden"
 
### Und nu?
 
Na, bei Groupon einen Deal starten natürlich!
Dazu fügst Du einen Deal in deinem Groupon Merchant Center hinzu mit allem was nötig ist und dann,....<br>bekommst Du eine SKU von Groupon!
Diese SKU trägst Du dann in deine zugehörige Variante unter **SKU** und Herkunft Groupon ein!

Hast Du einmal vergessen die SKU einzugeben, können keine Aufträge generiert werden bzw. diese werden ausgelassen. 
Unter Daten > Log > Groupon > generateOrderItemList findest du dann die SKU, die noch mit dem Artikel verknüpft werden muss. 
<img src = "http://i.imgur.com/mL94EW3.png" alt="image 1">

Hier die Anleitung von Groupon:
https://groupon.s3.amazonaws.com/seimages/guide/DealCentre/DE_DealCentre_MerchantManual_DEv2.pdf
 
#### Was Du wissen solltest
 
Wenn Du merkst, dass keine Aufträge angelegt werden, wird das folgende Gründe haben:
1. Du hast auf Groupon gar keine Aufträge
2. Du hast zwar Aufträge da, aber hast die SKU nicht in dem Artikel eingetragen
3. Du hast zwar Aufträge da, aber hast die SKU falsch eingetragen
4. Du hast zwar Aufträge da, aber hast die falsche Herkunft zu deiner SKU eingetragen
5. Kombinationen aus den gründen 1-4 in verschiedenster Form :-p
 
#### Was du noch wissen solltest!
 
Bei Groupon gibt es verschiedene Abrechnungsmodelle.
1. SOR = Groupon vertreibt deine "Neuware" unter dem Namen "Groupon". Dieses ist bei Groupon die normale Abrechnungsmethode.
Wichtig hier: Keine Paketbeilagen, keine von euch bedruckten Kartons verwenden und im Absender muss die Absenderadresse von Groupon stehen.
Mit der Absender Adresse ist aktuell noch ein Problem, da wir dieses "noch" nicht in das Plugin einbauen können, da die Funktion bei Plenty fehlt. Hier müsstet Ihr leider eure Absenderadresse überkleben :-(
Bei dieser Abrechnungsmethode bekommt Ihr dann eine pdf Rechnung von Groupon in euer System gedrückt (aktuell offener Punkt im Plugin).
2. DCO = Hier agiert Ihr als Händler wie üblich. Diese Methode ist z.B. für B-Ware.
Hier dürft Ihr dann als Absender der Ware auch auf dem Paket stehen und Plenty generiert dann eine normale Rechnung.
** Wenn Ihr demnach B-Ware verkaufen wollt, meldet das gleich mit an, damit Groupon euch im Merchant Center für B-Ware freigibt **
 
## Aktuell ist dieses die public Beta Version!
**Wir limitieren diese Version aktuell pro Shop auf eine kostenfreie Nutzung von einem Monat!**
 
Hierfür gibt es verschiedene Gründe und der wichtigste ist, Groupon ist sich aktuell noch nicht sicher, wie unsere Arbeit honoriert wird!
Es kann demnach sein, dass wir das Plugin komplett von Groupon bezahlt bekommen oder wir es später kostenpflichtig einstellen müssen.
 
**Je mehr Shops sich jetzt bei Groupon anmelden, desto höher ist die Wahrscheinlichkeit, dass Groupon das Plugin finanziert!**
 
## License
Alles erlaubt, solange es in deinem System bleibt und du nicht versuchst es zu cracken ;-)
