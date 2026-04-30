<?php
/**
 * Shared URLs for petition deep links and signature management.
 */

if ( ! function_exists( 'dk_speakout_petition_hub_url' ) ) {
	/**
	 * Base URL for ?petition=, ?dkspeakoutmanage=, etc.
	 * Configure the "Petition hub page" in SpeakOut settings (page should include [emailpetition] and [signaturemanage]).
	 *
	 * @param array $query_args Query arguments, or empty for base URL only.
	 * @return string
	 */
	function dk_speakout_petition_hub_url( $query_args = array() ) {
		$options  = get_option( 'dk_speakout_options' );
		$page_id  = isset( $options['petition_hub_page_id'] ) ? absint( $options['petition_hub_page_id'] ) : 0;
		$base = $page_id ? get_permalink( $page_id ) : home_url( '/' );
		if ( ! $base ) {
			$base = home_url( '/' );
		}
		if ( empty( $query_args ) ) {
			return $base;
		}
		return add_query_arg( $query_args, $base );
	}
}
