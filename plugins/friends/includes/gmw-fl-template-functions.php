<?php
/**
 * GMW FL search form function - Display xprofile fields
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_xprofile_fields( $gmw, $class ) {

	if ( ( !isset( $gmw['search_form']['profile_fields'] ) && !isset( $gmw['search_form']['profile_fields_date'] ) ) )
		return;

	$total_fields = ( isset( $gmw['search_form']['profile_fields'] ) ) ? $gmw['search_form']['profile_fields'] : array();

	if ( isset( $gmw['search_form']['profile_fields_date'] ) ) {
		array_unshift( $total_fields, $gmw['search_form']['profile_fields_date'] );
	}
	 
	echo '<div class="gmw-fl-form-xprofile-fields gmw-fl-form-xprofile-fields-'.$gmw['ID'].' '.$class.'">';

	$total_fields = apply_filters( 'gmw_fl_form_xprofile_field_before_displayed', $total_fields, $gmw );
	
	foreach ( $total_fields as $field_id ) {

		$fdata  	= new BP_XProfile_Field( $field_id );
		$fname  	= 'field_'.$field_id;
		$label		= $fdata->name;
		$fclass		= 'field-'.$field_id;
		$fid		= 'gmw-'.$gmw['ID'].'-field-'.$field_id;
		$children 	= $fdata->get_children();

		echo '<div class="editfield '.$fdata->type.' gmw-'.$gmw['ID'].'-field-'.$field_id.'-wrapper">';
		 
		switch ( $fdata->type ) {

			case 'datebox':
			case 'birthdate':
				$value = ( isset( $_REQUEST[$fname] ) ) ? esc_attr (stripslashes ( $_REQUEST[$fname] ) ) : '';
				$max   = ( isset( $_REQUEST[$fname . '_max'] ) ) ? esc_attr (stripslashes ( $_REQUEST[$fname.'_max'] ) ) : '';
				
				echo 	'<label for="'.$fid.'">' . __('Age Range (min - max)', 'GMW') . '</label>';
				echo 	'<input size="3" type="text" name="'.$fname.'" id="'.$fid.'" class="'.$fclass.'" value="'.$value.'" placeholder="'.__( 'Min', 'GMW' ).'" />';
				echo 	'&nbsp;-&nbsp;';
				echo 	'<input size="3" type="text" name="'.$fname.'_max" id="'.$fid.'_max" class="'.$fclass.'_max" value="'.$max.'" placeholder="'.__( 'Max', 'GMW' ).'" />';
				break;				 
			case 'textbox':
				$value = ( isset( $_REQUEST[$fname] ) ) ? esc_attr (stripslashes ( $_REQUEST[$fname] ) ) : '';
				 
				echo '<label for="'.$fid.'">'.$label.'</label>';
				echo '<input type="text" name="'.$fname.'" id="'.$fid.'" class="'.$fclass.'" value="'.$value.'" />';
				break;

			case 'number':
				$value = ( isset( $_REQUEST[$fname] ) ) ? esc_attr (stripslashes ( $_REQUEST[$fname] ) ) : '';
				 
				echo '<label for="'.$fid.'">'.$label.'</label>';
				echo '<input type="number" name="'.$fname.'" id="'.$fid.'" value="'.$value.'" />';
				break;
				 
			case 'textarea':
				$value = ( isset( $_REQUEST[$fname] ) ) ? esc_attr (stripslashes ( $_REQUEST[$fname] ) ) : '';
				 
				echo '<label for="'.$fid.'">'.$label.'</label>';
				echo '<textarea rows="5" cols="40" name="'.$fname.'" id="'.$fid.'" class="'.$fclass.'">'.$value.'</textarea>';
				break;
					
			case 'selectbox':
				$value = ( isset( $_REQUEST[$fname] ) ) ? esc_attr (stripslashes ( $_REQUEST[$fname] ) ) : '';

				echo '<label for="'.$fid.'">'.$label.'</label>';
				echo '<select name="'.$fname.'" id="'.$fid.'" class="'.$fclass.'">';
				echo 	'<option value="">'.__( ' -- All -- ', 'GMW' ).'</option>';
				 
				foreach ( $children as $child ) {
					$option   = trim( $child->name );
					$selected = ( $option == $value ) ? "selected='selected'" : "";
					echo '<option '.$selected.' value="'.$option.'" />'.$option.'</label>';
				}
				 
				echo '</select>';
				break;

			case 'multiselectbox':
				$value = ( isset( $_REQUEST[$fname] ) ) ? $_REQUEST[$fname] : array();

				echo '<label for="'.$fid.'">'.$label.'</label>';
				echo '<select name="'.$fname.'[]" id="'.$fid.'" class="'.$fclass.'" multiple="multiple">';
				 
				foreach ( $children as $child ) {
					$option   = trim( $child->name );
					$selected = ( in_array( $option, $value ) ) ? "selected='selected'" : "";
					echo '<option '.$selected.' value="'.$option.'" />'.$option.'</label>';
				}
				 
				echo "</select>";
				break;
				 
			case 'radio':
				$value = ( isset( $_REQUEST[$fname] ) ) ? esc_attr (stripslashes ( $_REQUEST[$fname] ) ) : '';
				 
				echo '<div class="radio">';
				echo '<span class="label">'.$label.'</span>';

				foreach ( $children as $child ) {
					$option  = trim( $child->name );
					$checked = ( $child->name == $value ) ? "checked='checked'" : "";
					echo '<label><input '.$checked.' type="radio" name="'.$fname.'" value="'.$option.'" />'.$option.'</label>';
				}

				echo '<a href="#" onclick="event.preventDefault();jQuery(this).closest(\'div\').find(\'input\').prop(\'checked\', false);">'. __('Clear', 'buddypress'). '</a><br/>';
				echo '</div>';

				break;
			case 'checkbox':
				$value = ( isset( $_REQUEST[$fname] ) ) ? $_REQUEST[$fname] : array();

				echo '<div class="checkbox">';
				echo '<span class="label">'.$label.'</span>';

				foreach ( $children as $child ) {
					$option	 = trim( $child->name );
					$checked = ( in_array( $option, $value ) ) ? "checked='checked'" : "";
					echo '<label><input '.$checked.' type="checkbox" name="'.$fname.'[]" value="'.$option.'" />'.$option.'</label>';
				}
				echo '</div>';

				break;
		} // switch

		echo '</div>';
	}
	echo '</div>';
}

/**
 * Query xprofile fields
 * @version 1.0
 * @author Eyal Fitoussi
 * @author Some of the code in this function was inspired by the code written by Andrea Taranti the creator of BP Profile Search - Thank you
 */
