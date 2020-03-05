/**
 * Geocoding providers.
 *
 * Hook custom geocoders into GMW_Geocoders.
 * 
 * @type {Object}
 */
var GMW_Geocoders = {

	/**
	 * Default options.
	 * 
	 * @type {Object}
	 */
	'options' : {
		//query 	   : '',
		'region'   	     : gmwVars.settings.general.country_code || 'us',
		'language'	     : gmwVars.settings.general.language_code || 'en',
		'suggestResults' : false,
		'limit' 	     : 10,
	},

	/**
	 * Default location fields to output.
	 * 
	 * @type {Object}
	 */
	'locationFields' : {
		'latitude' 			: '',
		'longitude' 		: '',
		'lat' 				: '',
		'lng' 				: '',
		'street_number' 	: '',
		'street_name' 		: '',
		'street' 			: '',
		'premise' 			: '',
		'neighborhood'   	: '',
		'city'           	: '',
		'county'         	: '',
		'region_name'    	: '',
		'region_code'    	: '',
		'postcode'       	: '',
		'country_name'   	: '',
		'country_code'   	: '',
		'address'           : '',
		'formatted_address' : '',
		'place_id'          : '',
		'level'				: ''
	},

	/**
	 * Nominatim geocoder ( Open Street Maps ).
	 * 
	 * @return {[type]} [description]
	 */
	'google_maps' : {

		// default options
		'options' : {
			//'language' : 'en',
			//'region'   : 'us'
			//'bounds' : // The bounding box of the viewport within which to bias geocode results more prominently.
		},

		// default location fields to return.
		'locationFields' : {},

		// Get results
		get : function( type, options, success_callback, failure_callback ) {

			var self 	 = this,
				geocoder = new google.maps.Geocoder(),
				params   = {
					'region'   : options.region,
	        		'language' : options.language
	        	};

			if ( type == 'reverseGeocode' ) {
			
				params.latLng = new google.maps.LatLng( options.q[0], options.q[1] );

			} else {

				self.defaultFields.address = options.q;
				params.address = options.q;
			}

			// get result from Nominatim.
			geocoder.geocode( params, function( data, status ) {

				self.response.data = data;

				// abort if geocoder failed.
				if ( status !== google.maps.GeocoderStatus.OK ) {
					
					self.geocodeFailed( status, failure_callback );	

					return;
				}

				if ( type == 'reverseGeocode' ) {

					// We don't want "PROXIMATE" results. 
					// It's either what the user enters or nothing at all.
					if ( data[0].geometry.location_type == 'APPROXIMATE' ) {

						self.response.data = data[0] = [];

						console.log( 'No results - Approximate.');

						return self.geocodeFailed( 'ZERO_RESULTS', failure_callback );	
					}

					return self.geocodeSuccess( data[0], success_callback );
		
				} else { 
				
					return self.geocodeSuccess( data[0], success_callback );
				}
			});
		}, 

		/**
		 * Collect location data into an object.
		 *
		 * @param  {[type]} result [description]
		 * @return {[type]}        [description]
		 */
		getLocationFields : function( result ) {
						
			var fields = {};
			var ac     = result.address_components;
			var pid    = typeof result.place_id !== 'undefined' ? result.place_id : '';
	    	
	    	fields.place_id          = pid;
	    	fields.formatted_address = result.formatted_address;
	    	fields.lat               = fields.latitude  = result.geometry.location.lat();
	    	fields.lng               = fields.longitude = result.geometry.location.lng();

	    	// ac ( address_component ): complete location data object
	    	// ac[x]: each location field in the address component
			for ( var x in ac ) {

				if ( ac[x].types == 'street_number' && ac[x].long_name != undefined ) {
					fields.street_number = ac[x].long_name;
				}
				
				if ( ac[x].types == 'route' && ac[x].long_name != undefined ) {	
					fields.street_name = ac[x].long_name;
					fields.street 	   = fields.street_number + ' ' + fields.street_name; 
				}

				if ( ac[x].types == 'subpremise' && ac[x].long_name != undefined ) {
					fields.premise = ac[x].long_name;
				}
				
				if ( ac[x].types == 'neighborhood,political' && ac[x].long_name != undefined ) {
				 	fields.neighborhood = ac[x].long_name;
				}
	 
		        if ( ac[x].types == 'locality,political' && ac[x].long_name != undefined ) {
		        	fields.city = ac[x].long_name;
				} else if ( ac[x].types == 'postal_town' && ac[x].long_name != undefined ) {
					fields.city = ac[x].long_name;
				}
		        
		        if ( ac[x].types == 'administrative_area_level_1,political' ) {
		        	fields.region_name = ac[x].long_name;
		          	fields.region_code = ac[x].short_name;
		        }  
		       
	  			if ( ac[x].types == 'administrative_area_level_2,political' && ac[x].long_name != undefined ) {
	  				fields.county = ac[x].long_name;
				}

		        if ( ac[x].types == 'postal_code' && ac[x].long_name != undefined ) {
		        	fields.postcode = ac[x].long_name;
				}
		        
		        if ( ac[x].types == 'country,political' ) {
		        	fields.country_name = ac[x].long_name;		        
		          	fields.country_code = ac[x].short_name;
		        } 
	        }

			return fields;
		}
	},

	/**
	 * Nominatim geocoder ( Open Street Maps ).
	 * 
	 * @return {[type]} [description]
	 */
	'nominatim' : {

		// Rest URL
		'geocodeUrl' : 'https://nominatim.openstreetmap.org/search',

		'reverseGeocodeUrl' : 'https://nominatim.openstreetmap.org/reverse',

		// default options
		'options' : {
			'format' 		       : 'jsonv2', // [html|xml|json|jsonv2] Output format.
			'addressdetails'       : '1', 	// Include a breakdown of the address into elements
			'accept-language'      : 'en', // Preferred language order for showing search results.
			'zoom'			       : '18',
			'email'			  	   : gmwVars.settings.api.nominatim_email || '',
			'region'		  	   : 'us',
			'limit'			   	   : 10    // Limit the number of returned results. Default is 10.
			//'email'			   : <valid email address> // If you are making large numbers of request please include a valid email address
			//'countrycodes' 	   : GMW_Geocoder.region,
			//'suggestResults' : true,
			//'json_callback'	   : <string> // Wrap json output in a callback function (JSONP).
			//'q'				   : <query> // query string to search for.
			//'viewbox'			   : <x1>,<y1>,<x2>,<y2> // The preferred area to find search results.
			//'bounded'			   : [0|1] // Restrict the results to only items contained with the viewbox.
			//'exclude_place_ids'  : <place_id,[place_id],[place_id]> // If you do not want certain openstreetmap objects to appear in the search result.
			
			//'dedupe'			   : [0|1]
			//'polygon_geojson'    : 1 // Output geometry of results in geojson format.
			//'polygon_kml'		   : 1 // Output geometry of results in kml format.
			//'polygon_svg'		   : 1 // Output geometry of results in svg format.
			//'polygon_text'	   : 1 // Output geometry of results as a WKT.
			//'extratags'		   : 1 // Include additional information in the result if available, e.g. wikipedia link, opening hours.
			//'namedetails'		   : 1 // Include a list of alternative names in the results.
		},

		// default location fields to return.
		'locationFields' : {
			'place_id'          : 'place_id',
			'formatted_address' : 'display_name',
			'lat' 				: 'lat',
			'lng' 				: 'lon',
			'street_number' 	: 'address.house_number',
			'street_name' 		: 'address.road',
			'city'           	: [ 'address.city', 'address.town', 'address.suburb' ],
			'county'         	: 'address.county',
			'region_name'    	: 'address.state',
			'postcode'       	: 'address.postcode',
			'country_name'   	: 'address.country',
			'country_code'   	: 'address.country_code',
		},

		// Initialize.
		initialize : function() {
			this.options['accept-language'] = this.options.language;
		},

		// Get results
		get : function( type, options, success_callback, failure_callback ) {

			var self = this,
				search = options.q,
				params,
				query;

			// remove q from options
			delete options.q;

			params = jQuery.param( options );
				
			if ( type == 'reverseGeocode' ) {
				
				query = this.reverseGeocodeUrl + '?lat=' + search[0] + '&lon=' + search[1] + '&' + params;
			
			} else {

				self.defaultFields.address = search;
				query = this.geocodeUrl + '?q=' + search + '&' + params;
			}
				
			// get result from Nominatim.
			self.jqXHR = jQuery.getJSON( query, function( data, e ) {

				self.response.data = data;

				if ( typeof( data.error ) !== 'undefined' ) {
				
					return self.geocodeFailed( data.error, failure_callback );	
				
				} else if ( type == 'reverseGeocode' ) {

					return self.geocodeSuccess( data, success_callback );

				// if no results.
				} else if ( data.length == 0 ) {

					return self.geocodeFailed( 'No results found.', failure_callback );
		
				// Create suggested results dropdown when there are multiple results and feature is enabled.
				} else if ( options.suggestResults && data.length > 1 ) {

					return self.suggestResults( data, 'display_name', success_callback );

				} else {

					// if there are multiple locations we try to get the default location based on the region.
					if ( data.length > 1 ) {
						
						for ( var t in data ) {
							
							if ( typeof( data[t].address.country_code ) !== 'undefined' && data[t].address.country_code == options.region ) {
								
								// if location found use it and break the loop.
								self.geocodeSuccess( data[t], success_callback );

								return;
							}
						}
					}
					
					return self.geocodeSuccess( data[0], success_callback );
				}

			}).fail( function( jqXHR, textStatus, errorThrown ) { 
				self.geocodeFailed( textStatus + ' ' + errorThrown, failure_callback );
			});
		}
	},

	'regions' : {
	    "AL": "Alabama",
	    "AK": "Alaska",
	    "AS": "American Samoa",
	    "AZ": "Arizona",
	    "AR": "Arkansas",
	    "CA": "California",
	    "CO": "Colorado",
	    "CT": "Connecticut",
	    "DE": "Delaware",
	    "DC": "District Of Columbia",
	    "FM": "Federated States Of Micronesia",
	    "FL": "Florida",
	    "GA": "Georgia",
	    "GU": "Guam",
	    "HI": "Hawaii",
	    "ID": "Idaho",
	    "IL": "Illinois",
	    "IN": "Indiana",
	    "IA": "Iowa",
	    "KS": "Kansas",
	    "KY": "Kentucky",
	    "LA": "Louisiana",
	    "ME": "Maine",
	    "MH": "Marshall Islands",
	    "MD": "Maryland",
	    "MA": "Massachusetts",
	    "MI": "Michigan",
	    "MN": "Minnesota",
	    "MS": "Mississippi",
	    "MO": "Missouri",
	    "MT": "Montana",
	    "NE": "Nebraska",
	    "NV": "Nevada",
	    "NH": "New Hampshire",
	    "NJ": "New Jersey",
	    "NM": "New Mexico",
	    "NY": "New York",
	    "NC": "North Carolina",
	    "ND": "North Dakota",
	    "MP": "Northern Mariana Islands",
	    "OH": "Ohio",
	    "OK": "Oklahoma",
	    "OR": "Oregon",
	    "PW": "Palau",
	    "PA": "Pennsylvania",
	    "PR": "Puerto Rico",
	    "RI": "Rhode Island",
	    "SC": "South Carolina",
	    "SD": "South Dakota",
	    "TN": "Tennessee",
	    "TX": "Texas",
	    "UT": "Utah",
	    "VT": "Vermont",
	    "VI": "Virgin Islands",
	    "VA": "Virginia",
	    "WA": "Washington",
	    "WV": "West Virginia",
	    "WI": "Wisconsin",
	    "WY": "Wyoming"
	 },
	'countries' : {
		"AF": "Afghanistan",
		"AX": "Åland Islands",
		"AL": "Albania",
		"DZ": "Algeria",
		"AS": "American Samoa",
		"AD": "AndorrA",
		"AO": "Angola",
		"AI": "Anguilla",
		"AQ": "Antarctica",
		"AG": "Antigua and Barbuda",
		"AR": "Argentina",
		"AM": "Armenia",
		"AW": "Aruba",
		"AU": "Australia",
		"AT": "Austria",
		"AZ": "Azerbaijan",
		"BS": "Bahamas",
		"BH": "Bahrain",
		"BD": "Bangladesh",
		"BB": "Barbados",
		"BY": "Belarus",
		"BE": "Belgium",
		"BZ": "Belize",
		"BJ": "Benin",
		"BM": "Bermuda",
		"BT": "Bhutan",
		"BO": "Bolivia",
		"BA": "Bosnia and Herzegovina",
		"BW": "Botswana",
		"BV": "Bouvet Island",
		"BR": "Brazil",
		"IO": "British Indian Ocean Territory",
		"BN": "Brunei Darussalam",
		"BG": "Bulgaria",
		"BF": "Burkina Faso",
		"BI": "Burundi",
		"KH": "Cambodia",
		"CM": "Cameroon",
		"CA": "Canada",
		"CV": "Cape Verde",
		"KY": "Cayman Islands",
		"CF": "Central African Republic",
		"TD": "Chad",
		"CL": "Chile",
		"CN": "China",
		"CX": "Christmas Island",
		"CC": "Cocos (Keeling) Islands",
		"CO": "Colombia",
		"KM": "Comoros",
		"CG": "Congo",
		"CD": "Congo, Democratic Republic",
		"CK": "Cook Islands",
		"CR": "Costa Rica",
		"CI": "Cote D\"Ivoire",
		"HR": "Croatia",
		"CU": "Cuba",
		"CY": "Cyprus",
		"CZ": "Czech Republic",
		"DK": "Denmark",
		"DJ": "Djibouti",
		"DM": "Dominica",
		"DO": "Dominican Republic",
		"EC": "Ecuador",
		"EG": "Egypt",
		"SV": "El Salvador",
		"GQ": "Equatorial Guinea",
		"ER": "Eritrea",
		"EE": "Estonia",
		"ET": "Ethiopia",
		"FK": "Falkland Islands (Malvinas)",
		"FO": "Faroe Islands",
		"FJ": "Fiji",
		"FI": "Finland",
		"FR": "France",
		"GF": "French Guiana",
		"PF": "French Polynesia",
		"TF": "French Southern Territories",
		"GA": "Gabon",
		"GM": "Gambia",
		"GE": "Georgia",
		"DE": "Germany",
		"GH": "Ghana",
		"GI": "Gibraltar",
		"GR": "Greece",
		"GL": "Greenland",
		"GD": "Grenada",
		"GP": "Guadeloupe",
		"GU": "Guam",
		"GT": "Guatemala",
		"GG": "Guernsey",
		"GN": "Guinea",
		"GW": "Guinea-Bissau",
		"GY": "Guyana",
		"HT": "Haiti",
		"HM": "Heard Island and Mcdonald Islands",
		"VA": "Holy See (Vatican City State)",
		"HN": "Honduras",
		"HK": "Hong Kong",
		"HU": "Hungary",
		"IS": "Iceland",
		"IN": "India",
		"ID": "Indonesia",
		"IR": "Iran",
		"IQ": "Iraq",
		"IE": "Ireland",
		"IM": "Isle of Man",
		"IL": "Israel",
		"IT": "Italy",
		"JM": "Jamaica",
		"JP": "Japan",
		"JE": "Jersey",
		"JO": "Jordan",
		"KZ": "Kazakhstan",
		"KE": "Kenya",
		"KI": "Kiribati",
		"KP": "Korea (North)",
		"KR": "Korea (South)",
		"XK": "Kosovo",
		"KW": "Kuwait",
		"KG": "Kyrgyzstan",
		"LA": "Laos",
		"LV": "Latvia",
		"LB": "Lebanon",
		"LS": "Lesotho",
		"LR": "Liberia",
		"LY": "Libyan Arab Jamahiriya",
		"LI": "Liechtenstein",
		"LT": "Lithuania",
		"LU": "Luxembourg",
		"MO": "Macao",
		"MK": "Macedonia",
		"MG": "Madagascar",
		"MW": "Malawi",
		"MY": "Malaysia",
		"MV": "Maldives",
		"ML": "Mali",
		"MT": "Malta",
		"MH": "Marshall Islands",
		"MQ": "Martinique",
		"MR": "Mauritania",
		"MU": "Mauritius",
		"YT": "Mayotte",
		"MX": "Mexico",
		"FM": "Micronesia",
		"MD": "Moldova",
		"MC": "Monaco",
		"MN": "Mongolia",
		"MS": "Montserrat",
		"MA": "Morocco",
		"MZ": "Mozambique",
		"MM": "Myanmar",
		"NA": "Namibia",
		"NR": "Nauru",
		"NP": "Nepal",
		"NL": "Netherlands",
		"AN": "Netherlands Antilles",
		"NC": "New Caledonia",
		"NZ": "New Zealand",
		"NI": "Nicaragua",
		"NE": "Niger",
		"NG": "Nigeria",
		"NU": "Niue",
		"NF": "Norfolk Island",
		"MP": "Northern Mariana Islands",
		"NO": "Norway",
		"OM": "Oman",
		"PK": "Pakistan",
		"PW": "Palau",
		"PS": "Palestinian Territory, Occupied",
		"PA": "Panama",
		"PG": "Papua New Guinea",
		"PY": "Paraguay",
		"PE": "Peru",
		"PH": "Philippines",
		"PN": "Pitcairn",
		"PL": "Poland",
		"PT": "Portugal",
		"PR": "Puerto Rico",
		"QA": "Qatar",
		"RE": "Reunion",
		"RO": "Romania",
		"RU": "Russian Federation",
		"RW": "Rwanda",
		"SH": "Saint Helena",
		"KN": "Saint Kitts and Nevis",
		"LC": "Saint Lucia",
		"PM": "Saint Pierre and Miquelon",
		"VC": "Saint Vincent and the Grenadines",
		"WS": "Samoa",
		"SM": "San Marino",
		"ST": "Sao Tome and Principe",
		"SA": "Saudi Arabia",
		"SN": "Senegal",
		"RS": "Serbia",
		"ME": "Montenegro",
		"SC": "Seychelles",
		"SL": "Sierra Leone",
		"SG": "Singapore",
		"SK": "Slovakia",
		"SI": "Slovenia",
		"SB": "Solomon Islands",
		"SO": "Somalia",
		"ZA": "South Africa",
		"GS": "South Georgia and the South Sandwich Islands",
		"ES": "Spain",
		"LK": "Sri Lanka",
		"SD": "Sudan",
		"SR": "Suriname",
		"SJ": "Svalbard and Jan Mayen",
		"SZ": "Swaziland",
		"SE": "Sweden",
		"CH": "Switzerland",
		"SY": "Syrian Arab Republic",
		"TW": "Taiwan, Province of China",
		"TJ": "Tajikistan",
		"TZ": "Tanzania",
		"TH": "Thailand",
		"TL": "Timor-Leste",
		"TG": "Togo",
		"TK": "Tokelau",
		"TO": "Tonga",
		"TT": "Trinidad and Tobago",
		"TN": "Tunisia",
		"TR": "Turkey",
		"TM": "Turkmenistan",
		"TC": "Turks and Caicos Islands",
		"TV": "Tuvalu",
		"UG": "Uganda",
		"UA": "Ukraine",
		"AE": "United Arab Emirates",
		"GB": "United Kingdom",
		"US": "United States",
		"UM": "United States Minor Outlying Islands",
		"UY": "Uruguay",
		"UZ": "Uzbekistan",
		"VU": "Vanuatu",
		"VE": "Venezuela",
		"VN": "Viet Nam",
		"VG": "Virgin Islands, British",
		"VI": "Virgin Islands, U.S.",
		"WF": "Wallis and Futuna",
		"EH": "Western Sahara",
		"YE": "Yemen",
		"ZM": "Zambia",
		"ZW": "Zimbabwe"
	}
};

