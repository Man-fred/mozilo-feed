<?php

/* * ****************
 *
 *    @class CacheMan
 *    @author Heiko Ramaker, www.web-skripte.de
 *    @version 1.0
 *
 * ***************** */

class CacheMan {

    var $CacheDir = '/FEED/cache/'; // Cache-Verzeichnis
    var $TimeInCache = "600"; // Gueltigkeit (in Sekunden)
    var $FileName;
    var $cachedFile = '';
    var $addDelimiter = FALSE; // Begrenzer vor und nach dem Content
    var $host = "http://deine-domain.de"; // URL des Projekts
    var $uri;
    var $ActPage; // Aktuell aufgerufene Seite
    var $FileExt = "txt"; // Dateiendung der gecachten Datei
    var $loadFromCache = FALSE; // Cache deaktivieren

    function CacheMan() {
        global $PLUGIN_DIR_REL;

        $this->CacheDir = $PLUGIN_DIR_REL . '/FEED/cache/';
        $this->_setUri();
        $this->_setActPage();
        $this->FileName = $this->CacheDir . $this->TimeInCache . '_' . md5($this->ActPage) . '.' . $this->FileExt;
    }

    /*
      Caching starten
     */

    function _startCaching() {
        $this->_checkGuilty();
        // Es existiert noch eine gueltige Datei im Cache
        if ($this->loadFromCache === TRUE) {
            //@readfile($this->FileName);
            $this->cachedFile = @file_get_contents($this->FileName);
            return TRUE;
        }
        // keine gueltige Datei vorhanden, neues Caching starten
        else {
            @ob_start();//"ob_gzhandler");
            return FALSE;
        }
    }

    function _nowCaching() {
        if ($this->loadFromCache === FALSE) {
            $this->cachedFile = ob_get_contents();//preg_replace("(\r\n|\n|\r)", "", ob_get_contents());
            // Datei zum Schreiben &ouml;ffnen
            $fp = @fopen($this->FileName, 'w');
            if ($fp) {
                // Inhalt in die Datei schreiben
                $cacheThisText = "";
                if ($this->addDelimiter == TRUE) {
                    $cacheThisText .= "<!-- Start " . $this->TimeInCache . " web-skripte.de Delimiter -->";
                }
                $cacheThisText .= $this->cachedFile;
                if ($this->addDelimiter == TRUE) {
                    $cacheThisText .= "<!-- End " . $this->TimeInCache . " web-skripte.de Delimiter -->";
                }
                @fwrite($fp, $cacheThisText);
                // Datei schliessen
                @fclose($fp);
            }
            return TRUE;
        }
    }

    /*
      Pruefen ob die Datei im Cache liegt und gueltig ist
     */

    function _checkCache() {
        if (@file_exists($this->FileName)) {
            $this->_clear();
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /*
      Ausgeben wann die Datei zuletzt aktualisiert wurde (UNIX-Timestamp)
     */

    function _lastChanged() {
        // Datei existiert im Cache
        if ($this->_checkCache() === TRUE) {
            $timestamp = @filemtime($this->FileName);
            $this->_clear();
            return $timestamp;
        }
        // Datei existiert nicht im Cache
        else {
            return FALSE;
        }
    }

    /*
      Pruefen, ob die gecachte Datei noch gueltig ist
     */

    function _checkGuilty() {
        // Datei existiert, also pruefen ob sie noch gueltig ist
        if ($this->_checkCache() !== FALSE) {
            // Datei ist noch gueltig
            if (time() - $this->_lastChanged() < $this->TimeInCache) {
                $this->loadFromCache = TRUE;
            }
            // Datei im Cache ist zu alt
            else {
                $this->loadFromCache = FALSE;
            }
        }
        // Datei existiert nicht mehr, kann also auch nicht mehr gueltig sein
        else {
            $this->loadFromCache = FALSE;
            return FALSE;
        }
    }

    /*
      Caching beenden
     */

    function _endCaching() {
        if ($this->loadFromCache === FALSE) {
            $this->_nowCaching();
            //@ob_end_flush();
            @ob_end_clean();
            //return $outString;
        }
    }

    /*
      Dateiname fuer gecachte Version waehlen
     */

    function _setFileName($cname) {
        $this->FileName = $this->CacheDir . $this->TimeInCache . '_' . md5($this->ActPage . $cname) . '.' . $this->FileExt;
    }

    /*
      Gueltigkeitsdauer setzen in Sekunden
     */

    function _setGuilty($ctime) {
        $this->TimeInCache = $ctime;
    }

    /*
      Aktuelle Seiten-URL
     */

    function _setActPage() {
        $this->ActPage = $this->host . $this->uri;
    }

    /*
      REQUEST_URI setzen
     */

    function _setUri() {
        $this->uri = $_SERVER['REQUEST_URI'];
    }

    /*
      Status Cache loeschen
     */

    function _clear() {
        @clearstatcache();
    }

    function _setCacheDir($cdir) {
        $this->CacheDir = $cdir;
    }

}

?>
