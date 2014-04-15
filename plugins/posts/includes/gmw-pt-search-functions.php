<?php

/**
 * PT Search form function - Posts, Pages post types dropdown.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_form_get_post_types_dropdown( $gmw, $title, $class, $all ) {

    if ( !isset( $gmw['search_form']['post_types'] ) || ( isset( $gmw['search_form']['post_types'] ) && count( $gmw['search_form']['post_types'] ) == 1 ) )
        return;

    if ( isset( $title ) && !empty( $title ) )
        echo '<label for="gmw-posts-dropdown-' . $gmw['ID'] . '">' . $title . '</label>';

    $output = '<select name="gmw_post" id="gmw-posts-dropdown-' . $gmw['ID'] . '" class="gmw-posts-dropdown gmw-posts-dropdown-' . $gmw['ID'] . ' ' . $class . '">';

    $output .= '<option value="' . implode( ' ', $gmw['search_form']['post_types'] ) . '">' . $all . '</option>';

    foreach ( $gmw['search_form']['post_types'] as $post_type ) :

        if ( isset( $_GET['gmw_post'] ) && $_GET['gmw_post'] == $post_type )
            $pti_post = 'selected="selected"';
        else
            $pti_post = "";
        $output .= '<option value="' . $post_type . '" ' . $pti_post . '>' . get_post_type_object( $post_type )->labels->name . '</option>';

    endforeach;

    $output .= '</select>';

    return apply_filters( 'gmw_form_post_types', $output, $gmw );

}

function gmw_pt_form_post_types_dropdown( $gmw, $title, $class, $all ) {
    echo gmw_pt_form_get_post_types_dropdown( $gmw, $title, $class, $all );

}

/**
 * PT search form function - Display taxonomies/categories
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_form_taxonomies( $gmw, $tag, $class ) {
	
	$tag	= ( isset( $tag ) && !empty( $tag ) ) ? $tag : 'div';
	$class  = ( isset( $class ) && !empty( $class ) ) ? $class : '';
	
    $taxonomies = apply_filters( 'gmw_search_form_taxonomies', false, $gmw, $tag, $class );

    if ( $taxonomies !== false )
        return;

    if ( count( $gmw['search_form']['post_types'] ) < 2 && ( isset( $gmw['search_form']['taxonomies'] ) ) ) :

    	
        $output = '';

		$orgTag = $tag;
		if ( $orgTag == 'ul' ) {
			echo '<ul>';
			$tag = 'li';
		} elseif ( $orgTag == 'ol') {
			echo '<ol>';
			$tag = 'li';
		}
        
        //output dropdown for each taxonomy 
        foreach ( $gmw['search_form']['taxonomies'] as $tax => $values ) :

            if ( isset( $values['style'] ) && $values['style'] == 'drop' ) :

                $get_tax = false;

                $output = '<'.$tag.' class="gmw-single-taxonomy-wrapper gmw-dropdown-taxonomy-wrapper gmw-dropdown-' . $tax . '-wrapper '.$class.'">';

                $output .= '<label for="' . get_taxonomy( $tax )->rewrite['slug'] . '">' . apply_filters( 'gmw_pt_' . $gmw['ID'] . '_' . $tax . '_label', get_taxonomy( $tax )->labels->singular_name, $tax, $gmw ) . ': </label>';

                if ( isset( $_GET['tax_' . $tax] ) )
                    $get_tax = $_GET['tax_' . $tax];

                $args = array(
                    'taxonomy'        => $tax,
                    'echo'            => false,
                    'hide_empty'      => 1,
                    'depth'           => 10,
                    'hierarchical'    => 1,
                    'show_count'      => 0,
                    'class'           => 'gmw-dropdown-' . $tax . ' gmw-dropdown-taxonomy',
                    'id'              => $tax . '-tax',
                    'name'            => 'tax_' . $tax,
                    'selected'        => $get_tax,
                    'show_option_all' => __( ' - All - ', 'GMW' ),
                );

                $args = apply_filters( 'gmw_pt_dropdown_taxonomy_args', $args, $gmw );
                $output .= wp_dropdown_categories( $args );

                $output .= '</'.$tag.'>';

                echo apply_filters( 'gmw_search_form_taxonomy', $output, $gmw, $args, $tag, $class );

            endif;

        endforeach;
		
		if ( $orgTag == 'ul' ) {
			echo '</ul>';
		} elseif ( $orgTag == 'ol') {
			echo '</ol>';
		}
        
    endif;

}

/**
 * GMW Search results function - Per page dropdown
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_get_per_page_dropdown( $gmw, $class ) {

    $perPage  = explode( ",", $gmw['search_results']['per_page'] );
    $lastpage = ceil( $gmw['total_results'] / $gmw['get_per_page'] );
    $per_page = '';
	
    if ( count( $perPage ) > 1 ) :

        $per_page = '<select name="gmw_per_page" id="gmw-pt-per-page-dropdown-' . $gmw['ID'] . '" class="gmw-pt-per-page-dropdown ' . $class . '">';

        foreach ( $perPage as $pp ) :

            if ( isset( $_GET['gmw_per_page'] ) && $_GET['gmw_per_page'] == $pp )
                $pp_s = 'selected="selected"';
            else
                $pp_s = "";
            $per_page .= '<option value="' . $pp . '" ' . $pp_s . '>' . $pp . ' ' . __( 'per page', 'GMW' ) . '</option>';

        endforeach;
		
        $per_page .= '</select>';
		
        $per_page .= '<script>
			jQuery(document).ready(function($) {
			   	$(".gmw-pt-per-page-dropdown").change(function() {
				   	var totalResults = '.$gmw["total_results"].'
			
				   	var lastPage = Math.ceil(totalResults/$(this).val());
				   	var newPaged = ('.$gmw["paged"].' > lastPage ) ? lastPage : '.$gmw["paged"].';
					
				   	if ( window.location.search.length ) {
				   		window.location.href = window.location.href.replace(/(gmw_per_page=).*?(&)/,"$1" + $(this).val() + "$2") + "&paged="+newPaged;
				   	} else {
				   		window.location.href = window.location.href + "?gmw=auto&gmw_per_page="+$(this).val() + "&gmw_form='.$gmw['ID'].'&paged="+newPaged;
				   	}
			    });
			});
	    </script>';
        
        

    endif;

    return apply_filters( 'gmw_pt_get_per_page', $per_page, $gmw );

}

function gmw_pt_per_page_dropdown( $gmw, $class ) {
    echo gmw_pt_get_per_page_dropdown( $gmw, $class );

}

/**
 * GMW PT results function - Paginations
 * @version 1.0
 * @author Eyal Fitoussi (original code was taken from http://www.awcore.com/dev/1/3/Create-Awesome-PHPMYSQL-Pagination_en )
 */