/**
 * Base Geocoder class.
 * 
 * @param {[type]} provider [description]
 */
function GMW_Geocoder( provider, inputField ) {

	// Provider Name.
	this.provider = provider || 'nominatim';

	this.inputField = inputField || false;

	// Extend this class with the geocoder functions.
	jQuery.extend( this, GMW_Geocoders[ this.provider ] );

	// set default options.
	this.options = jQuery.extend( GMW_Geocoders.options, this.options );
	
	this.response.provider = this.provider;

	// Can be used in child class to execute some function on init.
	this.initialize();	
}

// Ghost geocoder function.
function gmw_geocoder( provider ) {
	return new GMW_Geocoder( provider );
}

/**
 * Default options.
 * 
 * @type {Object}
 */
GMW_Geocoder.prototype.options = {};

/**
 * Default output location fields.
 * 
 * @type {Object}
 */
GMW_Geocoder.prototype.defaultFields = GMW_Geocoders.locationFields;

/**
 * Response from geocoder provider.
 * 
 * @type {Object}
 */
GMW_Geocoder.prototype.jqXHR = {};

/**
 * This geocoder repose.
 * 
 * @type {Object}
 */
GMW_Geocoder.prototype.response = {
	provider : '',
	type     : '',
	status   : '',
	data     : {},
	result   : {}
};

