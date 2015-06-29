TYPO3.jQuery = jQuery.noConflict();

TYPO3.jQuery(document).ready(function() {	
	
	TYPO3.jQuery( "form.feuser-form" ).submit(function( event ) {
		if(TYPO3.jQuery("#feuser-usergroup-selector-min")){
			var countOptions = TYPO3.jQuery('select.feuser-usergroup-selector-display option').size();
			var minCount = TYPO3.jQuery("#feuser-usergroup-selector-min").val();
			if(countOptions<minCount){
				alert("Sie müssem mindestens " + minCount + " Benutzergruppe(n) auswählen.");
				event.preventDefault();
			}
		}
	});		
	TYPO3.jQuery(".feuser-usergroup-selector-wrapper").bind("contextmenu",function(e){
		value = TYPO3.jQuery(this).attr('id').replace(/feuser-usergroup-selector-wrapper-/g,'');
		TYPO3.jQuery(".feuser-usergroup-selector-context").hide();
		TYPO3.jQuery("#feuser-usergroup-selector-context-" + value).show();		
	   return false;
	});
	TYPO3.jQuery(".feuser-usergroup-selector-context").mouseleave(function() {
		TYPO3.jQuery(this).hide();
	}); 	
	
	TYPO3.jQuery("#selected-folder").click(function(){
		if ( TYPO3.jQuery("#folder-list").is(":visible") ) {
			TYPO3.jQuery( "#folder-expander" ).removeClass( "t3-icon-move-up t3-icon-move-down" );			
			TYPO3.jQuery( "#folder-expander" ).addClass( "t3-icon-move-down" );
		} else { 
			TYPO3.jQuery( "#folder-expander" ).removeClass( "t3-icon-move-up t3-icon-move-down" );
			TYPO3.jQuery( "#folder-expander" ).addClass( "t3-icon-move-up" );
		}		
		TYPO3.jQuery("#folder-list").toggle("fast");
		
	});
	
	TYPO3.jQuery("#folder-list-close").click(function(){
		TYPO3.jQuery("#folder-list").hide("fast");
	});
	
	TYPO3.jQuery("#group-list-close").click(function(){
		TYPO3.jQuery("#group-list").hide("fast");
	});
	
	TYPO3.jQuery("#selected-group").click(function(){
		if ( TYPO3.jQuery("#group-list").is(":visible") ) {
			TYPO3.jQuery( "#group-expander" ).removeClass( "t3-icon-move-up t3-icon-move-down" );			
			TYPO3.jQuery( "#group-expander" ).addClass( "t3-icon-move-down" );
		} else { 
			TYPO3.jQuery( "#group-expander" ).removeClass( "t3-icon-move-up t3-icon-move-down" );
			TYPO3.jQuery( "#group-expander" ).addClass( "t3-icon-move-up" );
		}		
		TYPO3.jQuery("#group-list").toggle("fast");
		
	});
	
	TYPO3.jQuery("#group-list-close").click(function(){
		TYPO3.jQuery("#group-list").hide("fast");
	});
	
	TYPO3.jQuery("#filter-query").on("click", function () {
	   TYPO3.jQuery(this).select();
	});
	
	TYPO3.jQuery("#filter-mailing").on("change", function () {
	  TYPO3.jQuery("#filterForm").submit();
	});
	
	TYPO3.jQuery("#filter-state").on("change", function () {
	  TYPO3.jQuery("#filterForm").submit();
	});
	
	TYPO3.jQuery("#filter-quality").on("change", function () {
	  TYPO3.jQuery("#filterForm").submit();
	});
	
	TYPO3.jQuery("#filter-per-page").on("change", function () {
	  TYPO3.jQuery("#filterForm").submit();
	});
	
	TYPO3.jQuery(this).ajaxStart(function(){         
		  TYPO3.jQuery("body").append("<div id='tx-moox-feuser-admin-overlay'><img src='/typo3conf/ext/moox_feusers/Resources/Public/Images/ajax-loader.gif' /></div>");
    });
	
	TYPO3.jQuery(this).ajaxStop(function(){
          TYPO3.jQuery("#tx-moox-feuser-admin-overlay").remove();
    });	
		
	TYPO3.jQuery(".feuser-details").on("click", function () {
		TYPO3.jQuery(this).hide("fast");
	});
	
	TYPO3.jQuery(".feuser-checkbox").on("change", function () {
		if(TYPO3.jQuery(this).prop("checked")){
			TYPO3.jQuery(this).parent().parent().addClass("tx-moox-feusers-admin-table-row-selected");
		} else {
			TYPO3.jQuery(this).parent().parent().removeClass("tx-moox-feusers-admin-table-row-selected");
		}
		checkedCnt 	= TYPO3.jQuery(".feuser-checkbox:checked").length;
		boxCnt 		= TYPO3.jQuery(".feuser-checkbox").length;
		if(checkedCnt==boxCnt){
			TYPO3.jQuery(".feuser-check-all").prop("checked", true);
		} else {
			TYPO3.jQuery(".feuser-check-all").prop("checked", false);
		}		
		toggleSelectionMenu();
	});
	
	TYPO3.jQuery(".feuser-check-all").on("change", function () {
		if(TYPO3.jQuery(this).prop("checked")){
			TYPO3.jQuery(".feuser-check-all").prop("checked", true);
			TYPO3.jQuery(".feuser-checkbox").each(function(){
				TYPO3.jQuery(this).prop("checked", true)
				TYPO3.jQuery(this).parent().parent().addClass("tx-moox-feusers-admin-table-row-selected");
			});
		} else {
			TYPO3.jQuery(".feuser-check-all").prop("checked", false);
			TYPO3.jQuery(".feuser-checkbox").each(function(){
				TYPO3.jQuery(this).prop("checked", false)
				TYPO3.jQuery(this).parent().parent().removeClass("tx-moox-feusers-admin-table-row-selected");
			});			
		}
		toggleSelectionMenu();
	});
	
	TYPO3.jQuery(".feuser-move-expander").on("mouseenter", function () {
		id = TYPO3.jQuery(this).attr("id");
		id = id.replace("feuser-move-expander-","");		
		showMoveSelection(id)
	});
	
	TYPO3.jQuery(".feuser-move-selection").on("mouseleave", function () {
		TYPO3.jQuery(".feuser-move-selection").hide("fast");
	});
	
	/*
	TYPO3.jQuery(".feuser-bounces").on("click", function () {
		TYPO3.jQuery(this).hide("fast");
	});
	*/
});

