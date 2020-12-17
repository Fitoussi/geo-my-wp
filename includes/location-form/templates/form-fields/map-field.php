<fieldset class="gmw-lf-field-wrapper <?php echo esc_attr( $field['id'] ); ?>">
	
	<?php if ( ! empty( $field['label'] ) ) { ?>
			
		<label for="<?php echo esc_attr( $field['id'] ); ?>">
			
			<?php echo esc_attr( $field['label'] ); ?>
			
		</label>

	<?php } ?>

	<div class="map-wrapper">

		<div id="<?php echo esc_attr( $field['id'] ); ?>" class="<?php echo esc_attr( $field['class'] ); ?>">
		</div>
	</div>

	<?php if ( ! empty( $field['desc'] ) ) { ?>
		<em class="description"><?php echo esc_html( $field['desc'] ); ?></em>
	<?php } ?>
	
</fieldset>
