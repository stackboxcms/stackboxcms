// Bind all DOM events
$(function() {
	$('#cx_admin_bar a').attr('rel', '#cx_modal');
});

// Window onLoad
$(window).load(function() {
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
});