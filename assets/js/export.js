jQuery(document).ready(function($){
	var form = $('#export-site'),
		filters = form.find('.export-filters');
	filters.hide();
	form.find('input:radio').on( 'change', function() {
		filters.slideUp('fast');
		switch ( $(this).val() ) {
			case 'attachment': $('#attachment-filters').slideDown(); break;
			case 'posts': $('#post-filters').slideDown(); break;
			case 'pages': $('#page-filters').slideDown(); break;
		}
	});
});
