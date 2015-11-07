<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/* See accompanying LICENSE file for licensing terms of this software (BSD) */

/**
* QuickTime PEAR package
* @package pearqt
* @version 1.0
* @copyright See accompanying COPYRIGHT file
* @author Neil Smith <quicktime@comatose.freeserve.co.uk>
* @author Marcus Bointon <marcus@synchromedia.co.uk>
* Additional documentation links
* @link http://pearqt.sourceforge.net/ The PEAR::QuickTime home page
* @link http://www.apple.com/quicktime/download/ The official QuickTime download page
* @link http://www.apple.com/quicktime/download/standalone/ Download page for the standalone installer
* @link http://www.apple.com/quicktime/authoring/qtwebfaq.html A good general starting point
* @link http://www.apple.com/quicktime/authoring/embed2.html Embedding QT in web pages
* @link http://developer.apple.com/internet/ieembedprep.html Upcoming changes in IE6
* @link http://www.apple.com/quicktime/authoring/qtjavascript.html Detecting QT with JavaScript
* @link http://developer.apple.com/quicktime/index.html Starting page for Apple QuickTime developer docs
* @link http://www.apple.com/mpeg4/3gpp/ 3gpp support in QuickTime
* @link http://www.ietf.org/internet-drafts/draft-singer-avt-3gpp-mime-01.txt 3gpp MIME types
* @link http://www.totallyhip.com/ Creators of LiveStage Pro, wired sprite authoring tool
*/

/**
* Main QuickTime class
* @package pearqt
*/
class QuickTime {

   /**
   * The movie path name and anchor
   * @var string
   */
   var $url;
   /**
   * The base directory for movies
   * @var string
   */
   var $directory;
   /**
   * The server scheme and host name / port
   * @var string
   */
   var $baseurl;
   /**
   * The initial source file URL
   * @var string
   */
   var $src;
   /**
   * File type extension
   * @var string
   */
   var $type;
   /**
   * The URL Schema
   * @var array
   */
   var $urlschema = array();
   /**
   * Embedded player width requested
   * @var integer
   */
   var $width;
   /**
   * Embedded player height requested
   * @var integer
   */
   var $height;
   /**
   * Does the movie use JavaScript?
   * @var boolean
   */
   var $javascript;
   /**
   * Where to reference the QT plugin
   * @var string
   */
   var $pluginspage;
   /**
   * Where to get the QT activeX plugin
   * @var string
   */
   var $codebase;
   /**
   * IE Class ID string
   * @var string
   */
   var $classid;
   /**
   * Any error strings we wish to return
   * @var string
   */
   var $errors;
   /**
   * An array of parameters for the embedded player
   */
   var $params;
   /**
   * An array of movie names to play in sequence
   * @var array
   */
   var $qtnext = array();
   /**
   * Player object name / id
   * @var string
   */
   var $name;
   /**
   * Embed mimetype
   * @var string
   */
   var $mimetype;
   /**
   * MIME types we will consider
   * @var array
   */
   var $supported_mimetypes;

   /**
   * PHP4 Constructor
   * @see __construct()
   * @param string $name The name of the movie (not its filename)
   * @param string $url URL to load the movie from
   * @param string $width Movie display width, in pixels
   * @param string $height Movie display height, in pixels
   */
   function QuickTime($name = '', $url = '', $width = 320, $height = 240)
   {
      $this->__construct($name, $url, $width, $height);
   }

   /**
   * Constructor
   * @param string $name The name of the movie (not its filename)
   * @param string $url URL to load the movie from
   * @param string $width Movie display width, in pixels
   * @param string $height Movie display height, in pixels
   */
   function __construct($name = '', $url = '', $width = 320, $height = 240) {
      error_reporting(E_NONE);   //   Debug here !
      /**
      * @global $_QuickTime_instances Keeps a count of how many instances we have
      */
      global $_QuickTime_instances;
      $this->setDefaultParams();
      $this->setInstance(&$_QuickTime_instances, $name);
      $this->setMimeTypeList();
      $this->setName($name);
      $this->setURL($url);
      $this->setWidth($width);
      $this->setHeight($height);
      $this->javascript = true;
      $this->pluginspage = 'http://www.apple.com/quicktime/download/';
      $this->codebase = 'http://www.apple.com/qtactivex/qtplugin.cab';
      $this->classid = 'clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B';
   }

