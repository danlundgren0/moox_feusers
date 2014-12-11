var $j = jQuery.noConflict();

$j(document).ready(function() {	
	
	$j( "form.feuser-form" ).submit(function( event ) {
		if($j("#feuser-usergroup-selector-min")){
			var countOptions = $j('select.feuser-usergroup-selector-display option').size();
			var minCount = $j("#feuser-usergroup-selector-min").val();
			if(countOptions<minCount){
				alert("Sie müssem mindestens " + minCount + " Benutzergruppe(n) auswählen.");
				event.preventDefault();
			}
		}
	});		
	$j(".feuser-usergroup-selector-wrapper").bind("contextmenu",function(e){
		value = $j(this).attr('id').replace(/feuser-usergroup-selector-wrapper-/g,'');
		$j(".feuser-usergroup-selector-context").hide();
		$j("#feuser-usergroup-selector-context-" + value).show();		
	   return false;
	});
	$j(".feuser-usergroup-selector-context").mouseleave(function() {
		$j(this).hide();
	}); 	
	
	$j("#selected-folder").click(function(){
		if ( $j("#folder-list").is(":visible") ) {
			$j( "#folder-expander" ).removeClass( "t3-icon-move-up t3-icon-move-down" );			
			$j( "#folder-expander" ).addClass( "t3-icon-move-down" );
		} else { 
			$j( "#folder-expander" ).removeClass( "t3-icon-move-up t3-icon-move-down" );
			$j( "#folder-expander" ).addClass( "t3-icon-move-up" );
		}		
		$j("#folder-list").toggle("fast");
		
	});
	
	$j("#group-list-close").click(function(){
		$j("#group-list").hide("fast");
	});
	
	$j("#selected-group").click(function(){
		if ( $j("#group-list").is(":visible") ) {
			$j( "#group-expander" ).removeClass( "t3-icon-move-up t3-icon-move-down" );			
			$j( "#group-expander" ).addClass( "t3-icon-move-down" );
		} else { 
			$j( "#group-expander" ).removeClass( "t3-icon-move-up t3-icon-move-down" );
			$j( "#group-expander" ).addClass( "t3-icon-move-up" );
		}		
		$j("#group-list").toggle("fast");
		
	});
	
	$j("#group-list-close").click(function(){
		$j("#group-list").hide("fast");
	});
	
	$j("#filter-query").on("click", function () {
	   $j(this).select();
	});
	
	$j("#filter-mailing").on("change", function () {
	  $j("#filterForm").submit();
	});
	
	$j("#filter-state").on("change", function () {
	  $j("#filterForm").submit();
	});
	
	$j("#filter-per-page").on("change", function () {
	  $j("#filterForm").submit();
	});
	
	$j(this).ajaxStart(function(){         
		  $j("body").append("<div id='tx-moox-feuser-admin-overlay'><img src='/typo3conf/ext/moox_feusers/Resources/Public/Images/ajax-loader.gif' /></div>");
    });
	
	$j(this).ajaxStop(function(){
          $j("#tx-moox-feuser-admin-overlay").remove();
    });	
		
	$j(".feuser-details").on("click", function () {
		$j(this).hide("fast");
	});
});

function mooxFeusersToggleDetails(id){
	$j("#folder-list").hide("fast");
	$j("#group-list").hide("fast");
	if ( $j("#feuser-details-" + id).is(":visible") ) {
		visible = true;
	} else { 
		visible = false;
	}	
	$j(".feuser-details").hide("fast");
	$j(".feuser-details-expander-reset").removeClass( "t3-icon-move-up t3-icon-move-down" );			
	$j(".feuser-details-expander-reset").addClass( "t3-icon-move-down" );
	if (visible) {		
		$j("#feuser-details-" + id).hide("fast");		
		$j("#feuser-details-" + id + "-expander").removeClass( "t3-icon-move-up t3-icon-move-down" );			
		$j("#feuser-details-" + id + "-expander").addClass( "t3-icon-move-down" );
	} else { 
		$j("#feuser-details-" + id).show("fast");
		$j("#feuser-details-" + id + "-expander").removeClass( "t3-icon-move-up t3-icon-move-down" );			
		$j("#feuser-details-" + id + "-expander").addClass( "t3-icon-move-up" );		
	}	
}