function gmw_pt_get_paginations( $gmw ) {

    $adjacents = "2";

    $lastpage = ceil( $gmw['total_results'] / $gmw['get_per_page'] );

    if ( $gmw['paged'] == 0 )
        $paged = 1;
    elseif ( $gmw['paged'] > $lastpage )
        $paged = $lastpage;
    else
        $paged = $gmw['paged'];

    $start = ($paged - 1) * $gmw['get_per_page'];

    $firstPage = 1;
    if ( isset( $page ) )
        $prev      = ($page == 1) ? 1 : $page - 1;

    $prev = $paged - 1;
    $next = $paged + 1;
    $lpm1 = $lastpage - 1;

    $gmw_pagi = "";

    if ( $lastpage > 1 ) {
        $gmw_pagi .= '<ul class="gmw-pagination gmw-prem-pagination gmw-pagination-' . $gmw['ID'] . '">';

        $gmw_pagi .= "<li class='details'> " . __( 'Page', 'GMW' ) . " " . $paged . " " . __( 'of', 'GMW' ) . " " . $lastpage . "</li>";

        if ( $paged == 1 ) {
            $gmw_pagi .= "<li><a class='current'>" . __( 'First', 'GMW' ) . "</a></li>";
            $gmw_pagi .= "<li><a class='current'>" . __( 'Prev', 'GMW' ) . "</a></li>";
        } else {
            $gmw_pagi .= "<li><a href='" . add_query_arg( array( 'paged' => $firstPage ) ) . "'>" . __( 'First', 'GMW' ) . "</a></li>";
            $gmw_pagi .= "<li><a href='" . add_query_arg( array( 'paged' => $prev ) ) . "'>" . __( 'Prev', 'GMW' ) . "</a></li>";
        }

        if ( $lastpage < 7 + ($adjacents * 2) ) {

            for ( $counter = 1; $counter <= $lastpage; $counter++ ) {
                if ( $counter == $paged )
                    $gmw_pagi.= "<li><a class='current'>$counter</a></li>";
                else
                    $gmw_pagi.= "<li><a href='" . add_query_arg( array( 'paged' => $counter ) ) . "'>$counter</a></li>";
            }
        } elseif ( $lastpage > 5 + ($adjacents * 2) ) {
            if ( $paged < 1 + ($adjacents * 2) ) {
                for ( $counter = 1; $counter < 4 + ($adjacents * 2); $counter++ ) {
                    if ( $counter == $paged )
                        $gmw_pagi.= "<li><a class='current'>$counter</a></li>";
                    else
                        $gmw_pagi.= "<li><a href='" . add_query_arg( array( 'paged' => $counter ) ) . "'>$counter</a></li>";
                }
                $gmw_pagi.= "<li class='dot'>...</li>";
                $gmw_pagi.= "<li><a href='" . add_query_arg( array( 'paged' => $lpm1 ) ) . "'>$lpm1</a></li>";
                $gmw_pagi.= "<li><a href='" . add_query_arg( array( 'paged' => $lastpage ) ) . "'>$lastpage</a></li>";
            } elseif ( $lastpage - ($adjacents * 2) > $paged && $paged > ($adjacents * 2) ) {
                $gmw_pagi.= "<li><a href='" . add_query_arg( array( 'paged' => 1 ) ) . "'>1</a></li>";
                $gmw_pagi.= "<li><a href='" . add_query_arg( array( 'paged' => 2 ) ) . "'>2</a></li>";
                $gmw_pagi.= "<li class='dot'>...</li>";

                for ( $counter = $paged - $adjacents; $counter <= $paged + $adjacents; $counter++ ) {
                    if ( $counter == $paged )
                        $gmw_pagi.= "<li><a class='current'>$counter</a></li>";
                    else
                        $gmw_pagi.= "<li><a href='" . add_query_arg( array( 'paged' => $counter ) ) . "'>$counter</a></li>";
                }
                $gmw_pagi.= "<li class='dot'>..</li>";
                $gmw_pagi.= "<li><a href='" . add_query_arg( array( 'paged' => $lpm1 ) ) . "'>$lpm1</a></li>";
                $gmw_pagi.= "<li><a href='" . add_query_arg( array( 'paged' => $lastpage ) ) . "'>$lastpage</a></li>";
            } else {
                $gmw_pagi.= "<li><a href='" . add_query_arg( array( 'paged' => 1 ) ) . "'>1</a></li>";
                $gmw_pagi.= "<li><a href='" . add_query_arg( array( 'paged' => 2 ) ) . "'>2</a></li>";
                $gmw_pagi.= "<li class='dot'>..</li>";

                for ( $counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++ ) {
                    if ( $counter == $paged )
                        $gmw_pagi.= "<li><a class='current'>$counter</a></li>";
                    else
                        $gmw_pagi.= "<li><a href='" . add_query_arg( array( 'paged' => $counter ) ) . "'>$counter</a></li>";
                }
            }
        }

        if ( $paged < $counter - 1 ) {
            $gmw_pagi.= "<li><a href='" . add_query_arg( array( 'paged' => $next ) ) . "'>" . __( 'Next', 'GMW' ) . "</a></li>";
            $gmw_pagi.= "<li><a href='" . add_query_arg( array( 'paged' => $lastpage ) ) . "'>" . __( 'Last', 'GMW' ) . "</a></li>";
        } else {
            $gmw_pagi.= "<li><a class='current'>" . __( 'Next', 'GMW' ) . "</a></li>";
            $gmw_pagi.= "<li><a class='current'>" . __( 'Last', 'GMW' ) . "</a></li>";
        }
        $gmw_pagi.= "</ul>\n";
    }
    return apply_filters( 'gmw_pt_get_pagination', $gmw_pagi, $gmw );

}

