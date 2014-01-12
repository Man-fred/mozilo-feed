mozilo-feed
===========

### Beschreibung
FeedCreator erzeugt einen ATOM-Feed aus den letzten Änderungen einer mit mozilo erstellten Website, wenn gewünscht auch aus Einträgen im Gästebuch moziloGB. Zusätzlich zum Seitentitel kann eine Zusammenfassung auf jeder Inhaltsseite angegeben werden, die bei der Seitenanzeige entweder unterdrückt oder angezeigt wird.

Einzelne Kategorien können im Backend aus dem Feed herausgenommen werden.

### Benutzung
**{FEED}** als einziger Inhalt einer Inhaltsseite erzeugt den Atom-Feed. Getestet mit Firefox, Internet Explorer und Crome. Läßt sich auch in Google-Reader zufügen.

**{FEED|LINK}** kann im Layout in der template.html im Header verwendet werden und erzeugt dann einen Link-Eintrag. Dadurch erscheint in den Browsern das RSS-Zeichen zum automatischen abonnieren.

**{FEED|...Text zur Beschreibung der Inhaltsseite...}** kann auf jeder Inhaltsseite plaziert werden und erscheint dann als Text im Feed. Im Backend kann gesteuert werden, ob der Text auch auf der Inhaltsseite erscheinen soll oder nicht.

**{FEED|SITEMAP}** erzeugt im moziloCMS-Verzeichnis auf dem Webserver eine Datei mit Namen "sitemap.xml". Diese Datei kann bei den Suchmaschinen bekannt gemacht werden. Informationen dazu unter http://www.sitemaps.org

### Einschränkung
Mozilo-Plugins dürfen nicht innerhalb der Zusammenfassung im Feed genutzt werden.

### Features im Hintergrund
Damit das Auswerten aller Seiten und aller Gästebucheinträge nicht ständig neu gemacht werden muss, habe ich einen Cachemanager für den FeedCreator aktiviert. Der ist jetzt so an mozilo angepasst, dass er jetzt prinzipiell überall als PHP-Klasse eingebunden werden kann. Bei Interesse gerne melden.
