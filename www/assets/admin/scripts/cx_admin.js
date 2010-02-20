// Bind all DOM events
$(function() {
	$('#cx_admin_bar a').attr('rel', '#cx_modal');
});

// Window onLoad
$(window).load(function() {
	/**
	 * Open link in the admin bar in a modal window
	 */
	$('#cx_admin_bar a[rel]').overlay({
		//effect: 'apple',
		expose: {
			color: '#333',
			loadSpeed: 200,
			opacity: 0.9
		},
		closeOnClick: false,
		onBeforeLoad: function() {
            // grab wrapper element inside content
            var wrap = this.getContent().find("#cx_modal_content");
            // load the page specified in the trigger
            wrap.load(this.getTrigger().attr("href"));
        }
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
});