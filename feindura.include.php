<?php 
/*
    feindura - Flat File Content Management System
    Copyright (C) Fabian Vogelsteller [frozeman.de]

    This program is free software;
    you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
    without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with this program;
    if not,see <http://www.gnu.org/licenses/>.

* feindura.include.php version 0.13
*
*
* !!! PROTECTED VARs (do not overwrite these in your script)
* -> $feindura_adminConfig
* -> $feindura_websiteConfig
* -> $feindura_categoryConfig
* -> $feindura_statisticConfig
* -> $feindura_pluginsConfig
* -> $feindura_websiteStatistic
*/

// -> starts a SESSION; needed to prevent multiple counting of the visitor in the statistics
ini_set('session.gc_maxlifetime', 3600); // saves the session for 60 minutes
session_name("session");
session_start();

// -> CHECKS if cookies are enabled
if(!isset($_COOKIE['feindura_checkCookies']) || $_COOKIE['feindura_checkCookies'] != 'true')    
    setcookie( "feindura_checkCookies", 'true'); // try to set a cookie, to check in the next webpage whether its set or not

// -> INCLUDE ALL important FUNCTIONS, CLASSES and CONFIG vars
require_once(dirname(__FILE__)."/library/includes/general.include.php");

// -> rename the config var names
$feindura_adminConfig = $adminConfig;
$feindura_websiteConfig = $websiteConfig;
$feindura_categoryConfig = $categoryConfig;
$feindura_statisticConfig = $statisticConfig;
$feindura_pluginsConfig = $pluginsConfig;
$feindura_websiteStatistic = $websiteStatistic;
// -> delete old config vars
unset($adminConfig,$websiteConfig,$categoryConfig,$statisticConfig,$pluginsConfig,$websiteStatistic);

?>