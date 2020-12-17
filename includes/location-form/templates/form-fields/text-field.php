<fieldset class="gmw-lf-field-wrapper <?php echo esc_attr( $field['id'] ); ?> <?php echo esc_attr( $field['type'] ); ?>">

	<?php if ( ! empty( $field['label'] ) ) { ?>

		<label for="<?php echo esc_attr( $field['id'] ); ?>">
			<?php echo esc_attr( $field['label'] ); ?>	
		</label>

	<?php } ?>

	<input type="text" class="<?php echo esc_attr( $field['class'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo sanitize_text_field( esc_attr( $field['value'] ) ); ?>" <?php echo esc_attr( $field['attributes'] ); ?> />

	<?php if ( ! empty( $field['desc'] ) ) { ?>
		<em class="description">
			<?php echo esc_html( $field['desc'] ); ?>
		</em>
	<?php } ?>
</fieldset>
