<?php
$r = array_merge(
	[
		'post_type' => 'post',
		'label'     => '',
		'options'   => [ 'categories', 'author', 'date', 'status' ],
	],
	$args
);
?>

<p><label><input id="toggle-<?php echo esc_attr( $r['post_type'] ); ?>" class="content-type-toggle post-type-toggle" type="checkbox" name="post-types[]" value="<?php echo esc_attr( $r['post_type'] ); ?>" /> <?php echo esc_html( $r['label'] ); ?></label></p>

<?php if ( ! empty( $r['options'] ) ) : ?>
	<ul id="<?php echo esc_attr( $r['post_type'] ); ?>-filters" class="export-filters">
		<?php if ( in_array( 'categories', $r['options'], true ) ) : ?>
			<li>
				<label><span class="label-responsive"><?php esc_html_e( 'Categories:', 'openlab-import-export' ); ?></span>
				<?php
				wp_dropdown_categories(
					[
						'name'            => $r['post_type'] . '_cat',
						'show_option_all' => __( 'All', 'openlab-import-export' )
					]
				);
				?>
				</label>
			</li>
		<?php endif; ?>

		<?php if ( in_array( 'author', $r['options'], true ) ) : ?>
			<li class="export-author">
				<label><span class="label-responsive"><?php esc_html_e( 'Authors:', 'openlab-import-export' ); ?></span>
				<?php
				add_filter( 'wp_dropdown_users', '\OpenLab\ImportExport\make_dropdown_multiple' );
				wp_dropdown_users(
					array(
						'class'           => 'use-select2 export-author-dropdown',
						'include'         => OpenLab\ImportExport\get_post_author_ids( $r['post_type'] ),
						'name'            => $r['post_type'] . '_author[]',
						'multi'           => true,
						'show_option_all' => __( 'All', 'openlab-import-export' ),
						'show'            => 'display_name_with_login',
					)
				);
				remove_filter( 'wp_dropdown_users', '\OpenLab\ImportExport\make_dropdown_multiple' );
				?>
				</label>
			</li>
		<?php endif; ?>

		<?php if ( in_array( 'date', $r['options'], true ) ) : ?>
			<li>
				<fieldset>
				<legend class="screen-reader-text"><?php esc_html_e( 'Date range:', 'openlab-import-export' ); ?></legend>
				<label for="<?php echo esc_attr( $r['post_type'] ); ?>-start-date" class="label-responsive"><?php esc_html_e( 'Start date:', 'openlab-import-export' ); ?></label>
				<select name="<?php echo esc_attr( $r['post_type'] ); ?>_start_date" id="<?php echo esc_attr( $r['post_type'] ); ?>-start-date">
					<option value="0"><?php esc_html_e( '&mdash; Select &mdash;', 'openlab-import-export' ); ?></option>
					<?php OpenLab\ImportExport\export_date_options(); ?>
				</select>
				<label for="<?php echo esc_attr( $r['post_type'] ); ?>-end-date" class="label-responsive"><?php esc_html_e( 'End date:', 'openlab-import-export' ); ?></label>
				<select name="<?php echo esc_attr( $r['post_type'] ); ?>_end_date" id="<?php echo esc_attr( $r['post_type'] ); ?>-end-date">
					<option value="0"><?php esc_html_e( '&mdash; Select &mdash;', 'openlab-import-export' ); ?></option>
					<?php OpenLab\ImportExport\export_date_options(); ?>
				</select>
				</fieldset>
			</li>
		<?php endif; ?>

		<?php if ( in_array( 'status', $r['options'], true ) ) : ?>
			<li>
				<label for="<?php echo esc_attr( $r['post_type'] ); ?>-status" class="label-responsive"><?php esc_html_e( 'Status:', 'openlab-import-export' ); ?></label>
				<select name="<?php echo esc_attr( $r['post_type'] ); ?>_status" id="<?php echo esc_attr( $r['post_type'] ); ?>-status">
					<option value="0"><?php esc_html_e( 'All', 'openlab-import-export' ); ?></option>
					<?php
					$post_stati = get_post_stati( array( 'internal' => false ), 'objects' );
					foreach ( $post_stati as $status ) :
						?>
					<option value="<?php echo esc_attr( $status->name ); ?>"><?php echo esc_html( $status->label ); ?></option>
					<?php endforeach; ?>
				</select>
			</li>
		<?php endif; ?>
	</ul>
<?php endif; ?>