/**
 * Initial function. 
 * 
 * Can be used with child class to execute function when class initiate.
 * 
 * @return {[type]} [description]
 */
GMW_Geocoder.prototype.initialize = function() {};

/**
 * Set default options.
 * 
 * @param {[type]} options [description]
 */
GMW_Geocoder.prototype.setOptions = function( options ) {
	jQuery.extend( this.options, options );
};

/**
 * Geocode.
 * 
 * @param  {[type]} options          [description]
 * @param  {[type]} success_callback [description]
 * @param  {[type]} failure_callback [description]
 * @return {[type]}                  [description]
 */
GMW_Geocoder.prototype.geocode = function( options, success_callback, failure_callback ) {
	
	this.response.type = 'geocode';

	this.setOptions( options );
	
	// if failure callback function was not provided we will use the success callback instead.
	if ( typeof( failure_callback ) === 'undefined' ) {
		failure_callback = success_callback;
	}

	this.get( 'geocode', this.options, success_callback, failure_callback );
};

/**
 * Reverse Geocode.
 * 
 * @param  {[type]} options          [description]
 * @param  {[type]} success_callback [description]
 * @param  {[type]} failure_callback [description]
 * @return {[type]}                  [description]
 */
GMW_Geocoder.prototype.reverseGeocode = function( options, success_callback, failure_callback ) {
	
	this.response.type = 'reverseGeocode';

	this.setOptions( options );

	// if failure callback function was not provided we will use the success callback instead.
	if ( typeof( failure_callback ) === 'undefined' ) {
		failure_callback = success_callback;
	}

	this.get( 'reverseGeocode', this.options, success_callback, failure_callback );
};

