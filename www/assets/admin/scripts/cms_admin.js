var cms = cms || {};

/**
 * MODAL / Content Area
 */
cms.modal = (function (cms, $) {
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
        p.el = $('#cms_modal');
        p.elContent = $('#cms_modal_content');
        
        // Setup dialog
        p.el.dialog({
            autoOpen: false,
            closeOnEscape: true,
            modal: true,
            'title' : '',
            width: $(window).width() * 0.9,
            height: $(window).height() * 0.85,
            open: function(event,ui) {},
            close: function(e,ui) {
                // Destroy CKEditor instance so it can be re-created for next AJAX call
                try {
                    $("form li.app_form_field_editor textarea", p.elContent).ckeditorGet().destroy();
                } catch(e) {}
            }

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
            autoUpdateElementJquery: true,
            baseFloatZIndex: 9000
        });
        
        p.bindSubmit();
        m.show();
    };
    m.error = function(msg) {
        alert(msg);
    };
    
    // Expose public methods
    return m;
}(cms, jQuery));


/**
 * When DOM is ready / event binding
 */
$(function() {
    /**
     * Initialize commonly referenced elements to avoid multiple DOM lookups
     */
    var cms_admin_bar = $('#cms_admin_bar');
    var cms_admin_modules = $('#cms_admin_modules');
    var cms_regions = $('div.cms_region');
    var cms_modules = $('div.cms_module');
    
    
    /**
     * Initialize dialog window
     */
    cms.modal.init();
    
    
    /**
     * Open link in the admin bar in a modal window
     */
    $('#cms_admin_bar a[rel=modal], div.cms_ui_controls a').live('click', function() {
        var tLink = $(this);
        cms.modal.loading();
        $.ajax({
            type: "GET",
            url: tLink.attr('href'),
            success: function(data, textStatus, req) {
                cms.modal.content(data);
            },
            error: function(req) { // req = XMLHttpRequest object
                cms.modal.error("[ERROR] Unable to load URL: " + req.responseText);
            }
        });
        return false;
    });
    
    
    /**
     * Click 'ADD CONTENT' button
     */
    $('#cms_admin_bar_addContent').toggle(function() {
        var elModHeight = cms_admin_modules.height();
        cms_admin_modules.css({visibility: 'visible', height: 0}).animate({height: elModHeight});
        $('body').animate({paddingTop: '+=' + elModHeight});
        return false;
    }, function() {
        var elModHeight = cms_admin_modules.height();
        cms_admin_modules.slideUp().animate({height: 0}, 500, function() {
            cms_admin_modules.css({visibility: 'hidden', height: elModHeight});
        });
        $('body').animate({paddingTop: cms_admin_bar.height()});
        return false;
    });
    
    
    /**
     * Module drag-n-drop, adding to page regions
     */
    $('#cms_admin_modules div.cms_module_tile').draggable({
        helper: 'clone',
        connectToSortable: cms_regions,
        start: function(e, ui) {
            cms_regions.addClass('cms_region_highlight');
        },
        stop: function(e, ui) {
            cms_regions.removeClass('cms_region_highlight');
        }
    });
    cms_regions.sortable({
        items: 'div.cms_module, div.cms_module_tile',
        connectWith: cms_regions,
        handle: 'div.cms_ui_controls .cms_ui_title',
        placeholder: 'cms_module_placeholder',
        forcePlaceholderSize: true,
        start: function(e, ui) {
            cms_regions.addClass('cms_region_highlight');
        },
        stop: function(e, ui) {
            // Remove region highlight
            cms_regions.removeClass('cms_region_highlight');
            
            var nRegion = $(e.target); // region will be drop target
            var nRegionName = nRegion.attr('id').replace('cms_region_', '');
            // Admin module, dragged from floating pane
            if(ui.item.is('div.cms_module_tile')) {
                var nModule = $('#' + ui.item.context.id);
                var nModuleName = nModule.attr('id').replace('cms_module_tile_', '');
                $.ajax({
                    type: "POST",
                    url: cms.config.url + cms.page.url + 'm,Page_Module,0.html',
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
                url: cms.config.url + cms.page.url + 'm,Page_Module,0/saveSort.html',
                data: 'ajax=1' + cms_serializeRegionModules(),
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
    $('#cms_admin_bar_editPage').toggle(function(e) {
        cms_modules.addClass('cms_ui_edit');
    }, function(e) {
        cms_modules.removeClass('cms_ui_edit');
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
    function cms_serializeRegionModules() {
        var str = "";
        $('div.cms_region').each(function() {
            var regionName = this.id.replace('cms_region_', '');
            $('div.cms_module', this).not('.ui-helper').each(function() {
                var moduleId = parseInt($(this).attr('id').replace('cms_module_', ''));
                str += "&modules["+regionName+"][]="+moduleId+"";
            });
        });
        return str;
    }
});



/***
 * Patch for dialog-fix ckeditor problem [ by ticket #4727 ]
 * @link http://dev.jqueryui.com/ticket/4727
 */
$.extend($.ui.dialog.overlay, { create: function(dialog){
    if (this.instances.length === 0) {
        // prevent use of anchors and inputs
        // we use a setTimeout in case the overlay is created from an
        // event that we're going to be cancelling (see #2804)
        setTimeout(function() {
            // handle $(el).dialog().dialog('close') (see #4065)
            if ($.ui.dialog.overlay.instances.length) {
                $(document).bind($.ui.dialog.overlay.events, function(event) {
                    var parentDialog = $(event.target).parents('.ui-dialog');
                    if (parentDialog.length > 0) {
                        var parentDialogZIndex = parentDialog.css('zIndex') || 0;
                        return parentDialogZIndex > $.ui.dialog.overlay.maxZ;
                    }
                    
                    var aboveOverlay = false;
                    $(event.target).parents().each(function() {
                        var currentZ = $(this).css('zIndex') || 0;
                        if (currentZ > $.ui.dialog.overlay.maxZ) {
                            aboveOverlay = true;
                            return;
                        }
                    });
                    
                    return aboveOverlay;
                });
            }
        }, 1);
        
        // allow closing by pressing the escape key
        $(document).bind('keydown.dialog-overlay', function(event) {
            (dialog.options.closeOnEscape && event.keyCode
                    && event.keyCode == $.ui.keyCode.ESCAPE && dialog.close(event));
        });
            
        // handle window resize
        $(window).bind('resize.dialog-overlay', $.ui.dialog.overlay.resize);
    }
    
    var $el = $('<div></div>').appendTo(document.body)
        .addClass('ui-widget-overlay').css({
        width: this.width(),
        height: this.height()
    });
    
    (dialog.options.stackfix && $.fn.stackfix && $el.stackfix());
    
    this.instances.push($el);
    return $el;
}});