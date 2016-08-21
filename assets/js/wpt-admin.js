jQuery(document).ready(function($){ 

	$('select[name="wptsettings_settings[wptsettings_helper_orgs]"]').change(function(){
		var objectid = $('option:selected', this).val();
		$('#org-id').html('');
		resetDropdown('Boards');
		resetDropdown('Lists');
		resetDropdown('Cards');
		if (objectid != '0') {
			$('#org-id').html(objectid);
			populateDropdown(objectid, 'boards');
		}
		
	 });
	 
	 $('select[name="wptsettings_settings[wptsettings_helper_boards]"]').change(function(){
		var objectid = $('option:selected', this).val();
		$('#board-id').html('');
		resetDropdown('Lists');
		resetDropdown('Cards');
		if (objectid != '0') {
			$('#board-id').html(objectid);
			populateDropdown(objectid, 'lists');
		}
		
	 });
	 
	 $('select[name="wptsettings_settings[wptsettings_helper_lists]"]').change(function(){
		var objectid = $('option:selected', this).val();
		$('#list-id').html('');
		resetDropdown('Cards');
		if (objectid != '0') {
			$('#list-id').html(objectid);
			populateDropdown(objectid, 'cards');
		}
		
	 });
	 
	 $('select[name="wptsettings_settings[wptsettings_helper_cards]"]').change(function(){
		var objectid = $('option:selected', this).val();
		$('#card-id').html('');
		if (objectid != '0') {
			$('#card-id').html(objectid);
		}
		
	 });
	 
	 function populateDropdown(id, type) {
		  $.post(ajaxurl, 
            { 	action:'wpt_get_objects', 
            	id:id, 
            	type: type,
            	nonce: wp_trello.nonce }, 
            function(data){
                new_object = 'select[name="wptsettings_settings[wptsettings_helper_' + type + ']"]';
                $(new_object).empty();
                $.each(data.objects, function(key, val) {
					$(new_object).append('<option value="' + key + '">' + val +'</option>');
				})
				$(new_object).removeAttr('disabled');
            }
        , 'json');
        return false;
		 
	 }
	 
	 function resetDropdown(type) {
		 object = 'select[name="wptsettings_settings[wptsettings_helper_' + type.toLowerCase() + ']"]';
		 len = type.length;
		 labeltype = type.substr(0, len-1);
		 idlabel = '#' + labeltype.toLowerCase() + '-id';
		 $(idlabel).html('');
		 $(object).attr('disabled', 'disabled');	
		 $(object).empty();
		 $(object).append('<option value="0">Select ' + labeltype +'</option>');		 
	 }
	 
	 $('#wpt-disconnect').live('click', function(){      
       	var r = confirm("Disconnect from Trello?");
		if (r==true) {
			$.post(ajaxurl, 
				{ 	action:'wpt_disconnect',
					nonce: wp_trello.nonce
				 }, 
				function(data){
					window.location = data.redirect;
				}
			, 'json');
		}
	});
	   
	 	 
});