<div class="wrap nosubsub">
	<h1><?php esc_html_e( 'Export', 'openlab-import-export' ); ?></h1>

	<?php settings_errors(); ?>

	<p><?php esc_html_e( 'Use this tool to create a Site Archive file (.zip) that will be downloaded to your computer and can be used with OpenLab Import-Export to import into another site.', 'openlab-import-export' ); ?></p>

	<form method="post" id="export-site" action="<?php echo admin_url( 'admin-post.php' ); ?>">
		<h2><?php esc_html_e( 'Choose what to export', 'openlab-import-export' ); ?></h2>

		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Content to export', 'openlab-import-export' ); ?></legend>

			<p><label><input type="radio" name="content" value="all" checked="checked" aria-describedby="all-content-desc" /> <?php esc_html_e( 'All content', 'openlab-import-export' ); ?></label></p>
			<p class="description" id="all-content-desc"><?php esc_html_e( 'Choosing &#8216;All content&#8217; will include all of your posts, pages, comments, custom fields, terms, navigation menus, and custom posts. Below you can limit what is included in the export.', 'openlab-import-export' ); ?></p>


			<p><label><input type="radio" name="content" value="posts" /> <?php echo esc_html( _x( 'Posts', 'post type general name', 'openlab-import-export' ) ); ?></label></p>
			<ul id="post-filters" class="export-filters">
				<li>
					<label><span class="label-responsive"><?php esc_html_e( 'Categories:', 'openlab-import-export' ); ?></span>
					<?php wp_dropdown_categories( array( 'show_option_all' => __( 'All', 'openlab-import-export' ) ) ); ?>
					</label>
				</li>
				<li>
					<label><span class="label-responsive"><?php esc_html_e( 'Authors:', 'openlab-import-export' ); ?></span>
					<?php
					wp_dropdown_users(
						array(
							'include'         => OpenLab\ImportExport\get_post_author_ids( 'post' ),
							'name'            => 'post_author',
							'multi'           => true,
							'show_option_all' => __( 'All', 'openlab-import-export' ),
							'show'            => 'display_name_with_login',
						)
					);
					?>
					</label>
				</li>
				<li>
					<fieldset>
					<legend class="screen-reader-text"><?php esc_html_e( 'Date range:', 'openlab-import-export' ); ?></legend>
					<label for="post-start-date" class="label-responsive"><?php esc_html_e( 'Start date:', 'openlab-import-export' ); ?></label>
					<select name="post_start_date" id="post-start-date">
						<option value="0"><?php esc_html_e( '&mdash; Select &mdash;', 'openlab-import-export' ); ?></option>
						<?php OpenLab\ImportExport\export_date_options(); ?>
					</select>
					<label for="post-end-date" class="label-responsive"><?php esc_html_e( 'End date:', 'openlab-import-export' ); ?></label>
					<select name="post_end_date" id="post-end-date">
						<option value="0"><?php esc_html_e( '&mdash; Select &mdash;', 'openlab-import-export' ); ?></option>
						<?php OpenLab\ImportExport\export_date_options(); ?>
					</select>
					</fieldset>
				</li>
				<li>
					<label for="post-status" class="label-responsive"><?php _e( 'Status:', 'openlab-import-export' ); ?></label>
					<select name="post_status" id="post-status">
						<option value="0"><?php _e( 'All', 'openlab-import-export' ); ?></option>
						<?php
						$post_stati = get_post_stati( array( 'internal' => false ), 'objects' );
						foreach ( $post_stati as $status ) :
							?>
						<option value="<?php echo esc_attr( $status->name ); ?>"><?php echo esc_html( $status->label ); ?></option>
						<?php endforeach; ?>
					</select>
				</li>
			</ul>
		</fieldset>

		<input type="hidden" name="action" value="export-portfolio" />
		<?php wp_nonce_field( 'ol-export-portfolio' ); ?>
		<?php submit_button( __( 'Download Archive File', 'openlab-import-export' ) ); ?>
	</form>
</div>
