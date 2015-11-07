<?
/*
  NP_QuickTime - provide support for QuickTimeing in Nucleus
 
  Usage:
    1) install the plugin 
    2) upload the mp4, mov,  etc,  and use the skinvar <%QuickTime(Filename|hight|width)%>.
       For a file outside media folder put in the URL directly
 
  Known issue:
    - only one QuickTime in per post is assumed
 
*/
 
// plugin needs to work on Nucleus versions <=2.0 as well
if (!function_exists('sql_table'))
{
        function sql_table($name) {
                return 'nucleus_' . $name;
        }
}
 
class NP_QuickTime extends NucleusPlugin {
 
  var $authorid;
 
function getEventList() { return array('PreItem'); }
  function getName() { return 'QuickTime'; }
  function getAuthor() { return 'Max Krueger'; }
  function getURL() { return; }
  function getVersion() { return '0.1'; }
  function getDescription() { return 'This plugin provides QuickTime support in Nucleus via a new the skinvar <%QuickTime(Filename|hight|width)%>. Automatically inserts html for embedded player.  '; }
  // Note: 
  //       this code ripped from Podcast Plugin v0.5 Sep 22,2005
  //       
  //       Also uses a php class to create html
  //       
  //

  function getMinNucleusVersion() { return '250'; }
 
  function supportsFeature($what) {
    switch($what)
    {
      case 'SqlTablePrefix':
        return 1;
      default:
        return 0;
    }
  }
 
  function install() {
    $this->createOption('height','Default Height','text','240','datatype=numerical');
    $this->createOption('width','Default Width','text','320','datatype=numerical');
  }
 
  // This function generates the actual URL to the QuickTime
  function event_PreItem($data) {
    global $item;
    $item = &$data["item"];
    $this->authorid = $item->authorid;

    if (strstr($item->body . " " . $item->more, "<%QuickTime(")) {
      $item->body = preg_replace_callback("#<\%QuickTime\((.*?)\|(.*?)\)%\>#", array(&$this, 'replaceCallback'), $item->body);
      $item->mmore = preg_replace_callback("#<\%QuickTime\((.*?)\|(.*?)|\)%\>#", array(&$this, 'replaceCallback'), $item->more);
    }
  }
 
  function replaceCallback($matches) {
    global $CONF;

    $file = $matches[1];
    if ($matches[2]) {
   $dimensions=explode("|",$matches[2]);

        if ($dimensions[0]) { $height = $dimensions[0];
      } else { $height = $this->$getOption(height); }

        if ($dimensions[1]) { $width = $dimensions[1];
     } else { $width = $this->$getOption(width); }

   }


    // local or not

    if (!strstr($file, "http:") && !strstr($file, "rtsp:")) {
      $file=$CONF['MediaURL'] . $this->authorid . "/" .  $file; 
    }

    include_once($this->getDirectory()."class.quicktime.php");
    $this->$qt=new Quicktime("QuickTime",$file, $height, $width);
return "<div class=\"quicktime\">".$this->$qt->getHTML(). "</div>";
  }
 
  // This function embeds the QuickTime Player in skins

  function doSkinVar($skinType, $param1, $param2, $param3) {
    global $DIR_MEDIA, $CONF;
    $file=$param1;
    if ($param2<2) {$param2=$this->getOption('height'); }
    if ($param3<2) {$param2=$this->getOption('width'); }

    if (!strstr($param1, "http:")) {
   $file=$CONF['MediaURL'] . $this->authorid . "/" .  $file;
   }

// return nothing if filename is blank


   if ($param1) {
    include_once($this->getDirectory()."class.quicktime.php");
    $this->$qt1=new Quicktime("quicktime",$file, $param2, $param3);
    echo "<div class=\"quicktime\">".$this->$qt1->getHTML(). "</div>";
   }

  }

 
}
?>