   /**
   * Create default movie parameters [ removed hidden ]
   */
   function setDefaultParams() {
      $this->params = array(
         'movieid' => 0,
         'bgcolor' => '#FFFFFF',
         'autoplay' => true,
         'controller' => true,
         'loop' => true,
         'scale' => 'tofit',
         'volume' => 100
      );
   }

   /**
   * Keep track of names of instantiated QuickTime class instances
   * @param string $_QuickTime_instances The current array of instance names
   * @param string $name The name of the movie
   * @return boolean
   */
   function setInstance($_QuickTime_instances, $name) {
      if (!isset($_QuickTime_instances)) {
         $_QuickTime_instances = array();
      }
      if (in_array($name, $_QuickTime_instances)) {
         //   For javascript and movienames, we need unique names
         $this->setError('This name has already been used for a Quicktime player');
         return false;
      } else {
         //   Add to instance list, and set numeric movieid
         $_QuickTime_instances[] = $name;
         $this->params['movieid'] = count($_QuickTime_instances);
         return true;
      }
   }

   /**
   * Return a count of the number of instantiated movies
   * @todo I don't think this property should be called 'instance', as it makes it sounds like an object instance/reference, rather than a counter. movieCount perhaps?
   * @return integer
   */
   function getInstances() {
      return $this->params['movieid'];
   }

   /**
   * Set the source URL of the movie to open
   * @param string $url
   */
   function setURL($url = '') {
      $this->urlschema = parse_url($url);
      $this->baseurl = ($this->urlschema['scheme']?$this->urlschema['scheme'].'://':'');
      $this->baseurl .= ($this->urlschema['host']?$this->urlschema['host']:'');
      $this->baseurl .= ($this->urlschema['port']?':'.$this->urlschema['port']:'');
      $this->url = $this->urlschema['path'].htmlspecialchars($this->urlschema["query"]);
      $this->url .= ($this->urlschema['fragment']?'#'.$this->urlschema['fragment']:'');
      $this->directory = substr($this->urlschema['path'], 0, 1+strrpos($this->urlschema['path'], '/'));
      $this->params['src'] = $this->baseurl.$this->url;
      $this->setMimeType($this->urlschema['path']);
   }

   /**
   * Get the source URL of the movie to open
   * @return string
   */
   function getURL() {
      return $this->url;
   }

   /**
   * Is the current path relative?
   *
   * Quicktime needs absolute paths for QTNEXT or QTL, but may use same folder relative path for src
   * @todo Plan later to use this to implement directory relative paths
   * @param string $path
   * @return boolean
   */
   function getPathIsRelative($path) {
      if (preg_match('/^([^\/])+\/([^\/])+/', $path)) {
         return true;
      }
      return false;
   }

   /**
   * Set the MIME type to report to the browser
   * @param string $type A file extension, used to look up the correct MIME type in {@see $supported_mimetypes}
   */
   function setMimeType($type) {
      $this->type = substr($type, strrpos($type, '.')+1);
      $this->mimetype = $this->getMimeFromFileType($this->type);
   }

   /**
   * Set the Qtsrc parameter
   * Allow QuickTime to handle any content type it knows about
   * Modify the mime type as appropriate
   * @param string $href Link to the source material, note that it is relative to the src attribute
   */
   function setQTSrc($href) {
      $this->setMimeType($href);
      $this->params['type'] = $this->mimetype;
      $this->params['qtsrc'] = $href;
   }
   /**
   * Set the width of the movie
   * @param integer $width
   */
   function setWidth($width) {
      $width = (integer) $width;
      if ($width < 2) {
         $this->width = 2;
         return false;
      } else {
         $this->width = $width;
         return true;
      }
   }

   /**
   * Set the height of the movie
   *
   * If the controller is enabled, 16 pixels are added to make space for it
   * @param integer $height
   */
   function setHeight($height) {
      $height = (integer) $height;
      if ($height < 2 ) {
         $this->height = 2;
         return false;
      } else {
         $this->height = $height + ($this->params['controller'] ? 16 : 0);
         return true;
      }
   }

   /**
   * Set the name of the movie
   * This is the displayed name of the movie when opened in QTPlayer
   * @param string $htmlid
   */
   function setName($name = '') {
      if (preg_match('/^([a-z]{1})([a-z0-9_]*$)/i', $name)) {
         $this->name = $name;
         $this->setMovieName($name);
         return true;
      } else {
         $this->setError('The QuickTime HTML object name can only contain a-z, 0-9 and _');
         return false;
      }
   }

