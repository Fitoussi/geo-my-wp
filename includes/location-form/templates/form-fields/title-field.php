<fieldset class="gmw-lf-field-wrapper <?php echo esc_attr( $field['id'] ); ?> <?php echo esc_attr( $field['type'] ); ?>">

	<?php if ( ! empty( $field['label'] ) ) { ?>
		<div class="<?php echo esc_attr( $field['class'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" <?php echo esc_attr( $field['attributes'] ); ?>><?php echo esc_html( $field['label'] ); ?></div>
	<?php } ?>

	<?php if ( ! empty( $field['desc'] ) ) { ?>
		<em class="description">
			<?php echo esc_html( $field['desc'] ); ?>
		</em>
	<?php } ?>
</fieldset>
