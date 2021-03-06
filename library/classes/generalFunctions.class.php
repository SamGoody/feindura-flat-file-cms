<?php
/*
 * feindura - Flat File Content Management System
 * Copyright (C) Fabian Vogelsteller [frozeman.de]
 *
 * This program is free software;
 * you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not,see <http://www.gnu.org/licenses/>.
 */
/**
 * This file contains the {@link generalFunctions} class.
 * 
 * @package [Implementation]-[Backend]
 */
/**
* <b>Classname</b> generalFunctions<br>
* 
* Contains the basic functions for reading and saving pages
* 
* <b>Notice</b>: this class will be used by the implementation classes AND the backend of the feindura-CMS.
* 
* @package [Implementation]-[Backend]
* 
* @version 1.19
* <br>
*  <b>ChangeLog</b><br>
*    - 1.19 add parseDefaultLanguage() to checkLanguageFiles()
*    - 1.18 fixed checkLanguageFiles()
*    - 1.17 add chmod to savePage()
*    - 1.16 started documentation
*/ 
class generalFunctions {
 
 /* ---------------------------------------------------------------------------------------------------------------------------- */
 /* *** PROPERTIES *** */
 /* **************************************************************************************************************************** */
 
 // PUBLIC
 // *********
  
  /**
  * Contains the administrator-settings config <var>array</var>
  * 
  * @var array
  * @see generalFunctions()
  * 
  */ 
  var $adminConfig;
  
  /**
  * Contains the category-settings config <var>array</var>
  * 
  * @var array
  * @see generalFunctions()
  * 
  */ 
  var $categoryConfig;
  
   /**
  * Contains all page and category IDs on the first loading of a page.
  * 
  * Run on the first loading of a page.
  * Goes trough all category folders and look which pages are in which folders and saves the IDs in the this property,<br>
  * to speed up the page loading process.
  * 
  * Example of the returned array:
  * {@example loadPageIds.return.example.php}
  * 
  * @var array
  * 
  */
  var $storedPageIds = null;
  
 /**
  * Stores page-content <var>array's</var> in this property if a page is loaded
  * 
  * If a page is loaded (<i>included</i>) it's page-content array will be stored in the this array.<br>
  * If the page is later needed again it's page-content will be fetched from this property.<br>
  * It should speed up the page loading process.
  * 
  * @example loadPages.return.example.php of the returned array
  * 
  * @var array
  * 
  */
  var $storedPages = null;
  
 /**
  * Contains a <var>instance</var> of the {@link xssFilter::xssFilter() xssFilter} <var>class</var> for using in this <var>class</var>
  * 
  * The file with the {@link xssFilter::xssFilter() xssFilter} class is situated at <i>"feindura-CMS/library/classes/xssFilter.class.php"</i>.<br />   
  * A instance of the {@link xssFilter::xssFilter() xssFilter} class will be set to this property in the {@link xssFilter()} constructor.
  * 
  * @var class
  * @see xssFilter::xssFilter()
  *   
  */
  var $xssFilter;
 
 /* ---------------------------------------------------------------------------------------------------------------------------- */
 /* *** CONSTRUCTOR *** */
 /* **************************************************************************************************************************** */
  
 /**
  * <b> Type</b>      constructor<br>
  * <b> Name</b>      generalFunctions()<br>
  * 
  * The constructor of the class, gets the settings.
  * 
  * <b>Used Global Variables</b><br>
  *    - <var>$adminConfig</var> the administrator-settings config (included in the {@link general.include.php})
  *    - <var>$categoryConfig</var> the categories-settings config (included in the {@link general.include.php})
  * 
  * @param object|false $xssFilter (optional) an instance of the xssFilter class or FALSE      
  * 
  * @return void
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */ 
  function generalFunctions($xssFilter = false) {
    
    // run the parent class constructor
    $this->xssFilter = (is_a($xssFilter,'xssFilter')) ? $xssFilter : new xssFilter();
    
    // GET CONFIG FILES and SET CONFIG PROPERTIES
    $this->adminConfig = (isset($GLOBALS["adminConfig"])) ? $GLOBALS["adminConfig"] : $GLOBALS["feindura_adminConfig"];
    $this->categoryConfig = (isset($GLOBALS["categoryConfig"])) ? $GLOBALS["categoryConfig"] : $GLOBALS["feindura_categoryConfig"];

  }
  
 /* ---------------------------------------------------------------------------------------------------------------------------- */
 /* *** METHODS *** */
 /* **************************************************************************************************************************** */
 
