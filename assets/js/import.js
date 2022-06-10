(function ($) {
	var $uploadSubmitButton = $('input#upload-submit');
	var evtSource = new EventSource( ImportData.url );

	var updateImportStatus = function(data) {
		var message = $('#import-status-message').find('strong');

		if ( ! data.error ) {
			message.text( ImportData.strings.complete );
		} else {
			message.html( ImportData.strings.error );
		}
	};

	evtSource.onmessage = function ( message ) {
		var data = JSON.parse( message.data );
		switch ( data.action ) {
			case 'complete':
				evtSource.close();
				updateImportStatus(data);
				break;
		}
	};

	evtSource.addEventListener( 'log', function ( message ) {
		var data = JSON.parse( message.data );
		var row = document.createElement('tr');
		var level = document.createElement( 'td' );
		level.appendChild( document.createTextNode( data.level ) );
		row.appendChild( level );

		var message = document.createElement( 'td' );
		message.appendChild( document.createTextNode( data.message ) );
		row.appendChild( message );

		jQuery('#import-log').append( row );
	});

	// Validate zip input.
	$( '#importzip' ).on( 'change', function( el ) {
		var theFile = el.target.files[0];
		var error = '';

		// File type.
		if ( 'application/zip' !== theFile.type ) {
			error = ImportData.strings.errorType;
		}

		if ( ! error ) {
			var maxUploadSize = parseInt( ImportData.maxUploadSize );
			if ( theFile.size > ImportData.maxUploadSize ) {
				error = ImportData.strings.errorSize;
			}
		}

		if ( error ) {
			$( '#ol-import-error' ).html( error );
			$uploadSubmitButton.prop( 'disabled', true );
		} else {
			$( '#ol-import-error' ).html( '' );
			$uploadSubmitButton.prop( 'disabled', false );
		}
	} );

	$uploadSubmitButton.on( 'click', function( e ) {
		$uploadSubmitButton.val( ImportData.strings.uploading ).attr( 'disabled', true );
		$uploadSubmitButton.closest( 'form' ).submit();
	} );
})(jQuery);
