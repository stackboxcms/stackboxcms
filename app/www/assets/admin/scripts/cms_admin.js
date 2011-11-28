var cms = cms || {};

// Additional config settings (some config on-page already)
cms.config = cms.config || {};
cms.config.editMode = false;

/**
 * Custom log() wrapper
 * @see http://paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
 */
window.log = function() {
  log.history = log.history || [];   // store logs to an array for reference
  log.history.push(arguments);
  if(this.console){
    console.log( Array.prototype.slice.call(arguments) );
  }
};

/**
 * UI/UX
 */
cms.ui = (function (cms, $) {
    // Expose public methods
    return {
        // Return and enforce edit mode or set it to new passed boolean value
        editMode: function(flag) {
            var _flag = ("undefined" != flag ? flag : cms.config.editMode);
            if(true === _flag) {
                cms.config.editMode = true;
                $('div.cms_module').addClass('cms_ui_edit');
            } else if(false === _flag) {
                cms.config.editMode = false;
                $('div.cms_module').removeClass('cms_ui_edit');
            }
            return cms.config.editMode;
        }
    };
}(cms, jQuery));


/**
 * MODAL / Content Area
 */
cms.modal = (function (cms, $) {
    // PRIVATE
    var p = {};
    
    // Bind submit button and link events
    p.bindEvents = function() {
        // Links inside modal window
        p.elContent.delegate('a:not(.app_action_cancel):not([rel=popup]):not([rel=nomodal])', 'click', function(e) {
            var link = $(this);

            // Not in CKEditor window
            if(link.closest('#cke_content').length) {
                return;
            }

            // Not anchor link
            var href = link.attr('href');
            if(!href || href.indexOf('#') === 0) {
                return;
            }

            // Not javascript link
            if(!href || href.indexOf('javascript:') === 0) {
                return;
            }

            // Re-open in modal window
            log('cms.modal.bindEvents > Intercepting link href to load with AJAX in modal window');
            m.openLink(link);
            e.preventDefault();
            return false;
        })
        // Open links inside popup window
        p.elContent.delegate('a[rel=popup]', 'click', function(e) {
            var link = $(this);

            // Not anchor link
            if(!link.attr('href') || link.attr('href').indexOf('#') === 0) {
                return;
            }

            // Open in popup window
            var newWindow = window.open(link.attr('href'), 'cms_popup', 'height=400,width=800');
            if (window.focus) {
                newWindow.focus();
            }

            e.preventDefault();
            return false;
        })
        // Close modal window on 'cancel'
        .delegate('a.app_action_cancel', 'click', function(e) {
            m.hide();
            return false;
        })
        // AJAX form submit
        .delegate('form', 'submit', function(e) {
            var tForm = $(this);
            
            // Assemble form data into object for use below
            formData = tForm.serializeArray();
            tData = {};
            $.each(formData, function(i, field) {
                tData[field.name] = field.value;
            });
            
            var fMethod = tForm.attr('method');
            $.ajax({
                type: fMethod ? fMethod.toUpperCase() : "POST",
                url: tForm.attr('action'),
                data: tForm.serialize(),
                success: function(data, textStatus, req) {
                    if("GET" === fMethod.toUpperCase()) {
                        m.content(req.responseText);
                        return;
                    }

                    nData = $(data);
                    if(tData._method == 'DELETE') {
                        if(tData.item_id) {
                            nModule = $('#cms_module_' + tData.item_id).remove();
                        }
                    } else {
                        nModule = $('#' + nData.attr('id')).replaceWith(data);

                        // Show edit controls if in edit mode
                        cms.ui.editMode(true);
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
            title : '',
            width: $(window).width() * 0.9,
            height: $(window).height() * 0.85,
            open: function(event,ui) {},
            close: function(e,ui) {
                // Destroy CKEditor instance so it can be re-created for next AJAX call
                try {
                    $("form .app_form_field_editor textarea", p.elContent).ckeditorGet().destroy();
                } catch(e) {}
            }
        });
        
        // Initialize...
        m.hide();
        p.bindEvents();
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
    m.openLink = function(a) {

        // Not anchor link
        if(!a.attr('href') || a.attr('href').indexOf('#') === 0) {
            return false;
        }

        cms.modal.loading();
        $.ajax({
            type: "GET",
            url: a.attr('href'),
            success: function(data, textStatus, req) {
                cms.modal.content(data);
            },
            error: function(req) { // req = XMLHttpRequest object
                cms.modal.error("[ERROR " + req.status + "]\nResponse returned HTTP " + req.status + " error status");
                cms.modal.content(req.responseText);
            }
        });
        return false;
    };
    m.content = function(content) {
        p.elContent.html(content);
        
        // Set dialog buttons based on content
        var dialogButtons = [];
        var dialogForm = $('form', p.elContent);
        if(dialogForm && dialogForm.length > 0) {
            // Hide form buttons
            dialogForm.find('.app_form_actions').hide();

            if(dialogForm.find('input[name=_method]').val() == "DELETE" || dialogForm.attr('method') == 'DELETE') {
                dialogButtons[0] = {
                    text: "Delete",
                    id: "cms_modal_btn_delete",
                    click: function() {
                        $("form", this).submit();
                    }
                };
            } else {
                dialogButtons[0] = {
                    text: "Save",
                    id: "cms_modal_btn_save",
                    click: function() {
                        $("form", this).submit();
                    }
                };
            }
        }

        // Cancel button is always displayed
        dialogButtons[dialogButtons.length] = {
            text: "Cancel",
            id: "cms_modal_btn_cancel",
            click: function() {
                $(this).dialog("close");
            }
        };

        // Set dialog buttons
        p.el.dialog("option", "buttons", dialogButtons);

        // Hack to set button icons because dialog button support does not set icons with button definitions above
        $('#cms_modal_btn_save').button({ icons: { primary: 'ui-icon-check' } });
        $('#cms_modal_btn_cancel').button({ icons: { primary: 'ui-icon-cancel' } });
        $('#cms_modal_btn_delete').button({ icons: { primary: 'ui-icon-trash' } });

        // Convert form submit buttons to jQuery UI style
        $(".cms_ui form input[type=button], .cms_ui form input[type=submit], .cms_ui form button, a.cms_button", p.elContent).button();

        // Load CKEditor in editor fields
        $("form .app_form_field_editor textarea", p.elContent).ckeditor(function(e) {
            //alert(CKEDITOR.instances[$(e).attr('name')]);
        }, {
            toolbar: [
                ['PasteText','PasteFromWord'],
                ['Bold','Italic','Underline','Strike'],
                ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
                ['Link','Unlink','Anchor'],
                ['Image','File','Flash','Table','HorizontalRule','Smiley','SpecialChar','-','About'],
                '/',
                ['Format','FontSize'],
                ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
                ['Subscript','Superscript'],
                ['TextColor','BGColor','-','RemoveFormat'],
                ['Find','Replace'],
                ['ShowBlocks','-','Source']
            ],
            autoUpdateElementJquery: true,
            baseFloatZIndex: 9000,

            /* file upload support */
            filebrowserBrowseUrl : cms.editor.fileBrowseUrl,
            filebrowserUploadUrl : cms.editor.fileUploadUrl,
            filebrowserImageBrowseUrl : cms.editor.imageBrowseUrl,
            filebrowserImageUploadUrl : cms.editor.imageUploadUrl,
            filebrowserWindowWidth  : 800,
            filebrowserWindowHeight : 500
        });
        
        m.show();
    };
    m.error = function(msg) {
        alert(msg);
    };
    m.selector = function() {
        return p.el;
    }
    
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
    var cms_regions = $('.cms_region, .cms_region_global');
    var cms_modules = $('div.cms_module');
    var cms_admin_edit_mode = false;
    
    
    /**
     * Initialize dialog window
     */
    cms.modal.init();
    
    
    /**
     * Open link in the admin bar in a modal window
     */
    $('#cms_admin_bar a[rel=modal], div.cms_ui_controls a').live('click', function(e) {
        cms.modal.openLink($(this));
        return false;
    });
    
    
    /**
     * Click 'ADD CONTENT' button
     */
    var elModHeight = cms_admin_modules.height();
    $('#cms_admin_bar_addContent').toggle(function() {
        cms_admin_modules.css({visibility: 'visible', height: 0}).stop().animate({height: elModHeight});
        $('body').animate({paddingTop: '+=' + elModHeight});
        return false;
    }, function() {
        cms_admin_modules.animate({height: 0}, 500, function() {
            cms_admin_modules.css({visibility: 'hidden', height: elModHeight});
        });
        $('body').animate({paddingTop: cms_admin_bar.height()});
        return false;
    });
    
    
    /**
     * Module drag-n-drop, adding to page regions
     */
    cms_regions.sortable({
        items: '.cms_module, .cms_module_tile',
        connectWith: cms_regions,
        handle: '.cms_ui_controls .cms_ui_title',
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
                var nModule = ui.item;
                var nModuleName = nModule.attr('rel').replace('cms_module_tile_', '');
                $.ajax({
                    type: "POST",
                    url: cms.config.url + cms.page.url + 'm,Page_Module,0.html',
                    data: {'region': nRegionName, 'name': nModuleName},
                    success: function(data, textStatus, req) {
                        // Replace content on page with new content from AJAX response
                        nModule.replaceWith(data);

                        // Put CMS in edit mode
                        cms.ui.editMode(true);
                    },
                    error: function(req) { // req = XMLHttpRequest object
                        // Newly installed modules will return '1'
                        if(req.responseText == 1) {
                            window.location.reload();
                            return;
                        }
                        alert("[ERROR "+req.status+"] Unable to save data:\n\n" + req.responseText);
                    }
                });
            }
            // Serialize modules to save module/region positions
            $.ajax({
                type: "GET",
                url: cms.config.url + cms.page.url + 'm,page_module,0/saveSort.html',
                data: 'ajax=1' + cms_serializeRegionModules(),
                success: function(data, textStatus, req) {
                    // Show edit controls if CMS in edit mode
                    cms.ui.editMode();
                },
                error: function(req) { // req = XMLHttpRequest object
                    //alert("[ERROR] Unable to load URL: \n" + req.responseText);
                }
            });
        }
    });
    $('#cms_admin_modules div.cms_module_tile').draggable({
        helper: 'clone',
        connectToSortable: cms_regions,
        start: function(e, ui) {
            $(this).hide();
            cms_regions.addClass('cms_region_highlight');
        },
        stop: function(e, ui) {
            $(this).show();
            cms_regions.removeClass('cms_region_highlight');
        }
    });
    

    /**
     * Module editing - display controls on click
     */
    $('#cms_admin_bar_editPage').toggle(function(e) {
        cms.ui.editMode(true);
    }, function(e) {
        cms.ui.editMode(false);
    });
    
    
    /**
     * Ensure datepicker is always available
     */
    $('form .app_form_field_datetime input').live('click', function(e) {
        $(this).datepicker({showOn:'focus'}).focus();
    });

    
    /**
     * Ensure module zIndex is in reverse normal order so option menus don't get hidden behind modules below
     */
    var moduleZindex = 990;
    $('.cms_module .cms_ui_controls').each(function() {
        $(this).css('zIndex', moduleZindex--);
    });
    
    
    /**
     * Serialize module order in regions into URL for Cont-xt to handle saving
     */
    function cms_serializeRegionModules() {
        var str = "";
        $('.cms_region, .cms_region_global').each(function() {
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





/**
 * jQuery 'makeSlug' plugin
 * @link http://forrst.com/posts/jQuery_plugin_to_automatically_create_slug_from-g2T
 */
(function ($) {
    // DONT FORGET TO NAME YOUR PLUGIN
    jQuery.fn.makeSlug = function (options, i) {
        if (this.length > 1) {
            var a = new Array();
            this.each(
                function (i) {
                    a.push($(this).makeSlug(options, i));
                });
            return a;
        }
        var opts = $.extend({}, $().makeSlug.defaults, options);
        
        /* PUBLIC FUNCTIONS */
        
        this.destroy = function (reInit) {
            var container = this;
            var reInit = (reInit != undefined) ? reInit : false;
            $(container).removeData('makeSlug'); // this removes the flag so we can re-initialize
        };
        
        this.update = function (options) {
            opts = null;
            opts = $.extend({}, $().makeSlug.defaults, options);
            this.destroy(true);
            return this.init();
        };
        
        this.init = function (iteration) {
            if ($(container).data('makeSlug') == true)
                return this; // this stops double initialization
            
            // call a function before you do anything
            if (opts.beforeCreateFunction != null && $.isFunction(opts.beforeCreateFunction))
                opts.beforeCreateFunction(targetSection, opts);
                
            var container = this; // reference to the object you're manipulating. To jquery it, use $(container). 
            
            $(container).keyup(function(){
                if(opts.slug !== null) opts.slug.val(makeSlug($(this).val()));
            });
            
            $(container).data('makeSlug', true);
            
            // call a function after you've initialized your plugin
            if (opts.afterCreateFunction != null && $.isFunction(opts.afterCreateFunction))
                opts.afterCreateFunction(targetSection, opts);
            return this;
        };
        
        /* PRIVATE FUNCTIONS */
        
        function makeSlug(str) { 
            str = str.replace(/^\s+|\s+$/g, ''); // trim
            str = str.toLowerCase();
            
            // remove accents, swap ñ for n, etc
            var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
            var to   = "aaaaeeeeiiiioooouuuunc------";
            for (var i=0, l=from.length ; i<l ; i++) {
                str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
            }
            
            str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
            .replace(/\s+/g, '-') // collapse whitespace and replace by -
            .replace(/-+/g, '-'); // collapse dashes
            
            return str;
        };
        
        // Finally
        return this.init(i);
    };

    // DONT FORGET TO NAME YOUR DEFAULTS WITH THE SAME NAME
    jQuery.fn.makeSlug.defaults = {
        slug: null,
        beforeCreateFunction: null, // Remember: these are function _references_ not function calls
        afterCreateFunction: null
    };
})(jQuery);




/* ============================================================
 * bootstrap-dropdown.js v1.4.0
 * http://twitter.github.com/bootstrap/javascript.html#dropdown
 * ============================================================
 * Copyright 2011 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */


!function( $ ){

  "use strict"

  /* DROPDOWN PLUGIN DEFINITION
   * ========================== */

  $.fn.dropdown = function ( selector ) {
    return this.each(function () {
      $(this).delegate(selector || d, 'click', function (e) {
        var li = $(this).parent('li')
          , isActive = li.hasClass('open')

        clearMenus()
        !isActive && li.toggleClass('open')
        return false
      })
    })
  }

  /* APPLY TO STANDARD DROPDOWN ELEMENTS
   * =================================== */

  var d = 'a.menu, .dropdown-toggle'

  function clearMenus() {
    $(d).parent('li').removeClass('open')
  }

  $(function () {
    $('html').bind("click", clearMenus)
    $('body').dropdown( '[data-dropdown] a.menu, [data-dropdown] .dropdown-toggle' )
  })

}( window.jQuery || window.ender );


/* ========================================================
 * bootstrap-tabs.js v1.4.0
 * http://twitter.github.com/bootstrap/javascript.html#tabs
 * ========================================================
 * Copyright 2011 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================== */


!function( $ ){

  "use strict"

  function activate ( element, container ) {
    container
      .find('> .active')
      .removeClass('active')
      .find('> .dropdown-menu > .active')
      .removeClass('active')

    element.addClass('active')

    if ( element.parent('.dropdown-menu') ) {
      element.closest('li.dropdown').addClass('active')
    }
  }

  function tab( e ) {
    var $this = $(this)
      , $ul = $this.closest('ul:not(.dropdown-menu)')
      , href = $this.attr('href')
      , previous
      , $href

    if ( /^#\w+/.test(href) ) {
      e.preventDefault()

      if ( $this.parent('li').hasClass('active') ) {
        return
      }

      previous = $ul.find('.active a').last()[0]
      $href = $(href)

      activate($this.parent('li'), $ul)
      activate($href, $href.parent())

      $this.trigger({
        type: 'change'
      , relatedTarget: previous
      })
    }
  }


 /* TABS/PILLS PLUGIN DEFINITION
  * ============================ */

  $.fn.tabs = $.fn.pills = function ( selector ) {
    return this.each(function () {
      $(this).delegate(selector || '.tabs li > a, .pills > li > a', 'click', tab)
    })
  }

  $(document).ready(function () {
    $('body').tabs('ul[data-tabs] li > a, ul[data-pills] > li > a')
  })

}( window.jQuery || window.ender );