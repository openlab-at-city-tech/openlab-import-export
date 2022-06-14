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

		<h2><?php esc_html_e( 'Readme file', 'openlab-import-export' ); ?></h2>

		<p id="readme-description"><?php esc_html_e( 'A readme text file will be included with the exported archive file. It will include information on how this archive file can be imported into another site. You can also include your own custom text in the box below.', 'openlab-import-export' ); ?></p>

		<label for="readme-additional-text" class="screen-reader-text"><?php esc_html_e( 'Additional text for readme file', 'openlab-import-export' ); ?></label>

		<textarea class="widefat" name="readme-additional-text" id="readme-additional-text" aria-describedby="readme-description"></textarea>

		<h2><?php esc_html_e( 'Acknowledgements', 'openlab-import-export' ); ?></h2>

		<p id="acknowledgements-description"><?php esc_html_e( 'The text below will be included on an acknowledgments page which is added to the export file and will appear on any site that imports your site’s contents. You can edit the acknowledgments below, if necessary.', 'openlab-import-export' ); ?></p>

		<label for="acknowledgements-text" class="screen-reader-text"><?php esc_html_e( 'Acknowledgments text', 'openlab-import-export' ); ?></label>

		<textarea class="widefat" name="acknowledgements-text" id="acknowledgements-text" aria-describedby="acknowledgements-description"><?php echo esc_textarea( OpenLab\ImportExport\get_acknowledgements_text() ); ?></textarea>

		<input type="hidden" name="action" value="export-site" />

		<?php wp_nonce_field( 'ol-export-site' ); ?>

		<h2><?php esc_html_e( 'Download Archive File', 'openlab-import-export' ); ?></h2>

		<?php
		$space_used = get_space_used();

		$space_used_rounded = round( $space_used, 1 );

		/**
		 * Maximum recommended size for an exported attachments directory, in MB.
		 *
		 * @param int
		 */
		$max_recommended_size = apply_filters( 'openlab_import_export_max_recommended_size_for_exported_attachments', 10 );

		$exceeds_max = $space_used > $max_recommended_size;

		?>

		<p><?php esc_html_e( 'Two versions of the archive file are available. You may choose to download one or both, depending on your needs.', 'openlab-import-export' ); ?></p>

		<ul>
			<li class="archive-download-type">
				<div class="archive-download-type-button">
					<?php
					submit_button(
						// translators: Approximate size of archive file, in MB.
						sprintf( __( 'Download Archive with Attachments (~%s MB)', 'openlab-import-export' ), $space_used_rounded ),
						'primary large',
						'submit-with-attachments',
						false,
						[ 'aria-describedby' => 'submit-with-attachments-gloss' ]
					);
					?>
				</div>

				<div id="submit-with-attachments-gloss">
					<?php if ( $exceeds_max ) : ?>
						<?php esc_html_e( 'Includes all image and other media files uploaded to the selected content. This archive is complete and fully self-contained, and is appropriate for long-term archiving of your site. Because it contains all media files, it can be imported to a new site even when the current site is no longer available; but the zip file may be too large to import into certain WordPress installations.', 'openlab-import-export' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Includes all image and other media files uploaded to the selected content. This archive is complete and fully self-contained, and is appropriate for long-term archiving of your site.', 'openlab-import-export' ); ?>
					<?php endif; ?>
				</div>
			</li>

			<li class="archive-download-type">
				<div class="archive-download-type-button">
					<?php
					submit_button(
						__( 'Download Archive without Attachments (<1 MB)', 'openlab-import-export' ),
						'primary large',
						'submit-without-attachments',
						false,
						[ 'aria-describedby' => 'submit-without-attachments-gloss' ]
					);
					?>
				</div>

				<div id="submit-without-attachments-gloss">
					<?php esc_html_e( '"Archive without Attachments" does not include any attached images or other media files. When importing this archive to a new site, the importer will attempt to download attached files from the current site (requiring the current site to be publicly available). This archive format is useful if your site contains a large amount of media, such that the archive file containing attachments is too large to upload.', 'openlab-import-export' ); ?>
				</div>
			</li>
		</ul>


	</form>
</div>
