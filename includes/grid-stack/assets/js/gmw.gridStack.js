
var GMW_Grid_Stacks = {};

/**
 * Base Map generator.
 *
 * Can be extended to work with different map providers.
 *
 * @param {[type]} map_id [description]
 * @param {[type]} vars  [description]
 */
var GMW_Grid_Stack = function( gridOpts, gmwOpts ) {

	this.slug = gmwOpts.slug;

	this.id = gmwOpts.id;

	this.defaultWidgets = gmwOpts.widgets;

	this.grid = gridOpts;

	this.outputElement = gmwOpts.outputElement;
	//this.subGrids = [];

	this.savedData = '';

	this.init();
};

/**
 * Initialize.
 *
 * @return {[type]} [description]
 */
GMW_Grid_Stack.prototype.init = function() {

	var self = this;

	// Looks for saved data.
	/*try {

		self.savedData = jQuery(self.outputElement).val();

		self.savedData = JSON.parse(self.savedData);

		self.grid.children = self.savedData.children;

	} catch( error ) {

		console.log( error );

		self.savedData = '';
	}*/

	var grid = GridStack.addGrid(document.querySelector('.' + self.id), self.grid );

	this.grid = grid;

	// Enable grid inserter.
	if ( jQuery( '#' + self.id ).find( '.gmw-grid-stack-inserter' ).length ) {

		jQuery( '#' + self.id ).find( '.gmw-grid-stack-inserter .grid-stack-item' ).on( 'click', function() {

			// Clone inserter into an item with default hieght and position.
			var newWidget = grid.addWidget( this.cloneNode( true ), { y : 500 } );

			self.addNewGridItem( newWidget );

			grid.update( newWidget, { x : 0, w : 12 } );
		});
	}

	// Loop through all grid items and run some tasks.
	grid.el.querySelectorAll( '.grid-stack-item' ).forEach( ( widget ) => {

		// Append action buttons to widget.
		self.appendActionButtons( widget );

		// Tasks for SubGrids.
		if ( jQuery( widget ).hasClass( 'grid-stack-sub-grid' ) ) {

			// Init existing subGrid to run some tasks and enable events.
			self.initSubGrid( widget.querySelector( '.grid-stack-nested' ), widget.getAttribute( 'gs-id' )  );

			// For Widgets.
		} else {}
	});

	/**
	 * When dropping a new item from the inserter into the root grid.
	 */
	grid.on( 'dropped', function( event, prevNode, widget ) {

		// Proceed only if a new item from the inserter.
		if ( prevNode ) return;

		self.addNewGridItem( widget.el );

		grid.update(widget.el, { x: 0, w: 12 });

		self.saveData();
	});

	/**
	 * Tasks on different events.
	 *
	 * Element ( el ) would be either an array of elements or a single element.
	 *
	 * Array of elements would be for 'change', 'added', and 'removed.
	 *
	 * A single element would be for 'dragstart', 'drag', 'dragstop', 'resizestart', 'resize', and 'resizestop'.
	 */
	grid.on('dragstart dragstop added', function (event, el) {

		// On dragstart.
		if (event.type == 'dragstart') {

			// When start dragging, resize widget to 2 columns to make it possible to add it to a subGrid if needed.
			if (!jQuery(el).hasClass('grid-stack-sub-grid')) {
				grid.update(el, { w: 2 });
			}

			// Remove pre-drag class when start dragging. We no longer need it at this point.
			jQuery(el).removeClass('item-pre-drag').removeAttr('data-originalY').removeAttr('data-originalX');
		}

		//if ( event.type == 'drag' ) {}

		// On dragstop.
		if (event.type == 'dragstop') {

			// Resize widgets on root grid to 100%.
			grid.update(el, { x: 0, w: 12 });

			// When stop dragging on root grid, update all subgrid to make sure there are no empty spaces.
			grid.engine.nodes.forEach((widget) => {

				if (typeof widget.subGrid !== 'undefined') {
					widget.subGrid.compact('moveScale');
				}
			});
		}

		// Resize Start
		//if ( event.type == 'resizestart' ) {}

		// Resize stop.
		//if ( event.type == 'resizestop' ) {}

		// On added
		if (event.type == 'added') {

			// Resize widgets on root grid to 100%.
			grid.update(el[0].el, { x: 0, w: 12 });
		}

		// On removed
		//if ( event.type == 'removed' ) {}

		// On change.
		//if ( event.type == 'change' ) {}

		self.saveData();
	});
}

