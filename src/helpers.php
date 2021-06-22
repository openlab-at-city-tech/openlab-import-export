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