function gmw_fl_query_xprofile_fields( $gmw, $formValues ) {

	$total_fields = false;
	$total_fields = ( isset( $gmw['search_form']['profile_fields'] ) ) ? $gmw['search_form']['profile_fields'] : array();
	$fields_empty = true;
	$usersid	  = array(
			'status' => 'no_fields',
			'ids'	 => array()
	);
	
	if ( !empty( $gmw['search_form']['profile_fields_date']) ) {
		array_unshift( $total_fields, $gmw['search_form']['profile_fields_date'] );
	}

	if ( empty( $total_fields ) )
		return $usersid;

	global $bp, $wpdb, $wp_version;
	
	foreach ( $total_fields as $field_id ) {

		$fdata  = new BP_XProfile_Field( $field_id );
		$fname  = 'field_'.$field_id;
		$value  = ( isset( $formValues[$fname] ) ) ? $formValues[$fname] : '';
		$max   	= ( isset( $formValues[$fname.'_max'] ) ) ? $formValues[$fname.'_max'] : '';
		$sql 	= $wpdb->prepare ( "SELECT `user_id` FROM {$bp->profile->table_name_data} WHERE `field_id` = %d ", $field_id );
		 
		if ( !$value && !$max ) 
			continue;
	
		$fields_empty = false;
		
		if ( $value || $max ) {

			switch ( $fdata->type ) {
			
				case 'textbox':
				case 'textarea':
					$value 	 = str_replace ( '&', '&amp;', $value );

					if ( $wp_version < 4.0 ) {
						$escaped = '%'. esc_sql ( like_escape ( trim( $value ) ) ). '%';
					} else {
						$escaped = '%' . $wpdb->esc_like( trim( $value ) ) . '%';
					}

					$sql .= $wpdb->prepare ( "AND value LIKE %s", $escaped );
					break;
					 
				case 'number':
					$sql .= $wpdb->prepare ( "AND value = %d", $value );
					break;

				case 'selectbox':
				case 'radio':
					$value = str_replace ( '&', '&amp;', $value );
					$sql  .= $wpdb->prepare ( "AND value = %s", $value );
				break;
						
				case 'multiselectbox':
				case 'checkbox':

					$values = $value;
					$like   = array ();
					 
					foreach ($values as $value) {
						$value   = str_replace ( '&', '&amp;', $value );

						if ( $wp_version < 4.0 ) {
							$escaped = '%'. esc_sql ( like_escape( $value ) ). '%';
						} else {
							$escaped = '%' . $wpdb->esc_like( $value ) . '%';
						}
						$like[]  = $wpdb->prepare ( "value = %s OR value LIKE %s", $value, $escaped );
					}
					 
					$sql .= 'AND ('. implode (' OR ', $like). ')';
					 
					/*
					 $like = array();

					foreach ( $value as $curvalue ) {
					$like[] = "value = '$curvalue' OR value LIKE '%\"$curvalue\"%' ";
					}

					$sql .= ' AND (' . implode(' OR ', $like) . ')';
					*/
					 
				break;
				case 'datebox':
				case 'birthdate':

					$value = ( !$value ) ? '1' : $value;
					$max    = ( !$max ) ? '200' : $max;
										
					if ( $max < $value ) {
						$max = $value;
					}

					$time  = time();
					$day   = date("j", $time);
					$month = date("n", $time);
					$year  = date("Y", $time);
					$ymin  = $year - $max - 1;
					$ymax  = $year - $value;

					if ( $max !== '')   $sql .= $wpdb->prepare("AND DATE(value) > %s", "$ymin-$month-$day");
					if ( $value !== '') $sql .= $wpdb->prepare(" AND DATE(value) <= %s", "$ymax-$month-$day");
					
					// $sql = "SELECT user_id from {$bp->profile->table_name_data}";
					//$sql .= " WHERE field_id = $field_id AND value > '$ymin-$month-$day' AND value <= '$ymax-$month-$day'";

					break;					 
			}
					
			$results 		= $wpdb->get_col( $sql, 0 );
			$usersid['ids'] = ( empty( $usersid['ids'] ) ) ? $results : array_intersect( $usersid['ids'], $results ); 
					 
		} // if value //
	} // for eaech //

	if ( !$fields_empty )
		$usersid['status'] = ( !empty( $usersid['ids'] ) ) ? 'ids_found' : 'no_ids_found';
		
	return $usersid;
}

