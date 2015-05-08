<?php

if ( !class_exists( 'GMW_Single_Location' ) )
	return;

/**
 * GMW_Single_Member_Location Class
*
* Display different location elements of a post.
*
* @version 1.0
* @author Eyal Fitoussi
* @since 2.6.1
*/
class GMW_Single_Member_Location extends GMW_Single_Location {

	/**
	 * Extends the default shortcode atts
	 * @since 2.6.1
	 * Public $args
	 *
	 */
	protected $ext_args = array(
			'elements'			  => 'title,address,map,distance,directions',
			'item_type'	 		  => 'member',
			'prefix'	 		  => 'fl',
			'item_info_window'	  => 'title,address,distance',
			'show_in_single_post' => 0,
	);
	
	/**
	 * @since 2.6.1
	 * Public $item_info
	 * Oget the post location information from database
	 */
	public function item_info() {

		global $members_template;
		
		if ( is_single() && empty( $this->args['show_in_single_post'] ) ) 
			$this->args['no_location_message'] = false;
		
		if ( empty( $this->args['item_id'] ) && empty( $members_template->member->ID ) && !bp_is_user() && ( !is_single() || empty( $this->args['show_in_single_post'] ) ) )
			return false;
				
		if ( !empty( $this->args['item_id'] ) ) {
			$this->args['item_id'] = $this->args['item_id'];
		} elseif ( bp_is_user() ) {
			global $bp;
			$this->args['item_id'] = $bp->displayed_user->id;
		} elseif ( is_single() ) {
			global $post;
			$this->args['item_id'] = $post->post_author;
		} elseif ( !empty( $members_template->member->ID ) ) {
			$this->args['item_id'] = $members_template->member->ID;
		} else {
			return  false;
		}
		
		$item_info = gmw_get_member_info_from_db( $this->args['item_id'] );
		
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
				'address_label' 	=> __( 'Address: ', 'GMW' ),
				'resize_map'		=> __( 'Resize map', 'GMW' ),
		), $this->args, $this->item_info );

		return $labels;
	}

	/**
	 * Get the post title
	 */
	public function title() {
		return apply_filters( 'gmw_sl_title', '<h3 class="gmw-sl-title display-name">'.bp_core_get_userlink( $this->args['item_id'] ).'</h3>', $this->item_info, $this->args, $this->user_position );
	}

	/**
	 * Get contact information
	 * @since 2.6.1
	 * @access public
	 */
	public function additional_info() {		
		return apply_filters( 'gmw_sl_additional_info', false, $this->args, $this->item_info, $this->user_position );
	}
	
	/**
	 * Display no location message
	 * @since 2.6.1
	 * @access public
	 */
	public function no_location_message() {
		return apply_filters( 'gmw_sl_no_location_message', '<h3 class="no-location">'. get_the_author_meta( 'display_name', $this->args['item_id'] ) . ' ' .esc_attr( $this->args['no_location_message'] ) .'</h3>', $this->item_info, $this->args, $this->user_position );
	}
}