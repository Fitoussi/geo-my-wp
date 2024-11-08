jQuery( document ).ready( function( $ ) {

	var GMW_Admin = {

	    init : function () {

			// Hide page loader.
			setTimeout( function() {
				jQuery( '#gmw-admin-page-loader' ).fadeOut( 'slow' );
			}, 100 );

			//
			$( window ).bind( 'keydown', function( event ) {

			    if ( event.ctrlKey || event.metaKey ) {

			        switch ( String.fromCharCode( event.which ).toLowerCase() ) {
			        case 's':
			            event.preventDefault();
			            jQuery( '#gmw-form-editor-submit, .gmw-update-settings-button .gmw-settings-action-button' ).click();
			            break;
			        }
			    }
			});

			jQuery( '.gmw-premium-extension-option-disabled' ).closest( '.gmw-settings-multiple-fields-wrapper' ).find( 'input, select, checkbox, textarea' ).prop( 'disabled', true );
			jQuery( '.gmw-premium-extension-option-disabled' ).on( 'click', function() {

				var proData = {
					'feature' : 'premium_settings',
					'name'    : 'Premium Settings',
					'url'     : 'https://geomywp.com/extensions/premium-settings',
					'content' : 'Enhance GEO my WP forms with additional taxonomy options, custom map markers, AJAX info-windows, keywords search box, markers clusters, "no results" options, and more...',
				};

				GMW_Admin.premium_feature_modal( proData );
			});

	        // initiate tabs
	        GMW_Admin.tabs_switcher_init();

	       	jQuery( '#gmw-new-form-selector' ).on( 'change', function(e) {

	   			var selected = jQuery( this ).find( ':selected' );

	    		if ( selected.hasClass( 'gmw-premium-feature' ) ) {

	    			GMW_Admin.premium_feature_modal( selected.data() );

	    			jQuery( '#gmw-new-form-selector' ).val( '' );

	    			e.preventDefault();
	    		} else {

	    			window.location.href = jQuery( this ).val();
	    		}
	    	});

	        //jQuery( '.gmw-admin-page' ).closest( 'body' ).find( 'div.notice' ).detach().prependTo( '.gmw-admin-page-panels-wrapper' ).addClass( 'gmw-admin-notice' );
	        // Move admin notices to the content area of GEO my WP settings pages.
	       /* if ( jQuery( '.gmw-admin-page-panels-wrapper' ).find( '.gmw-admin-page-navigation.gmw-sub-nav' ).length ) {
	        	jQuery( adminNoticeClass ).detach().insertAfter( '.gmw-admin-page-panels-wrapper .gmw-admin-page-navigation.gmw-sub-nav' ).show();
	        } else {
	        	jQuery( adminNoticeClass ).detach().prependTo( '.gmw-admin-page-panels-wrapper' ).show();
	        }*/

			//var adminNoticeClass = 'div.error, div.update-nag, .wcvendors-message, .um-admin-notice, .notice.is-dismissible, .notice-error, .notice-info, #peepso_php_warning, .notice-warning, .gmw-admin-notice-top';
	        //jQuery( adminNoticeClass ).detach().prependTo( '#gmw-admin-notices-holder' ).show();

			var adminNoticeClass = 'div[class*="notice"], div[class*="error"], div.update-nag, .wcvendors-message, #peepso_php_warning';
			var adminNotices = jQuery('body.gmw-admin-page').find('h1').closest('div').children(adminNoticeClass);
			var adminNoticeClass2 = 'div.update-nag';
			var adminNotices2 = jQuery('body.gmw-admin-page').find(adminNoticeClass2);

	        //jQuery( adminNoticeClass ).detach().prependTo( '#gmw-admin-notices-holder' ).show();


			setTimeout(function () {
				adminNotices.not( '.notice-success' ).detach().prependTo('#gmw-admin-notices-holder').show();
				adminNotices2.not( '.notice-success' ).detach().prependTo('#gmw-admin-notices-holder').show();
			}, 500);

	        jQuery( '.notice-success' ).detach().insertBefore( '#gmw-admin-notices-holder' ).show();

        	//jQuery( '.gmw-admin-notice-box.admin-notice-top' ).detach().prependTo( '.gmw-admin-page-panels-wrapper' ).show().find( 'p' ).css( 'margin', '0' );
        	//jQuery( '.notice' ).detach().prependTo( '.gmw-admin-page-panels-wrapper' ).show().find( 'p' ).css( 'margin', '0' );

	        jQuery( '.gmw-admin-page select' ).css( { 'width': '100%' } );

	        // apply select2 to all select elements in GEO my WP admin pages
	        if ( jQuery().select2 ) {

	        	// This script make delete entire tag when using backspace instead of deleting each letter at a time, which is the original behaviour.
	        	// This function also initiate the select2.
	        	jQuery.fn.select2.amd.require(['select2/selection/search'], function (Search) {

				    var oldRemoveChoice = Search.prototype.searchRemoveChoice;

				    Search.prototype.searchRemoveChoice = function () {
				        oldRemoveChoice.apply(this, arguments);
				        this.$search.val('');
				    };
				});

	        	// Init select2.
				jQuery('.gmw-admin-page select:not( .gmw-smartbox-not )').each(function () {
	                jQuery( this ).select2({
	                	tags: true,
	                    dropdownParent    : jQuery( this ).parent(),
	                    containerCssClass : 'gmw-select2-container',
	                    allowClear: true,
	                    tags: false,
	                    //placeholder: ''
	                });
	            });

	      		// Initiate select2 sortable where needed, exept for select2 that are loaded via ajax. Those will be initiate next.
	      		jQuery( '.gmw-admin-page select[multiple][data-sortable="1"]' ).not( '[data-gmw_ajax_load_options]' ).each( function() {
					GMW_Admin.select2_sortable( jQuery( this ) );
				});

				// Initiate select2 sortable when options done loading via AJAX.
	        	jQuery( document ).on( 'gmw_ajax_options_loaded', function( event, args, data, element ) {

					setTimeout( function() {

						jQuery( '#select2-setting-search_form-form_template-results' ).find( 'li' ).each( function() {
			        		console.log(jQuery(this))
			        	});

			        }, 2000 );

					if ( args.gmw_ajax_load_options == 'gmw_get_location_meta' ) {

						setTimeout( function() {
							GMW_Admin.select2_sortable( element );
						}, 200 );
					}
				} );
	        }

	        if ( jQuery().wpColorPicker ) {
	        	jQuery( '.gmw-color-picker-field' ).wpColorPicker();
	        }

	        GMW_Admin.multiple_address_fields_selector();

	        if ( typeof jconfirm !== 'undefined' ) {

	        	jconfirm.defaults = {
					closeIcon          : true,
					draggable          : false,
					backgroundDismiss  : true,
					escapeKey          : true,
					animationBounce    : 1,
					useBootstrap       : false,
					theme              : 'modern',
					boxWidth           : '400px',
					type               : 'blue',
					typeAnimated       : false,
					animateFromElement : false,
				};

				jQuery( '.gmw-premium-feature' ).click( function() {
					GMW_Admin.premium_feature_modal( jQuery( this ).data() );
				});
	        }

	        // do only on form editor page
	        if ( jQuery( '#gmw-edit-form-page, .geo-my-wp_page_gmw-settings, .geo-my-wp_page_gmw-import-export' ).length ) {

	            // initiate form editor functions
	            GMW_Admin.form_editor_init();

	            //GMW_Admin.limit_description();
	        }

	        GMW_Admin.popup_element();

	        GMW_Admin.tooltips();
	    },

	    popup_element : function() {

	    	jQuery( document ).on( 'click', '.gmw-popup-element-toggle', function(e) {

	        	e.preventDefault();

	        	var element = jQuery( this ).attr( 'data-element' );

	        	jQuery( element ).fadeToggle( 'fast', function() {

	        		if ( jQuery( element ).find( '#gmw-lf-map' ).length && typeof GMW_Location_Form !== 'undefined' ) {

		        		//setTimeout( function() {
		        			GMW_Location_Form.resizeMap( GMW_Location_Form.map );
		        		//}, 200 );
		    		}

	        	} );
	        });

	    	// Hide form when clicking outside of it.
			$( '.gmw-popup-element-wrapper' ).click( function() {

				$( this ).fadeOut( 'fast' );

			}).children().click( function(e) {

				return false;
			});

			jQuery( '.gmw-popup-element-meta-fields-submit' ).click( function() {
				jQuery( this ).closest( 'form' ).submit();
			});

			// Hide form when clicking the close toggle.
			jQuery( '.gmw-popup-element-close-button' ).click( function(e) {

				e.preventDefault();

				jQuery( this ).closest( '.gmw-popup-element-wrapper' ).fadeOut( 'fast' );

			});

			// Hide form on Escape key press.
			document.addEventListener( 'keydown', function(event){
				if ( event.key === 'Escape' ){
					jQuery( '.gmw-popup-element-wrapper' ).fadeOut( 'fast' );
				}
			});
	    },

	    /**
	     * Form Editor tabs switcher.
	     *
	     * @return {[type]} [description]
	     */
	    tabs_switcher_init : function() {

	        jQuery( '.gmw-nav-tab' ).click( function(e) {

	            e.preventDefault();

	            var tab;

	            if ( jQuery( this ).is( ':visible' ) ) {
	            	tab = jQuery( this );
	            } else {
	            	tab = jQuery( '.gmw-admin-page-navigation' ).find( 'a:visible:first' );
	            }

	            if ( history.pushState ) {

					var newurl = window.location.href.split( '&current_tab' )[0] + '&current_tab=' + tab.data( 'name' );

					window.history.pushState( { path : newurl }, '', newurl );
				}

	            // remove active class
	            jQuery( '.gmw-nav-tab, .gmw-tab-panel' ).removeClass( 'active' );

	            // set new tab as active
	            tab.addClass( 'active' );

	            // get tab name
	            var clickedTab = tab.data( 'name' );

	            // Set panel to active.
	            jQuery( '.gmw-tab-panel.' + clickedTab ).addClass( 'active' );

	            // Scroll to the top of the panel.
	            jQuery('.gmw-admin-page-panels-wrapper').scrollTop(0);
	        } );

	        // if no active tab was found then make the first tab active.
	        if ( ! jQuery( '.gmw-edit-form-page-nav-tabs' ).find( '.gmw-nav-tab.active' ).length ) {
	        	jQuery( '.gmw-admin-page-navigation a:first' ).click();
	        }
	    },

	    /**
	     * Select2 sortable.
	     *
	     * @param  {[type]} element [description]
	     * @return {[type]}         [description]
	     */
	    select2_sortable : function( element ) {

			if (!jQuery().sortable) {
				return false;
			}

			var ulElem     = element.parent().find( "ul.select2-selection__rendered" );
			var savedOrder = element.data( 'options_order' );

			if ( typeof savedOrder === 'undefined' ) {

				savedOrder = '';
			    element.attr( 'data-options_order', '' );
			} else {
				savedOrder = savedOrder.toString();
			}

			savedOrder = ( savedOrder != 0 && savedOrder.indexOf( ',' ) != -1 ) ? savedOrder.split(',') : '';

			ulElem.sortable({
			    items  : 'li.select2-selection__choice',
			    cursor : 'move',
			    update : function() {
			        orderSortedValues( element, jQuery( this ) );
			    }
			});

			// Reorder the select dropdown object based on order of sortable items.
			orderSortedValues = function( element, ulElem ) {

			   ulElem.children( 'li[title]' ).each( function( i, obj ) {

			        var child = element.children( 'option' ).filter( function () {
			        	return jQuery(this).html() == obj.title;
			        });

			        moveElementToEndOfParent( child );
			    });
			};

			moveElementToEndOfParent = function( child ) {

			    var parent = child.parent();

			    child.detach();

			    parent.append( child );
			};

			if ( savedOrder != '' ) {

				for ( var i = 0, l = savedOrder.length; i < l; i++ ) {

					var title = element.find( 'option[value="' + savedOrder[i] + '"]' ).html();

					ulElem.append( ulElem.find( 'li[title="' + title + '"]' ) );
				}

				setTimeout( function() {
					orderSortedValues( element, ulElem );
				}, 500 );
			}

			element.on( 'select2:select', function ( event ) {

		        var id    = event.params.data.id;
		        var child = jQuery( this ).children( "option[value=" + id + "]" );

		        moveElementToEndOfParent( child );

		        jQuery( this ).trigger( 'change' );
		    });
	    },

	    /**
	     * Tooltip.
	     *
	     * Insipred by the Gravity Forms plugin. Thanks!!
	     *
	     * @return {[type]} [description]
	     */
	    tooltips : function() {

	    	var $tooltips = jQuery( '.gmw-tooltip' );

			if ( ! $tooltips.length ) {
				return;
			}

	        $tooltips.tooltip( {
				show: {
					effect   : 'fadeIn',
					duration : 200,
					delay    : 100,
				},
				position:     {
					my : 'center bottom',
					at : 'center-3 top-11',
				},
				tooltipClass : 'arrow-bottom',
				items        : '[aria-label]',
				content      : function () {

					var content = jQuery( this ).attr( 'aria-label' );

					if ( content == '[placeholder]' && jQuery( this ).hasClass( 'gmw-settings-desc-tooltip' ) ) {
						content = jQuery( this ).closest( 'fieldset' ).find( '.gmw-settings-panel-content .gmw-settings-panel-description:first-child' ).text();
					}

					return GMW_Admin.strip_content( content );
				},
				open : function ( event, ui ) {
					if ( typeof ( event.originalEvent ) === 'undefined' ) {
						return false;
					}

					// set the tooltip offset on reveal based on tip width and offset of trigger to handle dynamic changes in overflow
					setTimeout( function() {
						var leftOffset = ( this.getBoundingClientRect().left - ( ( ui.tooltip[0].offsetWidth / 2 ) - 5 ) ).toFixed(3);
						ui.tooltip.css( 'left', leftOffset + 'px' );
					}.bind( this ), 100 );

					var $id = ui.tooltip.attr( 'id' );
					jQuery( 'div.ui-tooltip' ).not( '#' + $id ).remove();
				},
				close : function ( event, ui ) {
					ui.tooltip.hover( function () {
						jQuery( this ).stop( true ).fadeTo( 400, 1 );
					},
					function () {
						jQuery( this ).fadeOut( '500', function () {
							jQuery( this ).remove();
						} );
					} );
				}
			} );
	    },

	    /**
	     * Strip tooltip content.
	     *
	     * @param  {[type]} content [description]
	     * @return {[type]}         [description]
	     */
	    strip_content : function( content ) {

			var tempWrapper = document.createElement( 'div' );

			tempWrapper.innerHTML = content;

			var scripts = tempWrapper.getElementsByTagName( 'script' );

			for ( var i = 0; i < scripts.length; i++ ) {
				scripts[ i ].parentNode.removeChild( scripts[ i ] );
			}

			return tempWrapper.innerHTML;
	    },

	    /**
	     * [multiple_address_fields_selector description]
	     * @return {[type]} [description]
	     */
	    multiple_address_fields_selector : function() {


	    	jQuery( '.gmw-admin-page [data="multiselect_address_fields"]' ).on( 'select2:select', function( e ) {

	    		var multiSelect  = jQuery( this );
			 	var lastSelected = e.params.data.id;

		   		if ( lastSelected == 'address' || lastSelected == 'disabled' ) {

                    multiSelect.children( 'option' ).each( function() {

                        if ( jQuery( this ).val() != lastSelected ) {
                           	jQuery( this ).prop( 'selected', false );
                        }
                    });

               } else  {

                    multiSelect.children( 'option' ).each( function() {

                        if ( jQuery( this ).val() == 'address' || jQuery( this ).val() == 'disabled'  ) {
                            jQuery( this ).prop( 'selected', false );
                        }
                    });
                }

               	multiSelect.trigger( 'change.select2' );
			});

	        // members locator address fields settings
	        /*jQuery( '.gmw-admin-page [data="multiselect_address_fields"]' ).on( 'change', function( evt, params ) {

	            var multiSelect  = jQuery( this );
	            var lastSelected = multiSelect.closest('select').find('option').filter(':selected:last').val();

	            alert(lastSelected )
	            if ( typeof lastSelected !== 'undefined' ) {


	                if ( lastSelected == 'address' || lastSelected == 'disabled' ) {
	                    alert('t')
	                    multiSelect.children( 'option' ).each( function() {

	                        if ( jQuery( this ).val() != lastSelected ) {

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

	                multiSelect.trigger( 'changed' );
	            }
	        });*/
	    },

	    /**
	     * form editor functions
	     *
	     * @return {[type]} [description]
	     */
	    form_editor_init : function() {

	    	// Close form editor button.
	    	jQuery( '#form-editor-close-button' ).click( function() {
	            GMW.set_cookie( 'gmw_admin_tab', '', 1 );
	        });

	    	// Submit form editor on click.
	    	jQuery( '#gmw-form-editor-submit' ).click( function() {
	    		GMW_Admin.update_form( jQuery( '#gmw-form-editor' ) );
				//jQuery( this ).closest( 'form' ).submit();
			});

	    	jQuery( '.gmw-settings-multiple-fields-wrapper' ).find( ':visible:first' ).addClass( 'gmw-first-visible-option' );

	        // on form submission.
	        /*jQuery( '#gmw-form-editor' ).on( 'submit', function( e ) {

	            // Proceed only if ajax enabled for form update
	            if ( jQuery( this ).data( 'ajax_enabled' ) ) {

	                // prevent form submission
	                e.preventDefault();

	                // update form
	                GMW_Admin.update_form( jQuery( this ) );
	            }
	        } );*/

	        // Jump to option.
	        jQuery( '#gmw-go-to-option-wrapper' ).detach().prependTo( '.gmw-admin-page-navigation' ).find( 'select' ).on( 'change', function() {

	    		var info = jQuery( this ).val().split( '|' );

	    		jQuery( '.gmw-admin-page-navigation' ).find( '#' + info[0] ).click();

	    		jQuery( '.gmw-admin-page-panels-wrapper' ).scrollTop( jQuery( '.gmw-admin-page-panels-wrapper' ).scrollTop() - jQuery( '.gmw-admin-page-panels-wrapper' ).offset().top + jQuery( '#' + info[1] ).offset().top - 20 );

				jQuery('#' + info[1]).addClass('go-to-option-highlight').find('legend').click();

	    		jQuery( '#go-top-option' ).val('');

	    		setTimeout( function() {
	    			jQuery( '#' + info[1] ).removeClass( 'go-to-option-highlight' );
	    		}, 2000 );
	    	});

	        jQuery( '#setting-general_settings-minimize_options' ).change( function() {

	        	if ( jQuery( this ).is( ':checked' ) ) {
	        		jQuery( '#gmw-edit-form-page, #gmw-settings-page' ).removeClass( 'gmw-visible-options' );
	        	} else {
	        		jQuery( '#gmw-edit-form-page, #gmw-settings-page' ).addClass( 'gmw-visible-options' );
	        	}
	        });

	        jQuery( '#setting-general_settings-minimize_options' ).trigger( 'change' );

	        //jQuery( 'body.geo-my-wp_page_gmw-forms' ).find( '.gmw-settings-panel:visible' ).addClass( 'gmw-panel-visible' );

	      	// Toggle settings panel.
	        jQuery( 'body.geo-my-wp_page_gmw-forms, body.geo-my-wp_page_gmw-settings' ).find( '.gmw-settings-panel:not( .always-visible ) legend' ).on( 'click', function() {

	        	//jQuery( this ).closest( '.gmw-settings-panel' ).toggleClass( 'gmw-panel-visible' );

	        	if ( jQuery( this ).closest( '#gmw-edit-form-page, #gmw-settings-page' ).hasClass( 'gmw-visible-options' ) ) {
	        		return;
	        	}

	        	var thisSetting = jQuery( this ).closest( '.gmw-settings-panel:not( .always-visible )' ).find( '.gmw-settings-panel-content' );

	        	jQuery( this ).closest( '.gmw-tab-panel' ).find( '.gmw-settings-panel:not( .always-visible )' ).removeClass( 'gmw-panel-visible' ).find( '.gmw-settings-panel-content' ).not( thisSetting ).slideUp( 'fast' );

	        	jQuery( this ).closest( '.gmw-settings-panel:not( .always-visible )' ).toggleClass( 'gmw-panel-visible' ).find( '.gmw-settings-panel-content' ).slideToggle( 'fast' );
	        });

	        // Toggle Locator Button form options.
	        jQuery( '#setting-search_form-locator_button-usage' ).change( function() {

	        	var value       = jQuery( this ).val();
	        	var parentPanel = jQuery( this ).closest( '.gmw-settings-multiple-fields-wrapper' );

	        	parentPanel.find( '.gmw-settings-panel-field:not( .option-usage )' ).slideUp( 'fast' );

	            if ( value != 'disabled' ) {
	                parentPanel.find( '.option-locator_submit, .option-' + value ).slideDown( 'fast' );
	            }
			});

			jQuery( '#setting-search_form-keywords-usage' ).change(function () {

				var value = jQuery(this).val();
				var parentPanel = jQuery(this).closest('.gmw-settings-multiple-fields-wrapper');

				setTimeout(function () {
					if (jQuery.inArray("meta_fields", value) !== -1) {
						parentPanel.find('.gmw-settings-panel-field.option-meta_fields').slideDown('fast');
					} else {
						parentPanel.find('.gmw-settings-panel-field.option-meta_fields').slideUp('fast');
					}
				}, 200);
	        });

	        // Toggle radius options.
	        jQuery( '#setting-search_form-radius-usage' ).change( function() {

	        	var value       = jQuery( this ).val();
	        	var parentPanel = jQuery( this ).closest( '.gmw-settings-multiple-fields-wrapper' );

	        	parentPanel.find( '.gmw-settings-panel-field:not( .option-usage )' ).slideUp( 'fast' );

	        	parentPanel.find( '.gmw-settings-panel-field.usage_' + value ).slideDown( 'fast' );
	        });

	        // Toggle units options.
	        jQuery( '#setting-search_form-units-options' ).change( function() {

	        	if ( jQuery( this ).val() == 'both' ) {
	        		jQuery( '.search_form-units-tr.option-label' ).slideDown();
	        	} else {
	        		jQuery( '.search_form-units-tr.option-label' ).slideUp();
	        	}
	        });

	        jQuery( '#setting-search_form-locator, #setting-search_form-locator_button-usage, #setting-search_form-radius-usage, #setting-search_form-units-options' ).trigger( 'change' );

	        // Post types/taxonmies switcher for post types locator extension.
	        /*jQuery( '#setting-search_form-post_types' ).change( function() {

	            var postTypes = jQuery( this ).val();
	            var tabPanel  = jQuery( this ).closest( '.gmw-tab-panel' );

	            // Are we using GEO my WP core taxonmies options ( not premium settings ).
	        	var isCoreTax = jQuery( '#taxonomies-core-wrapper' ).length ? true : false;

	        	if ( isCoreTax ) {
	            	tabPanel.find( '.taxonomy-wrapper, .post-types-taxonomies-message' ).hide();
	            }

	            if ( postTypes.length == 0 ) {

	            	// Show no post types selected message.
	                jQuery( '.post-types-taxonomies-message.select-taxonomy').show();

	                // Hide post types sub settings.
	                jQuery( '.search_form-post_types_settings-tr.gmw-settings-panel-field:not( .option-post_types )' ).slideUp();

	            } else if ( postTypes.length == 1  ) {

	            	// Show taxonmies.
	            	if ( isCoreTax ) {

		            	if ( tabPanel.find( '.taxonomy-wrapper[data-post_types*="' + postTypes[0] + '"]' ).length ) {

		                	tabPanel.find( '.taxonomy-wrapper[data-post_types*="' + postTypes[0] + '"]' ).show();

		            	} else {

		            		// No taxonmies found message.
		                	jQuery( '.post-types-taxonomies-message.taxonomies-not-found' ).show();
		            	}
		            }

		            // Hide post types sub settings.
	            	jQuery( '.search_form-post_types_settings-tr.gmw-settings-panel-field:not( .option-post_types )' ).slideUp();

	            } else {

	            	// Show multiple taxonomies selected message.
	                jQuery( '.post-types-taxonomies-message.multiple-selected' ).show();

	                jQuery( '#setting-search_form-post_types_settings-usage' ).trigger( 'change' );

	                // Show post types sub settings.
	                jQuery( '.search_form-post_types_settings-tr.gmw-settings-panel-field.option-usage' ).slideDown();
	            }
	        } );*/

	        // Usage field toggle options.
	        jQuery( '.usage-select-field-toggle' ).change( function() {

				var value    = jQuery( this ).val();
				var parent   = jQuery( this ).closest( '.gmw-settings-multiple-fields-wrapper' );
	            var elements = parent.find( '.gmw-settings-panel-field:not( .option-usage )' );

	            if ( value != 'disabled' ) {

		            if ( value == 'pre_defined' ) {

		            	elements.slideUp( 'fast' );
		            	parent.find( '.gmw-settings-panel-field.option-options' ).slideDown( 'fast' );

		            } else if ( value == 'checkboxes' ) {
		            	elements.slideDown( 'fast' );
		            	parent.find( '.gmw-settings-panel-field.option-show_options_all, .gmw-settings-panel-field.option-required, .gmw-settings-panel-field.option-smartbox' ).slideUp( 'fast' );
		            } else {
		            	elements.slideDown( 'fast' );
		            }
		        }
	       	});

	        jQuery( '.usage-select-field' ).trigger( 'change' );

	        // Post Types usage toggle.
	        /*jQuery( '#setting-search_form-post_types_settings-usage' ).change( function() {

				var value    = jQuery( this ).val();
	            var elements = jQuery( '.search_form-post_types_settings-tr.gmw-settings-panel-field:not( .option-post_types ):not( .option-usage )' );

	            if ( value == 'pre_defined' ) {
	            	elements.slideUp( 'fast' );
	            } else {
	            	elements.slideDown( 'fast' );
	            }
	       	});*/

	        jQuery( '#setting-search_form-post_types' ).trigger( 'change' );

	        // Enable click event on an elements with the 'gmw_ajax_load_options' data attribute.
	        // This is for loading select options via ajax.
	       // jQuery( 'select[data-gmw_ajax_load_options]' ).next( '.select2-container' ).addClass( 'gmw-ajax-loader-block gmw-tooltip' ).attr( 'aria-label', 'Click to load options.' ).on( 'click', function() {
	        jQuery( 'select[data-gmw_ajax_load_options]' ).next( '.select2-container' ).addClass( 'gmw-ajax-loader-block' ).on( 'click', function() {

	        	// make sure we do this once on page load.
	        	if ( jQuery( this ).hasClass( 'loaded' ) ) {
	        		return false;
	        	}

	        	//jQuery( this ).tooltip( 'destroy' );
	        	jQuery( this ).addClass( 'loaded' );

	        	var select2Elem = jQuery( this );
	        	var selectElem  = jQuery( this ).prev( 'select' );
	        	var wrapElem    = jQuery( this ).closest( '.gmw-custom-fields-wrapper' );
	        	var args        = {};

	        	// Collect the ajax arguments.
	        	jQuery.each( selectElem.data(), function( name, value ) {
	        		if ( name.indexOf( 'ajax_load_' ) != -1 ) {
	        			args[ name ] = value;
	        		}
	        	});

	        	// Remove the "click to view" class and add the "loading options..." class.
	        	select2Elem.removeClass( 'gmw-ajax-loader-block gmw-tooltip' ).addClass( 'gmw-loading-select-options' );

	        	// Run ajax.
		        jQuery.ajax( {
		            type     : 'POST',
		            url      : gmwVars.ajaxUrl,
		            dataType : 'json',
		            data     : {
		            	'args' : args,
						action: 'gmw_get_field_options',
						nonce : gmwVars.get_field_options_ajax_nonce,
		            },
		            success : function( response ) {

		                // Remove the "loading" message...
		                select2Elem.removeClass( 'gmw-loading-select-options' );

						if (response.success) {

							// Collect the options for the select element.
							var options = $.map(response.data, function (item, id) {

								// If array of value => label.
								if (typeof item === 'object') {
									id = item.value;
									label = item.label;
								} else {
									label = item;
								}

								var option = jQuery('<option value="' + id + '">' + label + '</option>');

								// If option is already selected.
								if (selectElem.find('option[value="' + id + '"]').is(':selected')) {

									// Remove the original element which serves as a placeholder only. ( saying "click to view" );
									selectElem.find('option[value="' + id + '"]').remove();

									// Set the new element to selected.
									option.prop('selected', true);
								}

								return option;
							});

							// Append options to the select element.
							selectElem.append(options).trigger('change');

							jQuery(document).trigger('gmw_ajax_options_loaded', [args, response.data, selectElem, select2Elem, wrapElem]);

						} else {

							alert('Failed loading data.');
							console.log(response.data);
						}
		            }

		        // if failed.
		        }).fail( function ( jqXHR, textStatus, error ) {

		        	// Remove the "loading" message...
		            select2Elem.removeClass( 'gmw-loading-select-options' );

		            if ( window.console && window.console.log ) {

		                console.log( textStatus + ': ' + error );

		                if ( jqXHR.responseText ) {
		                    console.log(jqXHR.responseText);
		                }
		            }
		        });
			});

	        // Destroy togglers tooltip once toggle used once.
	        jQuery( '.gmw-settings-group-header i' ).on( 'mouseup', function() {

	        	if ( ! jQuery( this ).hasClass( 'gmw-tooltip' ) || jQuery( this ).hasClass( 'tooltip-destroyed' ) ) {
	        		return;
	        	}

	        	var thisClass = jQuery( this ).attr( 'class' ).split( ' ' )[0];

	        	jQuery( '.' + thisClass ).addClass( 'tooltip-destroyed' ).tooltip( 'destroy' );
	        });

	        if ( typeof wp.codeEditor !== 'undefined' ) {

		        jQuery( '.gmw-code-mirror-field' ).each( function() {

		    		var thisField = jQuery( this );
		    		var cm_editor = wp.codeEditor.initialize( thisField, {} );

		    		// Update Code Mirror changes when value changes.
			        $( document ).on( 'keyup', '.CodeMirror-code', function(){
			            thisField.html( cm_editor.codemirror.getValue() );
			            thisField.trigger( 'change' );
			        });

			        // Make sure changes from Code Mirror are saved when form is submitted.
			        jQuery( '#gmw-form-editor' ).on( 'submit', function( e ) {
			        	thisField.html( cm_editor.codemirror.getValue() );
			            thisField.trigger( 'change' );
			        });
		    	});
		    }

	        /*************************************************************************/
	        /************ For Premium Settings and other Premium Extensions **********/
	        /*************************************************************************/

        	// Address field usage toggle ( single/multiple ).
			jQuery( '#setting-search_form-address_field-usage' ).on( 'change', function() {

				if ( jQuery( this ).val() == 'single' ) {

					jQuery( '.gmw-settings-panel-field.option-multiple' ).slideUp( 'fast', function() {
						jQuery( '.gmw-settings-panel-field.single-address-field-option' ).slideDown( 'fast' );
					});

				} else {

					jQuery( '.gmw-settings-panel-field.single-address-field-option' ).slideUp( 'fast', function() {
						jQuery( '.gmw-settings-panel-field.option-multiple' ).slideDown( 'fast' );
					});
				}
			});

			// address field option usage ( for each field inside the multiple fields option ).
			$( '.address-field-usage-option' ).change( function() {

				var value     = $( this ).val();
				var container = $( this ).closest( '.address-field-settings' );

				container.find( '.gmw-settings-panel-inner-option' ).not( ':first' ).slideUp( 'fast' );

				if ( value == 'default' ) {

					container.find( '.gmw-settings-panel-inner-option.address-field-value-option-wrapper' ).slideDown( 'fast' );

				} else if ( value != 'disabled' ) {

					container.find( '.gmw-settings-panel-inner-option:not( .address-field-value-option-wrapper )' ).slideDown( 'fast' );
				}
			});

			$( 'form.gmw-premium-settings-enabled' ).find( '#setting-search_form-address_field-usage, .address-field-usage-option' ).trigger( 'change' );


        	/********** Toggle taxonomies and post types settings based on selected post types ************/

        	//$( 'form.gmw-premium-settings-enabled' ).find( '#setting-page_load_results-post_types, #setting-search_form-post_types' ).change( function() {
			/*$( 'form#gmw-form-editor' ).find( '#setting-page_load_results-post_types, #setting-search_form-post_types' ).change( function() {

				var element   = jQuery( this );
				var postTypes = jQuery( this ).val();
				var taxesWrap = element.closest( '.gmw-tab-panel' ).find( '.include-exclude-taxonomy-terms-wrapper' );
				var tabPanel  = element.closest( '.gmw-tab-panel' );

				taxesWrap.find( '.gmw-taxonomies-picker-wrapper' ).hide();

				tabPanel.find( '.taxonomy-wrapper' ).hide();

				for ( var i = 0; i < postTypes.length; ++i ) {

					taxesWrap.find( '.gmw-taxonomies-picker-wrapper.' + postTypes[i] ).show();

		            tabPanel.find( '.taxonomy-wrapper[data-post_types*="' + postTypes[i] + '"]' ).show();
				}

				if ( postTypes.length > 1 ) {

					jQuery( this ).closest( '.gmw-settings-panel-content' ).find( '.fields-group-post_types_settings' ).show();

				} else {

					jQuery( this ).closest( '.gmw-settings-panel-content' ).find( '.fields-group-post_types_settings' ).hide();
				}
			});*/

			$( 'form#gmw-form-editor' ).find( '#setting-page_load_results-post_types, #setting-search_form-post_types' ).change( function() {

				var element    = jQuery( this );
				var postTypes  = jQuery( this ).val();
				var tabPanel   = element.closest( '.gmw-tab-panel' );
				var taxesWrap  = tabPanel.find( '.include-exclude-taxonomy-terms-wrapper' );
				var multiplePt = tabPanel.find('#taxonomies-wrapper').hasClass('multiple-post-types') ? true : false;
				//var multiplePt  = tabPanel.find( '#search_form-taxonomies-tr #taxonomies-wrapper' ).hasClass( 'multiple-post-types' ) ? true : false;
				//var incExcTerms = tabPanel.find( '#taxonomies-wrapper' ).hasClass( 'incexc-terms-enabled' ) ? true : false;
				var taxesFound  = false;

				taxesWrap.find( '.gmw-taxonomies-picker-wrapper' ).slideUp();

				tabPanel.find( '.taxonomy-wrapper, .post-types-taxonomies-message' ).hide();

				for ( var i = 0; i < postTypes.length; ++i ) {

					taxesWrap.find( '.gmw-taxonomies-picker-wrapper.' + postTypes[i] ).slideDown();

					if ( tabPanel.find( '.taxonomy-wrapper[data-post_types*="' + postTypes[i] + '"]' ).length ) {

						taxesFound = true;

		            	tabPanel.find( '.taxonomy-wrapper[data-post_types*="' + postTypes[i] + '"]' ).show().find( '.taxonomy-settings' ).slideUp().find( '.gmw-settings-panel-field' ).slideDown();

		            	tabPanel.find( '.taxonomy-wrapper[data-post_types*="' + postTypes[i] + '"]' ).find( '.taxonomy-usage' ).trigger( 'change' );
		            }
				}

				// When no post types were selected.
				if ( postTypes.length == 0 ) {

	            	// Show no post types selected message.
	               	tabPanel.find( '.post-types-taxonomies-message.select-taxonomy').slideDown();

	                // Hide post types sub settings.
	                tabPanel.find( '.search_form-post_types_settings-tr.gmw-settings-panel-field:not( .option-post_types )' ).slideUp();

					// When at least one post type was selected.
				} else if ( postTypes.length >= 1 ) {

					// When a single post type was selected.
					if ( postTypes.length == 1 ) {

						tabPanel.find( '.search_form-post_types_settings-tr.gmw-settings-panel-field:not( .option-post_types )' ).slideUp();

						if ( ! taxesFound ) {
							tabPanel.find( '.post-types-taxonomies-message.taxonomies-not-found' ).slideDown();
						}

						// Show/hide the taxonomies filters and include exclude taxonomies options in the search form tab.
						if ( element.attr( 'id' ) == 'setting-search_form-post_types' && jQuery( '#search_form-include_exclude_terms-tr' ).length ) {
							tabPanel.find( '#search_form-taxonomies-tr' ).slideDown(); // Show filters.
							tabPanel.find( '#search_form-include_exclude_terms-tr' ).slideUp(); // Hide include/exclude options.
						}

						// When multiple post types selected.
					} else {

						// Show/hide the taxonomies options in the search form tab.
						if ( element.attr( 'id' ) == 'setting-search_form-post_types' && jQuery( '#search_form-include_exclude_terms-tr' ).length ) {
							tabPanel.find( '#search_form-taxonomies-tr' ).slideDown(); // Show filters.
							tabPanel.find( '#search_form-include_exclude_terms-tr' ).slideDown(); // Show include/exclude options.
						}

						if ( ! multiplePt ) {

							tabPanel.find( '#search_form-taxonomies-tr .post-types-taxonomies-message.multiple-selected' ).slideDown();
							tabPanel.find( '#taxonomies-wrapper .taxonomy-wrapper' ).hide();

							//jQuery( this ).closest( '.gmw-settings-panel-content' ).find( '.fields-group-post_types_settings' ).show();

						} else if ( ! taxesFound ) {

							tabPanel.find( '.post-types-taxonomies-message.taxonomies-not-found' ).slideDown();
						}

		                // Show post types sub settings.
	                	tabPanel.find( '#setting-search_form-post_types_settings-usage' ).trigger( 'change' );

	                	tabPanel.find( '.search_form-post_types_settings-tr.gmw-settings-panel-field.option-usage' ).slideDown();
					}



				}/* else {

					jQuery( this ).closest( '.gmw-settings-panel-content' ).find( '.fields-group-post_types_settings' ).hide();
				}*/
			});




			/*var postTypes = jQuery( this ).val();
	            var tabPanel  = jQuery( this ).closest( '.gmw-tab-panel' );

	            // Are we using GEO my WP core taxonmies options ( not premium settings ).
	        	var isCoreTax = jQuery( '#taxonomies-core-wrapper' ).length ? true : false;

	        	if ( isCoreTax ) {
	            	tabPanel.find( '.taxonomy-wrapper, .post-types-taxonomies-message' ).hide();
	            }

	            if ( postTypes.length == 0 ) {

	            	// Show no post types selected message.
	                jQuery( '.post-types-taxonomies-message.select-taxonomy').show();

	                // Hide post types sub settings.
	                jQuery( '.search_form-post_types_settings-tr.gmw-settings-panel-field:not( .option-post_types )' ).slideUp();

	            } else if ( postTypes.length == 1  ) {

	            	// Show taxonmies.
	            	if ( isCoreTax ) {

		            	if ( tabPanel.find( '.taxonomy-wrapper[data-post_types*="' + postTypes[0] + '"]' ).length ) {

		                	tabPanel.find( '.taxonomy-wrapper[data-post_types*="' + postTypes[0] + '"]' ).show();

		            	} else {

		            		// No taxonmies found message.
		                	jQuery( '.post-types-taxonomies-message.taxonomies-not-found' ).show();
		            	}
		            }

		            // Hide post types sub settings.
	            	jQuery( '.search_form-post_types_settings-tr.gmw-settings-panel-field:not( .option-post_types )' ).slideUp();

	            } else {

	            	// Show multiple taxonomies selected message.
	                jQuery( '.post-types-taxonomies-message.multiple-selected' ).show();

	                jQuery( '#setting-search_form-post_types_settings-usage' ).trigger( 'change' );

	                // Show post types sub settings.
	                jQuery( '.search_form-post_types_settings-tr.gmw-settings-panel-field.option-usage' ).slideDown();
	            }*/






			//$( 'form.gmw-premium-settings-enabled' ).find( '#setting-page_load_results-post_types, #setting-search_form-post_types' ).trigger( 'change' );

			setTimeout( function() {
				$( 'form#gmw-form-editor' ).find( '#setting-page_load_results-post_types, #setting-search_form-post_types' ).trigger( 'change' );
			}, 100 );


	       	/********** Toogle and sort differnt options ( taxonomies, custom fields... ) ************/

			jQuery( document ).on( 'click', '.gmw-settings-group-wrapper .gmw-settings-group-header', function() {

				var container = jQuery( this ).closest( '.gmw-setting-groups-container' );
				var thisGroup = jQuery( this ).closest( '.gmw-settings-group-wrapper' ).find( '.gmw-settings-group-content' );
				var isVisible = thisGroup.is( ':visible' );

				// hide all options.
				container.find( '.gmw-settings-group-content' ).slideUp( 'fast' );

				// Show the clicked settings group.
				if ( ! isVisible ) {
					thisGroup.slideDown('fast');
				}
			});

			jQuery( '#search_form-taxonomies-tr' ).find( 'select.taxonomy-usage' ).change( function() {
				jQuery( this ).closest( '.taxonomy-settings-table-wrapper' ).attr( 'data-type', jQuery( this ).val() );
			});

			// Sort them.
			if ( jQuery().sortable ) {

				// sortable options.
				jQuery( '.gmw-settings-group-draggable-area' ).sortable({
					items       :'> .gmw-settings-group-wrapper.gmw-sortable-item',
			        opacity     : 0.8,
			        cursor      : 'move',
			        axis        : 'y',
			        handle      :'.gmw-settings-group-header',
			        placeholder : 'gmw-sortable-placeholder',
			        start       : function( event, ui ) {

			        	jQuery( this ).sortable( 'refreshPositions' );

				      	// Do something when start dragging.
				        jQuery( document ).trigger( 'gmw_sortable_option_drag_start', [ event, ui ] );

				        // Add custom class to draggable area.
				        jQuery( this ).closest( '.gmw-settings-group-draggable-area' ).addClass( 'gmw-draggable-active' );

				        // Close all draggable items whem dragging an item.
				        //jQuery( this ).closest( '.gmw-setting-groups-container' ).find( '.gmw-settings-group-content' ).hide();
				    },
				    stop        : function( event, ui ) {

				    	jQuery( this ).closest( '.gmw-settings-group-draggable-area' ).removeClass( 'gmw-draggable-active' );
				    },
			    });
			}

			/**************** Custom Fields - Premium Settings extension **********************/

			// close options when dragging element.
			/*jQuery( '.gmw-settings-group-drag-handle' ).on( 'mousedown', function() {
				jQuery( this ).closest( '.gmw-setting-groups-container' ).find( '.gmw-settings-group-content' ).hide();
			} );*/

			// Disable field in the dropdown options once it was added to the form.
			jQuery( document ).on( 'gmw_ajax_options_loaded', function( event, args, data, selectElem ) {

				var wrapElem = selectElem.closest( '.gmw-custom-fields-wrapper' );

				if ( typeof args.gmw_ajax_load_is_custom_fields !== 'undefined' && args.gmw_ajax_load_is_custom_fields == '1' ) {

					wrapElem.find( '.gmw-custom-field-wrapper:not( .original-field )' ).each( function() {
			    		jQuery( '.gmw-custom-fields-picker option[value="' + jQuery( this ).data( 'field_name' ) + '"]' ).prop( 'disabled', true );
					});
				}
			} );

			// Delete custom field.
			$( document ).on( 'click', '.gmw-custom-field-delete', function() {

				if ( ! confirm( "Are you sure?" ) ) {
					return false;
				}

				var wrapElement = jQuery( this ).closest( '.gmw-custom-fields-wrapper' );
				var field       = $( this ).closest( '.gmw-custom-field-wrapper' );

				wrapElement.find( '.gmw-custom-fields-picker' ).find( 'option[value="' + field.attr( 'data-field_name' ) + '"]' ).prop( 'disabled', false );

				field.find( '.custom-field-settings' ).slideUp( 'normal', function() {

					field.fadeOut( 'slow', function() {
						field.remove();
					});
				});
			});

			// Create new field.
			$( '.gmw-new-custom-field-button' ).click( function(e) {

				var wrapElement = jQuery( this ).closest( '.gmw-custom-fields-wrapper' );
				var fieldName = wrapElement.find('.gmw-custom-fields-picker').val();

				if ( fieldName == '' || fieldName == null ) {

					alert( 'Select a field from the dropdown.' );

					return;
				}

				var fieldLabel  = wrapElement.find( '.gmw-custom-fields-picker option:selected' ).text();

				wrapElement.find( '.gmw-custom-fields-picker' ).val( '' ).find( 'option[value="' + fieldName + '"]' ).prop( 'disabled', true ).trigger( 'change' );

				// make sure field not already added to the form.
				if ( ! wrapElement.find( '.gmw-custom-field-wrapper[data-field_name="' + fieldName + '"]' ).length ) {

					// clone new field element
					var newField = wrapElement.find( '.gmw-custom-field-wrapper.original-field' ).clone().hide();

					// append some data to the new field
					newField.attr('data-field_name', fieldName).find('.custom-field-slug input').val(fieldName);
					newField.find('.custom-field-slug span').html(fieldName);
					newField.find( '.custom-field-label span' ).html( fieldLabel );

					newField.find( '.custom-field-label-option-wrap input' ).val( fieldLabel );

					// modify the name attribute of the new field based on the field name
					newField.appendTo( wrapElement.find( '#custom-fields-holder' ) ).find( 'input, select, textarea' ).each( function() {

						if ( typeof $(this).attr('name') !== 'undefined' ) {

							if (typeof jQuery(this).attr('id') !== 'undefined') {
								jQuery(this).attr('id', jQuery(this).attr('id').replace('%%field_name%%', fieldName));
							}

							var newName = $(this).attr('name').replace('%%field_name%%', fieldName);

							$(this).attr('name', newName).prop('disabled', false);
						}

						if ( jQuery().select2 && jQuery(this).prop('type').indexOf('select-') != -1 && jQuery( this ).hasClass( 'post-types-selector' ) ) {

							jQuery( this ).select2({
								tags: true,
								dropdownParent    : jQuery( this ).parent(),
								containerCssClass : 'gmw-select2-container',
								allowClear: true,
								tags: false,
								//placeholder: ''
							});
						}
					});

					// show new field
					newField.removeClass( 'original-field' ).fadeIn( 'slow', function() {
						jQuery( this ).find( '.gmw-custom-field-options-toggle' ).click();
					} );

				} else {

					alert( 'This field already exist in the form.' );
				}
			});




			// Create new field.
			/*$( '.gmw-new-custom-field-button' ).click( function(e) {

				var wrapElement = jQuery( this ).closest( '.gmw-custom-fields-wrapper' );
				var fieldName   = wrapElement.find( '.gmw-custom-fields-picker' ).val();

				if ( fieldName == '' || fieldName == null ) {

					alert( 'Select a field from the dropdown.' );

					return;
				}

				var fieldLabel  = wrapElement.find( '.gmw-custom-fields-picker option:selected' ).text();

				wrapElement.find( '.gmw-custom-fields-picker' ).val( '' ).find( 'option[value="' + fieldName + '"]' ).prop( 'disabled', true ).trigger( 'change' );

				// make sure field not already added to the form.
				if ( ! wrapElement.find( '.gmw-custom-field-wrapper[data-field_name="' + fieldName + '"]' ).length ) {

					// clone new field element
					var newField = wrapElement.find( '.gmw-custom-field-wrapper.original-field' ).clone().hide();

					// append some data to the new field
					newField.attr( 'data-field_name', fieldLabel ).find( '.custom-field-label input' ).val( fieldLabel );

					newField.find( '.custom-field-label-option-wrap input' ).val( fieldLabel );

					// modify the name attribute of the new field based on the field name
					newField.appendTo( wrapElement.find( '#custom-fields-holder' ) ).find( 'input, select, textarea' ).each( function() {

						if ( typeof jQuery( this ).attr( 'id' ) !== 'undefined' ) {
							$( this ).attr( 'id', jQuery( this ).attr( 'id' ).replace( '%%field_name%%', fieldName ) );
						}

						var newName = $( this ).attr( 'name' ).replace( '%%field_name%%', fieldName );

						$( this ).attr( 'name', newName ).prop( 'disabled', false );
					});

					console.log(fieldName);

					jQuery.ajax( {
						type     : 'POST',
						url      : gmwVars.ajaxUrl,
						dataType : 'json',
						data     : {
							action     : 'gmw_new_advanced_custom_field',
							field_name : fieldName,
							//security   : formElement.data( 'nonce' )
						},
						success : function( fieldObject ) {

							// if updated
							if ( fieldObject ) {

								console.log(fieldObject)
								console.log(newField);

								var fieldSettings = newField.find('.custom-field-settings');
								var usage;

								if (fieldObject.type == 'checkbox') {

									usage = 'checkboxes';

								} else {
									usage = fieldObject.type
								}

								fieldSettings.find('.custom-field-usage-selector').val(usage).trigger('change');
								//fieldSettings.find('.custom-field-option-label .div:first-child input').val( fieldObject.label );

								newField.removeClass( 'original-field' ).fadeIn( 'slow', function() {
									jQuery( this ).find( '.gmw-custom-field-options-toggle' ).click();
								} );

							// if update faield for some reason
							} else {


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

					});
					// show new field
					/*newField.removeClass( 'original-field' ).fadeIn( 'slow', function() {
						jQuery( this ).find( '.gmw-custom-field-options-toggle' ).click();
					} );*/

				/*} else {

					alert( 'This field already exist in the form.' );
				}
			});*/




			jQuery( '.gmw-field-usage-options-toggle' ).each( function() {

				var field = jQuery( this );

				field.find( '.gmw-settings-panel-field.option-usage' ).find( 'select' ).removeClass( 'gmw-options-toggle' );
				field.find( '.gmw-settings-panel-field.option-smartbox' ).attr( 'data-usage', 'select,multiselect' );
				field.find( '.gmw-settings-panel-field.option-label' ).attr( 'data-usage', 'text,number,select,date,time,datetime,multiselect,checkboxes,radio' );
				field.find( '.gmw-settings-panel-field.option-options' ).attr( 'data-usage', 'pre_defined,select,multiselect,checkboxes,radio' );
				field.find( '.gmw-settings-panel-field.option-show_options_all' ).attr( 'data-usage', 'select,radio,multiselect' );
				field.find( '.gmw-settings-panel-field.option-required' ).attr( 'data-usage', 'text,number,select,date,time,datetime,multiselect,radio' );
			});

			// Toggle options.
			$( document ).on( 'change', '.custom-field-usage-selector, #taxonomies-wrapper select.taxonomy-usage, .gmw-field-usage-options-toggle .gmw-settings-panel-field.option-usage select', function() {

				var thisValue     = jQuery( this ).val();
				var thisFieldWrap = jQuery( this ).closest( '.custom-field-settings, .taxonomy-wrapper, .gmw-field-usage-options-toggle' );

				thisFieldWrap.find( '.gmw-settings-panel-field' ).not( '.custom-field-usage-option-wrap, .taxonomy-usage-option-wrap, .gmw-settings-panel-field.option-usage, .gmw-settings-panel-field.gmw-option-toggle-not' ).slideUp( 'fast' );

				if ( thisValue == 'disable' || thisValue == 'disabled' ) {
					return;
				}

				thisFieldWrap.find( '.gmw-settings-panel-field[data-usage*="' + thisValue + '"], .gmw-settings-panel-field[data-usage=""]' ).not( '.gmw-settings-panel-field[data-usage_not*="' + thisValue + '"]' ).slideDown( 'fast' );

				//if ( 'pre_defined' !== thisValue && 'pre_defined' !== thisValue && 'pre_defined' !== thisValue && 'pre_defined' !== thisValue && ) {
					//thisFieldWrap.find( '.custom-field-comparison-selector' ).val( '=' ).trigger( 'change' );
				//}
			});

			jQuery( '#search_form-taxonomies-tr, .gmw-field-usage-options-toggle' ).find( 'select.taxonomy-usage, .gmw-settings-panel-field.option-usage select' ).trigger( 'change' );
			// Toggle options.
			/*$( document ).on( 'change', '.custom-field-usage-selector', function() {

				var thisFieldWrap = jQuery( this ).closest( '.custom-field-settings' );
				var thisField = thisFieldWrap.find( '.gmw-settings-panel-field' ).not( '.custom-field-usage-option-wrap' ).not( '.custom-field-value-option-wrap' ).not( '.custom-field-comparison-option-wrap' );

				if ( $( this ).val() == 'hidden' ) {

					thisField.slideUp( 'fast' );

				} else {

					thisField.show();

					thisField = thisFieldWrap.find( '.custom-field-smartbox-option-wrap' );

					if ( $( this ).val() == 'select' || $( this ).val() == 'multiselect' ) {
						thisField.slideDown( 'fast' );
					} else {
						thisField.slideUp( 'fast' );
					}

					thisField = thisFieldWrap.find( '.custom-field-options-option-wrap' );

					if ( $( this ).val() == 'select' || $( this ).val() == 'multiselect' || $( this ).val() == 'checkboxes' || $( this ).val() == 'radio' ) {
						thisField.slideDown( 'fast' );
					} else {
						thisField.slideUp( 'fast' );
					}

					thisField = thisFieldWrap.find( '.custom-field-type-option-wrap' );

					if ( $( this ).val() == 'text' ) {

						thisField.slideDown( 'fast' );

						thisField.find( 'select' ).trigger( 'change' );
					} else {

						thisField.slideUp( 'fast' );

						thisFieldWrap.find( '.custom-field-date-format-option-wrap' ).slideUp( 'fast' );
					}
				}
			});*/

			// Form options toggler.
	    	// Using the class 'gmw-options-toggle' in a form option will toggle all the other options inside that box when that option is toggled.
	    	// Works with checkbox and dropdown options.
	    	jQuery( '.gmw-options-toggle' ).change( function() {

	        	var isTab   = jQuery( this ).closest( '.gmw-settings-multiple-fields-wrapper' ).length ? false : true;
	        	var thisVal = jQuery( this ).val();

	        	var thisPanel,
	        		parentPanel,
	        		panelClass;

	        	if ( isTab ) {

	        		panelClass  = '.gmw-settings-panel';
	        		thisPanel   = jQuery( this ).closest( panelClass );
	        		parentPanel = jQuery( this ).closest( '.gmw-tab-panel' );

	        	} else {

	        		panelClass  = '.gmw-settings-panel-field';
	        		thisPanel   = jQuery( this ).closest( panelClass );
	        		parentPanel = jQuery( this ).closest( '.gmw-settings-multiple-fields-wrapper' );
	        	}

        		if ( ( jQuery( this ).is( 'select' ) && jQuery( this ).val() != '' && jQuery( this ).val() != 'disable' && jQuery( this ).val() != 'disabled' ) || jQuery( this ).is( ':checked' ) ) {

        			parentPanel.find( panelClass ).not('.option-meta_fields').slideDown( 'false' ).removeClass( 'disabled' );

	        	} else {

	        		parentPanel.find( panelClass ).not( thisPanel ).not('.option-meta_fields').slideUp( 'fast', function() {
	        			thisPanel.addClass( 'disabled' );
	        		});
	        	}
	        });

	    	// Trigger options toggler on page load.
	        jQuery( '.gmw-options-toggle' ).trigger( 'change' );

			// Toggle options.
			$( document ).on( 'change', '.custom-field-type-selector', function() {

				var thisField = jQuery( this ).closest( '.custom-field-settings' ).find( '.custom-field-date-format-option-wrap' );

				if ( $( this ).val() == 'DATE' ) {
					thisField.slideDown( 'fast' );
				} else {
					thisField.slideUp( 'fast' );
				}
			});

			// Toggle options.
			$( document ).on( 'change', '.custom-field-comparison-selector, .custom-field-date-comparison-selector', function() {

				var value   = $( this ).val();
				var options = jQuery( this ).closest( '.custom-field-settings' ).find( '.custom-field-second-option' );

				if ( value == 'BETWEEN' || value == 'NOT_BETWEEN' ) {

					setTimeout( function() {
						options.slideDown( 'fast' );
					}, 500 );

					options.slideDown( 'fast' );

				} else {
					options.slideUp( 'fast' );
				}
			});

			jQuery( '.custom-field-comparison-selector, .custom-field-date-comparison-selector, .custom-field-usage-selector' ).trigger( 'change' );

			/********** Info-window templates switcher and AJAX templates toggler ************/

			jQuery( 'select#setting-info_window-iw_type' ).on( 'change', function() {

				var wrapper = jQuery( this ).closest( '.gmw-settings-multiple-fields-wrapper' );

				wrapper.find( '.gmw-info-window-template' ).slideUp( 'fast' );

				wrapper.find( '.gmw-info-window-template.' + jQuery( this ).val() ).slideDown();
			});

			jQuery( '#setting-info_window-ajax_enabled' ).change( function() {

				var tempElement = jQuery( this ).closest( '.gmw-settings-multiple-fields-wrapper' ).find( '.option-template' );

				tempElement.slideUp( 'fast' );

				//jQuery( this ).closest( '.gmw-settings-multiple-fields-wrapper' ).find( '.info_window-iw_appearance-tr, #info-window-no-templates-message' ).hide();

				if ( jQuery( this ).is( ':checked' ) ) {

					tempElement.slideDown( 'fast' );

				}/* else {

					jQuery( '#info-window-no-templates-message' ).show();
				}*/
			});

			jQuery( 'select#setting-info_window-iw_type, #setting-info_window-ajax_enabled' ).trigger( 'change' );
	    },

	    /**
	     * Update form editor via ajax
	     *
	     * @return {[type]} [description]
	     */
	    update_form : function( formElement ) {

	        // disable submit buttons to prevent multiple submissions
	        //jQuery( '#gmw-form-editor input[type="submit"]' ).prop( 'disabled', true );

	        //show form cover
	        jQuery( '.gmw-edit-form #gmw-form-cover' ).show();

			jQuery( '#gmw-form-editor-submit' ).addClass( 'saving-form' );

	        jQuery( '#gmw-admin-page-loader' ).fadeIn( 'fast' ).find( 'span' ).html( 'Saving changes...' );

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
	                    //jQuery( '.gmw-edit-form input[type="submit"]' ).prop( 'disabled', false );

	                    // hide form cover
	                    //jQuery( '.gmw-edit-form #gmw-form-cover' ).hide();
	                    jQuery( '#gmw-form-editor, #gmw-form-editor-submit' ).removeClass( 'saving-form' );

	                    jQuery( '#gmw-form-editor-submit' ).addClass( 'form-saved' );

	                    // wait a bit and hide message
                        setTimeout(function() {

                           jQuery( '#gmw-form-editor-submit' ).removeClass( 'form-saved' );

                           	jQuery( '#gmw-admin-page-loader' ).fadeOut( 'fast', function() {
	                			jQuery( this ).find( 'span' ).html( '' );
	                		});

                        }, 1000 );

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

	            jQuery( '#gmw-form-editor, #gmw-form-editor-submit' ).removeClass( 'saving-form' );

	            jQuery( '#gmw-form-editor-submit' ).addClass( 'saving-form-failed' );

                // wait a bit and hide message
                setTimeout(function() {

                   jQuery( '#gmw-form-editor-submit' ).removeClass( 'saving-form-failed' );

                   jQuery( '#gmw-admin-page-loader' ).fadeOut( 'fast', function() {
            			jQuery( this ).find( 'span' ).html( '' );
            		});

                }, 3000 );
	        });
	    },

	    premium_feature_modal : function( args ) {

	    	var title;
	    	var image    = '<span class="gmw-premium-feature-image-wrapper"><img src="https://geomywp.com/wp-content/uploads/extensions-images/icons/' + args.feature + '_icon.svg" /><span></span></span>';
	    	var content  = '<div class="gmw-premium-feature-modal-content-wrapper">';
	    		content += '<div class="gmw-premium-feature-content">';
	    		content += '<p>' + args.content + '</p>';
	    		content += '</div>';
	    		content += '</div>';

			jQuery.alert( {
				title        : '<a href="' + args.url + '" target="_blank">' + args.name + ' Extension</a>',
				content      : content,
				boxWidth     : '450px',
				onOpenBefore : function() {

					this.$btnc.after( '<img class="gmw-premium-feature-mascot" src="https://geomywp.com/wp-content/uploads/assets/images/mascot-sign.png" />' );
					//this.$btnc.after( '<img class="gmw-premium-feature-mascot" src="https://graphic-mama.s3.amazonaws.com/previews/0v47rp9m1g8yk5ldownyjzq2/61603fd4289f6-Marker-mascot-pose2_original.jpg" style="max-width: 256px;margin-top: -147px;margin-left: 57px;" />' );
					this.$body.addClass( 'gmw-premium-feature-box' );

					jQuery( '.gmw-premium-feature-box' ).prepend( image);
				},
				buttons : {
					somethingElse : {
						text     : 'Visit Extension Page',
						btnClass : 'btn-green',
						keys     : [ 'enter' ],
						action   : function() {
							window.open( args.url, '_blank' );
							/*jQuery.alert( {
								title    : false,
								content  : 'some content here',
								icon     : 'fa fa-info-circle',
								type     : 'blue',
								boxWidth : '565px',
								buttons  : {
									confirm : {
										text     : 'confirm texxt here',
										btnClass : 'btn-confirm',
										keys     : [ 'enter' ],
									},
								},
							} );*/
						},
					},
				},
			} );
		},
	};

   GMW_Admin.init(jQuery);
});
