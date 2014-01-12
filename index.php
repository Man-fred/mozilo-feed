<?php

/* * *************************************************************
 * 
 * Die Plugin-Klasse muß...
 * - von der Klasse "Plugin" erben ("class [PLUGINNAME] extends Plugin")
 * - folgende Funktionen enthalten:
 *   getContent($value)
 *       -> gibt die HTML-Ersetzung der Plugin-Variable zurück
 *   getConfig()
 *       -> gibt die Konfigurationsoptionen als Array zurück
 *   getInfo()
 *       -> gibt die Plugin-Infos als Array zurück
 * 
 * ************************************************************* */

class FEED extends Plugin {

    // Plugin-Info
    var $name = 'FeedCreator';
    var $revision = '6';
    VAR $author = "ManfredB";
    VAR $website = "http://www.bielemeier.de/doku.php?id=themen:mozilocms:feedcreator";
    // 
    var $web = '';
    var $syntax = Null;
    var $linkGB = Null;
    var $linkGalleries = array();

    function getContent($value) {

//return str_replace ('src="/', 'src="/kkkkkkkkkkkkkkkk/', 'pppppppppppppppppppsrc="/jjjjjjjjjjjjjjjj');
        $values = explode(",", $value);

        global $CMS_CONF; // globale Variablen der index.php!
        global $URL_BASE;
        global $specialchars;
        global $CatPage;
        global $PLUGIN_DIR_REL;
        global $CAT_REQUEST;
        global $language;
        global $EXT_PAGE;
        global $EXT_HIDDEN;

        require_once($PLUGIN_DIR_REL . '/FEED/FeedCreator.php'); // Klasse einfuegen 
        $feedCreator = new FeedCreator(
                        'moziloCMS ' . $this->name . ' R.' . $this->revision,
                        $specialchars->rebuildSpecialChars($CMS_CONF->get("websitetitle"), false, false),
                        $specialchars->rebuildSpecialChars($CMS_CONF->get("websitedescription"), false, false),
                        $CatPage->get_hRef($this->settings->get("feedCategory"), $this->settings->get("feedPage")),
                        $CatPage->get_hRef($this->settings->get("feedCategory"), $this->settings->get("rss2Page")));

        $this->web = $feedCreator->web;

        if (is_array($values)) {
            $feed = !$values[0];
            if ($values[0] == 'LINK' || $values[0] == 'ATOMHEADER') {
                return $feedCreator->header('ATOM');
            } elseif ($values[0] == 'RSS2HEADER') {
                return $feedCreator->header('RSS2');
            } elseif ($values[0] == 'RSS2') {
                $feed = TRUE;
                $feedCreator->setType('RSS2');
            } else if ($values[0] == 'SITEMAP') {
                return $this->xmlSitemap();
            } else if ($values[0] && $this->settings->get("showteaser") == "true") {
                $content = implode(',', $values);
                return $content;
            }
        } else {
            $feed = TRUE;
        }
        if ($feed) {
            @require_once($PLUGIN_DIR_REL . '/FEED/CacheMan.php'); // Klasse einfuegen 
            $cacheAktiv = ($this->settings->get("cache") > 0);
            if ($cacheAktiv) {
                $cacheMan = new CacheMan();
                $cacheMan->_setGuilty($this->settings->get("cache")); // Optional: Gueltigkeit 
                $cacheMan->_setFileName('feed');
            }
            if (!$cacheAktiv || $cacheMan->_startCaching() === FALSE) {
                // wenn caching noch nicht vorhanden oder veraltet ist 
                $this->include_pages = array($EXT_PAGE);
                if ($this->settings->get("showhiddenpages") == "true")
                    $this->include_pages = array($EXT_PAGE, $EXT_HIDDEN);

                $latestchanged = $this->getChangedContent(true, $this->settings->get("showmoziloGB") == "true");
                krsort($latestchanged);
                $feedCreator->startFeed();

                //the iteration should start from here
                //also some additional elemens can also be skipped that all up to your requirements
                //add item elements to the feed eg elements inside <item> -Elementshere- </item>
                $i = 0;
                foreach ($latestchanged as $key => $cat) {
                    $feedCreator->addItem(
                            $specialchars->rebuildSpecialChars($cat['page'], false, false), $cat['link'], $cat['time'], $cat['author'], $cat['content']
                    );
                    if ($i++ >= $this->settings->get("anzahl"))
                        break;
                }
                // the database table iteration should end here
                // hier wird nur wirklich ausgegeben, wenn cache = 0,
                // sonst nur in den Zwischenspeicher geschrieben
                echo $feedCreator->endFeed();
            }
            if ($cacheAktiv) {
                $cacheMan->_endCaching();
                echo $cacheMan->cachedFile;
            }
            // Ausgabe hier abbrechen!
            exit();
        }
    }

// function getContent

