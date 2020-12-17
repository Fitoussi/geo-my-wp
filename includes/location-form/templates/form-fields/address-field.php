<fieldset class="gmw-lf-field-wrapper <?php echo esc_attr( $field['id'] ); ?>">
	
	<!-- label -->
	<?php if ( ! empty( $field['label'] ) ) : ?>	
		<label for="<?php echo esc_attr( $field['id'] ); ?>">
			<?php echo esc_attr( $field['label'] ); ?>			
		</label>
	<?php endif; ?>

	<div id="gmw-lf-autocomplete-wrapper">

		<!-- address input field -->
		<input type="text" class="<?php echo esc_attr( $field['class'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( sanitize_text_field( stripslashes( $field['value'] ) ) ); ?>" autocomplete="off" <?php echo esc_attr( $field['attributes'] ); ?> />

		<!-- locator icon -->
	    <i id="gmw-lf-locator-button" title="<?php _e( 'Get your current position', 'geo-my-wp' ); ?>" class="gmw-icon-target-light gmw-lf-locator-button"></i>
	    	
	</div>
	
	<!-- description -->
	<?php if ( ! empty( $field['desc'] ) ) : ?>
		<em class="description">
			<?php echo esc_html( $field['desc'] ); ?>
		</em>
	<?php endif; ?>

</fieldset>