function toggleSelectionMenu(){
	showMenu = TYPO3.jQuery(".feuser-checkbox:checked").length;
	if(showMenu){
		TYPO3.jQuery("#tx-moox-feusers-admin-multiple-selector-txt").html("<strong>" + showMenu + "</strong> Benutzer gewählt");
		TYPO3.jQuery("#tx-moox-feusers-admin-multiple-selector").show("fast");
	} else {
		TYPO3.jQuery("#tx-moox-feusers-admin-multiple-selector").hide("fast");
		TYPO3.jQuery("#tx-moox-feusers-admin-multiple-selector-txt").html("Kein Benutzer gewählt");
	}
}

function mooxFeusersToggleDetails(id){
	TYPO3.jQuery("#folder-list").hide("fast");
	TYPO3.jQuery("#group-list").hide("fast");
	if ( TYPO3.jQuery("#feuser-details-" + id).is(":visible") ) {
		visible = true;
	} else { 
		visible = false;
	}	
	TYPO3.jQuery(".feuser-details").hide("fast");
	TYPO3.jQuery(".feuser-bounces").hide("fast");
	TYPO3.jQuery(".feuser-details-expander-reset").removeClass( "t3-icon-move-up t3-icon-move-down" );			
	TYPO3.jQuery(".feuser-details-expander-reset").addClass( "t3-icon-move-down" );
	if (visible) {		
		TYPO3.jQuery("#feuser-details-" + id).hide("fast");		
		TYPO3.jQuery("#feuser-details-" + id + "-expander").removeClass( "t3-icon-move-up t3-icon-move-down" );			
		TYPO3.jQuery("#feuser-details-" + id + "-expander").addClass( "t3-icon-move-down" );
	} else { 
		TYPO3.jQuery("#feuser-details-" + id).show("fast");
		TYPO3.jQuery("#feuser-details-" + id + "-expander").removeClass( "t3-icon-move-up t3-icon-move-down" );			
		TYPO3.jQuery("#feuser-details-" + id + "-expander").addClass( "t3-icon-move-up" );		
	}	
}