/**
 * Search. 
 *
 * Will usually be same as geocode function.
 * 
 * @param  {[type]} address          [description]
 * @param  {[type]} success_callback [description]
 * @param  {[type]} failure_callback [description]
 * @return {[type]}                  [description]
 */
GMW_Geocoder.prototype.search = function( options, success_callback, failure_callback ) {
	
	this.response.type = 'search';

	this.setOptions( options );

	// if failure callback function was not provided we will use the success callback instead.
	if ( typeof( failure_callback ) === 'undefined' ) {
		failure_callback = success_callback;
	}

	this.get( 'search', this.options, success_callback, failure_callback );
};

/*
 * Get results from geocoder.
 * 
 * Override this function with geocoder child class.
 */
GMW_Geocoder.prototype.get = function() {};

/**
 * Geocode failed function.
 *
 * When geocoder fails or returns no results.
 * 
 * @param  {string}   status           failed/error message.
 * @param  {function} success_callback [description]
 * @param  {function} failure_callback [description]
 * 
 * @return {[type]}                  [description]
 */
GMW_Geocoder.prototype.geocodeFailed = function( status, failure_callback ) {

	this.response.status = status;

	console.log( 'Request failed. ' + this.response.status ); 
	console.log( this.jqXHR );

	failure_callback( this.response, status );
};

