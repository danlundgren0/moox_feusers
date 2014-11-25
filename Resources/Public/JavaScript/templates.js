var $j = jQuery.noConflict();

function hideTaskList(){
	$j( "#task-expander" ).removeClass( "t3-icon-move-up t3-icon-move-down" );			
	$j( "#task-expander" ).addClass( "t3-icon-move-down" );
	$j( "#task-list" ).hide("fast");
}

function hideUserList(){
	$j( "#user-expander" ).removeClass( "t3-icon-move-up t3-icon-move-down" );			
	$j( "#user-expander" ).addClass( "t3-icon-move-down" );
	$j( "#user-list" ).hide("fast");
}

function toggleInfoField(id){	
	if ( $j(id).is(":visible") ) {
		visible = true;
	} else { 
		visible = false;
	}
	hideUserList();
	hideTaskList();
	$j(".news-info-field").hide("fast");
	$j(".news-item-expander-reset").removeClass( "t3-icon-move-up t3-icon-move-down" );			
	$j(".news-item-expander-reset").addClass( "t3-icon-move-down" );
	if (visible) {		
		$j(id).hide("fast");		
		$j(id + "-expander").removeClass( "t3-icon-move-up t3-icon-move-down" );			
		$j(id + "-expander").addClass( "t3-icon-move-down" );
	} else { 
		$j(id).show("fast");
		$j(id + "-expander").removeClass( "t3-icon-move-up t3-icon-move-down" );			
		$j(id + "-expander").addClass( "t3-icon-move-up" );
	}	
}

function toggleDraftDetails(id){
	if ( $j(id).is(":visible") ) {
		visible = true;
	} else { 
		visible = false;
	}	
	$j(".draft-details").hide("fast");
	$j(".draft-details-expander-reset").removeClass( "t3-icon-move-up t3-icon-move-down" );			
	$j(".draft-details-expander-reset").addClass( "t3-icon-move-down" );
	if (visible) {		
		$j(id).hide("fast");		
		$j(id + "-expander").removeClass( "t3-icon-move-up t3-icon-move-down" );			
		$j(id + "-expander").addClass( "t3-icon-move-down" );
	} else { 
		$j(id).show("fast");
		$j(id + "-expander").removeClass( "t3-icon-move-up t3-icon-move-down" );			
		$j(id + "-expander").addClass( "t3-icon-move-up" );
	}	
}

function toggleSentDetails(id){
	if ( $j(id).is(":visible") ) {
		visible = true;
	} else { 
		visible = false;
	}	
	$j(".sent-details").hide("fast");
	$j(".sent-details-expander-reset").removeClass( "t3-icon-move-up t3-icon-move-down" );			
	$j(".sent-details-expander-reset").addClass( "t3-icon-move-down" );
	if (visible) {		
		$j(id).hide("fast");		
		$j(id + "-expander").removeClass( "t3-icon-move-up t3-icon-move-down" );			
		$j(id + "-expander").addClass( "t3-icon-move-down" );
	} else { 
		$j(id).show("fast");
		$j(id + "-expander").removeClass( "t3-icon-move-up t3-icon-move-down" );			
		$j(id + "-expander").addClass( "t3-icon-move-up" );
	}	
}

function toggleTaskDetails(id){
	if ( $j(id).is(":visible") ) {
		visible = true;
	} else { 
		visible = false;
	}	
	$j(".task-details").hide("fast");
	$j(".task-details-expander-reset").removeClass( "t3-icon-move-up t3-icon-move-down" );			
	$j(".task-details-expander-reset").addClass( "t3-icon-move-down" );
	if (visible) {		
		$j(id).hide("fast");		
		$j(id + "-expander").removeClass( "t3-icon-move-up t3-icon-move-down" );			
		$j(id + "-expander").addClass( "t3-icon-move-down" );
	} else { 
		$j(id).show("fast");
		$j(id + "-expander").removeClass( "t3-icon-move-up t3-icon-move-down" );			
		$j(id + "-expander").addClass( "t3-icon-move-up" );
	}	
}

function updateUserList(){	
	$j.ajax({
		url: $j( "#getUserListAjaxUrl" ).text(),
		data: '',
		success: function(result) {			
			if(result!=''){				
				$j( "#user-list" ).html(result);												
			}
		}
	});	
}

function addScheduleSubmit(){	
	$j( "#addScheduleForm" ).submit();
}

function reloadIframe(id,src,display,email){	
	$j(id).attr('src', src);
	$j(id + "-display").html(display);
	if(display=='Alle Benutzer/Adressen'){
		$j('#userNav0').hide("fast");
	} else {
		$j('#userNav0').show("fast");
	}
	hideUserList();
}

$j(document).ready(function() {	
	
	$j(this).ajaxStart(function(){         
		  $j("body").append("<div id='tx-moox-mailer-admin-overlay'><img src='/typo3conf/ext/moox_mailer/Resources/Public/Images/ajax-loader.gif' /></div>");
    });
	
	$j(this).ajaxStop(function(){
          $j("#tx-moox-mailer-admin-overlay").remove();
    });
	
	$j("#task-selector").click(function(){	
		hideUserList();
		if ( $j( "#task-list" ).is(":visible") ) {
			$j( "#task-expander" ).removeClass( "t3-icon-move-up t3-icon-move-down" );			
			$j( "#task-expander" ).addClass( "t3-icon-move-down" );
		} else { 
			$j( "#task-expander" ).removeClass( "t3-icon-move-up t3-icon-move-down" );
			$j( "#task-expander" ).addClass( "t3-icon-move-up" );
		}		
		$j("#task-list").toggle("fast");		
	});
	
	$j("#task-list-close").click(function(){
		$j("#task-list").hide("fast");
	});
	
	$j("#user-selector").click(function(){
		hideTaskList();
		if ( $j( "#user-list" ).is(":visible") ) {
			$j( "#user-expander" ).removeClass( "t3-icon-move-up t3-icon-move-down" );			
			$j( "#user-expander" ).addClass( "t3-icon-move-down" );
		} else { 
			$j( "#user-expander" ).removeClass( "t3-icon-move-up t3-icon-move-down" );
			$j( "#user-expander" ).addClass( "t3-icon-move-up" );			
		}		
		$j("#user-list").toggle("fast");		
	});
	
	$j("#user-list-close").click(function(){
		$j("#user-list").hide("fast");
	});

});