function mooxFeusersToggleErrors(id){
	TYPO3.jQuery("#folder-list").hide("fast");
	TYPO3.jQuery("#group-list").hide("fast");
	TYPO3.jQuery(".feuser-bounces-bounce").hide("fast");
	TYPO3.jQuery(".feuser-bounces-bounce-expander-reset").removeClass( "t3-icon-move-up t3-icon-move-down" );			
	TYPO3.jQuery(".feuser-bounces-bounce-expander-reset").addClass( "t3-icon-move-down" );
	if ( TYPO3.jQuery("#feuser-errors-" + id).is(":visible") ) {
		visible = true;
	} else { 
		visible = false;
	}	
	TYPO3.jQuery(".feuser-errors").hide("fast");
	TYPO3.jQuery(".feuser-details").hide("fast");	
	if (visible) {		
		TYPO3.jQuery("#feuser-errors-" + id).hide("fast");				
	} else { 
		TYPO3.jQuery("#feuser-errors-" + id).show("fast");		
	}	
}

function mooxFeusersToggleBounce(id){
	TYPO3.jQuery("#folder-list").hide("fast");
	TYPO3.jQuery("#group-list").hide("fast");
	if ( TYPO3.jQuery("#feuser-bounces-bounce-" + id).is(":visible") ) {
		visible = true;
	} else { 
		visible = false;
	}	
	TYPO3.jQuery(".feuser-bounces-bounce").hide("fast");
	TYPO3.jQuery(".feuser-details").hide("fast");	
	TYPO3.jQuery(".feuser-bounces-bounce-expander-reset").removeClass( "t3-icon-move-up t3-icon-move-down" );			
	TYPO3.jQuery(".feuser-bounces-bounce-expander-reset").addClass( "t3-icon-move-down" );
	if (visible) {		
		TYPO3.jQuery("#feuser-bounces-bounce-" + id).hide("fast");
		TYPO3.jQuery("#feuser-bounces-bounce-" + id + "-expander").removeClass( "t3-icon-move-up t3-icon-move-down" );			
		TYPO3.jQuery("#feuser-bounces-bounce-" + id + "-expander").addClass( "t3-icon-move-down" );
	} else { 
		TYPO3.jQuery("#feuser-bounces-bounce-" + id).show("fast");
		TYPO3.jQuery("#feuser-bounces-bounce-" + id + "-expander").removeClass( "t3-icon-move-up t3-icon-move-down" );			
		TYPO3.jQuery("#feuser-bounces-bounce-" + id + "-expander").addClass( "t3-icon-move-up" );		
	}	
}

