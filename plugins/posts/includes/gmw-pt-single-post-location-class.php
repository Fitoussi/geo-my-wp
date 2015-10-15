<?php

if ( !class_exists( 'GMW_Single_Location' ) )
	return;

/**
 * GMW_Single_Post_Location Class extends GMW_Single_Location class
 * 
 * Display the location of a single post
 * 
 * @version 1.0
 * @author Eyal Fitoussi
 * @since 2.6.1
 */
class GMW_Single_Post_Location extends GMW_Single_Location {

	/**
	 * Extends the default shortcode atts
	 * @since 2.6.1
	 * Public $args
	 *
	 */
	protected $ext_args = array(
		'elements'			=> 'title,address,map,distance,directions,additional_info',
		'item_type'	 		=> 'post',
		'prefix'	 		=> 'pt',
		'additional_info' 	=> 'address,phone,fax,email,website',
		'item_info_window'	=> 'title,address,distance,additional_info',
	);

	/**
	 * @since 2.6.1
	 * Public $item_info
	 * Oget the post location information from database
	 */
	public function item_info() {

		//check if user entered post id
		if ( empty( $this->args['item_id'] ) ) {
	
			$this->args['item_id'] = get_queried_object_id();
	
			if ( empty( $this->args['item_id'] ) )
				return;
		}
			
		//get the post's info
		$item_info = gmw_get_post_location_from_db( $this->args['item_id'] );
		
		return $item_info;
	}
	
	/**
	 * @since 2.6.1
	 * Public $labes
	 * Create labels for the elements
	 */
	public function labels() {
		
		//labels
		$labels = apply_filters( 'gmw_sl_labels', array(
			'distance_label'	=> __( 'Distance: ', 'GMW' ),
			'directions_submit' => __( 'Go', 'GMW' ),
			'directions_label' 	=> __( 'Get Directions', 'GMW' ),
			'resize_map'		=> __( 'Resize map', 'GMW' ),
			'address_label' 	=> __( 'Address', 'GMW' ),
			'phone_label' 		=> __( 'Phone', 'GMW' ),
			'fax_label' 		=> __( 'Fax', 'GMW' ),
			'email_label' 		=> __( 'Email', 'GMW' ),
			'website_label' 	=> __( 'Website', 'GMW' ),
			'na'				=> __( 'N/A', 'GMW' )
		), $this->args, $this->item_info );
		
		return $labels;
	}
	
	/**
	 * Get the post title
	 */
	public function title() {
		
		$title     = get_the_title( $this->item_info->post_id );
		$permalink = get_the_permalink( $this->item_info->post_id );
		
		return apply_filters( 'gmw_sl_title', "<h3 class=\"gmw-sl-title post-title\"><a href=\"{$permalink}\" title=\"{$title}\"'>{$title}</a></h3>", $this->item_info, $this->args, $this->user_position );
	}

	/**
	 * Get contact information
	 * @since 2.6.1
	 * @access public
	 */
	public function additional_info() {
		
		$contact_info = explode( ',', $this->args['additional_info'] );

		$output = '<div id="gmw-sl-additional-info-wrapper-'.$this->args['element_id'].'" class="gmw-sl-additional-info-wrapper gmw-sl-'.$this->args['item_type'].'-additional-info-wrapper">';

		$this->item_info->address = ( !empty( $this->item_info->formatted_address ) ) ? $this->item_info->formatted_address : $this->item_info->address;

		$output .= '<ul>';
		
		foreach ( $contact_info as $info ) {

			$label = ( !empty( $this->labels[$info.'_label'] ) ) ? $this->labels[$info.'_label'] : $info;
			
			if ( $info == 'website' && !empty( $this->item_info->website ) ) {

				$url = parse_url( $this->item_info->website );

				if ( empty( $url['scheme'] ) ) {
					$url['scheme'] = 'http';
				}

				$scheme  = $url['scheme'].'://';
				$path    = str_replace( $scheme,'',$this->item_info->website );
				$website = '<a href="'.$scheme.$path.'" title="'.$path.'" target="_blank">'.$path.'</a>';
				
				$output .= "<li class=\"gmw-{$info} {$info}\"><span class=\"label\">{$label}</span>: ";
				$output .= ( !empty( $this->item_info->$info ) ) ? $website : $this->labels['na'];
				$output .= '</li>';
			
			} elseif ( $info == 'email' && !empty( $this->item_info->email ) ) {
							
				$output .= "<li class=\"gmw-{$info} {$info}\"><span class=\"label\">{$label}</span>: ";
				$output .= ( !empty( $this->item_info->$info ) ) ? "<a href=\"mailto:{$this->item_info->email}\">{$this->item_info->email}</a>" : $this->labels['na'];
				$output .= '</li>';
				
			} else {

				$output .= "<li class=\"gmw-{$info} {$info}\"><i class=\"fa fa-{$label}\"></i><span class=\"label\">{$label}</span>: ";
				$output .= ( !empty( $this->item_info->$info ) ) ? $this->item_info->$info : $this->labels['na'];
				$output .= '</li>';
			}
		}
		$output .= '</li>';
		$output .= '</div>';

		return apply_filters( 'gmw_sl_additional_info', $output, $this->args, $this->item_info, $this->user_position );
	}
	
	/**
	 * Display no location message
	 * @since 2.6.1
	 * @access public
	 */
	public function no_location_message() {
		return apply_filters( 'gmw_sl_no_location_message', '<h3 class="no-location">'. esc_attr( $this->args['no_location_message'] ) .'</h3>', $this->item_info, $this->args, $this->user_position );
	}
}