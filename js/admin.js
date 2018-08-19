var af_assets = null;

// addEventListener support for IE8
function afBindEvent( element, eventName, eventHandler ) {
	if ( element.addEventListener ) {
		element.addEventListener(eventName, eventHandler, false );
	} else if ( element.attachEvent ) {
		element.attachEvent( 'on' + eventName, eventHandler );
	}
}

// Create the iframe
var iframe = document.createElement( 'iframe' );
iframe.setAttribute( 'src', iframeSource );
iframe.style.width = '1px';
iframe.style.height = '1px';
document.body.appendChild( iframe );

// Listen to message from child window
afBindEvent( window, 'message', function ( e ) {
	assets_string = e.data;
	// alert(assets_string);
	var assets = JSON.parse( assets_string );
	afDisplayForm( assets );
} );

function afDisplayForm( assets ) {
	var table_scripts = document.getElementById( 'af_table_scripts' );
	for ( var property in assets.scripts ) {
		if ( assets.scripts.hasOwnProperty( property ) ) {
			var tr = document.createElement( 'tr' );
			var td0 = document.createElement( 'td' );
			var td1 = document.createElement( 'td' );
			td0.innerHTML = assets.scripts[ property ].handle;
			td1.innerHTML = assets.scripts[ property ].src;
			tr.appendChild( td0 );
			tr.appendChild( td1 );
			table_scripts.appendChild( tr );
		}
	}
	var table_styles = document.getElementById( 'af_table_styles' );
	for ( var property in assets.styles ) {
		if ( assets.styles.hasOwnProperty( property ) ) {
			var tr = document.createElement( 'tr' );
			var td0 = document.createElement( 'td' );
			var td1 = document.createElement( 'td' );
			td0.innerHTML = assets.styles[ property ].handle;
			td1.innerHTML = assets.styles[ property ].src;
			tr.appendChild( td0 );
			tr.appendChild( td1 );
			table_styles.appendChild( tr );
		}
	}
}
