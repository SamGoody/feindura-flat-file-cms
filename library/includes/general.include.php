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
 * This file includes all necessary <var>classes</var> and configs for the use in the FRONTEND and the BACKEND
 *
 * @version 0.15
 */

error_reporting(E_ALL & ~E_NOTICE);// E_ALL ^ E_NOTICE ^ E_WARNING

/**
 * The absolut path of the webserver
 */ 
define('DOCUMENTROOT',$_SERVER["DOCUMENT_ROOT"]);

/**
 * The identity of the user
 */ 
define('IDENTITY', md5($_SERVER['HTTP_USER_AGENT'].'::'.$_SERVER['REMOTE_ADDR'].'::'.$_SERVER["HTTP_HOST"]));

/**
 * The permissions set to files, created by feindura
 * 
 * format: 0755  
 */ 
define('PERMISSIONS', 0755);


$phpTags = file(dirname(__FILE__)."/../processes/phptags");
/**
 * The php start tag for us in saveing functions
 */ 
define('PHPSTARTTAG',$phpTags[0]."\n");
/**
 * The php end tag for us in saveing functions
 */ 
define('PHPENDTAG',"\n".$phpTags[1]);

// ->> get CONFIGS
/**
 * The administrator-settings config
 * 
 * This config <var>array</var> is included from: <i>"feindura-CMS/config/admin.config.php"</i>
 * 
 * @global array $GLOBALS['adminConfig']
 */
if(!$adminConfig =      @include_once(dirname(__FILE__)."/../../config/admin.config.php"))
  $adminConfig =      array();
$GLOBALS['adminConfig'];

/**
 * The user-settings config
 * 
 * This config <var>array</var> is included from: <i>"feindura-CMS/config/user.config.php"</i>
 * 
 * @global array $GLOBALS['userConfig']
 */
if(!$userConfig =      @include_once(dirname(__FILE__)."/../../config/user.config.php"))
  $userConfig =      array();
$GLOBALS['userConfig'];

/**
 * The website-settings config
 * 
 * This config <var>array</var> is included from: <i>"feindura-CMS/config/website.config.php"</i>
 * 
 * @global array $GLOBALS['websiteConfig']
 */
if(!$websiteConfig =    @include_once(dirname(__FILE__)."/../../config/website.config.php"))
  $websiteConfig =    array();
$GLOBALS['websiteConfig'];

/**
 * The categories-settings config
 * 
 * This config <var>array</var> is included from: <i>"feindura-CMS/config/category.config.php"</i>
 * 
 * @global array $GLOBALS['categoryConfig']
 */
if(!$categoryConfig =   @include_once(dirname(__FILE__)."/../../config/category.config.php"))
  $categoryConfig =       array();
$GLOBALS['categoryConfig'];

/**
 * The statistic-settings config
 * 
 * This config <var>array</var> is included from: <i>"feindura-CMS/config/statistic.config.php"</i>
 * 
 * @global array $GLOBALS['statisticConfig']
 */
if(!$statisticConfig =  @include_once(dirname(__FILE__)."/../../config/statistic.config.php"))
  $statisticConfig =  array();
$GLOBALS['statisticConfig'];

/**
 * The plugin-settings config
 * 
 * This config <var>array</var> is included from: <i>"feindura-CMS/config/plugin.config.php"</i>
 * 
 * @global array $GLOBALS['pluginsConfig']
 */
if(!$pluginsConfig =  @include_once(dirname(__FILE__)."/../../config/plugins.config.php"))
  $pluginsConfig =  array();
$GLOBALS['pluginsConfig'];


/**
 * The website-statistics
 * 
 * This statistics <var>array</var> is included from: <i>"feindura-CMS/config/website.statistic.php"</i>
 * 
 * @global array $GLOBALS['websiteStatistic']
 */
if(!$websiteStatistic = @include_once(dirname(__FILE__)."/../../statistic/website.statistic.php"))
  $websiteStatistic = array();
$GLOBALS['websiteStatistic'];


// -> FUNCTIONS
/**
 * Includes the {@link sort.functions.php}
 */ 
require_once(dirname(__FILE__)."/../functions/sort.functions.php");

// ->> autoload CLASSES
/**
 * Autoloads all classes
 *  
 */ 
function __autoload($class_name) {
  require_once(dirname(__FILE__)."/../classes/".$class_name.".class.php");
}


?>