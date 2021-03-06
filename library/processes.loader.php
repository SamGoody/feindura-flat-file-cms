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
*/
// processes.loader.php version 0.1

/**
 * Includes the login and filters the incoming data by xssFilter
 */
require_once(dirname(__FILE__)."/includes/secure.include.php");

// start of loading the processes
// -------------------------------------------------------------------------------------------------------------

// if page ID is given, it loads the HTML-Editor
// --------------------------------------------
if(empty($_GET['site']) && ($_GET['category'] == 0 || !empty($_GET['category'])) && !empty($_GET['page'])) {
  
  // set the category 0 if there are no categories in the categoriesSettings.php
  if(empty($categoryConfig))
    $_GET['category'] = 0;
  
  include (dirname(__FILE__).'/processes/editor.process.php');
  
// OTHER BUTTONSwise, load the sites
// -------------------------------------------------------------------------------------------------------------
} else {
  
  // SWITCHES the &_GET['site'] var
  switch($_GET['site']) {
    // PAGES
    case 'pages':
      if(empty($categoryConfig))
        $_GET['category'] = 0;
      include (dirname(__FILE__).'/processes/listPages.process.php');
      break;
    // ADMINSETUP
    case 'adminSetup':
      include (dirname(__FILE__).'/processes/adminSetup.process.php');
      break;
    // ADMINSETUP
    case 'pageSetup':
      include (dirname(__FILE__).'/processes/pageSetup.process.php');
      break;
    // WEBSITESETUP
    case 'websiteSetup':
      include (dirname(__FILE__).'/processes/websiteSetup.process.php');
      break;
    // STATISTICSETUP
    case 'statisticSetup':
      include (dirname(__FILE__).'/processes/statisticSetup.process.php');
      break;
    // USERSETUP
    case 'userSetup':
      include (dirname(__FILE__).'/processes/userSetup.process.php');
      break;
    // PLUGINSETUP
    case 'pluginSetup':
      include (dirname(__FILE__).'/processes/pluginSetup.process.php');
      break;
    // MODULSETUP
    case 'modulSetup':
      include (dirname(__FILE__).'/processes/modulSetup.process.php');
      break;
    // BACKUP
    case 'backup':
      include (dirname(__FILE__).'/processes/backup.process.php');
      break;
  } //switch END

}

?>