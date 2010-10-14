var cx = cx || {};

/**
 * MODAL / Content Area
 */
cx.modal = (function (cx, $) {
    // PRIVATE
    var p = {};
    
    // Bind submit button
    p.bindSubmit = function() {
        p.el.find('form').submit(function(e) {
            var tForm = $(this);
            
            // Assemble form data into object for use below
            formData = tForm.serializeArray();
            tData = {};
            $.each(formData, function(i, field){
                tData[field.name] = field.value;
            });
            
            $.ajax({
                type: "POST",
                url: tForm.attr('action'),
                data: tForm.serialize(),
                success: function(data, textStatus, req) {
                    nData = $(data);
                    if(tData._method == 'DELETE') {
                        if(tData.item_dom_id) {
                            nModule = $('#' + tData.item_dom_id).remove();
                        }
                    } else {
                        nModule = $('#' + nData.attr('id')).replaceWith(data);
                    }
                    m.hide();
                },
                error: function(req) { // req = XMLHttpRequest object
                    if(req.status == 400){
                        // Validation error ("Bad Request")
                        m.error("There were some validation errors on save");
                        m.content(req.responseText);
                    } else {
                        m.error("[ERROR] Unable to save data: \n" + req.responseText);
                    }
                }
            });
            return false;
        });
    }
    
    // PUBLIC
    var m = {};
    m.init = function() {
        // Setup selectors
        p.el = $('#cx_modal');
        p.elContent = $('#cx_modal_content');
        
        // Setup dialog
        p.el.dialog({
            autoOpen: false,
            modal: true,
            'title' : '',
            width: $(window).width() * 0.9,
            height: $(window).height() * 0.85
            });
        
        // Initialize...
        m.hide();
        p.bindSubmit();
        
        // Close modal window on 'cancel'
        $('a.app_action_cancel', p.el).live('click', function() {
            m.hide();
            return false;
        });
    };
    m.show = function() {
        p.el.dialog('open');
    };
    m.hide = function() {
        p.el.dialog('close');
    };
    m.loading = function() {
        m.content('Loading...');
    };
    m.content = function(content) {
        p.elContent.html(content);
        
        // Load CKEditor in editor fields
        $("form li.app_form_field_editor textarea", p.elContent).ckeditor(function() {}, {
            toolbar: [
                ['PasteText','PasteFromWord'],
                ['Bold','Italic','Underline','Strike'],
                ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
                ['Link','Unlink','Anchor'],
                ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','-','About'],
                '/',
                ['Format','FontSize'],
                ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                ['Subscript','Superscript'],
                ['TextColor','BGColor','-','RemoveFormat'],
                ['Find','Replace'],
                ['ShowBlocks','-','Source']
            ],
            autoUpdateElementJquery: true
        });
        
        p.bindSubmit();
        m.show();
    };
    m.error = function(msg) {
        alert(msg);
    };
    
    // Expose public methods
    return m;
}(cx, jQuery));


/**
 * When DOM is ready / event binding
 */
$(function() {
    /**
     * Initialize commonly referenced elements to avoid multiple DOM lookups
     */
    var cx_admin_bar = $('#cx_admin_bar');
    var cx_regions = $('div.cx_region');
    var cx_modules = $('div.cx_module');
    
    
    /**
     * Initialize dialog window
     */
    cx.modal.init();
    
    
    /**
     * Open link in the admin bar in a modal window
     */
    $('#cx_admin_bar a[rel=modal], div.cx_ui_controls a').live('click', function() {
        var tLink = $(this);
        cx.modal.loading();
        $.ajax({
            type: "GET",
            url: tLink.attr('href'),
            success: function(data, textStatus, req) {
                cx.modal.content(data);
            },
            error: function(req) { // req = XMLHttpRequest object
                cx.modal.error("[ERROR] Unable to load URL: " + req.responseText);
            }
        });
        return false;
    });
    
    
    /**
     * Clieck 'ADD CONTENT' button
     */
    $('#cx_admin_bar_addContent').toggle(function() {
        $('#cx_admin_modules').slideDown();
        return false;
    }, function() {
        $('#cx_admin_modules').slideUp();
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
        handle: 'div.cx_ui_controls .cx_ui_title',
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
                var nModule = $('#' + ui.item.context.id);
                var nModuleName = nModule.attr('id').replace('cx_module_tile_', '');
                $.ajax({
                    type: "POST",
                    url: cx.config.url + cx.page.url + 'm,Page_Module,0.html',
                    data: {'region': nRegionName, 'name': nModuleName},
                    success: function(data, textStatus, req) {
                        nModule.replaceWith(data).effect("highlight", {color: '#FFFFCF'}, 2000).css({border: '1px solid red'});
                    },
                    error: function(req) { // req = XMLHttpRequest object
                        alert("[ERROR "+req.status+"] Unable to save data:\n\n" + req.responseText);
                    }
                });
            }
            // Serialize modules to save module/region positions
            $.ajax({
                type: "GET",
                url: cx.config.url + cx.page.url + 'm,Page_Module,0/saveSort.html',
                data: 'ajax=1' + cx_serializeRegionModules(),
                success: function(data, textStatus, req) {
                    // Do nothing for now, eventually might show some sort of activity notice in UI, etc.
                },
                error: function(req) { // req = XMLHttpRequest object
                    alert("[ERROR] Unable to load URL: \n" + req.responseText);
                }
            });
        }
    });
    
    
    /**
     * Module editing - display controls on hover
     */
    cx_modules.live('hover', function(e) {
        nModule = $(this);
        // Note: 'hover' actually binds to custom events 'mouseenter' and 'mouseleave'
        if(e.type == 'mouseover') {
            nModule.addClass('cx_ui_hover');
        } else {
            nModule.removeClass('cx_ui_hover');
        }
    });
    
    
    /**
     * Ensure datepicker is always available
     */
    $('form .app_form_field_datetime input').live('click', function(e) {
        $(this).datepicker({showOn:'focus'}).focus();
    });
    
    
    /**
     * Serialize module order in regions into URL for Cont-xt to handle saving
     */
    function cx_serializeRegionModules() {
        var str = "";
        $('div.cx_region').each(function() {
            var regionName = this.id.replace('cx_region_', '');
            $('div.cx_module', this).not('.ui-helper').each(function() {
                var moduleId = parseInt($(this).attr('id').replace('cx_module_', ''));
                str += "&modules["+regionName+"][]="+moduleId+"";
            });
        });
        return str;
    }
});