   /**
   * Get the HTML movie ID
   * @return string
   */
   function getName() {
      return $this->name;
   }

   /**
   * Optionally set the Quicktime Moviename differently from the
   * HTML name / ID attributes.
   *
   * Added because movie name can also be used for passing XML data to a wired sprite movie in QT5
   * @see setMovieQTList()
   * @param string $name
   */
   function setMovieName($moviename) {
      if (preg_match('/^([a-z]{1})([a-z0-9_]*$)/i', $moviename) || preg_match('/^<.*>$/', $moviename)) {
         $this->params['moviename'] = $moviename;
         return true;
      } else {
         $this->setError('Moviename may contain XML data or a-z, 0-9 and _ ');
         return false;
      }
   }

   /**
   * Get the Quicktime Moviename - instantiated with constructor
   * using setName(), or optionally by using setMovieName()
   * @return string
   */
   function getMovieName() {
      return $this->params['moviename'];
   }

   /**
   * Set the movieQTList
   *
   * This is embedded XML data passed into the movie for processing by wired sprites
   * Note that this is not valid XML, as it requires that it does NOT include an XML header
   * Also QuickTime does not support element attributes at all, so don't try to use them
   * @link http://developer.apple.com/documentation/QuickTime/QT6WhatsNew/Chap1/chapter_1_section_55.html Apple docs on this attribute
   * @param string $xml XML data to embed
   */
   function setMovieQTList($xml = '') {
      if (preg_match('/^<.*>$/', $xml)) {
         $this->params['movieqtlist'] = $xml;
      } else {
         $this->setError('setMovieQTList expects you to pass it XML data');
      }
   }

   /**
   * Get the movieQTList
   *
   * @return string
   */
   function getMovieQTList() {
      if (array_key_exists('movieqtlist', $this->params))
         return $this->params['movieqtlist'];
      else
         return '';
   }

   /**
   * Turn JavaScript on or off
   * @param boolean $state
   */
   function setJavaScript($state) {
      if ($state == true) {
         $this->javascript = true;
      } else {
         $this->javascript = false;
      }
   }

   /**
   * Get the JavaScript state
   * @return string
   */
   function getJavaScript() {
      return $this->javascript?'true':'false';
   }

   /**
   * Turn autoplay on or off
   * @param boolean $state
   */
   function setAutoPlay($state) {
      if ($state == true) {
         $this->params['autoplay'] = true;
      } else {
         $this->params['autoplay'] = false;
      }
   }

   /**
   * Get the autoplay state
   * @return string
   */
   function getAutoPlay() {
      return $this->params['autoplay'];
   }

   /**
   * Turn the controller on or off
   * @param boolean $state
   */
   function setController($state) {
      if ($state == true) {
         $this->params['controller'] = true;
      } else {
         $this->params['controller'] = false;
      }
   }

   /**
   * Get the controller state
   * @return string
   */
   function getController() {
      return $this->params['controller'];
   }

   /**
   * Set whether the movie is visible or not
   * Controller may not be visible, but the
   * player still takes up screen space if we
   * do not reduce the height and width to 2
   * @param boolean $state
   */
   function setHidden($state) {
      if ($state == true) {
         $this->params['hidden'] = 'true';
         $this->setController(false);
         $this->setWidth(2);
         $this->setHeight(2);
      } else {
         $this->params['hidden'] = '';
      }
   }

   /**
   * Get the hidden state
   * @return string
   */
   function getHidden() {
      return $this->params['hidden'] == 'true'? true : false;
   }

   /**
   * Set the movie's clickthrough URL and Target (if required)
   * @param string $href
   * @param string $target
   */
   function setHREF($href, $target = 'myself') {
      $this->params['href'] = $href;
      $this->params['target'] = $target;
   }

   /**
   * Get the clickthrough HREF assigned to the movie
   * @return string
   */
   function getHREF() {
      return $this->params['href'];
   }

   /**
   * Assign an array of movies to the QTNEXT attribute
   * @param array $movielist
   * @param string $target
   * @return boolean
   */
   function setQTNext($movielist, $target = 'myself') {
      //  Only 256 QTNEXT params are allowed !
      if (is_array($movielist) && count($movielist) <= 256) {
         //  Keep the QTNext list handy...
         $this->qtnext = $movielist;
         $qtnextcount = count($this->qtnext);
         for($i = 0; $i < $qtnextcount; $i++) {
            $this->params['qtnext'.($i+1)] = '<'.$this->qtnext[$i].'>T<'.$target.'>';
         }
         return true;
      } else {
         $this->setError('QTNext URLs must be an array, with up to 255 entries maximum');
         return false;
      }
   }

