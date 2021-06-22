(function($){
	$(document).ready(function(){
		var form = $('#export-site');

		form.find('.export-filters').addClass( 'hide-options' );

		form.find('.post-type-toggle').on( 'change', function() {
			toggleOptions( $(this).val() );
		});
	});

	function toggleOptions( postType ) {
		var $toggle = $( '.post-type-toggle[value="' + postType + '"]' );
		var $toggleFilters = $( '#' + postType + '-filters' );

		if ( $toggle.is( ':checked' ) ) {
			$toggleFilters.removeClass( 'hide-options' );
		} else {
			$toggleFilters.addClass( 'hide-options' );
		}
	}
}(jQuery));