    /*     * *************************************************************
     * 
     * Gibt die Konfigurationsoptionen als Array zurück.
     * 
     * ************************************************************* */
    function getConfig() {
        global $ADMIN_CONF;
        $language = $ADMIN_CONF->get("language");

        $config['deDE'] = array();
        $config['deDE']['anzahl'] = array(
            "type" => "text",
            "description" => 'Anzahl der zu erzeugenden Feed-Einträge',
            "maxlength" => "10",
            "size" => "10"
        );
        $config['deDE']['cache'] = array(
            "type" => "text",
            "description" => 'Dauer der Cache-Gültigkeit in Sekunden',
            "maxlength" => "10",
            "size" => "10",
            "default" => 450
        );
        $config['deDE']['hiddenCategory'] = array(
            "type" => "text",
            "description" => 'Unterdrückung von Kategorien, z.B. der Feed-Kategorie, Trennung der Kategorien durch Komma',
            "maxlength" => "100",
            "size" => "30"
        );
        $config['deDE']['feedCategory'] = array(
            "type" => "text",
            "description" => 'Feed-Kategorie',
            "maxlength" => "100",
            "size" => "30"
        );
        $config['deDE']['feedPage'] = array(
            "type" => "text",
            "description" => 'Feed-Inhaltsseite',
            "maxlength" => "100",
            "size" => "30"
        );
        $config['deDE']['showteaser'] = array(
            "type" => "checkbox",
            "description" => "Zusammenfassung auf Inhaltsseiten anzeigen"
        );
        $config['deDE']['showhiddenpages'] = array(
            "type" => "checkbox",
            "description" => "Versteckte Inhaltsseiten auswerten"
        );
        $config['deDE']['showmoziloGB'] = array(
            "type" => "checkbox",
            "description" => "Gästebucheinträge vom moziloGB-Plugin auswerten"
        );
        $config['deDE']['showmoziloGBTitel'] = array(
            "type" => "text",
            "description" => 'Titel zum Gästebucheintrag',
            "maxlength" => "100",
            "size" => "30"
        );
        $config['deDE']['gbCategory'] = array(
            "type" => "text",
            "description" => 'Gästebuch-Kategorie',
            "maxlength" => "100",
            "size" => "30"
        );
        $config['deDE']['gbPage'] = array(
            "type" => "text",
            "description" => 'Gästebuch-Inhaltsseite',
            "maxlength" => "100",
            "size" => "30"
        );
        /*
          $config['deDE']['showmoziloGBLink'] = array(
          "type" => "text",
          "description" => 'Link zur Gästebuchseite z.B "index.php?cat=Gästebuch',
          "maxlength" => "100",
          "size" => "30"
          ); */

        // Das gesamte Array zurückgeben
        if (isset($config[$language])) {
            return $config[$language];
        } else {
            return $config['deDE'];
        }
    }

// function getConfig


    /*     * *************************************************************
     * 
     * Gibt die Plugin-Infos als Array zurück - in dieser 
     * Reihenfolge:
     *   - Name und Version des Plugins
     *   - für moziloCMS-Version
     *   - Kurzbeschreibung
     *   - Name des Autors
     *   - Download-URL
     *   - Platzhalter für die Selectbox
     * 
     * ************************************************************* */