function gmw_pt_paginations( $gmw ) {
    echo gmw_pt_get_paginations( $gmw );

}

/**
 * PT Results function - Query taxonomies/categories dropdown
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_query_taxonomies( $tax_args, $gmw ) {

    if ( !isset( $gmw['search_form']['taxonomies'] ) || empty( $gmw['search_form']['taxonomies'] ) )
        return $tax_args;

    $ptc = ( isset( $_GET['gmw_post'] ) ) ? count( explode( " ", $_GET['gmw_post'] ) ) : count( $gmw['search_form']['post_types'] );
    if ( isset( $ptc ) && $ptc > 1 )
        return $tax_args;

    $rr      = 0;
    $get_tax = false;
    $args    = array( 'relation' => 'AND' );

    foreach ( $gmw['search_form']['taxonomies'] as $tax => $values ) :

        if ( $values['style'] == 'drop' ) :
            $get_tax = false;
            if ( isset( $_GET['tax_' . $tax] ) )
                $get_tax = $_GET['tax_' . $tax];

            if ( $get_tax != 0 ) :
                $rr++;
                $args[] = array(
                    'taxonomy' => $tax,
                    'field'    => 'id',
                    'terms'    => array( $get_tax )
                );
            endif;
        else :

        endif;

    endforeach;

    if ( $rr == 0 )
        $args = false;

    return $args;

}
add_filter( 'gmw_pt_tax_query', 'gmw_pt_query_taxonomies', 10, 2 );

/**
 * PT Results function - 'Within' message.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_within( $gmw, $sm, $om, $rm, $wm, $fm, $nm ) {

    if ( isset( $_GET['action'] ) && $_GET['action'] == 'gmw_post' )
        $nm = $gmw['org_address'];
    echo $sm . ' ' . $gmw['results_count'] . ' ' . $om . ' ' . $gmw['total_results'] . ' ' . $rm;
    if ( !empty( $gmw['org_address'] ) )
        echo ' ' . $wm . ' ' . $gmw['radius'] . ' ' . $gmw['units_array']['name'] . ' ' . $fm . ' ' . $nm;

}

/**
 * PT results function - thumbnail
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_thumbnail( $gmw, $post ) {

    if ( !isset( $gmw['search_results']['featured_image']['use'] ) )
        return;

    echo '<a href="' . get_permalink() . '" >' . get_the_post_thumbnail( $post->ID, array( $gmw['search_results']['featured_image']['width'], $gmw['search_results']['featured_image']['height'] ) ) . '</a>';

}

/**
 * PT results function - Display radius distance.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_by_radius( $gmw, $post ) {

    if ( isset( $gmw['your_lat'] ) && !empty( $gmw['your_lat'] ) )
        echo $post->distance . " " . $gmw['units_array']['name'];

}

/**
 * PT results function - Display taxonomies per result.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_get_taxonomies( $gmw, $post ) {

    if ( !isset( $gmw['search_results']['custom_taxes'] ) )
        return;
    
    if ( isset( $gmw['search_form']['taxonomies'] ) ) :

        $output = '';

        foreach ( $gmw['search_form']['taxonomies'] as $tax => $style ) :

            $terms = get_the_terms( $post->ID, $tax );

            if ( $terms && !is_wp_error( $terms ) ) :

                $termsArray = array();
            	$the_tax = get_taxonomy( $tax );
            	
                foreach ( $terms as $term ) {
                    $termsArray[] = $term->name;
                }

                //$taxTerms = '<span class="gmw-terms-wrapper gmw-'.$the_tax->rewrite['slug'].'-terms">' .join( ", ", $termsArray ).'</div>';
                //$taxTerms = apply_filters( 'gmw_pt_results_taxonomies_terms', $taxTerms , $gmw, $post, $the_tax, $termsArray, $terms );
                
                $taxonomy  = '<div class="gmw-taxes gmw-taxonomy-' . $the_tax->rewrite['slug'] . '">';
                $taxonomy .= 	'<span>' . $the_tax->labels->singular_name . ': </span>';
                $taxonomy .= 	'<span class="gmw-terms-wrapper gmw-'.$the_tax->rewrite['slug'].'-terms">'.join( ", ", $termsArray ).'</span>';
                $taxonomy .= '</div>';
                
                $output .= apply_filters( 'gmw_pt_results_taxonomy', $taxonomy, $gmw, $post, $the_tax, $terms, $termsArray );

            endif;

        endforeach;

        return $output;

    endif;

}

function gmw_pt_taxonomies( $gmw, $post ) {
    echo gmw_pt_get_taxonomies( $gmw, $post );

}

/**
 * PT results function - Day & Hours.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_days_hours( $post, $nr_message ) {

    $days_hours = get_post_meta( $post->ID, '_wppl_days_hours', true );
    $dc         = 0;

    if ( $days_hours ) {

        foreach ( $days_hours as $day ) {
            if ( !in_array( '', $day ) ) {
                $dc++;
                echo '<div class="single-days-hours"><span class="single-day">' . $day['days'] . ':</span><span class="single-hour">' . $day['hours'] . '</span></div>';
            }
        }
    }

    if ( (!$days_hours) || ($dc == 0) ) {
        echo '<span class="single-day">' . $nr_message . '</span>';
    }

}

/**
 * PT results function - Additional information.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_additional_info( $gmw, $post, $tag ) {

    if ( !isset( $gmw['search_results']['additional_info'] ) )
        return;
	
    $tag = ( isset( $tag ) && !empty( $tag ) ) ? $tag : 'div';
    
    $orgTag = $tag;
    if ( $orgTag == 'ul' ) {
    	echo '<ul class="gmw-additional-info-wrapper">';
    	$tag = 'li';
    } elseif ( $orgTag == 'ol') {
    	echo '<ol class="gmw-additional-info-wrapper">';
    	$tag = 'li';
    } else {
    	echo '<div class="gmw-additional-info-wrapper">';
    	$tag = 'div';
    }
    
    if ( isset( $gmw['search_results']['additional_info']['phone'] ) ) { 
    	
    	echo '<'.$tag.' class="gmw-phone gmw-additional-info">';
    	echo 	'<span>'. __( 'Phone: ', 'GMW' ) .'</span>';
    	echo 	( $post->phone ) ? '<span>' .$post->phone.'</span>' : '<span>' . __( 'N/A', 'GMW' ). '</span>';
    	echo '</'.$tag.'>';
    	
   	} 
   	
    if ( isset( $gmw['search_results']['additional_info']['fax'] ) ) { 
    	
    	echo '<'.$tag .' class="gmw-fax gmw-additional-info">';
    		echo '<span>'. __( 'Fax: ', 'GMW' ).'</span>';
    		echo ( $post->fax ) ? '<span>' .$post->fax .'</span>' : '<span>' . __( 'N/A', 'GMW' ). '</span>';
    	echo '</'.$tag.'>';
    	
    }
    
    if ( isset( $gmw['search_results']['additional_info']['email'] ) ) { 
    	
    	echo '<'.$tag.' class="gmw-email gmw-additional-info">';
    		echo '<span>'.__( 'Email: ', 'GMW' ).'</span>';
    		echo ( isset( $post->email ) && !empty( $post->email ) ) ? '<span><a href="mailto:' . $post->email . '">' . $post->email . '</a></span>' : '<span>' . __( 'N/A', 'GMW' ). '</span>';
    	echo '</'.$tag.'>';
    	
    }
    
    if ( isset( $gmw['search_results']['additional_info']['website'] ) ) {
    	 
    	echo '<'.$tag.' class="gmw-additional-info gmw-website">';
    	echo '<span>'.__( 'Website: ', 'GMW' ).'</span>';
    	echo ( isset( $post->website ) && !empty( $post->website ) ) ? '<span><a href="http://'.$post->website.'" target="_blank"></span>' . $post->website . '</a>' : '<span>' . __( 'N/A', 'GMW' ). '</span>';
    	echo '</'.$tag.'>';
    	 
    }
    
	if ( $orgTag == 'ul' ) {
		echo '</ul>';
	} elseif ( $orgTag == 'ol') {
		echo '</ol>';
	} else {
		echo '</div>';
	}
}

/**
 * PT results function - Excerpt from content.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_excerpt( $gmw, $post ) {

    if ( !isset( $gmw['search_results']['excerpt']['use'] ) )
        return;

    $count = ( isset( $gmw['search_results']['excerpt']['count'] ) && !empty( $gmw['search_results']['excerpt']['count'] ) ) ? $gmw['search_results']['excerpt']['count'] : 99999999999;
    echo wp_trim_words( strip_shortcodes( $post->post_content ), $count );

}

/**
 * PT results function - Get directions.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_get_directions( $gmw, $post, $title ) {

    if ( !isset( $gmw['search_results']['get_directions'] ) )
        return;

    $region = ( WPLANG ) ? explode( '_', WPLANG ) : array( 'en', 'US' );
    return apply_filters( 'gmw_pt_get_directions_link', '<span class="get-directions"><a href="http://maps.google.com/maps?f=d&hl=' . $region[0] . '&region=' . $region[1] . '&doflg=' . $gmw['units_array']['map_units'] . '&geocode=&saddr=' . $gmw['org_address'] . '&daddr=' . str_replace( " ", "+", $post->address ) . '&ie=UTF8&z=12" target="_blank">' . $title . '</a></span>', $gmw, $post );

}

function gmw_pt_directions( $gmw, $post, $title ) {
    echo gmw_pt_get_directions( $gmw, $post, $title );

}

/**
 * PT results function - Driving distance.
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_pt_driving_distance( $gmw, $post, $class, $title ) {

    if ( !isset( $gmw['search_results']['by_driving'] ) || $gmw['units_array'] == false )
        return;

    echo '<div id="gmw-driving-distance-' . $post->ID . '" class="' . $class . '"></div>';
    ?>
    <script>
        var directionsDisplay;
        var directionsService = new google.maps.DirectionsService();
        var directionsDisplay = new google.maps.DirectionsRenderer();

        var start = new google.maps.LatLng('<?php echo $gmw['your_lat']; ?>', '<?php echo $gmw['your_lng']; ?>');
        var end = new google.maps.LatLng('<?php echo $post->lat; ?>', '<?php echo $post->long; ?>');
        var request = {
            origin: start,
            destination: end,
            travelMode: google.maps.TravelMode.DRIVING
        };
        directionsService.route(request, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(result);
                if ('<?php echo $gmw['units_array']['name']; ?>' == 'Mi') {
                    totalDistance = (Math.round(result.routes[0].legs[0].distance.value * 0.000621371192 * 10) / 10) + ' <?php _e( 'mi', 'GMW' ); ?>';
                } else {
                    totalDistance = (Math.round(result.routes[0].legs[0].distance.value * 0.01) / 10) + ' <?php _e( 'km', 'GMW' ); ?>';
                }
                jQuery('#<?php echo 'gmw-driving-distance-' . $post->ID; ?>').text('<?php echo $title; ?>' + totalDistance);
            }
        });
    </script>
    <?php

}

/**
 * GMW_PT_Search_Query class
 * 
 */
