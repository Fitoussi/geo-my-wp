jQuery( document ).ready( function( $ ) {

    var GMW_Import_Locations = {

        //declare globals
        processing     : false, 
        form           : false,
        import_details : false,
        updated_ph     : false,
        imported_ph    : false,
        existing_ph    : false,
        updated_ph     : false,
        failed_ph      : false,
        action_ph      : false,
        importer       : {},

        init : function() {
                
            // on form submission
            $( 'body' ).on( 'submit', '.gmw-locations-importer', function(e) {     
                
                e.preventDefault(); 
                
                // disabled all importer submit buttons to prevent multiple importers                
                $( '.gmw-locations-importer-submit' ).attr( "disabled", "disabled" );

                // get the form element
                GMW_Import_Locations.form = $( this );
                
                // run importer
                if ( ! GMW_Import_Locations.processing ) {
                    
                    // remove the current import details if exists
                    GMW_Import_Locations.form.find( '.gmw-importer-details' ).remove();

                    // Create new import details element by cloning the original one.
                    GMW_Import_Locations.form.append( GMW_Import_Locations.form.closest( 'div.gmw-locations-importer-wrapper' ).find( '.gmw-importer-details' ).clone() );
                    
                    GMW_Import_Locations.import_details = GMW_Import_Locations.form.find( '.gmw-importer-details' );

                    // show the form details
                    GMW_Import_Locations.import_details.fadeIn( '300', function() {
                        GMW_Import_Locations.action_ph   = GMW_Import_Locations.import_details.find( '.action-ph' );
                        GMW_Import_Locations.updated_ph  = GMW_Import_Locations.import_details.find( '.updated-ph' );
                        GMW_Import_Locations.imported_ph = GMW_Import_Locations.import_details.find( '.imported-ph' );
                        GMW_Import_Locations.existing_ph = GMW_Import_Locations.import_details.find( '.existing-ph' );
                        GMW_Import_Locations.failed_ph   = GMW_Import_Locations.import_details.find( '.failed-ph' );
                    
                        //get the child class name
                        importAction = GMW_Import_Locations.form.find( '.gmw_locations_importer_action' ).val();

                        // run the importer
                        GMW_Import_Locations.import_data( importAction, 0, 0, 0, 0, 0, 0, 0 );
                    } );

                //prevent multiple importer running the same time
                } else {
                    alert( 'There is already another importer running. Please wait for the current importer to finish before starting another one.' );
                }
            });

            // abort function
            this.abort();
        },

        /**
         * Process importer
         * @param  {string} importAction      name of child class that extened GMW_Batch_Locations_Import
         * @param  {absint} recordsCompleted  records completed so far
         * @param  {absint} locationsUpdated  number of existing locations updated
         * @param  {absint} locationsImported number of locations imported
         * @param  {absint} locationsExist    number of locations already exist and were not imported
         * @param  {absint} locationsFailed   number of locations failed and were not imported
         * @param  {absint} totalLocations    total locations need to be imported
         * @param  {absint} batchNumber       batch number
         * @return {void}                
         */
        import_data : function( importAction, recordsCompleted, locationsUpdated, locationsImported, locationsExist, locationsFailed, totalLocations, batchNumber ) {

            //set processing to true
            GMW_Import_Locations.processing = true;

            // show abort button
            GMW_Import_Locations.form.find( '.gmw-locations-importer-abort' ).fadeIn( '300' );

            // get nonce value
            nonce = GMW_Import_Locations.form.find( '.gmw_locations_importer_nonce' ).val();

            // do ajax importing
            GMW_Import_Locations.importer = $.ajax({
                type     : 'POST',
                url      : gmwVars.ajaxUrl,
                dataType : 'json',
                data     : {
                    action            : 'gmw_locations_importer',
                    importAction      : importAction,
                    recordsCompleted  : recordsCompleted,
                    locationsUpdated  : locationsUpdated,
                    locationsImported : locationsImported,
                    locationsExist    : locationsExist,
                    locationsFailed   : locationsFailed,
                    totalLocations    : totalLocations,
                    batchNumber       : 0,
                    security          : nonce
                },
                success: function( response ) {

                    // show importing text
                    GMW_Import_Locations.action_ph.html( 'Importing...' );

                    // show total records processed
                    GMW_Import_Locations.form.find( '.completed-ph').html( response.records_completed );
                    GMW_Import_Locations.form.find( '.total-ph').html( response.total_locations );

                    // number of locations updated message
                    GMW_Import_Locations.updated_ph.html( response.locations_updated );

                    // number of locations imported message
                    GMW_Import_Locations.imported_ph.html( response.locations_imported );

                    // number of existing locations
                    GMW_Import_Locations.existing_ph.html( response.locations_exist );

                    // number of failed locations
                    GMW_Import_Locations.failed_ph.html( response.locations_failed );
                        
                    //if importer is done
                    if ( response.done ) {

                        //if no records were found
                        if ( response.total_locations == 0 ) {

                            // show no locations found message
                            GMW_Import_Locations.action_ph.html( 'No locations were found!' );

                            recordsFound = false; 

                        // if records completed
                        } else {

                            // complete prograss bar
                            GMW_Import_Locations.form.find( '.gmw-importer-progress-bar div' ).removeClass( 'importing' ).addClass( 'completed' );

                            // show importing completed message
                            GMW_Import_Locations.action_ph.html( 'Importing Completed!' ); 

                            recordsFound = true;
                        }

                        // hide spinner
                        GMW_Import_Locations.form.find( '.gmw-importer-spinner').hide();

                        // set processing to false
                        GMW_Import_Locations.processing = false;

                        // enabled submit buttons
                        $( '.gmw-locations-importer-submit' ).removeAttr( 'disabled' );

                        // hide abort button
                        GMW_Import_Locations.form.find( '.gmw-locations-importer-abort' ).fadeOut( '300' );

                        $( '.gmw-importer-details .done-message' ).fadeIn();

                        $.ajax( {
                            type     : 'POST',
                            url      : gmwVars.ajaxUrl,
                            dataType : 'json',
                            data     : {
                                action            : 'gmw_locations_importer_done',
                                importAction      : importAction,
                                recordsFound      : recordsFound,
                                recordsCompleted  : response.records_completed,
                                locationsUpdated  : response.locations_updated,
                                locationsImported : response.locations_imported,
                                locationsExist    : response.locations_exist,
                                locationsFailed   : response.locations_failed,
                                totalLocations    : response.total_locations,
                                security          : nonce
                            }
                        } );

                    //Otherwise go get more records
                    } else {

                        //process again with new records
                        GMW_Import_Locations.import_data( 
                            importAction, 
                            parseInt( response.records_completed ), 
                            parseInt( response.locations_updated ),
                            parseInt( response.locations_imported ), 
                            parseInt( response.locations_exist ), 
                            parseInt( response.locations_failed ), 
                            parseInt( response.total_locations ), 
                            parseInt( response.batch_number ) 
                        );
                    }

                    //animate progress bar
                    GMW_Import_Locations.form.find( '.gmw-importer-progress-bar div' ).animate( {
                        width: response.percentage + '%',
                    }, 50 );
                }

            // if importer failed or aborted by user
            }).fail( function ( response ) {    

                //display messages in console
                if ( window.console && window.console.log ) {

                    if ( response.responseText ) {
                        console.log( response.responseText );
                    }

                    console.log( response );
                }

                //if import aborted
                if ( response.statusText != 'undefined' && response.statusText == 'abort' ) {

                    message = 'Import canceled!';
                
                //othrewise show error message
                } else {

                    message = 'The importer could not proceed due to an error.'   
                }

                // add error class
                GMW_Import_Locations.action_ph.addClass( 'error' );

                // show error mesage
                GMW_Import_Locations.action_ph.html( message );

                // hide spinner
                GMW_Import_Locations.form.find( '.gmw-importer-spinner').hide();

                // set processing to false
                GMW_Import_Locations.processing = false;    

                // enable submit buttons
                $( '.gmw-locations-importer-submit' ).removeAttr( 'disabled' );

                // hide abort button
                GMW_Import_Locations.form.find( '.gmw-locations-importer-abort' ).fadeOut( '300' );

            });

            //do something when ajax batch is done
            GMW_Import_Locations.importer.done( function ( response ) {

                //show locations log in console
                console.log( response.log );
            });  
        },

        /**
         * Abort importer
         * @return {[type]} [description]
         */
        abort : function() {

            //abort Importer on canceled
            $( document ).on( 'click', '.gmw-locations-importer-abort', function () {
                if ( confirm( 'Are you sure? ' ) ) {
                    GMW_Import_Locations.importer.abort();
                } 
            });
        }
    };

    GMW_Import_Locations.init();
});
