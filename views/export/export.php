<div class="wrap nosubsub">
	<h1><?php esc_html_e( 'Export', 'openlab-import-export' ); ?></h1>

	<?php settings_errors(); ?>

	<p><?php esc_html_e( 'Use this tool to create a Site Archive file (.zip) that will be downloaded to your computer and can be used with OpenLab Import-Export to import into another site.', 'openlab-import-export' ); ?></p>

	<form method="post" id="export-site" action="<?php echo admin_url( 'admin-post.php' ); ?>">
		<h2><?php esc_html_e( 'Choose what to export', 'openlab-import-export' ); ?></h2>

		<fieldset>
			<legend class="screen-reader-text"><?php esc_html_e( 'Content to export', 'openlab-import-export' ); ?></legend>

			<p><label><input id="all-content" type="checkbox" name="post-types[]" value="all"  aria-describedby="all-content-desc" /> <?php esc_html_e( 'All content', 'openlab-import-export' ); ?></label></p>
			<p class="description" id="all-content-desc"><?php esc_html_e( 'Choosing &#8216;All content&#8217; will include all of your posts, pages, comments, custom fields, terms, navigation menus, and custom posts. Below you can limit what is included in the export.', 'openlab-import-export' ); ?></p>


			<?php
			/* Posts */
			$post_type_args = [
				'post_type' => 'post',
				'label'     => _x( 'Posts', 'post type general name', 'openlab-import-export' ),
				'options'   => [ 'categories', 'author', 'date', 'status' ],
			];
			OpenLab\ImportExport\Export\Service::render_view_part( 'export/post-type.php', $post_type_args );
			?>

			<?php
			/* Pages */
			$post_type_args = [
				'post_type' => 'page',
				'label'     => __( 'Pages', 'openlab-import-export' ),
				'options'   => [ 'author', 'date', 'status' ],
			];
			OpenLab\ImportExport\Export\Service::render_view_part( 'export/post-type.php', $post_type_args );
			?>

			<?php
			/* Menus */
			$post_type_args = [
				'post_type' => 'nav_menu_item',
				'label'     => __( 'Menus', 'openlab-import-export' ),
				'options'   => [],
			];
			OpenLab\ImportExport\Export\Service::render_view_part( 'export/post-type.php', $post_type_args );
			?>

			<?php
			/* Media */
			$post_type_args = [
				'post_type' => 'attachment',
				'label'     => __( 'Media', 'openlab-import-export' ),
				'options'   => [ 'date' ],
			];
			OpenLab\ImportExport\Export\Service::render_view_part( 'export/post-type.php', $post_type_args );
			?>

			<?php
			$other_post_types = get_post_types(
				[
					'_builtin'   => false,
					'can_export' => true,
				],
				'objects'
			);

			foreach ( $other_post_types as $post_type ) {
				$post_type_args = [
					'post_type' => $post_type->name,
					'label'     => $post_type->label,
					'options'   => [],
				];

				OpenLab\ImportExport\Export\Service::render_view_part( 'export/post-type.php', $post_type_args );
			}
			?>

		</fieldset>


		<input type="hidden" name="action" value="export-portfolio" />

		<?php wp_nonce_field( 'ol-export-site' ); ?>

		<?php submit_button( __( 'Download Archive File', 'openlab-import-export' ) ); ?>
	</form>
</div>