    function getInfo() {
        global $ADMIN_CONF;
        $language = $ADMIN_CONF->get("language");
        # nur eine Sprache ---------------------------------
        $info = array(
            // Plugin-Name + Version
            '<b>' . $this->name . '</b> Revision ' . $this->revision,
            // moziloCMS-Version
            "1.12 beta4",
            // Kurzbeschreibung nur <span> und <br /> sind erlaubt
            "FeedCreator erzeugt eine XML-Datei im Atom-Format.<br />" .
            "{FEED} sollte als einziger Eintrag auf der Inhaltsseite stehen, die als Feed-Link benutzt wird. Am besten auf einer versteckten Inhaltsseite plazieren. " .
            "Nach {FEED} wird die Ausgabe abgebrochen und Text vor {FEED} macht die Ausgabedatei ungültig!<br /><br />" .
            "{FEED|LINK} kann im Layout, template.html, im Header eingefügt werden. Dann wird der Feed automatisch erkannt.<br /><br />" .
            "Jede Inhaltsseite kann eine Zusammenfassung enthalten:<br />" .
            "{FEED|Zusammenfassung für den Feed-Eintrag, wahlweise keine Ausgabe auf der normalen Seite}<br /><br />" .
            "{FEED|SITEMAP} erzeugt im Stammverzeichnis eine sitemap.xml. Am besten auf einer versteckten Inhaltsseite plazieren und nach wesentlichen Änderungen aufrufen.",
            // Name des Autors
            $this->author,
            // Download-URL
            $this->website,
            // Platzhalter für die Selectbox in der Editieransicht 
            // - ist das Array leer, erscheint das Plugin nicht in der Selectbox
            array(
                '{FEED}' => 'Feed auf dieser Inhaltsseite erzeugen',
                '{FEED|LINK}' => 'Link auf den Feed im Header der Template-Datei einfügen',
                '{FEED|SITEMAP}' => 'Der Aufruf erzeugt im Stammverzeichnis eine sitemap.xml',
                '{FEED|Text der Zusammenfassung}' => 'Feed mit Zusammenfassung'
            )
        );
        return $info;
    }

