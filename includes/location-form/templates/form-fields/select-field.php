<fieldset class="gmw-lf-field-wrapper <?php echo esc_attr( $field['id'] ); ?> <?php echo esc_attr( $field['type'] ); ?>">
	
	<?php if ( ! empty( $field['label'] ) ) { ?>
		
		<label for="<?php echo esc_attr( $field['id'] ); ?>">
			<?php echo esc_attr( $field['label'] ); ?>	
		</label>

	<?php } ?>

	<select class="<?php echo esc_attr( $field['class'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" name="<?php echo esc_attr( $field_name ); ?>">
		
		<?php foreach ( $field['options'] as $code => $name ) { ?>
			
			<option value="<?php echo esc_attr( $code ); ?>" <?php if ( isset( $field['value'] ) || isset( $field['default'] ) ) selected( isset( $field['value'] ) ? $field['value'] : $field['default'], $code ); ?>>
				<?php echo esc_html( $name ); ?>
			</option>
		
		<?php } ?>
	</select>
	
	<?php if ( ! empty( $field['desc'] ) ) { ?>
		<small class="description">
			<?php echo esc_html( $field['desc'] ); ?>
		</small>
	<?php } ?>

</fieldset>