class GMW_PT_Search_Query extends GMW {

    /**
     * __construct function.
     */
    function __construct( $form, $results ) {

        do_action( 'gmw_pt_search_query_start', $form, $results );

        parent::__construct( $form, $results );

    }

    /**
     * Include search form
     * 
     */
    public function search_form() {

        $gmw = $this->form;

        do_action( 'gmw_pt_before_search_form', $this->form, $this->settings );

        wp_enqueue_style( 'gmw-' . $this->form['ID'] . '-' . $this->form['search_form']['form_template'] . '-form-style', GMW_PT_URL . 'search-forms/' . $this->form['search_form']['form_template'] . '/css/style.css' );
        include GMW_PT_PATH . 'search-forms/' . $this->form['search_form']['form_template'] . '/search-form.php';

        do_action( 'gmw_pt_after_search_form', $this->form, $this->settings );

    }

    /**
     * Modify wp_query clauses to search by distance
     * @param $clauses
     * @return $clauses
     */
    public function query_clauses( $clauses ) {

        global $wpdb;

        // join the location table into the query
        $clauses['join'] .= "INNER JOIN " . $wpdb->prefix . "places_locator gmwlocations ON $wpdb->posts.ID = gmwlocations.post_id ";

        // add the radius calculation and add the locations fields into the results
        if ( !empty( $this->form['org_address'] ) ) :

            $clauses['fields'] .= $wpdb->prepare( ", gmwlocations.* , ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance", array( $this->form['units_array']['radius'], $this->form['your_lat'], $this->form['your_lng'], $this->form['your_lat'] ) );
            $clauses['groupby'] = $wpdb->prepare( " $wpdb->posts.ID HAVING distance <= %d OR distance IS NULL", $this->form['radius'] );
            $clauses['orderby'] = 'distance';

        else :

            $clauses['fields'] .= ", gmwlocations.*";
        endif;

        return apply_filters( 'gmw_pt_location_query_clauses', $clauses, $this->form );

    }

