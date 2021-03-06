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
// javascripts/frontendEditing.js version 0.11 (requires mootools-core and CKEditor)

/*
// ->> FUNCTIONS

// ->> the function to SAVE the edited page data
function saveEditedPage(editorInstance) {
  editorInstance.updateElement();
  alert(editorInstance.getData());
  editorInstance.destroy();
}


// ->> the function will be execute when a user CLICK on the EDIT BUTTON
function feinduraEditPage(pageId,styleFiles,styleId,styleClass) {
  
  var pageContentName = 'feinduraPage' + pageId;
  var pageContentBlock = $(styleId);

  if(pageContentBlock != null) {
  
    // -> CHECK if instance alrady exists, close and save the page
    for(var i in CKEDITOR.instances) {    
       if(CKEDITOR.instances[i].name == pageContentName) {
        saveEditedPage(CKEDITOR.instances[i])
        return;
       }
    }

    // -> CREATES an editor instance by replacing the container DIV fo the page content
  	var editorInstance = CKEDITOR.replace(pageContentName, {
        width       : pageContentBlock.getSize().x + 24, // 2x 12px
        height      : pageContentBlock.getSize().y + 160, // 125px + 35px
        contentsCss : styleFiles,
        bodyId      : styleId,
        bodyClass   : styleClass
    });
    
    
    editorInstance.on('instanceReady',function(){
      var editorBlock = $('cke_' + pageContentName);
    
      editorBlock.setStyle('margin','-125px -12px -35px -12px');
    });
    
    // -> SAVE automatically IF user LET the editor
    editorInstance.on('blur',function() {
      saveEditedPage(editorInstance);
    });
  }
};

// ->> SET UP CKEDITOR
// *******************
CKEDITOR.config.dialog_backgroundCoverColor   = '#fff';
CKEDITOR.config.uiColor                       = '#cccccc';
CKEDITOR.config.forcePasteAsPlainText         = false;
CKEDITOR.config.scayt_autoStartup             = false;
CKEDITOR.config.colorButton_enableMore        = true;
//CKEDITOR.config.disableNativeSpellChecker = false;

CKEDITOR.config.toolbar = [
                          ['Save','-','Maximize','-','Source'],
                          ['Undo','Redo','-','RemoveFormat','SelectAll'],
                          ['Cut','Copy','Paste','PasteText','PasteFromWord'],
                          ['Find','Replace','-','Print','SpellChecker', 'Scayt'],
                           '/',
                          ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],	                                               
                          ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
                          ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                          ['Link','Unlink','Anchor'],
                          ['Image','Flash','Table','HorizontalRule','SpecialChar'],
                           '/',
                          ['Styles','Format','FontSize'], // 'Font','FontName',
                          ['TextColor','BGColor','-'],
                          ['ShowBlocks','-','About']
                          ];		// No comma for the last row.


// *---------------------------------------------------------------------------------------------------*
//  DOMREADY
// *---------------------------------------------------------------------------------------------------*
window.addEvent('domready',function(){
  CKEDITOR.plugins.registered['save'] = {
     init : function( editorInstance )
     {
        var command = editorInstance.addCommand( 'save',
           {
              modes : { wysiwyg:1, source:0 },
              exec : function( editorInstance ) {
                 //var fo=editor.element.$.form;
                 saveEditedPage(editorInstance);
              }
           }
        );
        editorInstance.ui.addButton( 'Save',{label : 'Save',command : 'save'});
     }
  }
});
*/

// var
var feindura_pageSaved = false;
var feindura_pageContent = null;

var feindura_jsLoadingCircleContainer = new Element('div',{'class':'feindura_loadingCircleContainer'});
var feindura_jsLoadingCircle = new Element('div',{'class': 'feindura_loadingCircleHolder','style':'margin-left: -40px;margin-top: -25px;'});
feindura_jsLoadingCircleContainer.grab(feindura_jsLoadingCircle);
var feindura_finishPicture = new Element('div',{'class':'feindura_requestFinish'});
var feindura_removeLoadingCircle;

// ->> FUNCTIONS
// *************

