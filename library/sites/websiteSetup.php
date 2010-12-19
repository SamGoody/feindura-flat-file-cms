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

* sites/websiteSetup.php version 1.9
*/

// CHECKs if the ncessary FILEs are WRITEABLE, otherwise throw an error
// ----------------------------------------------------------------------------------------
$checkFolders = $adminConfig['basePath'].'config/website.config.php';
$unwriteableList = isWritableWarning($checkFolders);

// check also website files if allowed
if($adminConfig['user']['editWebsiteFiles'])
  $unwriteableList .= isWritableWarningRecursive(array($adminConfig['websiteFilesPath']));
// check also stylesheet files if allowed
if($adminConfig['user']['editStyleSheets'])
  $unwriteableList .= isWritableWarningRecursive(array($adminConfig['stylesheetPath']));

// gives the error OUTPUT if one of these files in unwriteable
if($unwriteableList && checkBasePath()) {
  echo '<div class="block warning">
    <h1>'.$langFile['adminSetup_error_title'].'</h1>
    <div class="content">
      <p>'.$unwriteableList.'</p><!-- needs <p> tags for margin-left:..-->
    </div>
    <div class="bottom"></div>  
  </div>'; 
  
  echo '<div class="blockSpacer"></div>';
}

?>
<form action="index.php?site=websiteSetup#websiteConfig" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
  <div><input type="hidden" name="send" value="websiteSetup" /></div>
  
<!-- PAGE SETTINGS -->

<?php
// shows the block below if it is the ones which is saved before
$hidden = ($savedForm != 'websiteConfig') ? ' hidden' : '';
?>
<div class="block<?php /*echo $hidden;*/ ?>">
  <h1><a href="#" id="websiteSettings" name="websiteSettings"><?php echo $langFile['websiteSetup_websiteConfig_h1']; ?></a></h1>
  <div class="content">
    <table>
     
      <colgroup>
      <col class="left" />
      </colgroup>
  
      <tr><td class="leftTop"></td><td></td></tr>
      
      <tr><td class="left">
      <label for="title"><span class="toolTip" title="<?php echo $langFile['websiteSetup_websiteConfig_field1'].'::'.$langFile['websiteSetup_websiteConfig_field1_tip']; ?>">
      <?php echo $langFile['websiteSetup_websiteConfig_field1']; ?></span></label>
      </td><td class="right">
      <input id="title" name="title" value="<?php echo $websiteConfig['title']; ?>" />
      </td></tr>
      
      <tr><td class="left">
      <label for="publisher"><span class="toolTip" title="<?php echo $langFile['websiteSetup_websiteConfig_field2'].'::'.$langFile['websiteSetup_websiteConfig_field2_tip']; ?>">
      <?php echo $langFile['websiteSetup_websiteConfig_field2']; ?></span></label>
      </td><td class="right">
      <input id="publisher" name="publisher" value="<?php echo $websiteConfig['publisher']; ?>" />
      </td></tr>
      
      <tr><td class="left">
      <label for="websiteConfig_copyright"><span class="toolTip" title="<?php echo $langFile['websiteSetup_websiteConfig_field3'].'::'.$langFile['websiteSetup_websiteConfig_field3_tip']; ?>">
      <?php echo $langFile['websiteSetup_websiteConfig_field3']; ?></span></label>
      </td><td class="right">
      <input id="websiteConfig_copyright" name="websiteConfig_copyright" value="<?php echo $websiteConfig['copyright']; ?>" />
      </td></tr>
      
      <tr><td class="spacer"></td><td></td></tr>
      
      <tr><td class="left">
      <label for="keywords"><span class="toolTip" title="<?php echo $langFile['websiteSetup_websiteConfig_field4'].'::'.$langFile['websiteSetup_websiteConfig_field4_tip']; ?>">
      <?php echo $langFile['websiteSetup_websiteConfig_field4']; ?></span></label>
      </td><td class="right">
      <input id="keywords" name="keywords" value="<?php echo $websiteConfig['keywords']; ?>" class="inputToolTip" title="<?php echo $langFile['websiteSetup_websiteConfig_field4_inputTip']; ?>" />
      </td></tr>
      
      <tr><td class="left">
      <label for="description"><span class="toolTip" title="<?php echo $langFile['websiteSetup_websiteConfig_field5'].'::'.$langFile['websiteSetup_websiteConfig_field5_tip']; ?>">
      <?php echo $langFile['websiteSetup_websiteConfig_field5']; ?></span></label>
      </td><td class="right">
      <textarea id="description" name="description" cols="50" rows="4" style="white-space:normal;width:500px;height:70px;" class="inputToolTip" title="<?php echo $langFile['websiteSetup_websiteConfig_field5_inputTip']; ?>"><?php echo $websiteConfig['description']; ?></textarea>
      </td></tr>
      
      <tr><td class="leftBottom"></td><td></td></tr>
      
    </table>
    
    <!--<input type="reset" value="" class="button cancel" title="<?php echo $langFile['form_cancel']; ?>" />-->
    <input type="submit" value="" name="websiteConfig" class="button submit center" title="<?php echo $langFile['form_submit']; ?>" />
  </div>
  <div class="bottom"></div>
</div>

</form>
<?php

if($adminConfig['user']['editWebsiteFiles']) {  
  // BEARBEITUNG DER ERWEITERTEN WEBSEITEN-EINSTELLUNGEN 
  editFiles($adminConfig['websiteFilesPath'], $_GET['site'], "editWebsitefile",  $langFile['editFilesSettings_h1_websitefiles'], "websiteFilesAnchor");
}

if($adminConfig['user']['editStyleSheets']) {
  // BEARBEITUNG DER STYLESHEETDATEI
  editFiles($adminConfig['stylesheetPath'], $_GET['site'], "editCSSfile", $langFile['editFilesSettings_h1_style'], "cssFilesAnchor", "css");
}

?>