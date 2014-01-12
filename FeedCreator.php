<?php

/**
 * Description of FeedCreator
 *
 * @author ManfredB
 */
class FeedCreator {

    var $type = 'ATOM'; // historisch, Vorbelegung
    var $websitetitle = '';
    var $websitesubtitle = '';
    var $feedLink = '';
    var $web;
    var $generator;
    var $feedcontent = '';

    function FeedCreator($generator, $websitetitle, $websitesubtitle, $feedLink) {
// nicht alle PHP-Versionen haben dieses Define
        if (!defined('DATE_ATOM'))
            define('DATE_ATOM', "Y-m-d\TH:i:sP");
        if (!defined('DATE_XMLSITEMAP'))
            define('DATE_XMLSITEMAP', "Y-m-d");

        $this->generator = $generator;
        $this->websitetitle = $websitetitle;
        $this->websitesubtitle = $websitesubtitle;
        $this->feedLink = $feedLink;

        if ($_SERVER['SERVER_PORT'] == 443) {
            $this->web = 'https://' . $_SERVER['SERVER_NAME'];
        } else {
            $this->web = 'http://' . $_SERVER['SERVER_NAME'];
        }
    }

    function header($type) {
        $this->type = $type;
        switch ($this->type) {
            case 'ATOM' :
                return '<link rel="alternate" type="application/atom+xml" title="' . $this->websitetitle . ' ATOM-Feed" href="' . $this->feedLink . '">';
            case 'RSS2' :
                return '<link rel="alternate" type="application/rss+xml" title="' . $this->websitetitle . ' RSS2-Feed" href="' . $this->rss2Link . '" />';
        }
    }

    function setType($type) {
        $this->type = $type;
    }

    function startFeed() {
        if ($this->type == 'ATOM') {
            $this->feedcontent = '<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
   <title>' . $this->websitetitle . '</title>
   <subtitle>' . $this->websitesubtitle . '</subtitle>
   <link rel="alternate" type="text/html" href="' . $this->web . '/"/>
   <id>' . $this->web . '</id>
   <updated>' . date(DATE_ATOM, time()) . '</updated>
   <generator>' . $this->generator . '</generator>
<link rel="self" type="application/atom+xml" href="' . $this->web . $this->feedLink . '" />
';
        } elseif ($this->type == 'RSS2') {
            $this->feedcontent = '<?xml version="1.0" encoding="UTF-8" ?>
<?xml-stylesheet href="/resources/xsl/rss2.jsp" type="text/xsl"?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
<title>' . $this->websitetitle . '</title>
<link>' . $this->web . '</link>
<description>' . $this->websitesubtitle . '</description>
<language>de</language>
<copyright>' . $this->websitetitle . '</copyright>
<lastBuildDate>Sat, 05 May 2012 23:28:57 +0200</lastBuildDate>
<docs>http://blogs.law.harvard.edu/tech/rss</docs>
<ttl>10</ttl>
';
            /*
              <updated>' . date(DATE_ATOM, time()) . '</updated>
              <generator>'.$this->generator . '</generator>
              <link rel="self" type="application/atom+xml" href="' . $this->web . $feedLink . '" />
              ';
             */
        }
    }

    function addItem($title, $link, $time, $author, $content) {
        if ($this->type == 'ATOM') {
            $this->feedcontent .=
                    '<entry>
   <title>' . $title . '</title>
   <link rel="alternate" type="text/html" href="' . $this->web . $link . '"/>
   <published>' . date(DATE_ATOM, $time) . '</published>
   <updated>' . date(DATE_ATOM, $time) . '</updated>
   <id>' . $time/*$this->web . $link */. '</id>
   <author>
       <name>' . $author . '</name>
   </author>
   <summary type="html"><![CDATA[ ' . $content . ' ]]></summary>
   <content type="xhtml" xml:base="http://example.org/">
       <div xmlns="http://www.w3.org/1999/xhtml">
            ' . $content . '
       </div>
   </content>
</entry>
';
        } elseif ($this->type == 'RSS2') {
            $this->feedcontent .=
                    '<item>
<title>' . $title . '</title>
<link>' . $this->web . $link . '</link>
<pubDate>Sat, 05 May 2012 21:35:58 +0200</pubDate>
<content:encoded>
<![CDATA[ ' . $content . ' ]]>
</content:encoded>
<description>'.$content.'</description>
<guid>' . $this->web . $link . '</guid>
</item>
';
//   <published>' . date(DATE_ATOM, $time) . '</published>
//   <updated>' . date(DATE_ATOM, $time) . '</updated>
        }
    }

    function endFeed() {
        if ($this->type == 'ATOM') {
            $feedcontent = $this->feedcontent . '</feed>';
        } elseif ($this->type == 'RSS2') {
            $feedcontent = $this->feedcontent . '</channel>
</rss>';
        }
        $this->feedcontent = '';
        return $feedcontent;
    }

}

?>
