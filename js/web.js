function af_lateload( handle, media ) {
	var elem = document.getElementById( handle + '-css' );
	console.log( handle + '-css'  );
	if ( null !== elem ) {
		elem.media = media;
	}
}