/**
 * Display list of suggested results.
 * 
 * @param  {object}   data             results from geocoder.
 * @param  {string}   formatted        the formatted address variable name.
 * @param  {function} success_callback the callback function.
 * 
 * @return void
 */
GMW_Geocoder.prototype.suggestResults = function( results, formatted, success_callback ) {

	var self  = this,
		parts = '';

	for ( var i in results ) {
		parts += '<li data-value="'+ i +'">' + results[i][formatted] + '</li>';
	}

	jQuery( '<div class="gmw-geocoder-suggested-results-wrapper"><ul class="gmw-geocoder-suggested-results">' + parts + '</ul></div>' ).appendTo( 'body' ).find( 'li' ).on( 'click', function() {
		self.geocodeSuccess( results[ jQuery( this ).data( 'value' ) ], success_callback );
		jQuery( this ).closest( '.gmw-geocoder-suggested-results-wrapper' ).fadeOut().remove();
	});
};

/**
 * Get single location field value.
 * 
 * @param  {[type]} result    [description]
 * @param  {[type]} fieldName [description]
 * @return {[type]}           [description]
 */
GMW_Geocoder.prototype.getLocationFieldValue = function( result, fieldName ) {

	// When the field name is in sub object. 
	if ( fieldName.indexOf( '.' ) > -1 ) {

	  	return GMW.get_field_by_string( result, fieldName );
	
	} else if ( typeof( result[fieldName] ) !== 'undefined' ) {

		return result[fieldName];
	
	} else {

		return '';
	}
};