GMW_Grid_Stack.prototype.initSubGridColumns = function( subGrid ) {

	var self = this;

	/**
	 * When dropping a new item from the inserter into a SubGrid.
	 */
	subGrid.on( 'dropped', function( event, prevNode, widget ) {

		// Proceed only if a new item from the inserter.
		if ( prevNode ) return;

		self.addNewGridItem(widget.el);

		self.saveData();
	});

	/**
	 * Tasks on different events.
	 *
	 * Element ( el ) would be either an array of elements or a single element.
	 *
	 * Array of elements would be for 'change', 'added', and 'removed.
	 *
	 * A single element would be for 'dragstart', 'drag', 'dragstop', 'resizestart', 'resize', and 'resizestop'.
	 */
	subGrid.on( 'dragstart dragstop resizestart resize resizestop removed added', function( event, el ) {

		if ( event.type == 'dragstart' ) {

			// Remove pre-drag class when start dragging an item. We no longer need it at this point.
			jQuery( el ).removeClass( 'item-pre-drag' ).removeAttr( 'data-originalY' ).removeAttr( 'data-originalX' );
		}

		if ( event.type == 'resizestart' ) {
			jQuery( subGrid.el ).addClass( 'element-resizing' );
		}

		if ( event.type == 'resizestop' ) {
			jQuery( subGrid.el ).removeClass( 'element-resizing' );
		}

		if ( event.type == 'added' ) {

			/*subGrid.engine.nodes.forEach( ( column ) => {

				if ( column.y > 0 ) {

					var position = parseInt( subGrid.parentGridItem.y ) + parseInt( subGrid.parentGridItem.h );

					rootGrid.addWidget( column.el.cloneNode( true ), { h : 12, y : position } );
					subGrid.removeWidget( column.el );
				}
			});

			subGrid.compact( 'moveScale' );*/
		}

		//if ( event.type == 'dragstop' ) {}

		//if ( event.type == 'resize' ) {}

		//if ( event.type == 'removed' ) {}

		//if ( event.type == 'change' ) {}

		// Remove any blank spaces.
		if ( event.type == 'dragstop' || event.type == 'resize' || event.type == 'resizestop' || event.type == 'added' || event.type == 'removed' ) {
			subGrid.compact( 'moveScale' );
		}

		// When resizing an element, push the elements next to it to allow it to resize.
		if ( event.type == 'resizestart' || event.type == 'resize' ) {

			var sum = 0;

			jQuery( el ).closest( '.grid-stack-sub-grid' ).find( '.grid-stack-item:not( .grid-stack-placeholder )' ).each( function() {
				sum += parseInt( jQuery( this ).attr( 'gs-w' ) ) || 1 ;
			});

			if ( sum < 12 ) {

				var placement = parseInt( ( jQuery( el ).attr( 'gs-x' ) ) || 0 ) + ( parseInt( jQuery( el ).attr( 'gs-w' ) || 1 ) );

				jQuery( el ).closest( '.grid-stack-sub-grid' ).find( '.grid-stack-item' ).filter( function() {

					if ( parseInt( jQuery( this ).attr( 'gs-x' ) ) >= placement ) {
						subGrid.update( jQuery( this )[0], { x : parseInt( jQuery( this ).attr( 'gs-x' ) ) + 1  } );
					}
				});

			} else if ( sum == 12 ) {

				var placement1 = parseInt( ( jQuery( el ).attr( 'gs-x' ) ) || 0 ) + ( parseInt( jQuery( el ).attr( 'gs-w' ) || 1 ) );

				jQuery( el ).closest( '.grid-stack-sub-grid' ).find( '.grid-stack-item' ).filter( function() {

					if ( placement1 == parseInt( jQuery( this ).attr( 'gs-x' ) ) ) {

						//subGrid.compact( 'moveScale' );
						subGrid.update( jQuery( this )[0], { w : parseInt( jQuery( this ).attr( 'gs-w' ) ) - 1 } );
					}
				})

				sum1 = 0;

				jQuery( el ).closest( '.grid-stack-sub-grid' ).find( '.grid-stack-item:not( .grid-stack-placeholder )' ).each( function() {
					sum1 += parseInt( jQuery( this ).attr( 'gs-w' ) ) || 1 ;
				});

				//console.log(sum1)
				if ( sum1 < 12 ) {

					var placement2 = parseInt( ( jQuery( el ).attr( 'gs-x' ) ) || 0 ) + ( parseInt( jQuery( el ).attr( 'gs-w' ) || 1 ) );

					jQuery( el ).closest( '.grid-stack-sub-grid' ).find( '.grid-stack-item' ).filter( function() {

						if ( parseInt( jQuery( this ).attr( 'gs-x' ) ) == placement2 ) {
							subGrid.update( jQuery( this )[0], { x : parseInt( jQuery( this ).attr( 'gs-x' ) ) + 1  } );
						}
					});
				}
			}
		}

		self.saveData();
		//this.subGrids.push( subGrid );
	});

	/*subGrid.on('resizestart resize drag dragstart', function(event,  widget) {
		clearTimeout(reOrder)
	});

	let reOrder = setTimeout(() => {}, 1000);

	subGrid.on( 'dragstop resizestop removed added', function(event,  widget) {

		if ( event.type == 'added' ) {

			subGrid.compact( 'moveScale' );

		} else {

			reOrder = setTimeout( function() {
				subGrid.compact( 'moveScale' );
			}, 1000 );
		}
	});*/

	/*subGrid.on('removed', function(event, items) {
		items.forEach(function(item) {
			console.log(item)
		});
	});*/
}

