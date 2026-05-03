<?php
function browser(){
	if ( stristr($_SERVER['HTTP_USER_AGENT'], 'FireFox') ){
		return 'FireFox';
	}elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'Chrome') ) {
		return 'Chrome';
	}elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'Safari') ) {
		return 'Safari' ;
	}elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'Opera') ){
		return 'Opera';
	}elseif ( stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE') ){
		return 'MSIE'; 
	}
	return 'Unknown';

}
?>