/**
 * Get all the location fields.
 * 
 * @param  {[type]} result           [description]
 * @param  {[type]} success_callback [description]
 * @param  {[type]} failure_callback [description]
 * @return {[type]}                  [description]
 */
GMW_Geocoder.prototype.getLocationFields = function( result ) {
	
	var self   	   = this,
		fields 	   = this.defaultFields,
		fieldCount = Object.keys( this.locationFields ).length,
		count 	   = 0;

	// Loop through and look for the location fields in the result object.
	jQuery.each( this.locationFields, function( fieldType, fieldName ) {
		
		// When field name is an array, we can provide a few possible 
		// options where the value could be found.
		// For example, the value of city can sometimes be as city, town, suburb and so on.
		if ( jQuery.isArray( fieldName ) ) {	
			
			// loop through all possible options
			for ( var i = 0, n = fieldName.length; i < n + 1; ++i ) {
	
				if ( i < n ) {

					// look for field value.
					fields[fieldType] = self.getLocationFieldValue( result, fieldName[i] );

					// add cont and abort the loop once the value was found.
					if ( fields[fieldType] != '' ) {
						count++;
						return;
					} 

				// Add count when done and no value found.
				} else {
					count++;
				}
			}

		// get value when a single field.
		} else {
			fields[fieldType] = self.getLocationFieldValue( result, fieldName );
			count++;
		}

		// Proceed when done looping through all fields.
		// We do this count to make sure the plugin is gone through all fields
		// before finalizing the returning the result.
		if ( count == fieldCount ) {
			
			// combine street name and number into a single street field.
			fields.street = fields.street_number + ' ' + fields.street_name;

			// Create latitude,longitude duplicate to lat, lng for Backward compatibility.
			// Also parseFloat the field.
			fields.latitude  = fields.lat = parseFloat( fields.lat );
			fields.longitude = fields.lng = parseFloat( fields.lng );
						
			return fields;
		} 
	});
};

