<?php 
if ( $this->args['stand_alone'] ) {
	?>
	<input type="button" class="<?php echo esc_attr( $field['class'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" name="<?php echo esc_attr( $fieldName ); ?>" value="<?php echo esc_attr( $field['label'] ); ?>" />
	<?php
} elseif ( $this->args['submit_enabled'] ) {
	?>
	<input type="button" class="<?php echo esc_attr( $field['class'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" name="<?php echo esc_attr( $fieldName ); ?>" value="<?php echo esc_attr( $field['label'] ); ?>" />
	<?php
}
?>
