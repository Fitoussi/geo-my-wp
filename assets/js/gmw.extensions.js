var GMW_Extensions = {

    /**
     * Processing status
     * 
     * @type {Boolean}
     */
    processing : false,

    /**
     * Set processing status
     *
     * This will disable all links and buttons on the page to prevent multiple 
     *
     * AJAX actions at th same time.
     * 
     * @param {[type]} status [description]
     */
    set_processing : function( status ) {

        if ( status ) {

            jQuery( '#gmw-extensions-page .disabler-block' ).show();

        } else {

            setTimeout( function() {

                // enable everything back
                jQuery( '#gmw-extensions-page .disabler-block' ).hide();

            }, 2500 );
        }

        GMW_Extensions.processing = status;
    },

    /**
     * Run on page load
     * 
     * @return {[type]} [description]
     */
    init : function() {

        // sort premium extensions by active status
        jQuery( '.extensions-wrapper' ).find( '.gmw-extension-wrapper.premium.inactive' ).sort().appendTo( jQuery('.extensions-wrapper') );
        
        // info toggle
        jQuery( '.extensions-updater .info-toggle, .extensions-cache .info-toggle' ).hover( function() { 

            jQuery( this ).closest( 'div' ).find( '.info-wrapper' ).fadeIn( 'fast' );
        
        }, function() {
        
            jQuery( this ).closest( 'div' ).find( '.info-wrapper' ).fadeOut( 'fast' );
        });

        // menu tabs
        GMW_Extensions.tabs_init();

        // Extensions updater
        jQuery( document ).on( 'click', '.extensions-updater-button', function(e) {
            
            e.preventDefault();

            GMW_Extensions.set_processing( true );

            GMW_Extensions.extensions_updater( jQuery( this ) );
        } );

        jQuery( document ).on( 'click', '.gmw-extension-action-button', function(e) {
            
            e.preventDefault();

            GMW_Extensions.set_processing( true );

            GMW_Extensions.extension_activation( jQuery( this ) );
        } );

        jQuery( document ).on( 'click', '.gmw-license-action-button', function( e ) {

            e.preventDefault();

            GMW_Extensions.set_processing( true );

            GMW_Extensions.license_key_actions( jQuery( this ) );
        }); 
    },

    /**
     * Extensions menu page tabs
     * 
     * @return {[type]} [description]
     */
    tabs_init : function() {

        jQuery( '#gmw-extensions-filter ul li a' ).click( function (e) {

            e.preventDefault();

            if ( jQuery( this ).hasClass( 'filter-tab' ) ) {

                if ( jQuery( this ).data( 'filter' ) == '' ) {
                    
                    jQuery( '.extensions-title' ).fadeIn( 'fast' );
                
                } else {
                
                    jQuery( '.extensions-title' ).fadeOut( 'fast' );
                }
            }

            if ( jQuery( this ).hasClass( 'filter-tab' ) ) {

                type = filters = jQuery( this ).data( 'filter' );

                if ( jQuery( '#gmw-extensions-filter ul li a.active-tab' ).hasClass( 'current' ) ) {
                    filters = filters + '.active';
                }

                jQuery( '.filter-tab' ).removeClass( 'current' );
                jQuery( this ).addClass( 'current' );
               
                jQuery( '.extensions-wrapper .gmw-extension-wrapper' ).fadeOut( 'fast' );
                
                jQuery( '.extensions-wrapper .gmw-extension-wrapper' + filters ).fadeIn( 'fast' );
            
            } else {

                jQuery( this ).toggleClass( 'current' );

                type = filters = jQuery( '#gmw-extensions-filter ul li a.filter-tab.current' ).data( 'filter' ); 
                
                if ( jQuery( this ).hasClass( 'current' ) ) {
                    filters = filters + '.active';
                }

                jQuery( '.extensions-wrapper .gmw-extension-wrapper' ).fadeOut( 'fast' );
                
                jQuery( '.extensions-wrapper .gmw-extension-wrapper' + filters ).fadeIn( 'fast' );
            }
        });
    },

    /**
     * Activate / deavtivate license key
     * 
     * @param  {DOMelement} actionButton the button clicked
     * 
     * @return {[type]}              [description]
     */
    extensions_updater : function( actionButton ) {
             
        // show new updating button message
        actionButton.addClass( 'updating-message' );         
       
        // do ajax
        jQuery.ajax( {
            type     : 'POST',
            dataType : 'json',  
            url      : gmwVars.ajaxUrl,
            data     : { 
                action         : 'gmw_extensions_updater',
                updater_action : actionButton.attr( 'data-action' ),
                security       : actionButton.attr( 'data-nonce' )
            },
            success : function( response ) {             
                
                // show updated message
                actionButton.toggleClass( 'updating-message updated-message' ); 

                setTimeout( function() {

                    actionButton.removeClass( 'updated-message' ).fadeOut( 'fast', function() { 

                        // done enabling
                        if ( response == 'updater_enabled' ) {
                            
                            // update button status to deactivate status
                            actionButton.toggleClass( 'button-primary button-secondary' ).attr( 'data-action', 'disable' ).html( 'Disable Updater' ).fadeIn( 'fast' );
                            
                            // update class of wrapping element
                            actionButton.closest( '.extensions-updater' ).toggleClass( 'disabled enabled' );
                        
                        // done disabling
                        } else {

                            // update button to Activate status
                            actionButton.toggleClass( 'button-secondary button-primary' ).attr( 'data-action', 'enable' ).html( 'Enable Updater' ).fadeIn( 'fast' );
                            
                            // update class of wrapping element
                            actionButton.closest( '.extensions-updater' ).toggleClass( 'enabled disabled' );
                        }
                    });

                }, 1500 );                  
            }

        }).fail( function ( jqXHR, textStatus, error ) {

            if ( window.console && window.console.log ) {

                console.log( textStatus + ': ' + error );

                if ( jqXHR.responseText ) {
                    console.log( jqXHR.responseText );
                }
            }

            orgHtml  = actionButton.html();
            orgClass = actionButton.attr( 'class' );

            // show failed message
            actionButton.fadeOut( 'fast', function() {
                actionButton.html( 'Failed' ).removeClass( 'updating-message button-primary' ).addClass( 'gmw-icon-cancel button-secondary' ).fadeIn( 'fast' );
            });

            setTimeout( function() {
                
                // return button to its original state
                actionButton.fadeOut( 'fast', function() {
                    actionButton.html( orgHtml ).attr( 'class', orgClass ).removeClass( 'updating-message' ).fadeIn( 'fast' );
                });

            }, 1500 ); 

            // disable processing
            GMW_Extensions.set_processing( false );

        }).done( function ( response ) {

            // disable processing
            GMW_Extensions.set_processing( false );
        } );
    }, 

    /**
     * Activate / deactivate extension
     * 
     * @param  {[type]} actionLink [description]
     * @return {[type]}            [description]
     */
    extension_activation : function( actionLink ) {

        // show updating message
        actionLink.html( actionLink.data( 'updating_message' ) ).addClass( 'updating-message' );

        // get wrapper elment
        actionLinkWrap = actionLink.closest( '.gmw-extension-wrapper' );

        // get data
        licenseData = actionLink.data();

        // do ajax
        jQuery.ajax( {
            type     : 'GET',
            dataType : 'json',  
            url      : gmwVars.ajaxUrl,
            data     : { 
                action     : 'gmw_' + licenseData.action,
                gmw_action : licenseData.action,
                slug       : licenseData.slug,
                basename   : licenseData.basename,
                security   : licenseData.nonce
            },

            // saving success
            success : function( response ) {             

                // new activate/deactivate link
                newLink = response.newLink;

                // show success message
                actionLink.html( actionLink.data( 'updated_message' ) ).toggleClass( 'updating-message updated-message' );
                
                setTimeout( function() {

                    // hide current link
                    actionLink.fadeOut( 'slow', function() {

                        // update extension status 
                        actionLink.closest( '.gmw-extension-wrapper.active, .gmw-extension-wrapper.inactive' ).toggleClass( 'active inactive' ).removeClass( 'disabled' );
                    
                        // replace old with new link
                        jQuery( this ).replaceWith( newLink );

                        // show new link
                        jQuery( newLink ).fadeIn( 'slow' );

                        // loop through all extensions and look if any
                        // is disabled/enabled based on this extension
                        jQuery.each( response.dependends, function( slug, message ) {

                            // get list of required
                            //required = jQuery( this ).data( 'required' );
                            
                            //if ( required != '' && required.indexOf( licenseData.slug ) > -1 ) {

                                // if extension was activated we can remove the 
                                // disable activation from all dependents.
                                if ( licenseData.action == 'activate_extension' ) {

                                    jQuery( '.gmw-extension-wrapper[data-slug="'+slug+'"]' ).removeClass( 'disabled' );

                                // otherwise, we disable dependends
                                } else {

                                    jQuery( '.gmw-extension-wrapper[data-slug="'+slug+'"]' ).removeClass( 'active' ).addClass( 'inactive disabled' ).find( '.activation-disabled-message p span' ).html( message );
                                }
                            //}
                        }); 
                    } );

                }, 1500 );    
            }

        }).fail( function ( jqXHR, textStatus, error ) {

            if ( window.console && window.console.log ) {

                console.log( textStatus + ': ' + error );

                if ( jqXHR.responseText ) {
                    console.log( jqXHR.responseText );
                }
            }

            orgClass = actionLink.attr( 'class' );

            actionLink.fadeOut( 'slow', function() {

                // show failed message
                actionLink.html( 'Failed' ).toggleClass( 'updating-message gmw-icon-cancel disabled' ).fadeIn();
            });

            setTimeout( function() {
                
                actionLink.fadeOut( 'slow', function() {

                    // show success message
                    actionLink.html( actionLink.data( 'label' ) ).addClass( orgClass ).removeClass( 'gmw-icon-cancel updating-message disabled' ).fadeIn();

                    actionLinkWrap.find( '.activation-disabled-message p span' ).html( jqXHR.responseText );
                    
                    // disable onyl if needed
                    if ( jqXHR.status != 403 ) {
                        actionLinkWrap.addClass( 'disabled' );
                    }
                    
                });

            }, 1500 );

            // disable processing
            GMW_Extensions.set_processing( false );

        } ).done( function ( response ) {
            
            // disable processing
            GMW_Extensions.set_processing( false );
        });
    },

    /**
     * Activate / deavtivate license key
     * 
     * @param  {DOMelement} actionButton the button clicked
     * 
     * @return {[type]}              [description]
     */
    license_key_actions : function( actionButton ) {

        // license element
        licenseWrap = actionButton.closest( '.gmw-license-wrapper' );
        
        // message element
        actionMessageWrap = licenseWrap.find( '.actions-message' );
                
        // license data
        license_data = actionButton.data();
        
        // get key value
        license_data.license_key = licenseWrap.find( '.gmw-license-key' ).val();
        
        // hide action button
        actionButton.hide();

        // if license key blank we show "Removing" message
        if ( ! license_data.license_key ) {

            actionMessageWrap.addClass( 'updating-message button-primary' ).html( 'Removing key' ).show();  

        // show new updating button message
        } else if ( license_data.action == 'activate_license' ) {

            actionMessageWrap.addClass( 'updating-message button-primary' ).html( 'Activating' ).show();         
        
        } else {

            actionMessageWrap.addClass( 'updating-message button-secondary' ).html( 'Dectivating' ).show();
        }

        // do ajax
        jQuery.ajax( {
            type     : 'POST',
            dataType : 'json',  
            url      : gmwVars.ajaxUrl,
            data     : { 
                action   : 'gmw_license_key_actions',
                data     : license_data,
                security : license_data.nonce,
            },

            // activation/deactivation success
            success : function( response ){             
                
                console.log( response );

                // new license element
                newLicenseWrap = response.form;
                // license data
                licenseData = response.license_data;

                // show updated message
                if ( license_data.action == 'activate_license' ) {

                    // if license key input is not blank
                    if ( licenseData.remote_connection != 'blank_key' ) {
                        
                        if ( licenseData.license == 'valid' ) {
                            
                            actionMessageWrap.removeClass( 'updating-message' ).addClass( 'updated-message' ).html( 'Activated' ).show(); 
                        
                        } else {

                            actionMessageWrap.removeClass( 'updating-message button-primary' ).addClass( 'gmw-icon-cancel button-secondary' ).html( 'Failed' ).show(); 
                        }
                    }

                } else {

                    actionMessageWrap.removeClass( 'updating-message' ).addClass( 'updated-message' ).html( 'Dectivated' ).show();
                }

                setTimeout( function() {

                    // hide current license form
                    licenseWrap.hide();

                    // get new license form
                    newLicenseWrap = jQuery( newLicenseWrap ).hide();

                    // replace current with new license form
                    licenseWrap.replaceWith( newLicenseWrap );

                    // show new form
                    newLicenseWrap.show();

                }, 1500 );  
            }

        }).fail( function ( jqXHR, textStatus, error ) {

            if ( window.console && window.console.log ) {

                console.log( textStatus + ': ' + error );

                if ( jqXHR.responseText ) {
                    console.log( jqXHR.responseText );
                }
            }

            // hide updating message
            actionMessageWrap.fadeOut( 'slow', function() {

                // show failed message
                actionMessageWrap.html( 'Failed' ).removeClass( 'updating-message button-primary' ).addClass( 'gmw-icon-cancel button-secondary' ).fadeIn();
            });

            setTimeout( function() {
                
                actionMessageWrap.fadeOut( 'slow', function() {

                    actionButton.fadeIn();
                });
                
            }, 1500 );

            // disable processing
            GMW_Extensions.set_processing( false ); 

        }).done( function ( response ) {

            // disable processing
            GMW_Extensions.set_processing( false );
        });
    },
};

jQuery( document ).ready( function() {
   GMW_Extensions.init(); 
});
            