    // function getInfo
    // ------------------------------------------------------------------------------
    // Rueckgabe eines Arrays, bestehend aus:
    // - Name der zuletzt geaenderten Inhaltsseite
    // - kompletter Link auf diese Inhaltsseite  
    // - formatiertes Datum der letzten Aenderung
    // ------------------------------------------------------------------------------
    function getChangedContent($readCms, $readGb, $readGallery=true) {
        global $CMS_CONF, $BASE_DIR_CMS, $BASE_DIR, $GALLERIES_DIR_NAME; // globale Variablen der index.php!
        global $CHARSET;
        global $language;
        global $CatPage;
        global $PLUGIN_DIR_REL;
        global $specialchars;

        // Kategorien-Verzeichnis einlesen
        $hiddenCategory = explode(",", rtrim($this->settings->get("hiddenCategory")));
        $categoriesarray = $CatPage->get_CatArray(false, false, $this->include_pages);
        $latestchanged = array();
        $this->syntax = new Syntax();

        if ($readCms) {
            // Alle Kategorien durchsuchen
            foreach ($categoriesarray as $currentcategory) {
                if (!in_array($currentcategory, $hiddenCategory)) {
                    $contentarray = $CatPage->get_PageArray($currentcategory, $this->include_pages, true);
                    // Alle Inhaltsseiten durchsuchen
                    foreach ($contentarray as $currentcontent) {
                        $content = $CatPage->get_PageContent($currentcategory, $currentcontent);
                        $link = $CatPage->get_hRef($currentcategory, $currentcontent);
                        if ($readGb && strstr($content, '{moziloGB')) {
                            $this->linkGB = $link;
                        }
                        if ($readGallery && preg_match_all('/\{(?:SlimBox|Gallery)\|(.*?)[\},]/msu', $content, $match)) {
                            //$match = strst
                            foreach ($match[1] as $value) {
                                $this->linkGalleries[rawurlencode($value)] = $link;
                            }
                        }
                        if (preg_match('/\{FEED\|(.*?)\}/msu', $content, $match)) {
                            $content = $match[1];
                        } else {
                            //$parts = explode("\n", wordwrap(substr($content, 0, 140), 120, "\n"));
                            $content = substr($content, 0, 500); //$parts[0];
                        }
                        //mozilo-Syntax, aber keine Plugins
                        $content = $this->syntax->convertContent($content, $currentcategory, FALSE);
                        // interne Bildlinks extern verfügbar machen
                        $content = str_replace('src="/', 'src="' . $this->web . '/', $content);
                        $latestchanged[$CatPage->get_Time($currentcategory, $currentcontent) . $currentcategory . $currentcontent] =
                                array("cat" => $currentcategory,
                                    "page" => $currentcontent,
                                    "link" => $link,
                                    "time" => $CatPage->get_Time($currentcategory, $currentcontent),
                                    "author" => $specialchars->rebuildSpecialChars($CMS_CONF->get("websitetitle"), false, false),
                                    "content" => $content);
                    }
                }
            }
        }
        if ($readGb && $this->linkGB) {
            //$this->entries = array();
            $filecontent = file($PLUGIN_DIR_REL . '/moziloGB/data/gb.txt');
            foreach ($filecontent as $line) {
                $lineArray = explode("|", rtrim($line));
                //mozilo-Syntax, aber keine Plugins
                $content = $this->syntax->convertContent($lineArray[6], 'Feed', FALSE);
// aus moziloGB kopiert:		
                // HTML-Tags komplett entfernen
		//$content = strip_tags($content);
		/* $content = preg_replace("/<[\/\!]*?[^<>]*
?>/Usi","", $content); */
		//$content = htmlentities($content,ENT_COMPAT,$CHARSET);
		// Zeilenwexxel und Pipe
		$content = preg_replace("/\[br\]/", " ", $content);
		$content = preg_replace("/\[pipe\]/", " ", $content);
// aus moziloGB kopiert:		
                // interne Bildlinks extern verfügbar machen
                $content = str_replace('src="/', 'src="' . $this->web . '/', $content);
                $latestchanged[$lineArray[0] . 'moziloGB'] =
                        array(
                            "cat" => $this->settings->get("gbCategory"),
                            "link" => $this->linkGB, //$CatPage->get_hRef($this->settings->get("gbCategory"), $this->settings->get("gbPage")),
                            "page" => $this->settings->get("showmoziloGBTitel"),
                            "time" => $lineArray[0],
                            "author" => $lineArray[3],
                            "content" => $content
                );
            }
        }
        if ($readGallery && $this->linkGalleries) {
            //print_r($this->linkGalleries);
            //$this->entries = array();
            require_once ($BASE_DIR_CMS . 'GalleryClass.php');
            $galleryClass = new GalleryClass();
            $galleryClass->initial_Galleries($galleryClass->GalleriesArray, NULL, NULL, TRUE);
            //print_r($galleryClass->GalleriesArray);
            //print_r($galleryClass->GalleryArray);
            //print_r($this->linkGalleries);
            foreach ($galleryClass->GalleryArray as $galleryName => $currentgallery) {
                if (array_key_exists($galleryName, $this->linkGalleries)) {
                    $galleryTime = array();
                    foreach ($currentgallery as $pictureName => $picture) {
                        $time = filemtime($BASE_DIR . $GALLERIES_DIR_NAME . '/' . $galleryName . '/' . $pictureName);
                        $galleryTime[$time . $pictureName] = array(
                            "picture" => $pictureName,
                            "time" => $time,
                            "description" => $picture['description'] ? $picture['description'] : rawurldecode($pictureName)
                        );
                    }
                    krsort($galleryTime);
                    //print_r($galleryTime);
                    $content = '';
                    $description = '';
                    $time = 0;
                    $i = 0;
                    foreach ($galleryTime as $value) {
                        $i++;
                        if ($i > 1) {
                            $description .= ', ';
                        }
                        $content .= $value['picture'] . ', ';
                        $description .= $value['description'];
                        if ($time < $value['time'])
                            $time = $value['time'];
                        if ($i > 2)
                            break;
                    }
                    if ($i > 2) {
                        $description .= ' ... ';
                    }
                    $latestchanged[$time . $galleryName] =
                            array(
                                "cat" => 'Galerie ' . $galleryName, //$this->settings->get("galleryCategory"),
                                "link" => $this->linkGalleries[$galleryName], //$CatPage->get_hRef($this->settings->get("gbCategory"), $this->settings->get("gbPage")),
                                "page" => 'Galerie ' . $galleryName,
                                "time" => $time,
                                "author" => '',
                                "content" => $description
                    );
                } else {
                    //print_r($galleryName);
                }
            }
            //print_r($latestchanged);
            /*
              $filecontent = file($PLUGIN_DIR_REL . '/moziloGB/data/gb.txt');
              foreach ($filecontent as $line) {
              $lineArray = explode("|", rtrim($line));
              $latestchanged[$lineArray[0] . 'moziloGB'] =
              array(
              "cat" => $this->settings->get("gbCategory"),
              "link" => '', //$CatPage->get_hRef($this->settings->get("gbCategory"), $this->settings->get("gbPage")),
              "page" => $this->settings->get("showmoziloGBTitel"),
              "time" => $lineArray[0],
              "author" => $lineArray[3],
              "content" => $lineArray[6]
              );
              }
             */
        }
        return $latestchanged;
    }

