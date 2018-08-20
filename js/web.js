function af_lateload( handle, media ) {
	var elem = document.getElementById( handle + '-css' );
	elem.media = media;
}
