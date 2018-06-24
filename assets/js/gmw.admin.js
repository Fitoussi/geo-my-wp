jQuery( document ).ready( function( jQuery ) {
	
	var GMW_Admin = {

	    init : function () {

	        // apply chosen to all select elements in GEO my WP admin pages
	        if ( jQuery().chosen ) {
	            jQuery( '.gmw-admin-page select:not( .gmw-smartbox-not )' ).chosen( {
	                width : "100%"
	            });
	        }

	        // initiate tabs
	        GMW_Admin.tabs_switcher_init();

	        GMW_Admin.multiple_address_fields_selector();

	        // do only on form editor page
	        if ( jQuery( '#gmw-edit-form-page, .geo-my-wp_page_gmw-settings' ).length ) {

	            // initiate form editor functions
	            GMW_Admin.form_editor_init();

	            GMW_Admin.limit_description();
	        }
	    },

	    /**
	     * Tabs switcher
	     * 
	     * @return {[type]} [description]
	     */
	    tabs_switcher_init : function() {

	        jQuery( '.gmw-nav-tab' ).click( function(e) {
	            
	            e.preventDefault();

	            // hide all panels
	            jQuery( '.gmw-tab-panel' ).hide();

	            // remove active class
	            jQuery( '.gmw-nav-tab' ).removeClass('active');

	            // set new tab as active
	            jQuery( this ).addClass( 'active' );

	            // get tab name
	            clickedTab = jQuery( this ).data( 'name' );

	            // show panel
	            jQuery( '.gmw-tab-panel.'+ clickedTab ).show();

	            // set tab in cookie
	            GMW.set_cookie( 'gmw_admin_tab', clickedTab, 1 );        
	        } );

	        // get last tab saved in cookie
	        lastTab = GMW.get_cookie( 'gmw_admin_tab' );

	        // if tabs exist in cookie make it active
	        if ( lastTab != 'undefined' && jQuery( '#' + lastTab ).length ) {

	            jQuery( '.gmw-tabs-wrapper' ).find( 'a[data-name="' + lastTab + '"]' ).click();

	        // otherwise, make the first tab active
	        } else {
	            jQuery( '.gmw-tabs-wrapper li a:first' ).click();
	        }
	    },

	    /**
	     * [multiple_address_fields_selector description]
	     * @return {[type]} [description]
	     */
	    multiple_address_fields_selector : function() {

	        // members locator address fields settings 
	        jQuery( '.gmw-admin-page [data="multiselect_address_fields"]' ).on( 'change', function( evt, params ) {

	            var multiSelect = jQuery( this );

	            if ( params.selected ) {

	                if ( params.selected == "address" || params.selected == "disabled" ) {
	                    
	                    multiSelect.children( 'option' ).each( function() {

	                        if ( jQuery( this ).val() != params.selected ) {
	                            
	                            if ( jQuery( this ).is( ':selected' ) ) {

	                                jQuery( this ).attr( 'selected', false );
	                            }
	                        }
	                    });
	                
	               } else  {
	                  
	                    multiSelect.children( 'option' ).each( function() {

	                        if ( jQuery( this ).val() == 'address' || jQuery( this ).val() == 'disabled'  ) {
	                            
	                            if ( jQuery( this ).is( ':selected' ) ) {

	                                jQuery( this ).attr( 'selected', false );
	                            }
	                        }
	                    });
	                }

	                multiSelect.trigger( 'chosen:updated' );
	            }
	        }); 

	    },

	    /**
	     * form editor functions
	     * 
	     * @return {[type]} [description]
	     */
	    form_editor_init : function() {

	        jQuery( '.form-editor-close' ).click( function() {
	            GMW.set_cookie( 'gmw_admin_tab', '', 1 );
	        });
	        
	        //options box tabs
	        jQuery( '.gmw-options-box ul.options-tabs li a' ).on( 'click', function( e ) {
	        
	            e.preventDefault();

	            thisTab = jQuery( this );
	            tabName = jQuery( this ).attr( 'class' ).replace( 'tab-anchor ', '' );

	            thisTab.closest( 'ul.options-tabs' ).find( 'li' ).removeClass( 'active' );
	            thisTab.closest( 'li' ).addClass( 'active' );

	            thisTab.closest( 'div.gmw-options-box' ).find( 'ul.options-tabs-content li.tab-content' ).hide();
	            thisTab.closest( 'div.gmw-options-box' ).find( 'ul.options-tabs-content li.'+tabName ).show();
	        });

	        function locator_button_setting( value ) {
	        
	            if ( value == 'disabled' ) {
	                jQuery( '.fields-group-locator_button' ).find( '.single-option:not( .option-locator )' ).slideUp( 'fast' );            
	            } else {
	                jQuery( '.fields-group-locator_button' ).find( '.single-option:not( .option-locator )' ).slideUp( 'fast' );  
	                jQuery( '.fields-group-locator_button' ).find( '.option-locator_submit, .option-locator_' + value ).slideDown( 'fast' );
	            }
	        }

	        locator_button_setting( jQuery( '#setting-search_form-locator' ).val() );

	        jQuery( '#setting-search_form-locator' ).change( function() {
	            locator_button_setting( jQuery( this ).val() );
	        } );

	        var selectedCount = jQuery( '#gmw-edit-form-page #setting-search_form-post_types option:selected' ).length;

	        if ( selectedCount == 1 ) {

	            var selected = jQuery( '#gmw-edit-form-page #setting-search_form-post_types option:selected' ).val();

	            jQuery( '#post-type-' + selected + '-taxonomies-wrapper' ).slideDown( 'fast' );
	        
	        } else if ( selectedCount > 1 ) {

	            jQuery( '.posts-types-settings-wrapper' ).slideDown( 'fase' );

	            // premium settings option
	            jQuery( '#post-types-no-taxonomies-message' ).slideDown( 'fast' );
	        }

	        // Post types/taxonmies switcher. for post types locator extension
	        jQuery( '#gmw-edit-form-page #setting-search_form-post_types' ).change( function() {
	                
	            var selected = jQuery( this ).find( 'option:selected' );

	            if ( selected.length == 0 ) {

	                jQuery( '.post-type-taxonomies-wrapper').slideUp( 'fast' );
	                jQuery( '#post-types-no-taxonomies-message' ).slideUp( 'fast' );
	                jQuery( '#post-types-select-taxonomies-message').slideDown( 'fast' );

	                // premium settings options
	                jQuery( '.posts-types-settings-wrapper' ).slideUp( 'fase' );

	            } else if ( selected.length == 1  ) {

	                selected = selected.val();

	                jQuery( '#post-types-no-taxonomies-message' ).slideUp( 'fast' );
	                jQuery( '#post-types-select-taxonomies-message').slideUp( 'fast' );
	                jQuery( '#post-type-' + selected + '-taxonomies-wrapper' ).slideDown( 'fast' ); 

	                 // premium settings options
	                jQuery( '.posts-types-settings-wrapper' ).slideUp( 'fase' );
	            
	            } else {

	                jQuery( '#post-types-select-taxonomies-message').slideUp( 'fast' );
	                jQuery( '.post-type-taxonomies-wrapper').slideUp();
	                jQuery( '#post-types-no-taxonomies-message' ).slideDown( 'fast' );

	                 // premium settings options
	                jQuery( '.posts-types-settings-wrapper' ).slideDown( 'fase' );
	            }                   
	        } );

	        // on form submission
	        jQuery( '.gmw-edit-form' ).on( 'submit', function( e ) {

	            // Proceed only if ajax enabled for form update
	            if ( jQuery( this ).data( 'ajax_enabled' ) ) {

	                // prevent form submission
	                e.preventDefault();

	                // update form
	                GMW_Admin.update_form( jQuery( this ) );
	            }
	        } );
	    },

	    /**
	     * Update form editor via ajax
	     * 
	     * @return {[type]} [description]
	     */
	    update_form : function( formElement ) {
	        
	        // disable submit buttons to prevent multiple submissions
	        jQuery( '.gmw-edit-form input[type="submit"]' ).prop( 'disabled', true );

	        //show form cover
	        jQuery( '.gmw-edit-form #gmw-form-cover' ).fadeToggle();
	        
	        // Update form via ajax
	        jQuery.ajax( {
	            type     : 'POST',
	            url      : gmwVars.ajaxUrl,
	            dataType : 'json',
	            data     : {
	                action      : 'gmw_update_admin_form',
	                form_values : formElement.serialize(),
	                security    : formElement.data( 'nonce' )
	            },
	            success : function( response ) {
	                
	                // if updated 
	                if ( response ) {
	                    
	                    // enable submit buttons
	                    jQuery( '.gmw-edit-form input[type="submit"]' ).prop( 'disabled', false );

	                    // hide form cover
	                    jQuery( '.gmw-edit-form #gmw-form-cover' ).fadeToggle();

	                    //show success message
	                    jQuery( '.gmw-edit-form #form-update-messages p.success' ).fadeToggle( function() {

	                        // wait a bit and hide message
	                        setTimeout(function() {

	                           jQuery( '.gmw-edit-form #form-update-messages p.success' ).fadeToggle();
	                        
	                        }, 5000 );  
	                    });

	                // if update faield for some reason
	                } else {

	                    this.update_failed();
	                }
	            }

	        //if inporter failed or aborted by user
	        }).fail( function ( jqXHR, textStatus, error ) {

	            if ( window.console && window.console.log ) {

	                console.log( textStatus + ': ' + error );

	                if ( jqXHR.responseText ) {
	                    console.log(jqXHR.responseText);
	                }
	            }

	            // enable submit buttons
	            jQuery( '.gmw-edit-form input[type="submit"]' ).prop( 'disabled', false );

	            // hide form cover
	            jQuery( '.gmw-edit-form #gmw-form-cover' ).fadeToggle();

	            //show failed message
	            jQuery( '.gmw-edit-form #form-update-messages p.failed' ).fadeToggle( function() {

	                // wait a bit and hide message
	                setTimeout(function() {

	                   jQuery( '.gmw-edit-form #form-update-messages p.failed' ).fadeToggle();
	                   
	                }, 5000 );  
	            });
	        });
	    },

	    /**
	     * Create smoe more link in feature description in admin
	     * 
	     * @return {[type]} [description]
	     */
	    limit_description : function() {
	        
	        jQuery( '.gmw-nav-tab' ).click( function() {

	            var tab = jQuery( this ).data( 'name' );

	            jQuery( '.gmw-tab-panel.' + tab + ' .gmw-form-feature-desc-content .description:not( .long )' ).each( function() {
	            
	                var height = jQuery( this ).outerHeight();
	                
	                if ( height > 80 ) {
	                    jQuery( this ).addClass( 'long' );
	                    jQuery( this ).after( '<span class="read-more"></span>' );
	                }
	            });
	        });

	        jQuery( '.gmw-form-feature-desc-content .description' ).each( function() {
	            
	            var height = jQuery( this ).outerHeight();
	            
	            if ( height > 80 ) {
	                jQuery( this ).addClass( 'long' );
	                jQuery( this ).after( '<span class="read-more"></span>' );
	            }
	        });

	        jQuery( document ).on( 'click', '.gmw-form-feature-desc-content .read-more', function() {
	            jQuery( this ).toggleClass( 'open' ).closest( 'div' ).find( 'em' ).toggleClass( 'long' );
	        });
	    }
	};

   GMW_Admin.init(jQuery); 
});	
