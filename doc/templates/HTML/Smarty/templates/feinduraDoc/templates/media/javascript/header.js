/**
 * Javascripts for the header
 * 
 * @version 0.1 
 */ 

window.addEvent('domready',function() {
  
  // add contextual search
  if(!navigator.appVersion.match(/MSIE ([0-6]\.\d)/))
    new contextualSearch({site: 'http://feindura.org/api/',targetID: 'searchBox',css: 'media/header.css'});
  
  // add search input overtext
	new OverText('search-input',{ //searchfield
		positionOptions: {
			offset: {x: 4,y: 2}
		}
	});
	
});