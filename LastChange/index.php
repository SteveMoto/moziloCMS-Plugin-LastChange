<?php if(!defined('IS_CMS')) die();

/**
 * Plugin:   Plugin für moziloCMS, das die letzte Änderungen zurückgibt
 * @author:  mozilo, SteveMoto
 * @version: Revision 140
 * @license: GPL
 *
**/

class LastChange extends Plugin {

  var $include_pages = array();
  var $dateformat = NULL;


  function getContent($value) {

    global $language;

    $this->include_pages = array(EXT_PAGE);
    if($this->settings->get("showhiddenpagesinlastchanged") == "true")
       $this->include_pages = array(EXT_PAGE,EXT_HIDDEN);
    $messagetext = $language->getLanguageValue("message_lastchange_0");
    if($this->settings->get("messagetext"))
       $messagetext = $this->settings->get("messagetext");
    $this->dateformat = $language->getLanguageValue("_dateformat_0");
    if($this->settings->get("date"))
       $this->dateformat = $this->settings->get("date");
    if($value == "text") {
        return $messagetext;
    } elseif($value == "page") {
        $lastchangeinfo = $this->getLastChangedContentPageAndDateLAST();
         return $lastchangeinfo[0];
    } elseif($value == "pagelink") {
        $lastchangeinfo = $this->getLastChangedContentPageAndDateLAST();
         return $lastchangeinfo[1];
    } elseif($value == "date") {
        $lastchangeinfo = $this->getLastChangedContentPageAndDateLAST();
         return $lastchangeinfo[2];
    } else {
        $lastchangeinfo = $this->getLastChangedContentPageAndDateLAST();
         return $messagetext." ".$lastchangeinfo[1]." (".$lastchangeinfo[2].")";
    }
    return "";

  } // function getContent


  function getConfig() {
    global $ADMIN_CONF;
    $language = $ADMIN_CONF->get("language");

    $config['deDE'] = array();
    $config['deDE']['messagetext']  = array(
      "type" => "text",
      "description" => 'Eigener Text für "Letzte Änderung:"',
      "maxlength" => "100"
      );
    $config['deDE']['date']  = array(
      "type" => "text",
      "description" => "Eigenes Datumsformat",
      "maxlength" => "100"
      );
    $config['deDE']['showhiddenpagesinlastchanged'] = array(
      "type" => "checkbox",
      "description" => "Versteckte Inhaltsseiten mit einbeziehen"
      );

    // Nicht vergessen: Das gesamte Array zurückgeben
    if(isset($config[$language])) {
       return $config[$language];
    } else {
       return $config['deDE'];
    }

  } // function getConfig


  function getLastChangedContentPageAndDateLAST() {
  // ------------------------------------------------------------------------------
  // Rueckgabe eines Arrays, bestehend aus:
  // - Name der zuletzt geaenderten Inhaltsseite
  // - kompletter Link auf diese Inhaltsseite  
  // - formatiertes Datum der letzten Aenderung
  // ------------------------------------------------------------------------------

    global $language;
    global $CatPage;

    $latestchanged = array("cat" => "catname", "page" => "pagename", "time" => 0);
    $currentdir = $CatPage->get_CatArray(false, false, $this->include_pages);
    foreach($currentdir as $cat) {
      $latestofdir = $this->getLastChangeOfCatLAST($cat);
      if ($latestofdir['time'] > $latestchanged['time']) {
          $latestchanged['cat'] = $cat;
          $latestchanged['page'] = $latestofdir['page'];
          $latestchanged['time'] = $latestofdir['time'];
      }
    }
    $lastchangedpage = $CatPage->get_HrefText($latestchanged['cat'],$latestchanged['page']);
    $url = $CatPage->get_Href($latestchanged['cat'],$latestchanged['page']);
    $titel = $language->getLanguageHTML("tooltip_link_page_2", $lastchangedpage, $CatPage->get_HrefText($latestchanged['cat'],false));
    $linktolastchangedpage = $CatPage->create_LinkTag($url,$lastchangedpage,false,$titel,false,"lastchangelink");
    $lastchangedate = strftime($this->dateformat, $latestchanged['time']);
    return array($lastchangedpage, $linktolastchangedpage,$lastchangedate);

  } // function getLastChangedContentPageAndDateLAST


  function getLastChangeOfCatLAST($cat) {
  // ------------------------------------------------------------------------------
  // Einlesen eines Kategorie-Verzeichnisses, Rueckgabe der zuletzt geaenderten Datei
  // ------------------------------------------------------------------------------

    global $CatPage;

    $latestchanged = array("page" => "pagename", "time" => 0);
    $currentdir = $CatPage->get_PageArray($cat,$this->include_pages,true);
    foreach($currentdir as $page) {
      if ($CatPage->get_Time($cat,$page) > $latestchanged['time']) {
          $latestchanged['page'] = $page;
          $latestchanged['time'] = $CatPage->get_Time($cat,$page);
      }
    }
      return $latestchanged;

  } // function getLastChangeOfCatLAST


  function getInfo() {

    global $ADMIN_CONF;
    $adminlanguage = $ADMIN_CONF->get("language");

    $info['deDE'] = array(
      // Plugin-Name
      "<b>LastChange</b> \$Revision: 140 $",
      // CMS-Version
      "2.0",
      // Kurzbeschreibung
      'Zeigt die letzte Änderung an.<br />
      <br />
      <span style="font-weight:bold;">Nutzung:</span><br />
      {LastChange} gibt etwas aus wie: "Letzte Änderung: Willkommen (22.01.2021, 09:07:20)"<br />
      {LastChange|text} gibt etwas aus wie: "Letzte Änderung"<br />
      {LastChange|page} gibt etwas aus wie: "Willkommen"<br />
      {LastChange|pagelink} gibt etwas aus wie: "Willkommen" (mit Link auf die Inhaltsseite)<br />
      {LastChange|date} gibt etwas aus wie: "(22.01.2021, 09:07:20)"<br />
      <br />
      <span style="font-weight:bold;">Konfiguration:</span><br />
      Das Plugin bezieht den Text "Letzte Änderung" und das Datumsformat aus der CMS-Sprachdatei; man kann beides aber auch selbst angeben. Dabei orientiert sich das Datumsformat an der PHP-Funktion strftime().',
      // Name des Autors
      "mozilo, SteveMoto",
      // Download-URL
      "",
      array(
        '{LastChange}' => 'Letzte Änderung mit Link und Datum',
        '{LastChange|text}' => 'Text "Letzte Änderung:"',
        '{LastChange|page}' => 'Name der zuletzt geänderten Inhaltsseite',
        '{LastChange|pagelink}' => 'Link auf die zuletzt geänderte Inhaltsseite',
        '{LastChange|date}' => 'Datum der letzten Änderung')
        );

    if(isset($info[$adminlanguage])) {
        return $info[$adminlanguage];
    } else {
        return $info['deDE'];
    }

  } // function getInfo

} // class LastChange

?>
