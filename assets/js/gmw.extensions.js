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
    set_processing : function( status, reload ) {

        if ( status ) {

            jQuery( '#gmw-extensions-page .disabler-block' ).show();

            //jQuery( '#gmw-admin-page-loader' ).fadeTo( 'fast', '0.8' );
            jQuery( '#gmw-admin-page-loader' ).fadeIn( 'fast' );
            //jQuery( '#gmw-extensions-page .gmw-extension-wrapper.gmw-processing-action' ).find( '.disabler-block' ).show();

        } else {

        	// Reload page if needed. Usually when activating/deactivating a parent extension.
        	if ( reload ) {

        		setTimeout( function() {

		    		location.reload();

		    		return;

	            }, 1500 );

        	} else {

	            setTimeout( function() {

	                // enable everything back
	                jQuery( '#gmw-extensions-page .disabler-block' ).hide();

	                jQuery( '#gmw-admin-page-loader' ).fadeOut( 'fast', function() {

	                	jQuery( this ).find( 'span' ).html( '' );

	                	jQuery( '#gmw-extensions-page .gmw-extension-wrapper' ).removeClass( 'gmw-processing-action' );
	                });

	            }, 2500 );
	        }
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
        jQuery( '.extensions-wrapper' ).find( '.gmw-extension-wrapper.premium.inactive' ).sort().appendTo( jQuery( '.extensions-wrapper' ) );

        // info toggle
        jQuery( '.gmw-tooltip' ).hover( function() {

            jQuery( this ).addClass( 'active' );

        }, function() {

            jQuery( this ).removeClass( 'active' );
        });

        // menu tabs
        GMW_Extensions.tabs_init();

        jQuery( '.gmw-license-wrapper' ).each( function() {

        	if ( ! jQuery( this ).find( '.gmw-license-key' ).length ) {
        		jQuery( this ).closest( '.gmw-extension-wrapper' ).addClass( 'disabled' );
        	}
        });

        // Extensions updater
        jQuery( document ).on( 'click', '.extensions-updater-button', function(e) {

            e.preventDefault();

            GMW_Extensions.set_processing( true, false );

            GMW_Extensions.extensions_updater( jQuery( this ) );
        } );

        jQuery( document ).on( 'click', '.gmw-extension-action-button:not( .get-extension )', function(e) {

        	e.preventDefault();

        	if ( jQuery( this ).closest( '.gmw-extension-wrapper' ).hasClass( 'disabled' ) ) {
        		return false;
        	}

            jQuery( this ).closest( '.gmw-extension-wrapper' ).addClass( 'gmw-processing-action' );

            GMW_Extensions.set_processing( true, false );

            GMW_Extensions.extension_activation( jQuery( this ), false );
        } );

        jQuery( document ).on( 'click', '.gmw-license-action-button', function( e ) {

            e.preventDefault();

            jQuery( this ).closest( '.gmw-extension-wrapper' ).addClass( 'gmw-processing-action' );

            GMW_Extensions.set_processing( true, false );

            GMW_Extensions.license_key_actions( jQuery( this ) );
        });

        jQuery(document).on('keyup change', '.gmw-extension-bottom .gmw-license-key', function (e) {

            var thisToggle = jQuery(this).closest('.field-wrapper').find('.gmw-atb-toggle');

            if (jQuery(this).val().length === 0) {
                thisToggle.fadeOut('fast');
            } else {
                thisToggle.fadeIn('fast');
            }
        });

        jQuery('.gmw-extension-bottom').find('.gmw-license-key').trigger('change');
    },

    /**
     * Extensions menu page tabs
     *
     * @return {[type]} [description]
     */
    tabs_init : function() {

    	jQuery( '#gmw-extensions-tabs-wrapper span' ).on( 'click', function() {

    		var tab = jQuery( this ).data( 'type' );

    		jQuery( '.gmw-extensions-wrapper, #gmw-extensions-tabs-wrapper span' ).removeClass( 'active' );

    		if ( tab == 'all' ) {

    			jQuery( '.gmw-extensions-wrapper' ).addClass( 'active' );
    		} else {
    			jQuery( '.gmw-extensions-wrapper.' + tab ).addClass( 'active' );
    		}

        	if ( history.pushState ) {

				var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?page=gmw-extensions&tab=' + tab;

				window.history.pushState( { path : newurl }, '', newurl );
			}

    		jQuery( this ).addClass( 'active' );
    	});

        jQuery( '#gmw-extensions-status-filter' ).change( function (e) {

            e.preventDefault();

            var extensions = jQuery( '#gmw-extensions-filter' ).val();
            var status     = jQuery( '#gmw-extensions-status-filter' ).val();
            var thisClass  = '';

            jQuery( '.gmw-admin-page-content-inner .gmw-extension-wrapper' ).hide();

            if ( '' == status ) {


            	jQuery( '.gmw-admin-page-content-inner .gmw-extension-wrapper' ).show();
            	/*thisClass = '.' + thisVal;

            	if ( jQuery( '#gmw-active-extensions-filter' ).is( ':checked' ) ) {
            		thisClass += '.active';
            	}

            	jQuery( '.gmw-admin-page-content-inner .gmw-extension-wrapper' + thisClass ).show();*/

            } else {

            	jQuery( '.gmw-admin-page-content-inner .gmw-extension-wrapper.' + status ).show();
            }
        });
            //jQuery( '.gmw-admin-page-content-inner .gmw-extension-wrapper' ).hide();


            /*if ( jQuery( this ).hasClass( 'filter-tab' ) ) {

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
            }*/
        //});
    },

    /**
     * Activate / deactivate license key
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
                actionButton.html( 'Action failed' ).removeClass( 'updating-message button-primary' ).addClass( 'gmw-icon-cancel button-secondary' ).fadeIn( 'fast' );
            });

            setTimeout( function() {

                // return button to its original state
                actionButton.fadeOut( 'fast', function() {
                    actionButton.html( orgHtml ).attr( 'class', orgClass ).removeClass( 'updating-message' ).fadeIn( 'fast' );
                });

            }, 1500 );

            // disable processing
            GMW_Extensions.set_processing( false, false );

        }).done( function ( response ) {

            // disable processing
            GMW_Extensions.set_processing( false, false );
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
        actionLink.addClass( 'updating-message' ).find( '.gmw-atb-label' ).html( actionLink.data( 'updating_message' ) );

        jQuery( '#gmw-admin-page-loader span' ).html( actionLink.data( 'updating_message' ) + ' extension...' );

        // get wrapper element
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
                actionLink.toggleClass( 'updating-message updated-message' ).find( '.gmw-atb-label' ).html( actionLink.data( 'updated_message' ) );

                jQuery( '#gmw-admin-page-loader span' ).html( 'Extension ' + actionLink.data( 'updated_message' ) );

                setTimeout( function() {

                    // hide current link
                   // actionLink.fadeOut( 'fast', function() {

                        // update extension status
                        actionLink.closest( '.gmw-extension-wrapper.active, .gmw-extension-wrapper.inactive' ).toggleClass( 'active inactive' ).removeClass( 'disabled' );

                        // replace old with new link
                        //actionLink.replaceWith( newLink );

                        actionLink.fadeOut( 'fast', function() {
                        	jQuery( this ).replaceWith( newLink );
                        });
                        // show new link
                        //jQuery( newLink ).fadeIn( 'fast' );

                        // loop through all extensions and look if any
                        // is disabled/enabled based on this extension
                        jQuery.each( response.dependends, function( slug, message ) {

                            // get list of required
                            //required = jQuery( this ).data( 'required' );

                            //if ( required != '' && required.indexOf( licenseData.slug ) > -1 ) {

                                // if extension was activated we can remove the
                                // disable activation from all dependents.
                                if ( licenseData.action == 'activate_extension' ) {

                                    jQuery( '.gmw-extension-wrapper[data-slug="' + slug + '"]' ).removeClass( 'disabled' );

                                // otherwise, we disable dependends
                                } else {

                                    jQuery( '.gmw-extension-wrapper[data-slug="' + slug+ '"]' ).removeClass( 'active' ).addClass( 'inactive disabled' ).find( '.activation-disabled-message p span' ).html( message );
                                }
                            //}
                        });
                   // } );

                }, 1500 );
            }

        }).fail( function ( jqXHR, textStatus, error ) {

            if ( window.console && window.console.log ) {

                console.log( textStatus + ': ' + error );

                if ( jqXHR.responseText ) {
                    console.log( jqXHR.responseText );
                }
            }

            actionLink.removeClass( 'updating-message' ).addClass( 'updated-message failed-message' ).find( '.gmw-atb-label' ).html( actionLink.data( 'failed_message' ) );

            jQuery( '#gmw-admin-page-loader span' ).html( actionLink.data( 'failed_message' ) );

            setTimeout( function() {

            	actionLink.removeClass( 'updated-message' ).removeClass( 'failed-message' ).find( '.gmw-atb-label' ).html( actionLink.data( 'label' ) );

            }, 2000 );

            // disable processing
            GMW_Extensions.set_processing( false, false );

        } ).done( function ( response ) {

        	//var reload = jQuery( newLink ).data( 'is_parent' ) == 1 ? true : false;

        	var reload = false;

            // disable processing
            GMW_Extensions.set_processing( false, reload );
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

        // if license key blank we show "Removing" message
        if ( ! license_data.license_key ) {

            actionMessageWrap.addClass( 'updating-message' ).html( 'Removing license key' ).show().css( 'display', 'inline-flex' );

             jQuery( '#gmw-admin-page-loader span' ).html( 'Removing license key...' );

        } else {

        	actionButton.addClass( 'updating-message' ).find( '.gmw-atb-label' ).html( actionButton.data( 'updating_message' ) );

        	jQuery( '#gmw-admin-page-loader span' ).html( actionButton.data( 'updating_message' ) + '...' );
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

                // new license element
                newLicenseWrap = response.form;

                // license data
                licenseData = response.license_data;

                // show updated message
                if ( license_data.action == 'activate_license' ) {

                    // if license key input is not blank
                    if ( licenseData.remote_connection != 'blank_key' ) {

                        if ( licenseData.license == 'valid' ) {

                            actionButton.toggleClass( 'updating-message updated-message' ).find( '.gmw-atb-label' ).html( actionButton.data( 'updated_message' ) );

                            jQuery( '#gmw-admin-page-loader span' ).html( actionButton.data( 'updated_message' ) );
                        //actionMessageWrap.removeClass( 'updating-message' ).addClass( 'updated-message' ).html( 'License activated' ).show();

                        } else {

                        	actionButton.toggleClass( 'updating-message updated-message failed-message' ).find( '.gmw-atb-label' ).html( actionButton.data( 'failed_message' ) );

							jQuery( '#gmw-admin-page-loader span' ).html( actionButton.data( 'failed_message' ) );
                        }
                    }

                } else {

                    actionButton.toggleClass( 'updating-message updated-message' ).find( '.gmw-atb-label' ).html( actionButton.data( 'updated_message' ) );

                    jQuery( '#gmw-admin-page-loader span' ).html( actionButton.data( 'updated_message' ) );
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

                    if ( response.license_data.license == 'valid' || ( response.license_data.license == 'invalid' &&  response.license_data.error == 'expired' ) ) {

                    	newLicenseWrap.closest( '.gmw-extension-wrapper' ).removeClass( 'disabled' );

                    } else {

                    	if ( response.license_data.license == 'deactivated' || response.license_data.license == 'invalid' ) {
                    		newLicenseWrap.closest( '.gmw-extension-wrapper' ).addClass( 'disabled' ).find( '.activation-disabled-message' ).remove();
                    	}
                    }

                }, 2000 );
            }

        }).fail( function ( jqXHR, textStatus, error ) {

            if ( window.console && window.console.log ) {

                console.log( textStatus + ': ' + error );

                if ( jqXHR.responseText ) {
                    console.log( jqXHR.responseText );
                }
            }

            actionButton.removeClass( 'updating-message' ).addClass( 'updated-message failed-message' ).find( '.gmw-atb-label' ).html( actionButton.data( 'failed_message' ) );

            jQuery( '#gmw-admin-page-loader span' ).html( actionButton.data( 'failed_message' ) );

            setTimeout( function() {

            	actionButton.removeClass( 'updated-message' ).removeClass( 'failed-message' ).find( '.gmw-atb-label' ).html( actionButton.data( 'label' ) );

            }, 2000 );

            // disable processing
            GMW_Extensions.set_processing( false, false );

        }).done( function ( response ) {

            // disable processing
            GMW_Extensions.set_processing( false, false );
        });
    },

    /**
     * Activate / deactivate extension
     *
     * @param  {[type]} actionLink [description]
     * @return {[type]}            [description]
     */
    /*extension_activation : function( actionLink ) {

        // show updating message
        actionLink.addClass( 'updating-message' ).find( 'span.gmw-activation-button-label' ).html( actionLink.data( 'updating_message' ) );

        // get wrapper element
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
                actionLink.toggleClass( 'updating-message updated-message' ).find( 'span.gmw-activation-button-label' ).html( actionLink.data( 'updated_message' ) );

                setTimeout( function() {

                    // hide current link
                   // actionLink.fadeOut( 'fast', function() {

                        // update extension status
                        actionLink.closest( '.gmw-extension-wrapper.active, .gmw-extension-wrapper.inactive' ).toggleClass( 'active inactive' ).removeClass( 'disabled' );

                        // replace old with new link
                        actionLink.replaceWith( newLink );

                        // show new link
                        //jQuery( newLink ).fadeIn( 'fast' );

                        // loop through all extensions and look if any
                        // is disabled/enabled based on this extension
                        jQuery.each( response.dependends, function( slug, message ) {

                            // get list of required
                            //required = jQuery( this ).data( 'required' );

                            //if ( required != '' && required.indexOf( licenseData.slug ) > -1 ) {

                                // if extension was activated we can remove the
                                // disable activation from all dependents.
                                if ( licenseData.action == 'activate_extension' ) {

                                    jQuery( '.gmw-extension-wrapper[data-slug="' + slug + '"]' ).removeClass( 'disabled' );

                                // otherwise, we disable dependends
                                } else {

                                    jQuery( '.gmw-extension-wrapper[data-slug="' + slug+ '"]' ).removeClass( 'active' ).addClass( 'inactive disabled' ).find( '.activation-disabled-message p span' ).html( message );
                                }
                            //}
                        });
                   // } );

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
            GMW_Extensions.set_processing( false, false );

        } ).done( function ( response ) {

        	//var reload = jQuery( newLink ).data( 'is_parent' ) == 1 ? true : false;

        	var reload = false;

            // disable processing
            GMW_Extensions.set_processing( false, reload );
        });
    },*/

    /**
     * Activate / deavtivate license key
     *
     * @param  {DOMelement} actionButton the button clicked
     *
     * @return {[type]}              [description]
     */
    /*license_key_actions : function( actionButton ) {

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

            actionMessageWrap.addClass( 'updating-message button-primary' ).html( 'Removing license key' ).show().css( 'display', 'inline-flex' );

        // show new updating button message
        } else if ( license_data.action == 'activate_license' ) {

            actionMessageWrap.addClass( 'updating-message button-primary' ).html( 'Activating license' ).show().css( 'display', 'inline-flex' );

        } else {

            actionMessageWrap.addClass( 'updating-message button-secondary' ).html( 'Dectivating license' ).show().css( 'display', 'inline-flex' );
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

                            actionMessageWrap.removeClass( 'updating-message' ).addClass( 'updated-message' ).html( 'License activated' ).show();

                        } else {

                            actionMessageWrap.removeClass( 'updating-message button-primary' ).addClass( 'gmw-icon-cancel button-secondary' ).html( 'License activation failed' ).show();
                        }
                    }

                } else {

                    actionMessageWrap.removeClass( 'updating-message' ).addClass( 'updated-message' ).html( 'License dectivated' ).show();
                }

                setTimeout( function() {

                	console.log(response)
                    // hide current license form
                    licenseWrap.hide();

                    // get new license form
                    newLicenseWrap = jQuery( newLicenseWrap ).hide();

                    // replace current with new license form
                    licenseWrap.replaceWith( newLicenseWrap );

                    // show new form
                    newLicenseWrap.show();

                    if ( response.license_data.license == 'valid' || ( response.license_data.license == 'invalid' &&  response.license_data.error == 'expired' ) ) {

                    	newLicenseWrap.closest( '.gmw-extension-wrapper' ).removeClass( 'disabled' );

                    } else {

                    	if ( response.license_data.license == 'deactivated' || response.license_data.license == 'invalid' ) {
                    		newLicenseWrap.closest( '.gmw-extension-wrapper' ).addClass( 'disabled' ).find( '.activation-disabled-message' ).remove();
                    	}
                    }

                }, 2000 );
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
                actionMessageWrap.html( 'Action failed' ).removeClass( 'updating-message button-primary' ).addClass( 'gmw-icon-cancel button-secondary' ).fadeIn();
            });

            setTimeout( function() {

                actionMessageWrap.fadeOut( 'slow', function() {

                    actionButton.fadeIn();
                });

            }, 1500 );

            // disable processing
            GMW_Extensions.set_processing( false, false );

        }).done( function ( response ) {

            // disable processing
            GMW_Extensions.set_processing( false, false );
        });
    },*/
};

jQuery( document ).ready( function() {
   GMW_Extensions.init();
});
