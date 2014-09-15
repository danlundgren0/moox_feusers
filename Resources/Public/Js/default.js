/*---------------------*/

$(document).ready(function() {	
	
	$(this).ajaxStart(function(){
          $("body").append("<div id='tx-moox-feusers-overlay'><img src='typo3conf/ext/moox_feusers/Resources/Public/Images/ajax-loader.gif' /></div>");
    });
	
	$(this).ajaxStop(function(){
          $("#tx-moox-feusers-overlay").remove();
    });
	
});