/**
 * Add events to sub grids.
 */
GMW_Grid_Stack.prototype.initSubGrid = function( subGrid, type ) {

	var self = this;

	if ( ! subGrid ) {
		return;
	}

	if ( jQuery( subGrid ).hasClass( 'grid-stack-nested' ) ) {
		subGrid = subGrid.gridstack;
	}

	// SubGrid columns
	if ( type == 'columns' ) {
		self.initSubGridColumns( subGrid )
	}
}

/**
 * Create Columns subgrid.
 */
GMW_Grid_Stack.prototype.addSubGridColumns = function( widget, grid ) {

	var self = this;

	widget = typeof widget.el !== 'undefined' ? widget.el : widget;

	// Generate the subGrid.
	var subGrid = grid.makeSubGrid( widget, self.defaultWidgets.columns.subGridOpts, undefined, false );

	// Attach events.
	self.initSubGridColumns( subGrid );
};

GMW_Grid_Stack.prototype.addWidgetSpacer = function( widget, grid ) {

}

GMW_Grid_Stack.prototype.addWidgetActionHook = function( widget, grid ) {

	/*var opts = {
		content : '<div contentEditable="true">Action Name</div>',
	}*/
	grid.update(widget, {} );
}

/**
* Adding a new widget to the grid.
*/
GMW_Grid_Stack.prototype.addNewGridItem = function( widget ) {

	var self       = this;
	var widgetType = widget.getAttribute( 'gs-id' );
	var widgetOpts = {};

	if (self.defaultWidgets[widgetType].single_instance && jQuery('.' + self.grid.opts.class ).find('.grid-stack-item[gs-id="' + widgetType + '"]').length > 1 ) {

		alert('field already exists');

		self.removeWidget(widget);

		return;
	}

	// Clear the default content of the widget from the inserter.
	//widget.querySelector('.grid-stack-item-content').innerHTML = '';

	// Remove inserter icons and inserter class.
	jQuery(widget).removeClass( 'grid-item-inserter' ).find('.grid-item-inserter-content').remove();

	// Unwrap the widget content.
	jQuery(widget).find('.grid-item-content-holder').children().unwrap();

	if ( typeof self.defaultWidgets[ widgetType ] !== 'undefined' ) {
		widgetOpts = self.defaultWidgets[ widgetType ];
	} else {
		widgetOpts = self.defaultWidgets['default_widget'];
	}

	// Remove the content from the widget options. The content already exists from the inserter.
	//delete widgetOpts.content;

	// Preserve the row of the widget.
	widgetOpts.y = widget.getAttribute( 'gs-y' );

	// Enable action buttons.
	self.appendActionButtons(widget);

	// Update widget with its default values.
	self.grid.update(widget, widgetOpts);

	console.log(widget)
	//jQuery( widget ).find( )

	// Append action buttons to widget.


	// Get action buttons.
	//var actionButtons = getActionButtons( widgetType );

	// Append action button to new widget.
	//jQuery( actionButtons ).prependTo( jQuery( widget ) );

	//widget = grid.addWidget( widgetOpts );

	if ( widgetType == 'columns' ) {
		self.addSubGridColumns( widget, self.grid );
	}

	if ( widgetType == 'spacer' ) {
		self.addWidgetSpacer( widget, self.grid );
	}

	if ( widgetType == 'action_hook' ) {
		self.addWidgetActionHook( widget, self.grid );
	}

	self.saveData();
}

