// When DOM is ready
$(function() {
	/**
	 * Initialize commonly referenced elements to avoid multiple DOM lookups
	 */
	cx_admin_bar = $('#cx_admin_bar');
	cx_modal = $('#cx_modal');
	cx_regions = $('div.cx_region');
	cx_modules = $('div.cx_module');
	
	/**
	 * Initialize dialog window
	 */
	cx_modal.dialog({
		autoOpen: false,
		modal: true,
		minWidth: 400,
		minHeight: 300
	});
	
	/**
	 * Open link in the admin bar in a modal window
	 */
	$('a', cx_admin_bar).live('click', function() {
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
	$('form', cx_modal).live('submit', function() {
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
	$('#cx_admin_modules div.cx_module_tile').draggable({
		helper: 'clone',
		connectToSortable: cx_regions,
		start: function(e, ui) {
			cx_regions.addClass('cx_region_highlight');
		},
		stop: function(e, ui) {
			cx_regions.removeClass('cx_region_highlight');
		}
	});
	cx_regions.sortable({
		items: 'div.cx_module, div.cx_module_tile',
		connectWith: cx_regions,
		placeholder: 'cx_module_placeholder',
		forcePlaceholderSize: true,
		start: function(e, ui) {
			cx_regions.addClass('cx_region_highlight');
		},
		stop: function(e, ui) {
			// Remove region highlight
			cx_regions.removeClass('cx_region_highlight');
			
			var nRegion = $(e.target); // region will be drop target
			var nRegionName = nRegion.attr('id').replace('cx_region_', '');
			// Admin module, dragged from floating pane
			if(ui.item.is('div.cx_module_tile')) {
				nModule = ui.item;
				nModuleName = nModule.attr('id').replace('cx_module_tile_', '');
				$.ajax({
					type: "POST",
					url: cx.config.url + cx.page.url + 'm,Page_Module,0.html',
					data: {'region': nRegionName, 'name': nModuleName},
					success: function(data, textStatus, req) {
						nModule.replaceWith(data).fadeIn();
					},
					error: function(req) { // req = XMLHttpRequest object
						// Validation error
						if(req.status == 400){
							alert("Validation errors");
						} else {
							alert("[ERROR "+req.status+"] Unable to save data:\n\n" + req.responseText);
						}
					}
				});
			}
			// Serialize modules and save positions
			console.log('Module Serialization: ' + cx_serializeRegionModules());
		}
	});
	
	
	/**
	 * Module editing - display controls on hover
	 */
	cx_modules.live('hover', function(e) {
		nModule = $(this);
		
		// Note: 'hover' actually binds to custom events 'mouseenter' and 'mouseleave'
		if(e.type == 'mouseenter') {
			
		} else if(e.type == 'mouseleave') {
			
		}
	});
	
	
	/**
	 * 
	 */
	$('form .app_form_field_datetime input').live(function(e) {
		$(this).datepicker();
	});
});


// Custom function to serialize module order in regions
function cx_serializeRegionModules() {
	var str = "";
	cx_regions.each(function() {
		var regionName = this.id.replace('cx_region_', '');
		$('div.cx_module', this).not('.ui-helper').each(function() {
			var moduleId = parseInt($(this).attr('id').replace('cx_module_', ''));
			str += "&modules["+regionName+"][]="+moduleId+"";
		});
	});
	return str;
}