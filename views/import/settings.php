<div class="wrap nosubsub import-page">
	<?php $this->render_header(); ?>

	<p><del><?php esc_html_e( 'Step 1: Choose and upload your OpenLab Site Archive file (.zip).', 'openlab-import-export' ); ?></del></p>
	<p><strong><?php esc_html_e( 'Step 2: Import the OpenLab Site Archive', 'openlab-import-export' ); ?></strong></p>

	<form method="post" action="<?php echo esc_url( $this->get_url(2) ); ?>">
		<input type="hidden" name="import_id" value="<?php echo esc_attr( $this->id ); ?>" />

		<?php if ( $this->archive_has_attachments ) : ?>

			<input type="hidden" name="archive-has-attachments" value="1" />

		<?php else : ?>

			<p><?php esc_html_e( 'This archive file does not contain any media files. During the import process, the importer will attempt to copy media files from the original site.', 'openlab-import-export' ); ?></p>

			<p><?php _e( '<strong>Please note</strong>: the original site must be publicly accessible in order to import the media files. If the site is not public, before continuing, please change privacy settings to public on the original site or contact the site owner in order to complete the import process for media files.', 'openlab-import-export' ); ?></p>
		<?php endif; ?>

		<?php wp_nonce_field( sprintf( 'site.import:%d', $this->id ) ); ?>
		<?php submit_button( __( 'Start Importing', 'openlab-import-export' ) ); ?>
	</form>
</div>