GMW_Grid_Stack.prototype.removeWidget = function (widget) {

	var self = this;

	//this.singleInstanceWidgets.splice( jQuery.inArray( widget.getAttribute('gs-id'), this.singleInstanceWidgets ), 1 );

	// Delete widget that is inside a subGrid.
	if ( jQuery( widget ).parent().hasClass( 'grid-stack-nested' ) ) {

		widget.parentElement.gridstack.removeWidget( widget );

		// Delete widget on the main grid.
	} else {

		self.grid.removeWidget( widget );
	}

	self.saveData();
}

/**
 * Append action buttons to widgets and initiate thier actions.
 */
GMW_Grid_Stack.prototype.appendActionButtons = function (widget) {

	var self       = this;
	var type       = widget.getAttribute('gs-id');
	var widgetElem = jQuery(widget);

	if (typeof self.defaultWidgets[type] === 'undefined' || !self.defaultWidgets[type].action_buttons) {
		return;
	}
	console.log(self.defaultWidgets[type].action_buttons)

	var actionButtons = '';
	var actionBtn = '';


	actionButtons = jQuery('<div class="grid-item-action-buttons"></div>');


	self.defaultWidgets[type].action_buttons.forEach((button) => {

		if (jQuery('#' + self.id).find('.action-buttons-holder .gmw-item-action-button[data-id="' + button + '"]').length) {

			jQuery('#' + self.id).find('.action-buttons-holder .gmw-item-action-button[data-id="' + button + '"]').clone().appendTo( actionButtons );

			//console.log(actionBtn[0])
			//actionButtons += actionBtn[0];
		}
	});

	actionButtons.prependTo(jQuery(widget));

	// Abort if no action buttons found.
	if (!widgetElem.find('> .grid-stack-item-content > .grid-item-action-buttons').length) {
		return;
	}

	// Detach the action buttons from the item content. We will append it outside the content.
	var btns = widgetElem.find( '> .grid-stack-item-content > .grid-item-action-buttons' ).detach();

	// Append Action buttons only if needed. They might already exists if the widget is coming from the inserter.
	if ( ! widgetElem.find( '> .grid-item-action-buttons' ).length) {
		btns.prependTo(widgetElem);
	}

	// Make buttons visible.
	widgetElem.find( '.grid-item-action-buttons' ).show();

	//jQuery(self.defaultWidgets[type].actionBtnsOutput).prependTo(jQuery(widget));

	widgetElem.find( '> .grid-item-action-buttons .gmw-item-action-button' ).on( 'click', function(e) {

		e.preventDefault();

		var actionBtn = jQuery( this );

		if ( actionBtn.hasClass( 'widget-action-remove' ) ) {

			// Delete widget that is inside a subGrid.
			self.removeWidget(widget);

		} else if ( actionBtn.hasClass( 'widget-action-columns-size' ) ) {

			if ( jQuery( widget ).hasClass( 'grid-stack-sub-grid' ) && jQuery( widget ).find( '.grid-stack-nested' ).length ) {

				var subGrid    = jQuery( widget ).find( '.grid-stack-nested' )[0].gridstack;
				var rootGrid   = subGrid.parentGridItem.grid;
				var widgetSize = parseInt( actionBtn.attr( 'data-widget_size' ) );

				if ( widgetSize && subGrid ) {

					// Loop through widgets in subGrid and resize them.
					subGrid.engine.nodes.forEach( ( widget ) => {

						// update widget.
						subGrid.update( widget.el, { w : widgetSize } );

						// Fill up spaces.
						subGrid.compact( 'moveScale' );

						// If a widget pushed out of row, move it to the root grid.
						if ( widget.y > 0 ) {

							// Get position of the subGrid so we can place the widget below it.
							var position = parseInt( subGrid.parentGridItem.y ) + parseInt( subGrid.parentGridItem.h );

							// Clone widget to the root grid.
							rootGrid.addWidget( widget.el.cloneNode( true ), { h : 13, y : position } );

							// Remove widget from subGrid.
							subGrid.removeWidget( widget.el );
						}
					});

					subGrid.compact( 'moveScale' );
				}
			}
		}
	});

	self.grid
}