    function xmlSitemap() {
        global $EXT_PAGE;
        global $EXT_HIDDEN;
        global $BASE_DIR;
        global $URL_BASE;

        if (!defined('DATE_XMLSITEMAP'))
            define('DATE_XMLSITEMAP', "Y-m-d");
        $this->include_pages = array($EXT_PAGE);
        if ($this->settings->get("showhiddenpages") == "true")
            $this->include_pages = array($EXT_PAGE, $EXT_HIDDEN);

        $latestchanged = $this->getChangedContent(TRUE, FALSE);
        $feedcontent = '<?xml version="1.0" encoding="UTF-8"?>
<!-- generator="' . $this->name . ' ' . $this->revision . ' mozilo" -->
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';

//the iteration should start from here
//also some additional elemens can also be skipped that all up to your requirements
//add item elements to the feed eg elements inside <item> -Elementshere- </item>
        $i = 0;
        foreach ($latestchanged as $key => $cat) {
            $feedcontent .=
                    '   <url>
      <loc>' . $this->web . $cat['link'] . '</loc>
      <lastmod>' . date(DATE_XMLSITEMAP, $cat['time']) . '</lastmod>
   </url>
';
            //<changefreq>monthly</changefreq>
            //<priority>0.8</priority>
        }
//the database table iteration should end here
        $feedcontent .= '</urlset>';
        // Datei zum Schreiben &ouml;ffnen
        if ($this->settings->get("test")) {
            $fp = @fopen($BASE_DIR . $this->settings->get("test") . 'sitemap.xml', 'w');
        } else {
            $fp = @fopen($BASE_DIR . 'sitemap.xml', 'w');
        }
        if ($fp) {
            // Inhalt in die Datei schreiben
            @fwrite($fp, $feedcontent);
            // Datei schliessen
            @fclose($fp);
        }
        return 'XML-Sitemap angelegt unter: ' . $this->web . $URL_BASE . 'sitemap.xml<br /><br />' .
                'Diese Datei sollte den Suchmaschinen bekannt gemacht werden.<br /><br />';
    }

}

// class FEED
?>