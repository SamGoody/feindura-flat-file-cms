<?php 
/*
    feindura - Flat File Content Management System
    Copyright (C) 2009 Fabian Vogelsteller [frozeman.de]

    This program is free software;
    you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
    without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with this program;
    if not,see <http://www.gnu.org/licenses/>.
*
* general.include.php version 0.1
*/

define('DOCUMENTROOT',$_SERVER["DOCUMENT_ROOT"]);

$phpTags = file(dirname(__FILE__)."/process/phptags.txt"); 
define('PHPSTARTTAG',$phpTags[0]."\n");
define('PHPENDTAG',"\n".$phpTags[1]);

?>