function mooxFeusersUpdateUsergroupsField(id){
	var countOptions = $j(id + '_list option').size();
	var cnt = 1;
	$j(".feuser-usergroup-selector-wrapper .t3-icon-toolbar-menu-shortcut.feuser-usergroup-selector-icon").addClass( "t3-icon-mimetypes t3-icon-mimetypes-x t3-icon-x-sys_category feuser-usergroup-selector-icon" );
	$j(".feuser-usergroup-selector-wrapper .t3-icon-toolbar-menu-shortcut.feuser-usergroup-selector-icon").removeClass( "t3-icon-apps t3-icon-apps-toolbar t3-icon-toolbar-menu-shortcut feuser-usergroup-selector-icon" );	
	if(countOptions>0){
		$j(id + '_list option').each( function() {
			$j(this).text($j(this).text().replace(/ \(Hauptgruppe\)/g,''));			
			if(cnt==1){
				$j("#feuser-usergroup-selector-hidden").val($j(this).attr('value'));
				$j(this).text($j(this).text() + " (Hauptgruppe)");											
				$j("#feuser-usergroup-selector-icon-" + $j(this).attr('value')).removeClass( "t3-icon-mimetypes t3-icon-mimetypes-x t3-icon-x-sys_category feuser-usergroup-selector-icon" );
				$j("#feuser-usergroup-selector-icon-" + $j(this).attr('value')).addClass( "t3-icon-apps t3-icon-apps-toolbar t3-icon-toolbar-menu-shortcut feuser-usergroup-selector-icon" );				
			} else {
				$j("#feuser-usergroup-selector-hidden").val($j("#feuser-usergroup-selector-hidden").val() + "," + $j(this).attr('value'));
			}
			cnt = cnt + 1;
		});	
	} else {
		$j("#feuser-usergroup-selector-hidden").val("");
	}
	$j("#feuser-usergroup-selector-cnt").text(countOptions);
}