   /**
   * Set the movie scaling factor
   * @param string $aspect Must be an aspect ratio float, or the string 'tofit' or 'aspect'
   * @return boolean
   */
   function setScale($aspect) {
      if (is_numeric($aspect)) {
         $this->params['scale'] = $aspect;
         return true;
      }
      if (strtolower($aspect) == 'tofit' || strtolower($aspect) == 'aspect') {
         $this->params['scale'] = strtolower($aspect);
         return true;
      }
      $this->setError('Scale must be a number (aspect ratio) or one of "tofit" or "aspect"');
      return false;
   }

   /**
   * Get the movie scaling factor
   * @return string May actually contain a floating point value
   */
   function getScale() {
      return $this->params['scale'];
   }

   /**
   * Set the volume level
   * @param integer $level
   * @return boolean
   */
   function setVolume($level) {
      $level = (integer) $level;
      if ($level >= 0 && $level <= 100) {
         $this->params['volume'] = $level;
         return true;
      } else {
         $this->setError('Volume must be a number between 0 and 100');
         return false;
      }
   }

   /**
   * Get the volume level
   * @return integer
   */
   function getVolume() {
      return $this->params['volume'];
   }

   /**
   * Set the background colour
   *
   * Can use HTML-style 3- or 6-digit hex or standard colour names:
   * black, green, silver, lime, gray, olive, white, yellow, maroon, navy, red, blue, purple, teal, fuchsia, aqua
   * @param string $bgcolor As HTML colours: A named colour, or a 3 or 6 digit hex value preceded by a #
   * @return boolean
   */
   function setBackgroundColor($bgcolor) {
      //  Allow all hex (HTML) colours
      if (preg_match('/^#(([0-9A-F]){3}){1,2}$/i', $bgcolor)) {
         $this->params['bgcolor'] = strtolower($bgcolor);
         return true;
      }
      //  Allowable non-hex (named) colours
      $colormatches = array('black', 'green', 'silver', 'lime', 'gray', 'olive', 'white', 'yellow', 'maroon', 'navy', 'red', 'blue', 'purple', 'teal', 'fuchsia', 'aqua');
      if (in_array(strtolower($bgcolor), $colormatches)) {
         $this->params['bgcolor'] = strtolower($bgcolor);
         return true;
      }
      // Leave background colour set to default (black)
      $this->setError('Invalid background colour `'.$bgcolor.'` (need 3 or 6 digit hex colour, or colour name)');
      return false;
   }

   /**
   * Get the background colour
   * @return string
   */
   function getBackgroundColor() {
      return $this->params['bgcolor'];
   }

   /**
   * Get a named parameter that we know about
   * @param string $name the name of the parameter to get
   * @return string
   */
   function getParam($name) {
      $param = $this->params[$name];
      if (is_bool($param)) {
         return $param?'true':'false';
      }
      if (is_numeric($param) || is_string($param)) {
         return $param;
      }
   }

   /**
   * Return param list for 'object' embedding format
   * @return string
   */
   function getObjectTagParams() {
      $objecttag = '';
      $paramnames = array_keys($this->params);
      foreach($paramnames as $name) {
         $objecttag .= "\r\n\t".'<param name="'.$name.'" value="'.htmlspecialchars($this->getParam($name), ENT_QUOTES, "UTF-8").'" />';
      }
      return $objecttag;
   }

   /**
   * Return param list for 'embed' embedding format
   * @return string
   */
   function getEmbedTagParams() {
      $embedtag = '';
      foreach(array_keys($this->params) as $name) {
         $embedtag .= ' '.$name.'="'.htmlspecialchars($this->getParam($name), ENT_QUOTES, "UTF-8").'"';
      }
      return $embedtag;
   }