    /**
     * GMG WP Query
     * @param $gmw
     */
    public function results() {

        $tax_args  = false;
        $meta_args = false;

        //Get current page number
        $this->form['paged'] = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

        //query args
        $this->form['query_args'] = array(
            'post_type'           => ( isset( $_GET['gmw_post'] ) && !empty( $_GET['gmw_post'] ) ) ? gmw_multiexplode( array( ' ', '+' ), $_GET['gmw_post'] ) : $this->form['search_form']['post_types'],
            'post_status'         => array( 'publish' ),
            'tax_query'           => apply_filters( 'gmw_pt_tax_query', $tax_args, $this->form ),
            'posts_per_page'      => $this->form['get_per_page'],
            'paged'               => $this->form['paged'],
            'meta_query'          => apply_filters( 'gmw_pt_meta_query', $meta_args, $this->form ),
            'ignore_sticky_posts' => 1
        );

        //Hooks before query 
        $this->form = apply_filters( 'gmw_pt_form_before_posts_query', $this->form, $this->settings );

        do_action( 'gmw_pt_before_posts_query', $this->form, $this->settings );

        /* add filters to wp_query to do radius calculation and get locations detail into results */
        add_filter( 'posts_clauses', array( $this, 'query_clauses' ) );

        /* posts query */
        $gmw_query = new WP_Query( $this->form['query_args'] );

        /* remove filter */
        remove_filter( 'posts_clauses', array( $this, 'query_clauses' ) );

        /* hooks before posts loop */
        $this->form = apply_filters( 'gmw_pt_form_before_posts_loop', $this->form, $gmw_query, $this->settings );
        $gmw_query  = apply_filters( 'gmw_pt_posts_before_posts_loop', $gmw_query, $this->form, $this->settings );

        do_action( 'gmw_pt_before_posts_loop', $this->form, $this->settings, $gmw_query );

        //check if we got results and if so run the loop
        if ( $gmw_query->have_posts() ):

            global $post;
            $this->form['results']       = $gmw_query->posts;
            $this->form['results_count'] = count( $gmw_query->posts );
            $this->form['total_results'] = $gmw_query->found_posts;
            $this->form['max_pages']     = $gmw_query->max_num_pages;
            $this->form['post_count']    = ( $this->form['paged'] == 1 ) ? 1 : ( $this->form['get_per_page'] * ( $this->form['paged'] - 1 ) ) + 1;
            $this->form['region']        = ( WPLANG ) ? explode( '_', WPLANG ) : array( 'en', 'US' );
            $this->form['post_loader']   = GMW_URL . '/assets/images/gmw-loader.gif';

            add_action( 'the_post', array( $this, 'the_post' ), 1, 1 );

            do_action( 'gmw_pt_have_posts_start', $this->form, $this->settings, $gmw_query );

            //call results template file
            if ( isset( $this->form['search_results']['display_posts'] ) ) :

                $gmw = $this->form;

                //get custom stylesheet and results template from child/theme
                if ( strpos( $this->form['search_results']['results_template'], 'custom_' ) !== false ) :

                    wp_enqueue_style( 'gmw-current-style', get_stylesheet_directory_uri() . '/geo-my-wp/posts/search-results/' . str_replace( 'custom_', '', $this->form['search_results']['results_template'] ) . '/css/style.css' );
                    include( STYLESHEETPATH . '/geo-my-wp/posts/search-results/' . str_replace( 'custom_', '', $this->form['search_results']['results_template'] ) . '/results.php' );
                //get stylesheet and results template from plugin's folder
                else :

                    wp_enqueue_style( 'gmw-current-style', GMW_PT_URL . 'search-results/' . $this->form['search_results']['results_template'] . '/css/style.css' );
                    include GMW_PT_PATH . 'search-results/' . $this->form['search_results']['results_template'] . '/results.php';
                endif;

            /*
             * in case that we do not display posts we still run the loop on "empty" in order
             * to add element to the info windows of the map
             */
            else :

                while ( $gmw_query->have_posts() ) : $gmw_query->the_post();
                endwhile;
            endif;

            //if need to display map the function below will pass the locations 
            //information to the javascript function that displays the map 
            if ( $this->form['search_results']['display_map'] != 'na' ) :

                //info window labels
                $this->form['iw_labels'] = array(
                    'distance'          => __( 'Distance: ', 'GMW' ),
                    'address'           => __( 'Address: ', 'GMW' ),
                    'formatted_address' => __( 'Address: ', 'GMW' ),
                    'phone'             => __( 'Phone: ', 'GMW' ),
                    'fax'               => __( 'Fax: ', 'GMW' ),
                    'email'             => __( 'Email: ', 'GMW' ),
                    'website'           => __( 'website: ', 'GMW' ),
                    'directions'        => __( 'Get Directions: ', 'GMW' )
                );

                $this->form = apply_filters( 'gmw_pt_form_before_map', $this->form, $gmw_query, $this->settings );
                $gmw_query  = apply_filters( 'gmw_pt_posts_loop_before_map', $gmw_query, $this->form, $this->settings );

                do_action( 'gmw_pt_have_posts_before_map_displayed', $this->form, $this->settings, $gmw_query );

                wp_enqueue_script( 'gmw-pt-map' );
                wp_localize_script( 'gmw-pt-map', 'gmwForm', $this->form );

            endif;

            do_action( 'gmw_pt_have_posts_end', $this->form, $this->settings, $gmw_query );

            remove_action( 'the_post', array( $this, 'the_post' ), 1, 1 );

            wp_reset_query();
        else :

            $this->no_results( __( 'No results found', 'GMW' ) );
        endif;

    }

    /**
     * Modify each post within the loop
     */
    public function the_post( $post ) {

        // add permalink and thumbnail into each post in the loop
        // we are doing it here to be able to display it in the info window of the map
        $post->post_permalink = get_permalink( $post->ID );
        $post->post_thumbnail = get_the_post_thumbnail( $post->ID );
        $post->post_count     = $this->form['post_count'];
        $post->mapIcon        = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=' . $post->post_count . '|FF776B|000000';

        $this->form['post_count'] ++;

        do_action( 'gmw_pt_loop_the_post', $post, $this->form, $this->settings );

    }

}