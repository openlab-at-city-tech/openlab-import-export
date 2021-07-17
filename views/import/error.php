<div class="wrap nosubsub import-page">
	<?php $this->render_header(); ?>

	<p><strong><?php echo esc_html_e( 'Step 1: Choose and upload your OpenLab Site Archive file (.zip).', 'openlab-import-export' ); ?></strong></p>
	<p><strong class="error"><?php echo $error->get_error_message(); ?></strong></p>

	<p><?php printf( '<a class="button button-primary" href="%s">%s</a>', esc_url( $this->get_url( 0 ) ), esc_html__( 'Try Again', 'openlab-import-export' ) ); ?></p>
</div>
