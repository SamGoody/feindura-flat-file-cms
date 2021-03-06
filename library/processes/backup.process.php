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

* processes/backup.process.php version 0.1
*/

/**
 * Includes the login and filters the incoming data by xssFilter
 */
require_once(dirname(__FILE__)."/../includes/secure.include.php");


// ------------>> DOWNLOAD BACKUP
if(isset($_GET['downloadBackup'])) {

  // -> check backup folder
  $unwriteableList = false;
  $checkFolder = $adminConfig['basePath'].'backups/';  
  // try to create folder
  if(!is_dir(DOCUMENTROOT.$checkFolder))
    mkdir(DOCUMENTROOT.$checkFolder,PERMISSIONS); 
  $unwriteableList .= isWritableWarning($checkFolder);
  
  // ->> create archive
  if(!$unwriteableList) {
    
    // -> generate filename
    $websitePath = str_replace(array('/',"\\"),'+',$adminConfig['websitePath']);
    $websitePath = ($websitePath != '+') ? substr($websitePath,0,-1) : '';
    $backupName = 'feinduraBackup_'.$_SERVER['HTTP_HOST'].$websitePath.'_'.date('Y-m-d_H-i').'.zip';
    $backupFile = DOCUMENTROOT.$adminConfig['basePath'].'backups/'.$backupName;
    
    // -> generate archive
    require_once(dirname(__FILE__).'/../thirdparty/pclzip.lib.php');
    $archive = new PclZip($backupFile);
    $catchError1 = $archive->add(DOCUMENTROOT.$adminConfig['basePath'].'config/,'.DOCUMENTROOT.$adminConfig['basePath'].'statistic/',PCLZIP_OPT_REMOVE_PATH, DOCUMENTROOT.$adminConfig['basePath']);
    $catchError2 = $archive->add(DOCUMENTROOT.$adminConfig['savePath'],PCLZIP_OPT_REMOVE_PATH, dirname(DOCUMENTROOT.$adminConfig['savePath']));
    if($catchError1 == 0 && $catchError2 == 0) {
      $errorWindow = "BACKUP ERROR: ".$archive->errorInfo(true);
    
    // -> download file
    } else {
      
      if(@file_exists($backupFile)) {
         
        header("Content-Type: application/octet-stream");
        header('Content-Disposition: attachment; filename="'.basename($backupFile).'"');
        header("Content-length: ".filesize($backupFile));        
        readfile($backupFile) or die('something went wrong when reading the file?');
      } else
        $errorWindow = $langFile['BACKUP_ERROR_FILENOTFOUND'].'<br />'.$backupFile;      
    }
  
  // -> throw folder error
  } else
    $errorWindow = $unwriteableList;
}


// ------------>> RESTORE THE BACKUP
if(isset($_POST['send']) && $_POST['send'] == 'restore') {
  
  // var
  $error = false;
  
  // ->> use uploaded backup file
  if(!empty($_FILES['restoreBackupUpload']['tmp_name']) && !isset($_POST['restoreBackupFile'])) {
    // Check if the file has been correctly uploaded.
    if($_FILES['restoreBackupUpload']['name'] == '')
    	$error .= $langFile['pagethumbnail_upload_error_nofile'];
    
    if($error === false) {
      if($_FILES['restoreBackupUpload']['tmp_name'] == '')
        $error .= $langFile['pagethumbnail_upload_error_nouploadedfile'];
        
      // Check if the file filesize is not 0
      if($_FILES['restoreBackupUpload']['size'] == 0)
        $error .= $langFile['pagethumbnail_upload_error_filesize'].' '.ini_get('upload_max_filesize').'B';
    }
    
  // ->> otherwise use existing backup file
  } elseif(isset($_POST['restoreBackupFile'])) {
  
  // -> otherwise throw error
  } else {
    $error = $langFile['BACKUP_ERROR_NORESTROEFILE'];
  }
  
  if(!$error) {
    // set documentSaved status
    $documentSaved = true;
    $statisticFunctions->saveTaskLog(19); // <- SAVE the task in a LOG FILE
  } else
    $errorWindow .= $error;
  
  $savedForm = 'restorBackup';
}

?>