<?php
function dk_speakout_license_page() {
    // check security: ensure user has authority
    //if ( ! current_user_can( 'publish_posts' ) ) wp_die( 'Insufficient privileges: You need to be an editor to do that.' );
	
	include_once( 'class.wpml.php' );
	$wpml          = new dk_speakout_WPML();

	include_once( dirname( __FILE__ ) . '/license.view.php' );
}
?>