   /**
   * Generate the full HTML embedding code
   * @return string
   * @todo perhaps leave out params that are at default values?
   * @todo make the output format browser dependent
   */
   function getHTML() {
      if ($this->getError()) {
         $this->tag = $this->getError();
      } else {
      //  Additional params supported by OBJECT
      $this->tag = '<object id="'.$this->name.'" classid="'.$this->classid.'" width="'.$this->width.'" height="'.$this->height.'" codebase="'.$this->codebase.'">';
      $this->tag .= $this->getObjectTagParams();
      $this->tag .= "\r\n\t".'<embed name="'.$this->name.'" height="'.$this->height.'" width="'.$this->width.'" type="'.$this->mimetype.'" pluginspage="'.$this->pluginspage.'" enablejavascript="'.$this->getJavaScript().'"';
      $this->tag .= $this->getEmbedTagParams();
      $this->tag .= '>
         <noembed>
            <a href="'.$this->pluginspage.'" target="_blank">Download Quicktime to play this media</a> or <a href="'.$this->baseurl.$this->url.'">Click Here</a>
            </noembed>
         </embed>
      </object>';
      return $this->tag;
      }
   }

   /**
   * Deliver JavaScript embedding code
   *
   * Prepare for IE6.1 which requires javascript in an
   * external function, rather than HTML embedded directly
   * in the page to work around {@link http://developer.apple.com/internet/ieembedprep.html patent issues}.
   * @param string $function The JavaScript source code to output
   * @return mixed False on error, otherwise a string
   * @todo Should this print, rather than return? [Yes !]
   */
   function getJSCode($function = '') {
      if (headers_sent()) {
         $this->setError('Cannot send javascript file : page output has already been sent');
         return false;
      } else {
         header('Content-type: text/javascript');
         if ($function == '') {
            $this->jscode = 'function quicktime'.$this->params['movieid'];
         } else {
            $this->jscode = 'function '.$function.$this->params['movieid'];
         }
         $this->jscode .= "() {\r\n\treturn '";
         //  Clear tabs, newlines etc from javascript code
         $this->jscode .= preg_replace('/[\r\n\t ]+/', ' ', $this->getHTML());
         $this->jscode .= "'\r\n}\r\n\r\n";
         print $this->jscode;
      }
   }

   /**
   * Create embedding for JavaScript embedder
   *
   * Creates a document.write call which references the current QuickTime object
   * and embeds the JavaScript code
   * @param string $function The JavaScript source code to output
   * @return string
   */
   function callJSCode($function = '') {
      if ($function == '') {
         $this->jsfunction = 'quicktime'.$this->params['movieid'];
      } else {
         $this->jsfunction = $function.$this->params['movieid'];
      }
      $this->jscode = '<script language="javascript" type="text/javascript" >'."\r\n\t";
      $this->jscode .= 'document.write('.$this->jsfunction.'('.$this->movieid."))\r\n";
      $this->jscode .= "</script>\r\n";
      $this->jscode .= '<noscript><a href="'.$this->baseurl.$this->url.'">Click Here</a></noembed></noscript>';
      return $this->jscode;
   }

   /**
   * Create a QTLink reference movie
   *
   * Create a .qtl QuickTime Media Link file to dynamically link a web movie to a QTPlayer movie
   * WITHOUT creating an intermediate .qtl file that's left lying about.
   * You can also use this to pass data between the two movies for wired sprite functions.
   * Data to be passed between the two should be placed in the MovieQTList parameter in a QTList XML format
   * For compatibility with QuickTime 5, you can pass the data through the moviename tag instead
   * Thanks to Trevor Devore of Blue Mango Media for help on this
   * @link http://developer.apple.com/documentation/QuickTime/QT6WhatsNew/Chap1/chapter_1_section_54.html Apple docs on QuickTime Media Links
   * @link http://developer.apple.com/documentation/QuickTime/QT6WhatsNew/Chap1/chapter_1_section_55.html Apple docs on MovieQTList attribute
   * @link http://developer.apple.com/documentation/Quicktime/REF/whatsnewqt5/Max.2b.htm#pgfId=93760 More docs from QuickTime 5
   * @return boolean
   * @todo Add more params, filter output for unused params
   */
   function getQTLink() {
      if (headers_sent()) {
         $this->setError('Cannot send .qtl file : page output has already been sent');
         return false;
      }
      if (! $this->getError()) {
         $qtl = "<?xml version=\"1.0\"?>\n";
         $qtl .= "<?quicktime type=\"application/x-quicktime-media-link\"?>\n";
         $qtl .= '<embed src="'.$this->baseurl.$this->url.'" target="quicktimeplayer" autoplay="'.$this->getAutoPlay().'" controller="'.$this->getController().'" moviename="'.$this->getMovieName().'" movieqtlist="'.$this->getMovieQtList().'" type="'.$this->mimetype.'" />'."\n";
         header('Content-type: application/x-quicktimeplayer');
         header('Content-Length: '.strlen($qtl));
         header('Content-disposition: inline; filename=temp.qtl');
         print($qtl);
         exit; //  Must exit at this point, cannot send more content ;-)
      } else
         return false;
   }