function mooxFeusersAddOption(id,value,label){	
	var alreadyExisting = false;
	var maxCount = $j("#feuser-usergroup-selector-max").val();
	if(maxCount === undefined){
		maxCount = 0;
	}	
	var countOptions = $j(id + '_list option').size();
	if(countOptions<maxCount || maxCount==0){
		$j(id + '_list option').each(function(){
			if($j(this).attr('value')==value){
				alreadyExisting = true;			
			}
		});
		$j(id + '_list option').removeAttr('selected');
		if(!alreadyExisting){
			$j(id + '_list').append( new Option(label,value,false,true) );
			$j("#feuser-usergroup-selector-wrapper-" + value).addClass( "feuser-usergroup-selector-active" );
		}
	}
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersToggleOption(id,value,label){	
	var alreadyExisting = false;
	var maxCount = $j("#feuser-usergroup-selector-max").val();
	if(maxCount === undefined){
		maxCount = 0;
	}	
	var countOptions = $j(id + '_list option').size();
	$j(id + '_list option').each(function(){
		if($j(this).attr('value')==value){
			alreadyExisting = true;			
		}
    });
	$j(id + '_list option').removeAttr('selected');
	if(!alreadyExisting){
		if(countOptions<maxCount || maxCount==0){
			$j(id + '_list').append( new Option(label,value,false,true) );
			$j("#feuser-usergroup-selector-wrapper-" + value).addClass( "feuser-usergroup-selector-active" );
		}
	} else {
		$j(id + '_list option[value="' + value + '"]').remove();
		$j("#feuser-usergroup-selector-wrapper-" + value).removeClass( "feuser-usergroup-selector-active" );
	}
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersAddAllOptions(id){	
		
	$j(".feuser-usergroup-selector-wrapper").each(function(){
		value = $j(this).attr('id').replace(/feuser-usergroup-selector-wrapper-/g,'');
		mooxFeusersAddOption(id,value,$j("#feuser-usergroup-selector-title-" + value).attr('title'));		
    });
	$j(".feuser-usergroup-selector-wrapper").each(function(){
		value = $j(this).attr('id').replace(/feuser-usergroup-selector-wrapper-/g,'');
		$j(id + '_list option[value="' + value + '"]').attr('selected',true);		
    });	
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersRemoveOptions(id){	
	$j(id + '_list option:selected').each(function(){
		$j("#feuser-usergroup-selector-wrapper-" + $j(this).attr('value')).removeClass( "feuser-usergroup-selector-active" );
		$j(this).remove();
    });
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersRemoveAllOptions(id){	
	$j(id + '_list option').each(function(){
		$j("#feuser-usergroup-selector-wrapper-" + $j(this).attr('value')).removeClass( "feuser-usergroup-selector-active" );
		$j(this).remove();
    });
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersSetMainUsergroup(id,value,label){	
	
	value = typeof value !== 'undefined' ? value : 0;
	label = typeof label !== 'undefined' ? label : '';
	
	var newPos = 0;
	var countOptions = $j(id + '_list option:selected').size();
	if(value>0){
		mooxFeusersAddOption(id,value,label);
		$j(id + '_list option[value="' + value + '"]').remove();
		$j(id + '_list option').eq(newPos).before("<option value='"+value+"' selected='selected'>"+ label +"</option>");		
		$j(".feuser-usergroup-selector-context").hide();
	} else {
		if(countOptions>1){
			alert("Bitte wählen Sie nur eine Benutzergruppe als Hauptgruppe aus");
		} else {
			$j(id + '_list option:selected').each( function() {		
				value = $j(this).val();
				$j(id + '_list option').eq(newPos).before("<option value='"+value+"' selected='selected'>"+$j(this).text()+"</option>");
				$j(this).remove();		
				newPos = newPos + 1;
			});					
		}
	}
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersMoveOptionsToTop(id){	
	var newPos = 0;
	$j(id + '_list option:selected').each( function() {		
		$j(id + '_list option').eq(newPos).before("<option value='"+$j(this).val()+"' selected='selected'>"+$j(this).text()+"</option>");
		$j(this).remove();		
		newPos = newPos + 1;
    });
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersMoveOptionsOneUp(id){	
	$j(id + '_list option:selected').each( function() {
		var newPos = $j(id + '_list option').index(this) - 1;
		if (newPos > -1) {
			$j(id + '_list option').eq(newPos).before("<option value='"+$j(this).val()+"' selected='selected'>"+$j(this).text()+"</option>");
			$j(this).remove();
		}	
    });
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersMoveOptionsOneDown(id){	
	var countOptions = $j(id + '_list option').size();
	$j($j(id + '_list option:selected').get().reverse()).each( function() {
		var newPos = $j(id + '_list option').index(this) + 1;
		if (newPos < countOptions) {
			$j(id + '_list option').eq(newPos).after("<option value='"+$j(this).val()+"' selected='selected'>"+$j(this).text()+"</option>");
			$j(this).remove();
		}	
    });
	mooxFeusersUpdateUsergroupsField(id);
}

function mooxFeusersMoveOptionsToBottom(id){	
	var newPos = $j(id + '_list option').size();
	newPos = newPos - 1;
	$j($j(id + '_list option:selected').get().reverse()).each( function() {
		$j(id + '_list option').eq(newPos).after("<option value='"+$j(this).val()+"' selected='selected'>"+$j(this).text()+"</option>");
		$j(this).remove();
		newPos = newPos - 1;	
    });
	mooxFeusersUpdateUsergroupsField(id);
}