/**
 * Get geocoding level.
 * 
 * @param  {[type]} result [description]
 * @return {[type]}        [description]
 */
GMW_Geocoder.prototype.getGeocodingLevel = function( result ) {

	if ( result.street_name.trim() ) {

		return 'street';

	} else if ( result.city.trim() ) {

		return 'city';

	} else if ( result.postcode.trim() ) {

		return 'postcode';

	} else if ( result.region_name.trim() || result.region_code.trim() ) {

		return 'region';

	} else if ( result.country_name.trim() || result.country_code.trim() ) {

		return 'country';
	
	} else {
		return '';
	}
};

/**
 * Get missing country/region name/code.
 * 
 * @param  {[type]} result [description]
 * @return {[type]}        [description]
 */
GMW_Geocoder.prototype.getMissingData = function( result ) {

	var self = this;

	// Get region name if missing after geocoding.
	if ( result.region_name.trim() == '' && result.region_code.trim() != '' && GMW_Geocoders.regions[ result.region_code ] ) {
		result.region_name = GMW_Geocoders.regions[ result.region_code ];
	}

	// Get region code if missing.
	if ( result.region_code.trim() == '' && result.region_name.trim() != '' ) {

		jQuery.each( GMW_Geocoders.regions, function( regionCode, regionName ) {
			
			if ( result.region_name == regionName ) {
				
				result.region_code = regionCode;

				return false;
			}
		} );
	}

	// Get country name if missing.
	if ( result.country_name.trim() == '' && result.country_code.trim() != '' && GMW_Geocoders.countries[ result.country_code ] ) {
		result.country_name = GMW_Geocoders.countries[ result.country_code ];
	}

	// Get country code if missing.
	if ( result.country_code.trim() == '' && result.country_name.trim() != '' ) {

		jQuery.each( GMW_Geocoders.countries, function( countryCode, countryName ) {
			
			if ( result.country_name == countryName ) {
				
				result.country_code = countryCode;
				
				return false;
			}
		} );
	}

	return result;
};

