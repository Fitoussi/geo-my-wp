var GMW_Admin = {

    init : function () {

        // apply chosen to all select elements in GEO my WP admin pages
        if ( jQuery().chosen ) {
            jQuery( '.gmw-admin-page select' ).chosen( {
                width : "100%"
            });
        }

        // initiate tabs
        GMW_Admin.tabs_switcher_init();

        // do only on form editor page
        if ( jQuery( '#gmw-edit-form-page' ).length ) {

            // initiate form editor functions
            GMW_Admin.form_editor_init();
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
     * form editor functions
     * 
     * @return {[type]} [description]
     */
    form_editor_init : function() {

        jQuery( '.form-editor-close' ).click( function() {
            GMW.set_cookie( 'gmw_admin_tab', '', 1 );
        });
        
        // Post types/taxonmies switcher. for post types locator extension
        jQuery( '#gmw-edit-form-page .post-types-tax' ).click( function() {
                
            var checked = jQuery( this ).closest( '.posts-checkboxes-wrapper' ).find( ':checkbox:checked' );

            if ( checked.length == 1  ) {

                checked = checked.attr('id');

                jQuery( '#post-types-no-taxonomies-message' ).slideUp();
                jQuery( '#post-type-' + checked + '-taxonomies-wrapper' ).slideDown( 'fast' ); 
            
            } else {

                jQuery( '.post-type-taxonomies-wrapper').slideUp();
                jQuery( '#post-types-no-taxonomies-message' ).slideDown( 'fast' );
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
            url      : gmwAjaxUrl,
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
    }
}
jQuery( document ).ready( function( jQuery ) {
   GMW_Admin.init(jQuery); 
});	