function mooxFeusersUpdateUsergroupsField(id){
	var countOptions = TYPO3.jQuery(id + '_list option').size();
	var cnt = 1;
	TYPO3.jQuery(".feuser-usergroup-selector-wrapper .t3-icon-toolbar-menu-shortcut.feuser-usergroup-selector-icon").addClass( "t3-icon-mimetypes t3-icon-mimetypes-x t3-icon-x-sys_category feuser-usergroup-selector-icon" );
	TYPO3.jQuery(".feuser-usergroup-selector-wrapper .t3-icon-toolbar-menu-shortcut.feuser-usergroup-selector-icon").removeClass( "t3-icon-apps t3-icon-apps-toolbar t3-icon-toolbar-menu-shortcut feuser-usergroup-selector-icon" );	
	if(countOptions>0){
		TYPO3.jQuery(id + '_list option').each( function() {
			TYPO3.jQuery(this).text(TYPO3.jQuery(this).text().replace(/ \(Hauptgruppe\)/g,''));			
			if(cnt==1){
				TYPO3.jQuery("#feuser-usergroup-selector-hidden").val(TYPO3.jQuery(this).attr('value'));
				TYPO3.jQuery(this).text(TYPO3.jQuery(this).text() + " (Hauptgruppe)");											
				TYPO3.jQuery("#feuser-usergroup-selector-icon-" + TYPO3.jQuery(this).attr('value')).removeClass( "t3-icon-mimetypes t3-icon-mimetypes-x t3-icon-x-sys_category feuser-usergroup-selector-icon" );
				TYPO3.jQuery("#feuser-usergroup-selector-icon-" + TYPO3.jQuery(this).attr('value')).addClass( "t3-icon-apps t3-icon-apps-toolbar t3-icon-toolbar-menu-shortcut feuser-usergroup-selector-icon" );				
			} else {
				TYPO3.jQuery("#feuser-usergroup-selector-hidden").val(TYPO3.jQuery("#feuser-usergroup-selector-hidden").val() + "," + TYPO3.jQuery(this).attr('value'));
			}
			cnt = cnt + 1;
		});	
	} else {
		TYPO3.jQuery("#feuser-usergroup-selector-hidden").val("");
	}
	TYPO3.jQuery("#feuser-usergroup-selector-cnt").text(countOptions);
}