GMW_Grid_Stack.prototype.saveData = function (widget) {

	var self       = this;
	self.savedData = self.grid.save( true, true );

	self.savedData.children.forEach( ( child ) => {

		if ( child.id == 'action_hook' ) {

			child.action_name = jQuery( child.content ).text();
		}

		//console.log(child)
		//child.function = child.id;
		//delete child.content;
	});

	jQuery( self.outputElement ).val(JSON.stringify(self.savedData, null, '  '));
}

jQuery(document).ready(function ($) {

	/**
	 * Load Grids.
	 *
	 * @return void
	 */
	function initGrids() {

		if ( typeof gmwGridStacks === 'undefined' ) {
			return;
		}

		/**
		 * Reposition widget when mousedown on the drag action icon. To a position that works best when dragging the widget and trying to place it in a subgrid.
		 */
		jQuery( document ).on( 'mousedown', '.grid-stack-item:not( .grid-stack-sub-grid ) .widget-action-drag', function(e) {

			var widget         = jQuery( this ).closest( '.grid-stack-item' );
			var widgetPosition = widget.position();
			var widgetOffset   = jQuery( this ).offset();

			widget.css( {
				top : widgetPosition.top + e.pageY - widgetOffset.top - 7,
				left : widgetPosition.left + e.pageX - widgetOffset.left - 7,
			});

			widget.addClass( 'item-pre-drag' ).attr( 'data-originalY', widgetPosition.top ).attr( 'data-originalX', widgetPosition.left );
		});

		/**
		 * Return the widget to its original position if it wasn't dragged.
		 */
		jQuery( document ).on( 'mouseup', '.grid-stack-item:not( .grid-stack-sub-grid ) .widget-action-drag', function(e) {

			var widget = jQuery( this ).closest( '.grid-stack-item' );

			if ( widget.hasClass( 'item-pre-drag' ) ) {

				// place widget in its original position.
				widget.css( {
					top : parseInt( widget.attr( 'data-originalY' ) ),
					left : parseInt( widget.attr( 'data-originalX' ) ),
				});

				// Clear class.
				widget.removeClass( 'item-pre-drag' ).removeAttr( 'data-originalY' ).removeAttr( 'data-originalX' );
			}
		});

		GridStack.setupDragIn('.gmw-grid-stack-inserter .grid-stack-item.grid-item-inserter', { appendTo: 'body', helper: 'clone' });

		// loop through and generate all maps
		jQuery.each( gmwGridStacks, function ( slug, gridOpts) {

			// generate new map
			GMW_Grid_Stacks[slug] = new GMW_Grid_Stack( gridOpts.grid, gridOpts.options);
		});
	}

	initGrids();

});


