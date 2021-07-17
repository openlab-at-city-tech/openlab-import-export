<div class="wrap nosubsub import-page">
	<?php $this->render_header(); ?>

	<p><del><?php esc_html_e( 'Step 1: Choose and upload your Site Archive file (.zip).', 'openlab-import-export' ); ?></del></p>
	<p><del><?php esc_html_e( 'Step 2: Import the Site Archive', 'openlab-import-export' ); ?></del></p>
	<p id="import-status-message"><strong><?php esc_html_e( 'Step 3: Now importing.', 'openlab-import-export' ); ?></strong></p>

	<table id="import-log" class="widefat">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Type', 'openlab-import-export' ); ?></th>
				<th><?php esc_html_e( 'Message', 'openlab-import-export' ); ?></th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>
