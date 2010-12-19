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

* deletePage.php version 0.93
*/

/**
 * Includes the login and filters the incoming data by xssFilter
 */
require_once(dirname(__FILE__)."/../../includes/secure.include.php");

echo ' '; // hack for safari, otherwise it throws an error that he could not find htmlentities like &ouml;

// gets the vars
if(isset($_POST['category']))
  $category = $_POST['category'];
else
  $category = $_GET['category'];  
if(isset($_POST['id']))
  $page = $_POST['id'];
else
  $page = $_GET['page'];
$asking = $_POST['asking'];

// load the page
$pageContent = generalFunctions::readPage($page,$category);

// sets the none category (0) to emtpy
if($category == 0)
  $category = '';

// QUESTION
if(is_file(DOCUMENTROOT.$adminConfig['basePath'].'pages/'.$category.'/'.$page.'.php')) {
  $question = '<h1 class="red">'.$langFile['deletePage_question_part1'].' &quot;<span style="color:#000000;">'.$pageContent['title'].'</span>&quot; '.$langFile['deletePage_question_part2'].'</h1>';

// NOT EXISTING
} else {
  $question = '<h1>'.$langFile['deletePage_notexisting_part1'].' &quot;<span style="color:#000000;">'.$adminConfig['basePath'].'pages/'.$category.'/'.$page.'.php</span>&quot; '.$langFile['deletePage_notexisting_part2'].'</h1>
  <a href="?site=pages&amp;category='.$category.'&amp;page='.$page.'" class="ok center" onclick="closeWindowBox();return false;">&nbsp;</a>';
  
  // show only the ok button
  $asking = true;
}

// DELETING PROCESS
if($asking && is_file(DOCUMENTROOT.$adminConfig['basePath'].'pages/'.$category.'/'.$page.'.php')) {
  @chmod(DOCUMENTROOT.$adminConfig['basePath'].'pages/'.$category.'/'.$page, PERMISSIONS);

    // DELETING THUMBNAIL
    if(@unlink(DOCUMENTROOT.$adminConfig['basePath'].'pages/'.$category.'/'.$page.'.php')) {
      if(!empty($pageContent['thumbnail']))
        @unlink(DOCUMENTROOT.$adminConfig['uploadPath'].$adminConfig['pageThumbnail']['path'].$pageContent['thumbnail']);
      
      generalFunctions::setStoredPages($pageContent,true); // REMOVES the $pageContent array from the $storedPages property
      statisticFunctions::saveTaskLog(2,$pageContent['title']); // <- SAVE the task in a LOG FILE
      
      $question = '';
      echo 'DONTSHOW';        
      echo '<script type="text/javascript">/* <![CDATA[ */closeWindowBox(\'index.php?site=pages&category='.$category.'\');/* ]]> */</script>';

    } else {
      // DELETING ERROR --------------
      $question = '<h1>'.$langFile['deletePage_finish_error'].'</h1>
      <a href="?site=pages&amp;category='.$category.'&amp;page='.$page.'" class="ok center" onclick="closeWindowBox();return false;">&nbsp;</a>'."\n";
    }
}

echo ' '; // hack for safari, otherwise it throws an error that he could not find htmlentities like &ouml;
echo $question;


if(!$asking) {

?>
<div>
<form action="?site=deletePage" method="post" enctype="multipart/form-data" id="deletePageForm" onsubmit="requestSite('<?php echo $_SERVER['PHP_SELF']; ?>','','deletePageForm');return false;" accept-charset="UTF-8">
<input type="hidden" name="category" value="<?php echo $category; ?>" />
<input type="hidden" name="id" value="<?php echo $page; ?>" />
<input type="hidden" name="asking" value="true" />


<a href="?site=pages&amp;category=<?php echo $category; ?>&amp;page=<?php echo $page; ?>" class="cancel" onclick="closeWindowBox();return false;">&nbsp;</a>
<input type="submit" value="" class="button submit" />
</form>
</div>
<?php
}
?>