/**
 * GMW FL function - get list of groups in checkboxes or dropdown
 */
function gmw_fl_get_bp_groups( $gmw, $usage, $groups, $name ) {
		
	if ( !bp_is_active( 'groups' ) )
		return;
		
	$output = '';
	
	if ( $usage == 'checkbox' ) {
		$output .= '<span class="search-all">'.$gmw['labels']['search_form']['select_groups'].'</span>';
		$output .= '<ul id="gmw-'.$gmw['ID'].'-'.$name.'-checkboxes-wrapper" class="gmw-'.$name.'-checkboxes-wrapper gmw-'.$gmw['prefix'].'-'.$name.'-checkboxes-wrapper">';
	} else {
		$output .= '<label for="gmw-bp-groups-'.$usage.'-'.$gmw['ID'].'">'.$gmw['labels']['search_form']['select_groups'].'</label>';
		$output .= '<select name="'.$name.'" id="gmw-bp-groups-dropdown-'.$gmw['ID'].'" class="gmw-bp-groups-dropdown gmw-'.$gmw['prefix'].'-bp-groups-dropdown">';
		$output .= '<option value="">'.$gmw['labels']['search_form']['no_groups'].'</option>';
		$output .= '<option value="'.implode(',',$groups).'">'.$gmw['labels']['search_form']['all_groups'].'</option>';
	}
	
	if ( bp_has_groups() ) {
			
		while ( bp_groups() ) : bp_the_group();
			
		$gid =  bp_get_group_id();
			
			if ( !in_array( $gid, $groups ) )
				continue;
		
			$gname =  bp_get_group_name();
			if ( $usage == 'checkbox' ) {
				$output .= '<li id="gmw-'.$gmw['ID'].'-'.$name.'-'.$gid.'-checkbox-wrapper" class="gmw-'.$name.'-checkbox-wrapper gmw-'.$gmw['prefix'].'-'.$name.'-checkbox-wrapper">';
				$output .= '<input type="checkbox" id="gmw-'.$gmw['ID'].'-'.$name.'-'.$gid.'-checkbox" value="'.$gid.'" name='.$name.'[]" />';
				$output .= '<label for="gmw-'.$gmw['ID'].'-'.$name.'-'.$gid.'-checkbox">'.$gname.'</label>';
				$output .= '</li>';
			} else {
				$output .= '<option value="'.$gid.'">'.$gname.'</option>';
			}
					
		endwhile;
	}
	
	if ( $usage == 'checkbox' ) {
		$output .= '<ul>';
	} else {
		$output .= '</select>';
	}
	
	return apply_filters( 'gmw_fl_get_bp_groups', $output, $gmw, $usage, $groups, $name );
}

	function gmw_fl_bp_groups( $gmw, $usage, $groups, $name ) {
		echo gmw_fl_get_bp_groups( $gmw, $usage, $groups, $name );
	}