function mooxFeusersAddOption(id,value,label){	
	var alreadyExisting = false;
	var maxCount = TYPO3.jQuery("#feuser-usergroup-selector-max").val();
	if(maxCount === undefined){
		maxCount = 0;
	}	
	var countOptions = TYPO3.jQuery(id + '_list option').size();
	if(countOptions<maxCount || maxCount==0){
		TYPO3.jQuery(id + '_list option').each(function(){
			if(TYPO3.jQuery(this).attr('value')==value){
				alreadyExisting = true;			
			}
		});
		TYPO3.jQuery(id + '_list option').removeAttr('selected');
		if(!alreadyExisting){
			TYPO3.jQuery(id + '_list').append( new Option(label,value,false,true) );
			TYPO3.jQuery("#feuser-usergroup-selector-wrapper-" + value).addClass( "feuser-usergroup-selector-active" );
		}
	}
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersToggleOption(id,value,label){	
	var alreadyExisting = false;
	var maxCount = TYPO3.jQuery("#feuser-usergroup-selector-max").val();
	if(maxCount === undefined){
		maxCount = 0;
	}	
	var countOptions = TYPO3.jQuery(id + '_list option').size();
	TYPO3.jQuery(id + '_list option').each(function(){
		if(TYPO3.jQuery(this).attr('value')==value){
			alreadyExisting = true;			
		}
    });
	TYPO3.jQuery(id + '_list option').removeAttr('selected');
	if(!alreadyExisting){
		if(countOptions<maxCount || maxCount==0){
			TYPO3.jQuery(id + '_list').append( new Option(label,value,false,true) );
			TYPO3.jQuery("#feuser-usergroup-selector-wrapper-" + value).addClass( "feuser-usergroup-selector-active" );
		}
	} else {
		TYPO3.jQuery(id + '_list option[value="' + value + '"]').remove();
		TYPO3.jQuery("#feuser-usergroup-selector-wrapper-" + value).removeClass( "feuser-usergroup-selector-active" );
	}
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersAddAllOptions(id){	
		
	TYPO3.jQuery(".feuser-usergroup-selector-wrapper").each(function(){
		value = TYPO3.jQuery(this).attr('id').replace(/feuser-usergroup-selector-wrapper-/g,'');
		mooxFeusersAddOption(id,value,TYPO3.jQuery("#feuser-usergroup-selector-title-" + value).attr('title'));		
    });
	TYPO3.jQuery(".feuser-usergroup-selector-wrapper").each(function(){
		value = TYPO3.jQuery(this).attr('id').replace(/feuser-usergroup-selector-wrapper-/g,'');
		TYPO3.jQuery(id + '_list option[value="' + value + '"]').attr('selected',true);		
    });	
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersRemoveOptions(id){	
	TYPO3.jQuery(id + '_list option:selected').each(function(){
		TYPO3.jQuery("#feuser-usergroup-selector-wrapper-" + TYPO3.jQuery(this).attr('value')).removeClass( "feuser-usergroup-selector-active" );
		TYPO3.jQuery(this).remove();
    });
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersRemoveAllOptions(id){	
	TYPO3.jQuery(id + '_list option').each(function(){
		TYPO3.jQuery("#feuser-usergroup-selector-wrapper-" + TYPO3.jQuery(this).attr('value')).removeClass( "feuser-usergroup-selector-active" );
		TYPO3.jQuery(this).remove();
    });
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersSetMainUsergroup(id,value,label){	
	
	value = typeof value !== 'undefined' ? value : 0;
	label = typeof label !== 'undefined' ? label : '';
	
	var newPos = 0;
	var countOptions = TYPO3.jQuery(id + '_list option:selected').size();
	if(value>0){
		mooxFeusersAddOption(id,value,label);
		TYPO3.jQuery(id + '_list option[value="' + value + '"]').remove();
		TYPO3.jQuery(id + '_list option').eq(newPos).before("<option value='"+value+"' selected='selected'>"+ label +"</option>");		
		TYPO3.jQuery(".feuser-usergroup-selector-context").hide();
	} else {
		if(countOptions>1){
			alert("Bitte wählen Sie nur eine Benutzergruppe als Hauptgruppe aus");
		} else {
			TYPO3.jQuery(id + '_list option:selected').each( function() {		
				value = TYPO3.jQuery(this).val();
				TYPO3.jQuery(id + '_list option').eq(newPos).before("<option value='"+value+"' selected='selected'>"+TYPO3.jQuery(this).text()+"</option>");
				TYPO3.jQuery(this).remove();		
				newPos = newPos + 1;
			});					
		}
	}
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersMoveOptionsToTop(id){	
	var newPos = 0;
	TYPO3.jQuery(id + '_list option:selected').each( function() {		
		TYPO3.jQuery(id + '_list option').eq(newPos).before("<option value='"+TYPO3.jQuery(this).val()+"' selected='selected'>"+TYPO3.jQuery(this).text()+"</option>");
		TYPO3.jQuery(this).remove();		
		newPos = newPos + 1;
    });
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersMoveOptionsOneUp(id){	
	TYPO3.jQuery(id + '_list option:selected').each( function() {
		var newPos = TYPO3.jQuery(id + '_list option').index(this) - 1;
		if (newPos > -1) {
			TYPO3.jQuery(id + '_list option').eq(newPos).before("<option value='"+TYPO3.jQuery(this).val()+"' selected='selected'>"+TYPO3.jQuery(this).text()+"</option>");
			TYPO3.jQuery(this).remove();
		}	
    });
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersMoveOptionsOneDown(id){	
	var countOptions = TYPO3.jQuery(id + '_list option').size();
	TYPO3.jQuery(TYPO3.jQuery(id + '_list option:selected').get().reverse()).each( function() {
		var newPos = TYPO3.jQuery(id + '_list option').index(this) + 1;
		if (newPos < countOptions) {
			TYPO3.jQuery(id + '_list option').eq(newPos).after("<option value='"+TYPO3.jQuery(this).val()+"' selected='selected'>"+TYPO3.jQuery(this).text()+"</option>");
			TYPO3.jQuery(this).remove();
		}	
    });
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersMoveOptionsToBottom(id){	
	var newPos = TYPO3.jQuery(id + '_list option').size();
	newPos = newPos - 1;
	TYPO3.jQuery(TYPO3.jQuery(id + '_list option:selected').get().reverse()).each( function() {
		TYPO3.jQuery(id + '_list option').eq(newPos).after("<option value='"+TYPO3.jQuery(this).val()+"' selected='selected'>"+TYPO3.jQuery(this).text()+"</option>");
		TYPO3.jQuery(this).remove();
		newPos = newPos - 1;	
    });
	mooxFeusersUpdateUsergroupsField(id);
}

function showMoveSelection(uid){	
	TYPO3.jQuery(".feuser-move-selection").hide("fast");
	TYPO3.jQuery("#feuser-move-selection-" + uid).show("fast");	
}