/* ---------------------------------------------------------------------------------- */
// FRONTEND EDITING AJAX REQUEST
function feindura_request(pageBlock,url,data,errorTexts,method,update) {
  
  // vars
  if(!method) method = 'get';
  
  // creates the request Object
  new Request({
    url: url,
    method: method,
    
    //-----------------------------------------------------------------------------
    onRequest: function() { //-----------------------------------------------------		
      
      // -> ADD the LOADING CIRCLE
      if(!pageBlock.get('html').contains(feindura_jsLoadingCircleContainer))
  		  pageBlock.grab(feindura_jsLoadingCircleContainer,'top');
  		feindura_removeLoadingCircle = feindura_loadingCircle(feindura_jsLoadingCircle, 24, 40, 12, 4, "#000");  		
  		// -> TWEEN jsLoadingCircleContainer    
      feindura_jsLoadingCircleContainer.set('tween',{duration: 100});
      feindura_jsLoadingCircleContainer.setStyle('opacity',0);
      feindura_jsLoadingCircleContainer.tween('opacity',0.8);

    },
    //-----------------------------------------------------------------------------
		onSuccess: function(html) { //-------------------------------------------------
			
			// -> fade out the loadingCircle
			feindura_jsLoadingCircleContainer.set('tween',{duration: 200});
			feindura_jsLoadingCircleContainer.fade('out');
			feindura_jsLoadingCircleContainer.get('tween').chain(function(){
			   // -> REMOVE the LOADING CIRCLE
			   feindura_removeLoadingCircle();
         feindura_jsLoadingCircleContainer.setStyle('background','transparent');
         feindura_jsLoadingCircleContainer.setStyle('opacity',1);
         // request finish picture
			   feindura_jsLoadingCircleContainer.grab(feindura_finishPicture,'top');
      });
			
      feindura_finishPicture.set('tween',{duration: 400});
      feindura_finishPicture.fade('in');
      feindura_finishPicture.get('tween').chain(function(){
        feindura_finishPicture.tween('opacity',0);
      }).chain(function(){
        feindura_finishPicture.dispose();
        feindura_jsLoadingCircleContainer.dispose();
        // update the pageBlock content
        if(update) {
          pageBlock.set('html', html);
    			//Inject the new DOM elements into the results div.
    			//pageBlock.adopt(html);
        }        
      });

		},
		//-----------------------------------------------------------------------------
		//Our request will most likely succeed, but just in case, we'll add an
		//onFailure method which will let the user know what happened.
		onFailure: function() { //-----------------------------------------------------
      
      // creates the errorWindow
      var errorWindow = new Element('div',{id:'feindura_errorWindow', 'style':'left:50%;margin-left:-260px;'});
      errorWindow.grab(new Element('div',{'class':'feindura_top', 'html': errorTexts.title}));
      var errorWindowContent = new Element('div',{'class':'feindura_content feindura_warning', 'html':'<p>'+errorTexts.text+'</p>'});
      var errorWindowOkButton = new Element('a',{'class':'feindura_ok', 'href':'#'});
      errorWindowContent.grab(errorWindowOkButton);
      errorWindow.grab(errorWindowContent);
      errorWindow.grab(new Element('div',{'class':'feindura_bottom'}));     
      
      // add functionality to the ok button
      errorWindowOkButton.addEvent('click',function(e) {
        e.stop();
        errorWindow.fade('out');
        errorWindow.get('tween').chain(function(){
          errorWindow.destroy();
        });
      });      
      
      // -> fade out the loadingCircle
      if(!pageBlock.get('html').contains(feindura_jsLoadingCircleContainer))
        pageBlock.grab(feindura_jsLoadingCircleContainer,'top');
			feindura_jsLoadingCircleContainer.set('tween',{duration: 200});
			feindura_jsLoadingCircleContainer.fade('out');
      
			feindura_jsLoadingCircleContainer.get('tween').chain(function(){
			   // -> REMOVE the LOADING CIRCLE
			   feindura_removeLoadingCircle();
			   feindura_jsLoadingCircleContainer.dispose();
			   // add errorWindow
         $(document.body).grab(errorWindow,'top');
      });

		}
  }).send(data);
}

// ->> ADD TOOLTIPS
function feindura_addToolTips() {
  // store titles and text
  feindura_storeTipTexts('.feindura_toolTip');
	
	// add the tooltips to the elements
  var feindura_toolTips = new Tips('.feindura_toolTip',{
    className: 'feindura_toolTipBox',
    offset: {'x': 10,'y': 15},
    fixed: false,
    showDelay: 200,
    hideDelay: 0 });
}

// ->> GET PAGE ID
function feindura_setPageIds(pageBlock) {
  if(pageBlock.hasClass('feindura_editPage') || pageBlock.hasClass('feindura_editTitle')) {
    var classes = pageBlock.get('class').split(' ');
    pageBlock.store('page', classes[1].substr(15));
    pageBlock.store('category', classes[2].substr(19));
    return true;
  } else
    return false;
}