   /**
   * Add an error message to the error list
   * @param string $errorstring The text of the error message
   */
   function setError($errorstring) {
      $this->errors .= $errorstring."\r\n";
   }

   /**
   * Get the error list
   * @return mixed
   */
   function getError() {
      if (strlen($this->errors) == 0) {
         return false;
      } else {
         return $this->errors;
      }
   }

   /**
   * Set the list of available MIME types
   *
   * This list of types means that the server does not have to be set up to deliver
   * QuickTime files with the correct types, which many servers are not set to do
   */
   function setMimeTypeList() {
      $this->supported_mimetypes = array(
         'mov' => 'video/quicktime', 'qt' => 'video/quicktime',
         'qtl' => 'application/x-quicktime-media-link',
         'wav' => 'audio/x-wav',
         'aiff' => 'audio/x-aiff', 'aifc' => 'audio/x-aiff', 'aif' => 'audio/x-aiff',
         'au' => 'audio/basic', 'snd' => 'audio/basic', 'ulw' => 'audio/basic',
         'avi' => 'video/avi', 'vfw' => 'video/avi',
         'mpeg' => 'video/x-mpeg', 'mpg' => 'video/x-mpeg',
         'midi' => 'audio/x-midi', 'mid' => 'audio/x-midi',
         'rtsp' => 'application/x-rtsp',
         'sdp' => 'application/sdp',
         'bmp' => 'image/x-bmp',
         'psd' => 'image/x-photoshop',
         'pict' => 'image/x-pict', 'pic' => 'image/x-pict', 'pct' => 'image/x-pict',
         'png' => 'image/png',
         'pntg' => 'image/x-macpaint',
         'qtif' => 'image/x-quicktime', 'qti' => 'image/x-quicktime',
         'tga' => 'image/x-targa',
         'tiff' => 'image/tiff', 'tif' => 'image/tiff',
         'swf' => 'application/x-shockwave-flash',
         'txt' => 'text/plain',
         'mp3' => 'audio/mpeg',
         'mp4' => 'video/mp4', 'm4v' => 'video/mp4', 'm4a' => 'audio/mp4',
         'm3url' => 'audio/x-mpegurl', 'm3u' => 'audio/x-mpegurl',
         'smil' => 'application/smil', 'smi' => 'application/smil',
         'jp2' => 'image/jp2',
         '3gp' => 'video/3gpp', '3gpp' => 'video/3gpp', '3g2' => 'video/3gpp'
      );
   }

   /**
   * Get the list of available MIME types
   * @return array
   */
   function getMimeTypeList() {
      return $this->supported_mimetypes;
   }

   /**
   * Add a MIME type
   *
   * Add any specified mime type not on the supported list
   * Note that any existing matching extension will be overwritten
   * @param string $extension The file extension e.g. 'mov'
   * @param string $type The corresponding MIME type e.g. 'video/quicktime'
   */
   function addMimeType($extension, $type) {
      $this->supported_mimetypes[strtolower($extension)] = strtolower($type);
   }

   /**
   * Get the currently specified MIME type for a given extension
   * @param string $extension The file extension to find the type for e.g. 'mov'
   * @return mixed
   */
   function getMimeFromFileType($extension = '') {
      if (preg_match("/[a-z0-9]{2,}/i", $extension)) {
         if (array_key_exists(strtolower($extension), $this->supported_mimetypes)) {
            return $this->supported_mimetypes[strtolower($extension)];
         } else {
            $this->setError('Unsupported file type `'.$extension.'`- please use addMimeType()');
         }
      } else {
         $this->setError('File type (extension) can only contain 0-9 and A-Z');
      }
      return false;
   }

   /**
   * Makes a binary QT reference movie from scratch
   * @author "Duchamp"
   * @link http://www.phpbuilder.com/snippet/detail.php?type=snippet&id=488 Originally found in this forum
   * @static
   * @param string $qturl The URL of the movie to reference
   */
   function makeRefMovie($qturl) {
     $n = strlen($qturl);
     header('Content-type: video/quicktime');
     echo pack('Na4Na4Na4Na4xxxxa4Na*x', $n+45, 'moov', $n+37, 'rmra', $n+29, 'rmda', $n+21, 'rdrf', 'url ', $n+1, $qturl);
   }

/* ---------------------------- */
}   //   End Class QuickTime
?>