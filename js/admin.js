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

function afCreateSelect( type, handle, val ) {
	var select = document.createElement( 'select' );
	var idx = 'asset_finder[' + type + '][' + handle + ']';
	select.id = idx;
	select.name = idx;
	var opt0 = document.createElement( 'option' );
	opt0.text = '-- No change --';
	opt0.value = '0';
	var opt1 = document.createElement( 'option' );
	opt1.text = 'Late-load';
	opt1.value = '1';
	var opt2 = document.createElement( 'option' );
	opt2.text = 'Remove';
	opt2.value = '2';
	select.appendChild( opt0 );
	select.appendChild( opt1 );
	select.appendChild( opt2 );
	select.value = val;
	return select;
}

function afSetRowColor( tr, action ) {
	tr.classList.add( 'af_row_' + action );
}

function afDisplayForm( assets ) {
	// alert( asset_finder_handles );
	var table_scripts = document.getElementById( 'af_table_scripts' );
	var count = 0;
	for ( var property in assets.scripts ) {
		if ( assets.scripts.hasOwnProperty( property ) ) {
			handle = assets.scripts[ property ].handle;
			var action = ('undefined' !== typeof( asset_finder_handles['scripts'][ handle ] ) ) ? asset_finder_handles['scripts'][ handle ] : '0';
			// console.log( action	);
			var selected_value = action;
			// var select = afCreateSelect( 'asset_finder[scripts][' + handle + ']', selected_value );
			var select = afCreateSelect( 'scripts', handle, selected_value );
			var tr = document.createElement( 'tr' );
			var td0 = document.createElement( 'td' );
			var td1 = document.createElement( 'td' );
			var td2 = document.createElement( 'td' );
			var id = 'scripts_' + handle;
			tr.id = id;
			td1.appendChild( select );
			td0.innerHTML = handle;
			td2.innerHTML = assets.scripts[ property ].src;
			tr.appendChild( td0 );
			tr.appendChild( td1 );
			tr.appendChild( td2 );
			table_scripts.appendChild( tr );
			afSetRowColor( tr, action );
			count ++;
		}
	}
	var table_styles = document.getElementById( 'af_table_styles' );
	var count = 0;
	for ( var property in assets.styles ) {
		if ( assets.styles.hasOwnProperty( property ) ) {
			handle = assets.styles[ property ].handle;
			var action = ('undefined' !== typeof( asset_finder_handles['styles'][ handle ] ) ) ? asset_finder_handles['styles'][ handle ] : '0';
			var id = 'styles_' + handle;
			var selected_value = action;
			var select = afCreateSelect( 'styles', handle, selected_value );
			var tr = document.createElement( 'tr' );
			var td0 = document.createElement( 'td' );
			var td1 = document.createElement( 'td' );
			var td2 = document.createElement( 'td' );
			tr.id = id;
			td1.appendChild( select );
			td0.innerHTML = handle;
			td2.innerHTML = assets.styles[ property ].src;
			tr.appendChild( td0 );
			tr.appendChild( td1 );
			tr.appendChild( td2 );
			table_styles.appendChild( tr );
			afSetRowColor( tr, action );
			console.log( action );
		}
	}
}
