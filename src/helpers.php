<?php

namespace OpenLab\ImportExport;

/**
 * Gets a list of user IDs that have authored posts of a given type.
 *
 * @since 1.0.0
 *
 * @param string $post_type The post type. Default 'post'.
 * @return array
 */
function get_post_author_ids( $post_type = 'post' ) {
	global $wpdb;

	$authors = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_author FROM {$wpdb->posts} WHERE post_type = %s", $post_type ) );

	return array_map( 'intval', $authors );

}

/**
 * Creates the date options fields for exporting a given post type.
 *
 * @global wpdb      $wpdb      WordPress database abstraction object.
 * @global WP_Locale $wp_locale WordPress date and time locale object.
 *
 * @since 1.0.0
 *
 * @param string $post_type The post type. Default 'post'.
 */
function export_date_options( $post_type = 'post' ) {
	global $wpdb, $wp_locale;

	$months = $wpdb->get_results(
		$wpdb->prepare(
			"
		SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
		FROM $wpdb->posts
		WHERE post_type = %s AND post_status != 'auto-draft'
		ORDER BY post_date DESC
			",
			$post_type
		)
	);

	$month_count = count( $months );
	if ( ! $month_count || ( 1 === $month_count && 0 === (int) $months[0]->month ) ) {
		return;
	}

	foreach ( $months as $date ) {
		if ( 0 === (int) $date->year ) {
			continue;
		}

		$month = zeroise( $date->month, 2 );
		echo '<option value="' . $date->year . '-' . $month . '">' . $wp_locale->get_month( $month ) . ' ' . $date->year . '</option>';
	}
}

/**
 * Adds the 'multiple' attribute to markup for a select element.
 *
 * @since 1.0.0
 *
 * @param string $markup
 * @return string
 */
function make_dropdown_multiple( $markup ) {
	return str_replace( '<select ', '<select multiple ', $markup );
};

/**
 * Gets the default Acknowledgements text.
 *
 * @since 1.0.0
 *
 * @return string
 */
function get_acknowledgements_text() {
	$admin_names = get_site_admin_names();

	return sprintf(
		// translators: 1. Link to site; 2. List of admin names
		esc_html__( 'This site is based on %1$s by %2$s.', 'openlab-import-export' ),
		sprintf(
			'<a href="%s">%s</a>',
			esc_html( get_option( 'home' ) ),
			esc_html( get_option( 'blogname' ) )
		),
		esc_html( implode( ', ', $admin_names ) )
	);
}

/**
 * Gets a list of names of administrators for the current site.
 *
 * @since 1.0.0
 *
 * @return array
 */
function get_site_admin_names() {
	$admin_names = array_map(
		function( $user ) {
			return $user->display_name;
		},
		get_users( [ 'role' => 'administrator' ] )
	);
	return $admin_names;
}

/**
 * Retrieve the instance of the WP_Filesystem.
 *
 * @todo Extract as utility function.
 *
 * @return mixed An instance of WP_Filesystem_* depending on method.
 */
function openlab_get_filesystem() {
	global $wp_filesystem;

	if ( ! $wp_filesystem ) {
		// Make sure the WP_Filesystem function exists.
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once untrailingslashit( ABSPATH ) . '/wp-admin/includes/file.php';
		}

		WP_Filesystem();
	}

	return $wp_filesystem;
}