// ->> DOMREADY
// ************
window.addEvent('domready',function() {

  // ->> add TOP BAR
  // ***************
  var feindura_topBar = Mooml.render('feindura_topBarTemplate');
  feindura_topBar.inject($(document.body),'top');
  $(document.body).setStyle('padding-top','60px');
  
  // ->> GO TROUGH ALL EDITABLE BLOCK
  $$('div.feindura_editPage, span.feindura_editTitle').each(function(pageBlock) {
    
    // STORE page IDS in the elements storage
    feindura_setPageIds(pageBlock);
    
    // save on blur
    pageBlock.addEvent('blur', function(e) {
      var page = $(e.target);
      
      //alert(MooRTE.Elements.linkPop.visible);
      if(page != null && MooRTE.Elements.linkPop.visible === false) {
        if(page.hasClass('feindura_editPage'))
          feindura_savePage(page,'content');
        else if(page.hasClass('feindura_editTitle'))
          feindura_savePage(page,'title');   
      }
    });    
    // on focus
    pageBlock.addEvent('focus', function() {
      feindura_pageContent = pageBlock.get('html');
      if(feindura_pageSaved)
        feindura_pageSaved = false;
    });
    
  });
  
  // ->> add BAR to EACH PAGE BLOCK  
  // ******************************  
  $$('div.feindura_editPage').each(function(pageBlock) {
    
    //var      
    var pageBarVisible = false;
    var pageBlockFocused = false;
    var parent = pageBlock.getParent();
    
    // ->> create PAGE BAR
    var pageBar = new Element('div',{'class': 'feindura_pageBar'});
    var pageBarContent = feindura_renderPageBar({ pageId: pageBlock.retrieve('page'), categoryId: pageBlock.retrieve('category'), pageBlockClasses: pageBlock.get('class') });
    pageBarContent.each(function(link){
      link.inject(pageBar,'bottom');
    });
    // -> inject the page bar
    pageBar.inject(pageBlock,'before');
    pageBar.set('tween',{duration: 300});
    pageBar.fade('hide');      
    
    // -> set the parent to position: relative      
    if(parent.getStyle('position') != 'relative' && parent.getStyle('position') != 'absolute') { parent.setStyle('position','relative'); }      
    
    // ->> add page bar on focus
    pageBlock.addEvent('mouseenter', function() {
      // -> show the page bar
      // -> set the position of the page bar
      pageBar.setPosition({
        x: pageBlock.getPosition(parent).x + (pageBlock.getSize().x - pageBar.getSize().x),
        y: pageBlock.getPosition(parent).y - pageBar.getSize().y - 5}
      );    
      pageBar.fade('in');
    });
    // ->> set pageBlockFocused on focus
    pageBlock.addEvent('focus', function(e) {
      pageBlockFocused = true;
    });
    
    // ->> remove all page bars on mouseout
    pageBlock.addEvent('mouseleave', function(e) {      
      // -> check if target is not feindura_editPage block
      if(!pageBlockFocused) {          
        pageBar.fade('out');
      }
    });
    // ->> set pageBlockFocused on focus
    pageBlock.addEvent('blur', function(e) {
      pageBar.fade('out');
      pageBlockFocused = false;
    });
    
    // ->> set page bar mouse events
    pageBar.addEvent('mouseenter', function(e) { pageBar.fade('in'); });
    pageBar.addEvent('mouseleave', function(e) { if(!pageBlockFocused) pageBar.fade('out'); });    
  });
  
  feindura_addToolTips()
  
  // ->> ADD EDITOR
  // **************
  
  // -> add save button
  /*
  MooRTE.Elements.extend({
    save : { img:27, onClick: function() {
        $$('div.feindura_editPage, span.feindura_editTitle').each(function(page) {                                     
            if(MooRTE.activeField == page) {
              feindura_pageSaved = false;
              if(page.hasClass('feindura_editPage'))
                feindura_savePage(page,'content');
              else if(page.hasClass('feindura_editTitle'))
                feindura_savePage(page,'title');      
            }
        });
      }}
  });
  */
  // -> set up toolbar  
  var feindura_MooRTEButtons = {Toolbar:['save.saveBtn','undo','redo','removeformat', // 'Html/Text'
                                        'bold','italic','underline','strikethrough',
                                        'justifyleft','justifycenter','justifyright','justifyfull',
                                        'outdent','indent','superscript','subscript',
                                        'insertorderedlist','insertunorderedlist','blockquote','inserthorizontalrule',
                                        'decreasefontsize','increasefontsize','hyperlink'
                                        ]};
                                        
  // -> create editor instance to edit all divs which have the class "feindura_editPage"
  new MooRTE('div.feindura_editPage, span.feindura_editTitle', {skin:'rteFeinduraSkin', defaults: feindura_MooRTEButtons, location:'pageTop'});
  //new MooRTE({'div.feindura_editPage, span.feindura_editTitle', {skin:'rteFeinduraSkin', buttons: feindura_MooRTEButtons,location:'pageTop'});
});