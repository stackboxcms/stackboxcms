// When DOM is ready
$(function() {
	/**
	 * Initialize dialog window
	 */
	cx_modal = $('#cx_modal');
	cx_modal.dialog({
		autoOpen: false,
		modal: true,
		minWidth: 400,
		minHeight: 300
	});
	
	/**
	 * Open link in the admin bar in a modal window
	 */
	$('#cx_admin_bar a').live('click', function() {
		var tLink = $(this);
		$.ajax({
			type: "POST",
			url: tLink.attr('href'),
			success: function(data, textStatus, req) {
				$('#cx_modal_content', cx_modal).html(data);
				cx_modal.dialog('open');
			},
			error: function(req) { // req = XMLHttpRequest object
				alert("[ERROR] Unable to load URL: " + req.responseText);
			}
		});
		return false;
	});
	
	
	/**
	 * Handle forms within modal windows (AJAX)
	 */
	$('#cx_modal form').live('submit', function() {
		var tForm = $(this);
		$.ajax({
			type: "POST",
			url: tForm.attr('href'),
			data: tForm.serialize(),
			success: function(msg, textStatus, req) {
				alert("Data Saved: " + msg);
				//$.tools.overlay.close();
			},
			error: function(req) { // req = XMLHttpRequest object
				// Validation error
				if(req.status == 400){
					alert("Validation errors");
				} else {
					alert("[ERROR] Unable to save data: " + req.responseText);
				}
			}
		});
		return false;
	});
	
	
	/**
	 * Module drag-n-drop, adding to page regions
	 */
	$('div.cx_admin_modules_module').draggable({
		helper: 'clone',
		connectToSortable: 'div.cx_region',
		stop: function(e, ui) {
			alert('Dropped module - too bad saving is not yet implemented...');
		}
	});
	$('div.cx_region').sortable({
		items: 'div.cx_module'
	});
	
	/**
	 *
	 */
	$('form .app_form_field_datetime input').live(function() {
		$(this).datepicker();
	});
});