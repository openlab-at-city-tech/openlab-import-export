(function($){
	$(document).ready(function(){
		var form = $( '#export-site' ),
		  $allContent = $( '#all-content' ),
			previouslyCheckedPostTypes = [];

		form.find( '.export-filters' ).addClass( 'hide-options' );

		form.find( '.post-type-toggle' ).on( 'change', function() {
			toggleOptions( $(this).val() );
		});

		$allContent.on(
			'change',
			function() {
				if ( $(this).is( ':checked' ) ) {
					form.find( '.post-type-toggle:checked' ).each(
						function() {
							previouslyCheckedPostTypes.push( $(this).val() );
						}
					);
					form.find( '.post-type-toggle' ).attr( 'checked', true ).attr( 'disabled', true );
					form.find( '.export-filters' ).removeClass( 'hide-options' );
				} else {
					form.find( '.post-type-toggle' ).attr( 'checked', false ).attr( 'disabled', false );

					previouslyCheckedPostTypes.forEach(
						function( postType ) {
							form.find( '.post-type-toggle[value="' + postType + '"]' ).attr( 'checked', true );
						}
					);
					previouslyCheckedPostTypes = [];

					form.find( '.post-type-toggle' ).each(
						function() {
							toggleOptions( $(this).val() );
						}
					);
				}
			}
		);
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