/**
 * Geocode success callback.
 * 
 * @param  {[type]} result           [description]
 * @param  {[type]} success_callback [description]
 * @return {[type]}                  [description]
 */
GMW_Geocoder.prototype.geocodeSuccess = function( result, success_callback ) {

	this.response.status = 'OK';

	// extend found fields with default fields to make sure the returned
	// object has all fields regardless if they have value or not.
	result = jQuery.extend( this.defaultFields, this.getLocationFields( result ) );

	// Change usa to US.
	if ( result.country_code.toLowerCase() == 'usa' ) {
		result.country_code = 'US';
	}

	// Keep country name same.
	if ( result.country_name.toLowerCase() == 'united states of america' ) {
		result.country_name = 'United States'; 
	}

	// Uppercase region and country codes.
	result.region_code = result.region_code.toUpperCase();
	result.country_code = result.country_code.toUpperCase();

	// get geocoding level.
	result.level = this.getGeocodingLevel( result );
	
	// Get missing region/country name/code.
	if ( result.country_code.trim() == '' || result.country_name.trim() == '' || result.region_name.trim() == '' || result.region_code.trim() == '' ) {
		result = this.getMissingData( result );
	}

	this.response.result = result;

	// Modify the result.
	this.response.result = GMW.apply_filters( 'gmw_geocoder_result_on_success', this.response.result, this.response );

	console.log( 'Geocoder results:' );
	console.log( this.response );

	success_callback( this.response, 'OK' ); 
};
