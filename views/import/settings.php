<div class="wrap nosubsub import-page">
	<?php $this->render_header(); ?>

	<p><del><?php esc_html_e( 'Step 1: Choose and upload your Portfolio Archive file (.zip).', 'openlab-import-export' ); ?></del></p>
	<p><strong><?php esc_html_e( 'Step 2: Import the Portfolio Archive', 'openlab-import-export' ); ?></strong></p>

	<form method="post" action="<?php echo esc_url( $this->get_url(2) ); ?>">
		<input type="hidden" name="import_id" value="<?php echo esc_attr( $this->id ); ?>" />
		<?php wp_nonce_field( sprintf( 'site.import:%d', $this->id ) ); ?>
		<?php submit_button( __( 'Start Importing', 'openlab-import-export' ) ); ?>
	</form>
</div>