  /**
  * <b>Name</b> parseDefaultLanguage()<br>
  * 
  * Checks for the browser language with the highest q-value
  * 
  * If no match to the browser language is found it uses the <var>$standardLang</var> parameter for loading a languageFile or returning the country code.
  * 
  * @author Darrin Yeager
  * @copyright Copyright (c) 2008 Darrin Yeager
  * @license http://www.dyeager.org/downloads/license-bsd.php BSD license
  * @link   http://www.dyeager.org/post/2008/10/getting-browser-default-language-php
  * 
  */
  function parseDefaultLanguage($http_accept, $deflang = "en") {
     if(isset($http_accept) && strlen($http_accept) > 1)  {
        # Split possible languages into array
        $x = explode(",",$http_accept);
        foreach ($x as $val) {
           #check for q-value and create associative array. No q-value means 1 by rule
           if(preg_match("/(.*);q=([0-1]{0,1}\.\d{0,4})/i",$val,$matches))
              $lang[$matches[1]] = (float)$matches[2];
           else
              $lang[$val] = 1.0;
        }
  
        #return default language (highest q-value)
        $qval = 0.0;
        foreach ($lang as $key => $value) {
           if ($value > $qval) {
              $qval = (float)$value;
              $deflang = $key;
           }
        }
     }
     return strtolower($deflang);
  }

 
 /**
  * <b>Name</b> checkLanguageFiles()<br>
  * 
  * Checks for the browser language and looks if there is a file in the language folder with tha same country code in the filename end.
  * 
  * If there is a language file which matches the browser language it loads either the language-file or returns the country code,
  * depending on the <var>$returnLangFile</var> parameter.
  * 
  * If no match to the browser language is found it uses the <var>$standardLang</var> parameter for loading a languageFile or returning the country code.
  * 
  * @param string|false $useLangPath      (optional) a absolut path to look for language files or FALSE to use the "feindura-cms/library/languages" folder
  * @param bool         $returnLangFile   (optional) if TRUE it includes and returns the language-file which matches the browser language
  * @param bool         $standardLang     (optional) a standard language for use if no match was found
  * 
  * @uses $adminConfig                           for the base path of the CMS
  * @uses generalFunctions::readFolderRecursive  to read the language folder
  * @uses generalFunctions::parseDefaultLanguage to get the right browser language
  * 
  * @return string|array|false a country code (like: de, en, fr..) or the language-file array or FALSE if the language file could not be opend
  * 
  * 
  * @version 1.02
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.02 add parseDefaultLanguage()
  *    - 1.01 fixed language files check, uses now readFolder recursive  
  *    - 1.0 initial release
  * 
  */
  function checkLanguageFiles($useLangPath = false, $returnLangFile = true, $standardLang = 'en') {
     
      // checks if a path given
      if(is_string($useLangPath)) {
        // adds "/" on the beginnging for absolute path
        if(substr($useLangPath,0,1) != '/')
          $useLangPath = '/'.$useLangPath;
          
        // adds the DOCUMENTROOT  
        $useLangPath = str_replace(DOCUMENTROOT,'',$useLangPath);  
        $useLangPath = DOCUMENTROOT.$useLangPath;
        
      } else
        $langPath = dirname(__FILE__).'/../languages/';
       
      // -> read language folder
      $langFiles = $this->readFolderRecursive($langPath);
      
      // -> get langFiles
      if(!empty($langFiles['files'])) {
                    
        // checks if the BROWSER STANDARD LANGUAGE is found in the SUPPORTED COUNTRY CODE         
        $browserLang = (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
          ? $this->parseDefaultLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"],$standardLang)
          : $this->parseDefaultLanguage(NULL,$standardLang);
        $browserLang = substr($browserLang,0,2);
        
    	  foreach($langFiles['files'] as $langFilePath) {

          $langFile = basename($langFilePath);

      		if(stristr(substr($langFile,-6,2).",", $browserLang.",") ||
             stristr(substr($langFile,0,2).",", $browserLang.",")) {
	  
      		  // returns either langFile or the COUNTRY CODE
      		  if($returnLangFile) {
      		    if($return = include(DOCUMENTROOT.$langFilePath))
                return $return;
             else
                return false;
      		  } else {
      			   return $browserLang;
      			}
      		}
      	}
        
        // if there is no SUPPORTED COUNTRY CODE, use the standard Lang  	
      	if($returnLangFile) {
          if(!empty($langFile)) {
            if($return = @include($langPath.substr($langFile,0,-6).$standardLang.'.php') ||
               $return = @include($langPath.$standardLang.substr($langFile,2)))
              return $return;
            else
              return false;
          } else
           return false;
    	     
    	  // return only the standard COUNTRY CODE
    	  } else
    		  return $standardLang;   		  
  		  
      } elseif($returnLangFile)
          return array();
      else
          return $standardLang;          
  }
  
  /**
   * <b>Name</b> checkMainVars()<br />
   * 
   * Check the "page", "category" and "language" GET variables whether they have the right type, otherwise exits the script.
   * 
   * <b>Notice</b>: this method will be used by the implementation classes AND the backend of the feindura-CMS.      
   * 
   * <b>Used Global Variables</b><br />
   *    - <var>$_GET</var> the http request variables
   * 
   * @param string $category the name of the category variable
   * @param string $page the name of the category variable   
   * 
   * @uses xssFilter::int()
   * 
   * @return void nothing just cancels the running script if necessary
   * 
   * @version 1.0
   * <br />
   * <b>ChangeLog</b><br />
   *    - 1.0 initial release
   * 
   */
  function checkMainVars($category = 'category', $page = 'page') {
       
    //check category
    if((isset($_GET[$category]) && $this->xssFilter->int($_GET[$category]) === false) ||
       (isset($_POST[$category]) && $this->xssFilter->int($_POST[$category]) === false))
      die('Wrong &quot;'.$category.'&quot; parameter! Script will be terminated.');
    // check page
    if((isset($_GET[$page]) && $_GET[$page] != 'new' && $this->xssFilter->int($_GET[$page]) === false) ||
       (isset($_POST[$page]) && $_POST[$page] != 'new' && $this->xssFilter->int($_POST[$page]) === false))
      die('Wrong &quot;'.$page.'&quot; parameter! Script will be terminated.');
    
  }

 /**
  * <b>Name</b> getCurrentUrl()<br>
  * 
  * Return the current URL ($_SERVER['REQUEST_URI']), optional with add parameters.
  * 
  * @param string $parameter (optional) a string of parameter(s) to add, with the following format: "key=value&key2=value2..."
  * 
  * @return string the current url
  * 
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function getCurrentUrl($parameter = null) {
    
    $currentURL = $_SERVER['REQUEST_URI'];
    
    if(!empty($parameter)) {
      $currentUrl = (strpos($currentURL,'?') === false)
        ? $_SERVER['REQUEST_URI'].'?'
        : $_SERVER['REQUEST_URI'].'&';
      
      return $currentUrl.$parameter;
    } else
      return $currentURL;
  }

 /**
  * <b>Name</b> getStoredPageIds()<br>
  * 
  * Fetches the {@link $storedPageIds} property.
  * 
  * If the {@link $storedPageIds} property is empty, it loads all page IDs into this property.
  * 
  * Example of the returned {@link $storedPageIds} property:
  * {@example loadPageIds.return.example.php}
  * 
  * @uses $storedPageIds the property to get
  * 
  * @return array the {@link $storedPageIds} property
  * 
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function getStoredPageIds() { // (false or Array)
    
    // load all page ids, if necessary
    if($this->storedPageIds === null)
      $this->storedPageIds = $this->loadPageIds(true);

    return $this->storedPageIds;
  }

 /**
  * <b>Name</b> getStoredPages()<br>
  * 
  * Fetches the {@link $storedPages} property.
  * 
  * Its also possible to fetch the {@link $storedPages} property from the <var>$_SESSION</var> variable. (CURRENTLY DEACTIVATED)
  * 
  * @uses $storedPages the property to get
  * 
  * @return array the {@link $storedPages} property
  * 
  * @example loadPages.return.example.php of the returned array
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function getStoredPages() {
    global $HTTP_SESSION_VARS;
    
    unset($_SESSION['storedPages']);    
    //echo 'STORED-PAGES -> '.count($this->storedPages);
    
    // if its an older php version, set the session var
    if(phpversion() <= '4.1.0')
      $_SESSION = $HTTP_SESSION_VARS;    
      
    // -> checks if the SESSION storedPages Array exists
    if(isset($_SESSION['storedPages']))
      return $_SESSION['storedPages']; // if isset, get the storedPages from the SESSION
    else
      return $this->storedPages; // if not get the storedPages from the PROPERTY  
  }

 /**
  * <b>Name</b> setStoredPages()<br>
  * 
  * Adds a <var>$pageContent</var> array to the {@link $storedPages} property.
  * Its also possible to store the {@link $storedPages} property in a <var>$_SESSION</var> variable. (CURRENTLY DEACTIVATED)
  * 
  * @param int  $pageContent   a $pageContent array which should be add to the {@link $storedPages} property
  * 
  * @uses $storedPages the property to add the $pageContent array
  * 
  * @return array passes through the given $pageContent array
  * 
  * 
  * @version 1.01
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.01 removed the $remove parameter  
  *    - 1.0 initial release
  * 
  */
  function setStoredPages($pageContent,$remove = false) {
    global $HTTP_SESSION_VARS;
    
    unset($_SESSION['storedPages']);
    
    // if its an older php version, set the session var
    if(phpversion() <= '4.1.0')
      $_SESSION = $HTTP_SESSION_VARS;  
    
    // stores the given parameter only if its a valid $pageContent array
    if($this->isPageContentArray($pageContent)) {
      
      // ->> ADD
      // -> checks if the SESSION storedPages Array exists
      if(isset($_SESSION['storedPages']))
        $_SESSION['storedPages'][$pageContent['id']] = $pageContent; // if isset, save the storedPages in the SESSION
      else {
        $this->storedPages[$pageContent['id']] = $pageContent; // if not save the storedPages in the PROPERTY
        $_SESSION['storedPages'][$pageContent['id']] = $pageContent;
      }
    }
    
    return $pageContent;
  }
  
 /**
  * <b>Name</b> removeStoredPage()<br>
  * 
  * Removes a <var>$pageContent</var> array from the {@link $storedPages} property.
  * Its also possible to remove the {@link $storedPages} property from the <var>$_SESSION</var> variable. (CURRENTLY DEACTIVATED)
  * 
  * @param int $id the ID of a page which should be removed from the {@link $storedPages} property
  * 
  * @uses $storedPages the property to remove the $pageContent array
  * 
  * @return bool TRUE if a page with this ID exists and could be removed, otherwise FALSE
  * 
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function removeStoredPage($id) {
    global $HTTP_SESSION_VARS;
    
    // var
    $return = false;
    
    // if its an older php version, set the session var
    if(phpversion() <= '4.1.0')
      $_SESSION = $HTTP_SESSION_VARS;  
    
    // ->> REMOVE
    if(is_numeric($id)) {
    // -> checks if the SESSION storedPages Array exists
      if(isset($_SESSION['storedPages']) && isset($_SESSION['storedPages'][$id])) {
        unset($_SESSION['storedPages'][$id]); // if isset, remove from the storedPages in the SESSION
        return true;
      } elseif(isset($this->storedPages[$id])) {
        unset($this->storedPages[$id]); // if not remove from the storedPages in the PROPERTY
        unset($_SESSION['storedPages'][$id]);
        return true;
      }
    }
    
    return $return;
  }
  
 /**
  * <b>Name</b> getPageCategory()<br>
  * 
  * Return the category ID of a page.
  * 
  * @param int $page a page ID from which to get the category ID
  * 
  * @uses getStoredPageIds() to get the {@link storedPageIds} property
  * 
  * @return int|false the right category ID or FALSE if the page ID doesn't exists
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function getPageCategory($page) {

    if($page !== false && is_numeric($page)) {
      // loads only the page IDs and category IDs in an array
      // but only if it hasn't done this yet      
      $allPageIds = $this->getStoredPageIds();
      
      if($allPageIds) {
        // gets the category id of the given page
        foreach($allPageIds as $everyPage) {
          // if its the right page, return the category of it        
          if($page == $everyPage['page']) {
             return $everyPage['category'];
          }
        }
        // if it found nothing
        return false;
        
      } else
        return false;
    } else
      return false;
  }


 /**
  * <b>Name</b> savePage()<br>
  * 
  * Save a page to it's flatfile.
  * 
  * Example of the saved $pageContent array:
  * {@example readPage.return.example.php}
  * 
  * <b>Used Constants</b><br>
  *    - <var>DOCUMENTROOT</var> the absolut path of the webserver
  *    - <var>PHPSTARTTAG</var> the php start tag
  *    - <var>PHPENDTAG</var> the php end tag
  * 
  * @param array $pageContent the $pageContent array of the page to save
  * 
  * @uses $adminConfig      for the save path of the flatfiles
  * @uses setStoredPages()  to store the saved file agiain, and overwrite th old stored page
  * 
  * @return bool TRUE if the page was succesfull saved, otherwise FALSE
  * 
  * @version 1.02
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.02 add preg_replace removing multiple slahses
  *    - 1.01 add chmod
  *    - 1.0 initial release
  * 
  */
  function savePage($pageContent) {
    
    // check if array is pageContent array
    if(!$this->isPageContentArray($pageContent))
      return false;
    
    $pageId = $pageContent['id'];
    $categoryId = $pageContent['category'];
    
    // get path
    $filePath = ($categoryId === false || $categoryId == 0)
    ? DOCUMENTROOT.$this->adminConfig['savePath'].$pageId.'.php'
    : DOCUMENTROOT.$this->adminConfig['savePath'].$categoryId.'/'.$pageId.'.php';
    
    // open the flatfile
    if(is_numeric($pageContent['id']) && ($file = @fopen($filePath,"w"))) {
      
      $pageContent = $this->escapeQuotesRecursive($pageContent);
      
      // escaps ",',\,NULL but undescappes the double quotes again
      $pageContent['content'] = preg_replace('#\\\\+#', "\\", $pageContent['content']);
      $pageContent['content'] = stripslashes($pageContent['content']);
      $pageContent['content'] = addslashes($pageContent['content']); //escaped ",',\,NUL
      $pageContent['content'] = preg_replace('#\\\\"+#', '"', $pageContent['content'] );
        
      // CHECK BOOL VALUES and change to FALSE
      $pageContent['public'] = (isset($pageContent['public']) && $pageContent['public']) ? 'true' : 'false';
      
      // WRITE
      flock($file,2);            
      fwrite($file,PHPSTARTTAG);
      
      fwrite($file,"\$pageContent['id'] =                 ".$pageContent['id'].";\n");
      fwrite($file,"\$pageContent['category'] =           ".$pageContent['category'].";\n");
      fwrite($file,"\$pageContent['public'] =             ".$pageContent['public'].";\n");
      fwrite($file,"\$pageContent['sortorder'] =          ".$pageContent['sortorder'].";\n\n");
      
      fwrite($file,"\$pageContent['lastsavedate'] =       '".$pageContent['lastsavedate']."';\n");
      fwrite($file,"\$pageContent['lastsaveauthor'] =     '".$pageContent['lastsaveauthor']."';\n\n"); 
      
      fwrite($file,"\$pageContent['title'] =              '".$pageContent['title']."';\n");
      fwrite($file,"\$pageContent['description'] =        '".$pageContent['description']."';\n\n");      
      
      fwrite($file,"\$pageContent['pagedate']['before'] = '".$pageContent['pagedate']['before']."';\n");
      fwrite($file,"\$pageContent['pagedate']['date'] =   '".$pageContent['pagedate']['date']."';\n");
      fwrite($file,"\$pageContent['pagedate']['after'] =  '".$pageContent['pagedate']['after']."';\n");           
      fwrite($file,"\$pageContent['tags'] =               '".$pageContent['tags']."';\n\n");
      
      // write the plugins
      if(is_array($pageContent['plugins'])) {
        foreach($pageContent['plugins'] as $key => $value) {
          // save plugin settings only if plugin is activated
          if($pageContent['plugins'][$key]['active']) {
            foreach($value as $insideKey => $finalValue) {
              // CHECK BOOL VALUES and change to FALSE
              if($pageContent['plugins'][$key][$insideKey] == 'true' ||
                 $pageContent['plugins'][$key][$insideKey] == 'false') {
                $pageContent['plugins'][$key][$insideKey] = (isset($pageContent['plugins'][$key][$insideKey]) && $pageContent['plugins'][$key][$insideKey] !== 'false') ? 'true' : 'false';
                fwrite($file,"\$pageContent['plugins']['".$key."']['".$insideKey."'] = ".$pageContent['plugins'][$key][$insideKey].";\n");
              } else
                fwrite($file,"\$pageContent['plugins']['".$key."']['".$insideKey."'] = '".$pageContent['plugins'][$key][$insideKey]."';\n");
  
            }
            fwrite($file,"\n");
          }        
        }
      }    
      
      fwrite($file,"\$pageContent['thumbnail'] =          '".$pageContent['thumbnail']."';\n");
      fwrite($file,"\$pageContent['styleFile'] =          '".$pageContent['styleFile']."';\n");
      fwrite($file,"\$pageContent['styleId'] =            '".$pageContent['styleId']."';\n");
      fwrite($file,"\$pageContent['styleClass'] =         '".$pageContent['styleClass']."';\n\n");
      
      fwrite($file,"\$pageContent['log_visitorcount'] =   '".$pageContent['log_visitorcount']."';\n");
      fwrite($file,"\$pageContent['log_visitTime_min'] =  '".$pageContent['log_visitTime_min']."';\n");
      fwrite($file,"\$pageContent['log_visitTime_max'] =  '".$pageContent['log_visitTime_max']."';\n");
      fwrite($file,"\$pageContent['log_firstVisit'] =     '".$pageContent['log_firstVisit']."';\n");
      fwrite($file,"\$pageContent['log_lastVisit'] =      '".$pageContent['log_lastVisit']."';\n");
      fwrite($file,"\$pageContent['log_searchwords'] =    '".$pageContent['log_searchwords']."';\n\n");
      
      fwrite($file,"\$pageContent['content'] = \n'".$pageContent['content']."';\n\n");
      
      fwrite($file,"return \$pageContent;");
      
      fwrite($file,PHPENDTAG);
      flock($file,3);
      fclose($file);
      
      @chmod($filePath, PERMISSIONS);
      
      // writes the new saved page to the $storedPages property      
      $this->removeStoredPage($pageContent['id']); // remove the old one
      unset($pageContent);
      $pageContent = include($filePath);
      $this->setStoredPages($pageContent);
      // reset the stored page ids
      $this->storedPagesIds = null;
      
      return true;
    }  
    return false;  
  }
  
 /**
  * <b>Name</b> readPage()<br>
  * 
  * Loads the $pageContent array of a page.
  * 
  * Checks first whether the given page ID was already loaded and is contained in the {@link $storedPages} property.
  * If not the {@link generalFunctions::readPage()} function is called to include the $pagecontent array of the page
  * and store it in the {@link $storedPages} property.
  * 
  * Example of the returned $pageContent array:
  * {@example readPage.return.example.php}
  * 
  * <b>Used Constants</b><br>
  *    - <var>DOCUMENTROOT</var> the absolut path of the webserver
  * 
  * @param int|array  $page           a page ID or a $pageContent array (will then returned immediately)
  * @param int        $category       (optional) a category ID, if FALSE it will try to load this page from the non-category
  * 
  * @uses getStoredPages()		for getting the {@link $storedPages} property
  * @uses setStoredPages()		to store a new loaded $pageContent array in the {@link $storedPages} property
  * 
  * @return array|FALSE the $pageContent array of the requested page or FALS, if it couldn't open the file
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function readPage($page,$category = false) {
    //echo 'PAGE: '.$page.' -> '.$category.'<br />';
    
    // if $page is a valid $pageContent array return it immediately
    if($this->isPageContentArray($page))
      return $page;
       
    $storedPages = $this->getStoredPages();
    
    // ->> IF the page is already loaded
    if(isset($storedPages[$page])) {
      //echo '<br />->USED STORED '.$page.'<br />';        
      return $storedPages[$page];
      
    // ->> ELSE load the page and store it in the storePages PROPERTY
    } else {
         
      // adds .php to the end if its missing
      if(substr($page,-4) != '.php')
        $page .= '.php';
    
      // adds a slash behind the $category / if she isn't empty
      if(!empty($category))
        if(substr($category,-1) !== '/')
            $category = $category.'/';
    
      if($category === false || $category == 0)
        $category = '';
    
      //echo '<br />LOAD PAGE: '.$page.'<br />';   
      //echo 'CATEGORY: '.$category.'<br />';
    
      if(@include(DOCUMENTROOT.$this->adminConfig['savePath'].$category.$page)) {
      
        // UNESCPAE the SINGLE QUOTES '
        $pageContent['content'] = str_replace("\'", "'", $pageContent['content'] );
      
        return $this->setStoredPages($pageContent);
      } else  // returns false if it couldn't include the page
        return false;
    }
  }
  
 /**
  * <b>Name</b> loadPageIds()<br>
  * 
  * Goes through the flatfiles folder and looks in which category is which page, it then returns an array with all IDs.
  * 
  * Example of the returned array:
  * {@example loadPageIds.return.example.php}
  * 
  * <b>Used Constants</b><br>
  *    - <var>DOCUMENTROOT</var> the absolut path of the webserver
  * 
  * @param int|bool $category   (optional) the category ID to check the containing page IDs, if FALSE its checks the non-category, if TRUE it checks all categories including the non-category (can also be the {@link $categoryConfig} property)
  * 
  * @uses $adminConfig          for the save path of the flatfiles
  * 
  * @return array|false an array with page IDs and the affiliated category IDs or empty array if the category had no pages
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function loadPageIds($category = false) {
                    
    // vars
    $pagesArray = array();
    $categoryDirs = array();
    $categoryArray = $this->categoryConfig;

    // if $category === true,
    // load ALL CATEGORIES and the NON-CATEGORY
    if($category === true && is_array($categoryArray)) {
      array_unshift($categoryArray,array('id' => 0));
      $category = $categoryArray;
    }
    
    // COLLECT THE DIRS in an array
    // if $category is an array, i stores alle dirs in $this->adminConfig['savePath'] in an array
    if(is_array($category)) {
      
        foreach($category as $categoryArray) {          
          $dir = '';
          
          // *** if it is $this->categoryConfig settings array
          if(is_array($categoryArray) &&
             array_key_exists('id',$categoryArray)) {
            // if category == 0, means that the files are stored in the $this->adminConfig['savePath'] folder
            if($categoryArray['id'] == 0)
              $dir = DOCUMENTROOT.$this->adminConfig['savePath'];
            elseif(is_numeric($categoryArray['id']))
              $dir = DOCUMENTROOT.$this->adminConfig['savePath'].$categoryArray['id'];
          
          // *** if its just an array with the ids of the categories
          } else {
            // if category == 0, means that the files are stored in the $this->adminConfig['savePath'] folder
            if(is_numeric($categoryArray) && $categoryArray == 0) //$categoryArray === false ||
              $dir = DOCUMENTROOT.$this->adminConfig['savePath'];
            elseif(is_numeric($categoryArray))
              $dir = DOCUMENTROOT.$this->adminConfig['savePath'].$categoryArray;
          }
          
          // stores the paths in an array
          $categoryDirs[] = $dir;
        }
    } else {    
      if($category === false || (is_numeric($category) && $category == 0))
        $categoryDirs[0] = DOCUMENTROOT.$this->adminConfig['savePath'];
      elseif(is_numeric($category))
        $categoryDirs[0] = DOCUMENTROOT.$this->adminConfig['savePath'].$category;
    }
    
    // LOAD THE FILES out of the dirs
    // goes trough all category dirs and put the page arrays into an array an retun it
    foreach($categoryDirs as $dir) {
  
      // opens every category dir and stores the arrays of the pages in an array
      if(is_dir($dir) && $dir != DOCUMENTROOT) {

        $pages = array();
        
        // checks if its a category or the non-category
        if($category === false || $category == 0 || !is_numeric(basename($dir)))
          $categoryId = false;
        else
          $categoryId = basename($dir);
      
        $catDir = opendir($dir);
        while(false !== ($file = readdir($catDir))) {
        if($file != "." && $file != "..") {
            if(is_file($dir."/".$file)){
              // load Pages, without a category
              if($categoryId === false) {
	        $pages[] = array('page' => substr($file,0,-4), 'category' => 0);
              // load Pages, with a category
              } else {
	        $pages[] = array('page' => substr($file,0,-4), 'category' => $categoryId);
              }
            }
          }
        }
        closedir($catDir);
        
        // adds the new sorted category to the return array
        $pagesArray = array_merge($pagesArray,$pages);
      }
    }

    // return the page and category ID(s)
    return $pagesArray;
  }
  
 /**
  * <b>Name</b> loadPages()<br>
  * 
  * Loads the $pageContent arrays from pages in a specific category(ies) or all categories.
  * 
  * Loads all $pageContent arrays of a given category, by going through the {@link $storedPageIds} property.
  * It check first whether the current $pageContent array was not already loaded and is contained in the {@link $storedPages} property.
  * If not the {@link generalFunctions::readPage()} function is called to include the $pagecontent array of the page
  * and store it in the {@link $storedPages} property.
  * 
  * <b>Notice</b>: after loading all $pageContent arrays of a category, the array with the containing $pageContent arrays will be sorted.
  * 
  * Example of the returned $pageContent arrays:
  * {@example loadPages.return.example.php}
  * 
  * @param bool|int|array  $category           (optional) a category ID, or an array with category IDs. TRUE to load all categories (including the non-category) or FALSE to load only the non-category pages
  * @param bool		         $loadPagesInArray   (optional) if TRUE it returns the $pageContent arrays of the pages in the categories, if FALSE it only returns the page IDs of the requested category(ies)
  * 
  * @uses $categoryConfig     to get the sorting of the category
  * @uses getStoredPages()		for getting the {@link $storedPages} property
  * @uses readPage()			    to load the $pageContent array of the page
  * 
  * @return array the $pageContent array of the requested pages
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */  
  function loadPages($category = false, $loadPagesInArray = true) {
    
    // IF $category FALSE set $category to 0
    if($category === false)
      $category = '0';
    
    // ->> RETURN $pageContent arrays
    if($loadPagesInArray === true) {
      
      //vars
      $pagesArray = array();

      // IF $category TRUE create array with non-category and all category IDs
      if($category === true) {
      	// puts the categories IDs in an array
      	$category = array(0);
      	foreach($this->categoryConfig as $eachCategory) {
      	  $category[] = $eachCategory['id'];
      	}
      }
      
      // change category into array
      if(is_numeric($category))
        $category = array($category);
        
      // go trough all given CATEGORIES       
      foreach($category as $categoryId) {
        
        // go trough the storedPageIds and open the page in it
        $newPageContentArrays = array();
        foreach($this->getStoredPageIds() as $pageIdAndCategory) {
          // use only pages from the right category
          if($pageIdAndCategory['category'] == $categoryId) {
            //echo 'PAGE: '.$pageIdAndCategory['page'].' -> '.$categoryId.'<br />';
            $newPageContentArrays[] = $this->readPage($pageIdAndCategory['page'],$pageIdAndCategory['category']);            
          }
        }
        
        // sorts the category
        if(is_array($newPageContentArrays)) { // && !empty($categoryId) <- prevents sorting of the non-category
          if($categoryId != 0 && $this->categoryConfig[$categoryId]['sortbypagedate'])
            $newPageContentArrays = $this->sortPages($newPageContentArrays, 'sortByDate');
          else
            $newPageContentArrays = $this->sortPages($newPageContentArrays, 'sortBySortOrder');
        }
      
        // adds the new sorted category to the return array
        $pagesArray = array_merge($pagesArray,$newPageContentArrays);
      }
      //print_r($pagesArray);
      return $pagesArray;
      
    // ->> RETURN ONLY the page & category IDs
    } else {
      
      // -> uses the $this->storedPageIds an filters out only the given category ID(s)
      $pageIds = $this->getStoredPageIds();
      
      if($category !== true) {
      	 $newPageIds = false;
      	 foreach($pageIds as $pageId) {
        	  if((is_array($category) && in_array($pageId['category'],$category)) || 
        	     $category == $pageId['category'])
        	    $newPageIds[] = array('page' => $pageId['page'], 'category' => $pageId['category']);
         }
      } else
	      $newPageIds = $pageIds;
      
      return $newPageIds;
    }
  }
  
 /**
  * <b>Name</b> isPageContentArray()<br>
  * 
  * Checks the given <var>$page</var> parameter is a valid <var>$pageContent</var> array.
  * 
  * @param int|array $page   the variable to check 
  * 
  * @return bool
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function isPageContentArray($page) {
               
    return (is_array($page) && array_key_exists('content',$page)) ? true : false;
  }
  
 /**
  * <b>Name</b> createHref()<br>
  * 
  * Creates a href-attribute from the given <var>$pageContent</var> parameter,
  * if the <var>sessionId</var> parameter is given it adds them on the end of the href string.
  * 
  * @param array        $pageContent  the $pageContent array of a page
  * @param string|false $sessionId    (optional) the session ID string in the following format: "sessionName=sessionId"
  * 
  * @uses $adminConfig    for the variabel names which the $_GET variable will use for category and page
  * @uses $categoryConfig for the category name if speaking URLs i activated
  * @uses encodeToUrl()   to encode the category and page name to a string useable in URLs
  *  
  * @return string the href string ready to use in a href attribute
  * 
  * @see feindura::createHref()
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function createHref($pageContent, $sessionId = false) {
    
    // vars
    $page = $pageContent['id'];
    $category = $pageContent['category'];
    
    // ->> create HREF with speaking URL
    // *************************************
    if($this->adminConfig['speakingUrl'] == 'true') {
      $speakingUrlHref = '';
      
      // adds the category to the href attribute
      if($category != 0) {
        $categoryLink = '/category/'.$this->encodeToUrl($this->categoryConfig[$category]['name']).'/';
      } else $categoryLink = '';
      
      
      $speakingUrlHref .= $categoryLink;
      if($categoryLink == '')
        $speakingUrlHref .= '/page/'.$this->encodeToUrl($pageContent['title']);
      else
        $speakingUrlHref .= $this->encodeToUrl($pageContent['title']);
      $speakingUrlHref .= '.html';
      
      if($sessionId)
        $speakingUrlHref .= '?'.$sessionId;
      
      return $speakingUrlHref;
    
    // ->> create HREF with varNames und Ids
    // *************************************
    } else {
      $getVarHref = '';
      
      // adds the category to the href attribute
      if($category != 0)
        $categoryLink = $this->adminConfig['varName']['category'].'='.$category.'&amp;';
      else $categoryLink = '';
      
      $getVarHref = '?'.$categoryLink.$this->adminConfig['varName']['page'].'='.$page;
      
      if($sessionId)
        $getVarHref .= '&amp;'.$sessionId;
      
      return $getVarHref;
    }  
  }
  
 /**
  * <b>Name</b> sortPages()<br>
  * 
  * Sort an array with the <var>$pageContent</var> arrays by a given sort-function.
  * The following sort functions can be used for the <var>$sortBy</var> parameter:<br>
  *   - "sortBySortOrder"
  *   - "sortByCategory"
  *   - "sortByDate"
  *   - "sortByVisitedCount"
  *   - "sortByVisitTimeMax"  
  * 
  * @param array        $pageContentArrays  the $pageContent array of a page
  * @param string|false $sortBy             (optional) the name of the sort function, if FALSE it uses automaticly the right sort-function of the category
  * 
  * @uses $categoryConfig        to find the right sort function for every category
  * @uses isPageContentArray()   to check if the given $pageContent arrays are valid
  * 
  * @return array the sorted array with the $pageContent arrays
  * 
  * @see sort.functions.php
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function sortPages($pageContentArrays, $sortBy = false) {
    
    if(is_array($pageContentArrays) && isset($pageContentArrays[0])) {
    
      // CHECK if the arrays are valid $pageContent arrays
      // OTHER BUTTONSwise return the unchanged array
      if(!$this->isPageContentArray($pageContentArrays[0]))
        return $pageContentArrays;
      
      // sorts the array with the given sort function
      //natsort($pagesArray);
      
      // first sort the ARRAY by CATEGORY
      usort($pageContentArrays, 'sortByCategory');
      
      // -> SPLIT the ARRAY IN CATEGORIES
      $lastCategory = false;
      $newPageContentArrays = array();
      foreach($pageContentArrays as $pageContentArray) {
          
          //print_r($pageContentArray);
          
          if($pageContentArray['category'] != $lastCategory) {
            $categoriesArrays[] = $newPageContentArrays;
            $newPageContentArrays = array();
          }
          
          // adds the pageContent Array to a new array
          $newPageContentArrays[] = $pageContentArray;  
          $lastCategory = $pageContentArray['category'];
      }
      // adds the last $newPageContentArrays
      $categoriesArrays[] = $newPageContentArrays;
      
      // -> SORTS every CATEGORY
      $newPageContentArray = array();
      $category = false;   
      foreach($categoriesArrays as $categoriesArray) {        
        
        // gets the current category
        if(isset($categoriesArray[0]))
          $category = $categoriesArray[0]['category'];
        
        // SORTS the category the GIVEN SORTFUNCTION
        if($sortBy === false) {
          if($category && $this->categoryConfig[$category]['sortbypagedate'])
            usort($categoriesArray, 'sortByDate');
          else
            usort($categoriesArray, 'sortBySortOrder');
        } else
            usort($categoriesArray, $sortBy);  
        
        
        // makes the category ascending, if its in the options
        if($category && $this->categoryConfig[$category]['sortascending'])
          $categoriesArray = array_reverse($categoriesArray);
         
        foreach($categoriesArray as $pageContent) {
          // creates the NEW sorted array
          $newPageContentArray[] = $pageContent;
        }
      }
      
      return $newPageContentArray;
    } else
      return $pageContentArrays;
  }

  /**
   * <b>Name</b> getStylesByPriority()<br />
   * 
   * Returns the right stylesheet-file path, ID or class-attribute.
   * If the <var>$givenStyle</var> parameter is empty,
   * it check if the category has a styleheet-file path, ID or class-attribute set return the value if not return the value from the {@link $adminConfig administartor-settings config}.
   * 
   * <b>Used Global Variables</b><br />
   *    - <var>$adminConfig</var> the administrator-settings config (included in the {@link general.include.php}) 
   *    - <var>$categoryConfig</var> the categories-settings config (included in the {@link general.include.php})
   * 
   * @param string $givenStyle the string with the stylesheet-file path, id or class
   * @param string $styleType  the key for the $pageContent, {@link $categoryConfig} or {@link $adminConfig} array can be "styleFile", "styleId" or "styleClass" 
   * @param int    $category   the ID of the category to bubble through
   * 
   * @return string the right stylesheet-file, ID or class
   * 
   * 
   * @version 1.01
   * <br />
   * <b>ChangeLog</b><br />
   *    - 1.01 moved to generalFunctions class   
   *    - 1.0 initial release
   * 
   */
  function getStylesByPriority($givenStyle,$styleType,$category) {
    
    // check if the $givenStyle is empty
    if(empty($givenStyle) || $givenStyle == 'a:0:{}') {
    
      return (empty($this->categoryConfig[$category][$styleType]) || $this->categoryConfig[$category][$styleType] == 'a:0:{}')
        ? $this->adminConfig['editor'][$styleType]
        : $this->categoryConfig[$category][$styleType];
    
    // OTHER BUTTONSwise it passes through the $givenStyle parameter
    } else
      return $givenStyle;
    
  }

 /**
  * <b>Name</b> getRealCharacterNumber()<br>
  * 
  * Shortens the given <var>$string</var> parameter to the given <var>$textLength</var> parameter and counts the contained htmlentities.
  * Then adds the length of htmlentites to the $textLength and return it.
  *
  * @param string    $string       the string to find out the real length for shorting
  * @param int|bool  $textLength   (optional) the number of which the text should be shorten or FALSE to return only the string length
  *
  * @return int the numbger of characters plus htmlentities characters
  *
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  */ 
  function getRealCharacterNumber($string, $textLength = false) {
    
    // get the full string length if no maximum characternumber is given
    if($textLength === false)
      return strlen($string);
      
    // shorten the string to the maximum characternumber
    $string = substr($string,0,$textLength);
    
    // find ..ml; and ..lig; etc and adds the number of findings * strlen($finding) (~6) characters to the length
    preg_match_all('/\&[A-Za-z]{1,6}\;/', $string, $entitiesFindings);
    foreach($entitiesFindings[0] as $finding) {
      $finding = preg_replace("/ +/", '', $finding);
      $textLength += (strlen($finding));
    }
      
    return $textLength;
  }
  
 /**
  * <b>Name</b> escapeQuotesRecursive()<br>
  * 
  * Escapes single quotes of an array or an string, and goes also deeper in the array.
  * 
  * @param array|string $data the data, where the quotes should be escaped
  * 
  * @return array|string the escaped array or string
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function escapeQuotesRecursive($data) {
    
    if(is_string($data)) {      
      $data = str_replace("\'","'",$data);
      $data = str_replace("'","\'",$data);
      return  $data;
      
    } elseif(is_array($data)) {
      $newData = array();
      foreach($data as $key => $value) {
        $newData[$key] = $this->escapeQuotesRecursive($value);
      }
      return $newData;
    } else
      return $data;
  }
  
 /**
  * <b>Name</b> cleanSpecialChars()<br>
  * 
  * Removes all special chars from a string.
  * 
  * @param string    $string          the string to clear
  * @param string    $replaceString   (optional) the string which replaces all special chars found
  * 
  * @return string the cleaned string
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function cleanSpecialChars($string,$replaceString = '') {
    
    // removes multiple spaces
    $string = preg_replace("/ +/", ' ', $string);
    // allows only a-z and 0-9, "_", ".", " "
    $string = preg_replace('/[^\w^.^&^;^ ]/u', $replaceString, $string);
    if(!empty($replaceString))
      $string = preg_replace('/'.$replaceString.'+/', $replaceString, $string);
    //$string = str_replace( array('�','�','�','?','�','|','@','[',']','�','�','�','!','�',',',";","*","�","{",'}','^','�','`','=',":"," ","%",'+','/','\\',"&",'#','!','?','�',"$","�",'"',"'","(",")"), $replaceSign, $string);
    
    return $string;
  }
  
 /**
  * <b>Name</b> prepareStringInput()<br>
  * 
  * Clears a string from double withe spaces, slashes and htmlentities all special chars.
  * 
  * @param string $text the string to clear
  * 
  * @return string the cleaned string
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function prepareStringInput($text) {
      
      // format text
      $text = preg_replace("/ +/", " ", $text);
      $text = preg_replace('#\\\\+#', '', $text);
      $text = stripslashes($text);
      $text = htmlentities($text,ENT_QUOTES,'UTF-8');
      
      return $text;
  }
  
 /**
  * <b>Name</b> shortenTitle()<br>
  * 
  * Shortens a string to its letter numbers (conciders htmlentities as multiple characters).
  * 
  * @param string $title  the title string to shorten
  * @param int    $length the number of letters the string should have after 
  * 
  * @return string the shortend title or the unchanged title, if shorten is not necessary
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function shortenTitle($title, $length) {
      
      //vars
      $realLength =  $this->getRealCharacterNumber($title,$length);
      
      // chek if shorting is necessary
      if(strlen($title) <= $realLength)
        return $title;
      // shorten the title
      else
        return substr($title,0,($realLength - 2)).'..'; // -2 because of the add ".."
  }
  
 /**
  * <b>Name</b> encodeToUrl()<br>
  * 
  * Converts a String so that it can be used in an URL.
  * 
  * @param string $string the strign which should be converted
  * 
  * @return string ready to use in an URL
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function encodeToUrl($string) {
      
      // makes the string to lower
      $string = strtolower($string);
      
      // format string
      $string = preg_replace("/ +/", '_', $string);    
      
      // changes umlaute
      $string = str_replace('&auml;','ae',$string);
      $string = str_replace('&uuml;','ue',$string);
      $string = str_replace('&ouml;','oe',$string);
      //$string = str_replace('&Auml;','Ae',$string);         
      //$string = str_replace('&Uuml;','Ue',$string);      
      //$string = str_replace('&Ouml;','Oe',$string);
      
      // clears htmlentities example: &amp;
      $string = preg_replace('/&[a-zA-Z0-9]+;/', '', $string);
      // allows only a-z and 0-9 and _ and -
      $string = preg_replace('/[^\w_-]/u', '', $string);
      
      // clears double __
      $string = preg_replace("/_+/", '_', $string);
      
      return $string;
  }

 /**
  * <b>Name</b> readFolder()<br />
  * 
  * Reads a folder and return it's subfolders and files.
  * 
  * Example of the returned array:
  * <code>
  * array(
  *    "files" => array(
  *                   0 => '/path/file1.php',
  *                   1 => '/path/file2.php',
  *                   ),
  *    "folders" => array(
  *                   0 => '/path/subfolder1',
  *                   1 => '/path/subfolder2',
  *                   2 => '/path/subfolder3'
  *                   )
  *    )
  * </code>
  * 
  * <b>Used Constants</b><br />
  *    - <var>DOCUMENTROOT</var> the absolut path of the webserver
  * 
  * @param string $folder the absolute path of an folder to read
  * 
  * @return array|false an array with the folder elements, FALSE if the folder not is a directory
  * 
  * @version 1.0
  * <br />
  * <b>ChangeLog</b><br />
  *    - 1.0 initial release
  * 
  */
  function readFolder($folder) {
    
    // TODO: use scandir()
    
    if(empty($folder))
      return false;
    
    //change windows path
    $folder = str_replace('\\','/',$folder);
    
    // -> adds / on the beginning of the folder
    if(substr($folder,0,1) != '/')
      $folder = '/'.$folder;
    // -> adds / on the end of the folder
    if(substr($folder,-1) != '/')
      $folder .= '/';
    
    //clean vars  
    $folder = preg_replace("/\/+/", '/', $folder);
    $folder = str_replace('/'.DOCUMENTROOT,DOCUMENTROOT,$folder);  
    
    // vars
    $return = false;  
    $fullFolder = $folder;
    
    // adds the DOCUMENTROOT  
    $fullFolder = str_replace(DOCUMENTROOT,'',$fullFolder);  
    $fullFolder = DOCUMENTROOT.$fullFolder; 
    
    // open the folder and read the content
    if(is_dir($fullFolder)) {
      $openedDir = @opendir($fullFolder);  // @ zeichen eingef�gt
      while(false !== ($inDirObjects = @readdir($openedDir))) {
        if($inDirObjects != "." && $inDirObjects != "..") {      
          if(is_dir($fullFolder.$inDirObjects)) {        
            $return['folders'][] = $folder.$inDirObjects;
          } elseif(is_file($fullFolder.$inDirObjects)) {
            $return['files'][] = $folder.$inDirObjects;
          }
        }
      }
      @closedir($openedDir);
    }
    
    return $return;  
  }

 /**
  * <b>Name</b> readFolderRecursive()<br />
  * 
  * Reads a folder recursive and return it's subfolders and files, opens then also the subfolders and read them, etc.
  * 
  * Example of the returned array:
  * <code>
  * array(
  *    "files" => array(
  *                   0 => '/path/file1.php',
  *                   1 => '/path/subfolder1/file2.php',
  *                   ),
  *    "folders" => array(
  *                   0 => '/path/subfolder1',
  *                   1 => '/path/subfolder2/subsubfolder1',
  *                   2 => '/path/subfolder2/subsubfolder2'
  *                   )
  *    )
  * </code>
  * 
  * <b>Used Constants</b><br />
  *    - <var>DOCUMENTROOT</var> the absolut path of the webserver
  * 
  * @param string $folder the absolute path of an folder to read
  * 
  * @return array|false an array with the folder elements, FALSE if the folder is not a directory
  * 
  * @version 1.0
  * <br />
  * <b>ChangeLog</b><br />
  *    - 1.0 initial release
  * 
  */
  function readFolderRecursive($folder) {
    
    // TODO: use scandir()
    
    if(empty($folder))
      return false;
    
    // adds a slash on the beginning
    if(substr($folder,0,1) != '/')
      $folder = '/'.$folder;
    
    //clean vars
    $folder = preg_replace("/\/+/", '/', $folder);
    $folder = str_replace('/'.DOCUMENTROOT,DOCUMENTROOT,$folder);
    
    //vars  
    $fullFolder = DOCUMENTROOT.$folder;  
    $goTroughFolders['folders'][0] = $fullFolder;
    $goTroughFolders['files'] = array();
    $subFolders = array();
    $files = array();
    $return['folders'] = false;
    $return['files'] = false;
      
    // ->> goes trough all SUB-FOLDERS  
    while(!empty($goTroughFolders['folders'][0])) {
  
      // ->> GOES TROUGH folders
      foreach($goTroughFolders['folders'] as $subFolder) {
        //echo '<br /><br />'.$subFolder.'<br />';     
        $inDirObjects = $this->readFolder($subFolder);
        
        // -> add all subfolders to an array
        if(isset($inDirObjects['folders']) && is_array($inDirObjects['folders'])) {        
          $subFolders = array_merge($subFolders, $inDirObjects['folders']);
        }        
      
        // -> add folders to the $return array
        if(isset($inDirObjects['folders']) && is_array($inDirObjects['folders'])) {
          foreach($inDirObjects['folders'] as $folder) {
            $return['folders'][] = str_replace(DOCUMENTROOT,'',$folder);
          }
        }
        // -> add files to the $return array
        if(isset($inDirObjects['files']) && is_array($inDirObjects['files'])) {
          foreach($inDirObjects['files'] as $file) {
            $return['files'][] = str_replace(DOCUMENTROOT,'',$file);
          }
        }
      }
      
      $goTroughFolders['folders'] = $subFolders;
      $goTroughFolders['files'] = $files;
  
      $subFolders = array();
      $files = array();
    }
  
    return $return;
  } 

 /**
  * <b>Name</b> folderIsEmpty()<br />
  * 
  * Check if a folder is empty.
  * 
  * <b>Used Constants</b><br />
  *    - <var>DOCUMENTROOT</var> the absolut path of the webserver
  * 
  * @param string $folder the absolute path of an folder to check
  * 
  * @return bool TRUE if its empty, otherwise FALSE
  * 
  * @version 1.0
  * <br />
  * <b>ChangeLog</b><br />
  *    - 1.0 initial release
  * 
  */
  function folderIsEmpty($folder) {
    
    if($this->readFolder(DOCUMENTROOT.$folder) === false)
      return true;
    else
      return false;
  
  }
  
  /**
   * <b>Name</b> createStyleTags()<br />
   * 
   * Goes through a folder recursive and creates a HTML <link> tag for every stylesheet-file found.
   * 
   * <b>Used Global Variables</b><br />
   *    - <var>$adminConfig</var> the administrator-settings config (included in the {@link general.include.php})
   * 
   * @param string $folder  the absolute path of the folder to look for stylesheet files
   * @param bool   $backend if TRUE is substract the {@link feinduraBase::$adminConfig $adminConfig['basePath']} from the stylesheet link
   * 
   * @uses generalFunctions::readFolderRecursive() to read the folder
   * 
   * @return string|false the HTML <link> tags or FALSE if no stylesheet-file was found
   * 
   * @version 1.0
   * <br />
   * <b>ChangeLog</b><br />
   *    - 1.0 initial release
   * 
   */
  function createStyleTags($folder, $backend = true) {
    
    //var
    $return = false;
    
    // ->> goes trough all folder and subfolders
    $filesInFolder = $this->readFolderRecursive($folder);
    if(is_array($filesInFolder['files'])) {
      foreach($filesInFolder['files'] as $file) {
        // -> check for CSS FILES
        if(substr($file,-4) == '.css') {
          // -> removes the $adminConfig('basePath')
          if($backend)          
            $file = str_replace($this->adminConfig['basePath'],'',$file);
          // -> WRITES the HTML-Style-Tags
          $return .= '  <link rel="stylesheet" type="text/css" href="'.$file.'" />'."\n";
        }
      }
    }
    
    return $return;
  }
  
 /**
  * <b>Name</b> showMemoryUsage()<br>
  * 
  * Shows the memory usage at the point of the script where this function is called.
  * 
  * @return void
  * 
  * @version 1.0
  * <br>
  * <b>ChangeLog</b><br>
  *    - 1.0 initial release
  * 
  */
  function showMemoryUsage() {
      $mem_usage = memory_get_usage(true);
      
      echo $mem_usage.' -> ';
      
      if ($mem_usage < 1024)
          echo $mem_usage." bytes";
      elseif ($mem_usage < 1048576)
          echo round($mem_usage/1024,2)." kilobytes";
      else
          echo round($mem_usage/1048576,2)." megabytes";
         
      echo "<br />";
  }
}
?>