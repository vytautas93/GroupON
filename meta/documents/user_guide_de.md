# Groupon Marketplace Plugin
 
zunächst einmal, was ist Groupon?
 
Antwort: Ein Marktplatz ;-)
 
,...Sprechen Sie mit Ihren Produkten eine riesige Zielgruppe an. Groupon Goods ist ein One-Stop-Shop für den täglichen Bedarf mit Markenprodukten und einmaligen, unschlagbaren Angeboten.
Nutzen Sie Online-, E-Mail- und Telefonwerbung. Mit Groupon Goods werden Ihre Produkte für mehr als 15 Millionen aktive Kunden zugänglich.
 
Wie melde ich mich dort an?
https://www.groupon.de/merchant/marketing-solutions/goods -> **Los geht´s**
 
## Was muss ich machen, dass alles sauber läuft und ich ich im Forum keine Beiträge schreiben muss?
 
Ganz eifach!
1. Du registrierst dich bei Groupon (wie oben schon beschrieben)
2. Du bekommst von Groupon einen Account wo Du die Händler ID und den Token einsehen kannst
> Diese trägst Du dann in dem Plugin unter Einstellungen unter dem Marktplatz z.B. DE ein
3. Du fügst unter "Einstellungen > Aufträge > Auftragsherkunft" Groupon hinzu und trägst die ID im Plugin unter Settings > Herkunft ID ein
4. Jetzt wählst Du eine Zahlart aus, mit der die Bestellungen importiert werden sollen, z.B. 1700 für Coupon (aber aktivieren vorher) und trägst diese im Plugin unter Settings > Zahlart ID ein
 
OK, damit der Versand bei Groupon auch bestätigt wird, musst du jetzt noch,...
5. Erstelle eine Ereignisaktion unter "Einstellungen > Aufträge > Ereignisaktionen" für deine Versendeten Bestellungen!
z.B. Statuswechsel auf die 7.0, Filter Herkunft "Groupon" und Aktionen Plugins "Groupon Versandbestätigung senden"
 
### Und nu?
 
Na, bei Groupon einen Deal starten natürlich!
Dazu fügst Du einen Deal in deinem Groupon Merchant Center hinzu mit allem was nötig ist und dann,....<br>bekommst Du eine SKU von Groupon!
Diese SKU trägst Du dann in deine zugehörige Variante unter **SKU** ein! Wichtig, die Herkunft muss deine oben unter 3 gewählte Auftragsherkunft **"Groupon"** sein!
 
#### Was Du wissen solltest
 
Wenn Du merkst, dass keine Aufträge angelegt werden, wird das folgende Gründe haben:
1. Du hast auf Groupon gar keine Aufträge
2. Du hast zwar Aufträge da, aber hast die SKU nicht in dem Artikel eingetragen
3. Du hast zwar Aufträge da, aber hast die SKU falsch eingetragen
4. Du hast zwar Aufträge da, aber hast die falsche Herkunft zu deiner SKU eingetragen
5. Kombinationen aus den gründen 1-4 in verschiedenster Form :-p
 
## Aktuell ist dieses die public Beta Version!
**Wir limitieren diese Version aktuell pro Shop auf eine kostenfreie Nutzung von einem Monat!**
 
Hierfür gibt es verschiedene Gründe und der wichtigste ist, Groupon ist sich aktuell noch nicht sicher, wie unsere Arbeit honoriert wird!
Es kann demnach sein, dass wir das Plugin komplett von Groupon bezahlt bekommen oder wir es später kostenpflichtig einstellen müssen.
 
**Je mehr Shops sich jetzt bei Groupon anmelden, desto höher ist die Wahrscheinlichkeit, dass Groupon das Plugin finanziert!**
 
## License
Alles erlaubt, solange es in deinem System bleibt und du nicht versuchst es zu cracken ;-)