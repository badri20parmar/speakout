<?php
/**
 * Elementor cards bridge:
 * inject SpeakOut signature count + Read more above existing "Sign Petition" button.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'dk_speakout_enqueue_elementor_bridge_assets' );

function dk_speakout_enqueue_elementor_bridge_assets() {
	if ( is_admin() ) {
		return;
	}
	wp_enqueue_script(
		'dk_speakout_elementor_bridge',
		plugins_url( 'js/elementor-bridge.js', dk_speakout_plugin_file() ),
		array( 'jquery' ),
		dk_speakout_asset_version(),
		true
	);
	wp_localize_script(
		'dk_speakout_elementor_bridge',
		'dk_speakout_elementor_bridge',
		array(
			'ajaxurl'    => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
			'nonce'      => wp_create_nonce( 'dk_speakout_elementor_bridge' ),
			'read_more'  => __( 'Read more', 'speakout' ),
			'signatures' => __( 'signatures', 'speakout' ),
		)
	);
}

add_action( 'wp_ajax_dk_speakout_elementor_cards', 'dk_speakout_elementor_cards_ajax' );
add_action( 'wp_ajax_nopriv_dk_speakout_elementor_cards', 'dk_speakout_elementor_cards_ajax' );

function dk_speakout_elementor_cards_ajax() {
	$nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'dk_speakout_elementor_bridge' ) ) {
		wp_send_json_error( array( 'message' => 'invalid_nonce' ), 403 );
	}

	include_once dirname( __FILE__ ) . '/class.petition.php';
	include_once dirname( __FILE__ ) . '/class.speakout.php';

	$options   = get_option( 'dk_speakout_options' );
	$post_ids  = isset( $_REQUEST['post_ids'] ) ? (array) wp_unslash( $_REQUEST['post_ids'] ) : array();
	$post_ids  = array_values( array_filter( array_map( 'absint', $post_ids ) ) );
	$response  = array();
	$use_count = isset( $options['display_count'] ) && (int) $options['display_count'] === 1;
	$dec       = isset( $options['decimal_separator'] ) ? $options['decimal_separator'] : '.';
	$thou      = isset( $options['thousands_separator'] ) ? $options['thousands_separator'] : ',';
	$width     = ( isset( $options['petition_theme'] ) && $options['petition_theme'] === 'basic' ) ? 300 : 200;

	foreach ( $post_ids as $post_id ) {
		$petition_id = dk_speakout_map_post_to_petition_id( $post_id, true );
		if ( ! $petition_id ) {
			continue;
		}
		$petition = new dk_speakout_Petition();
		if ( ! $petition->retrieve( $petition_id ) ) {
			continue;
		}
		$read_more_url = function_exists( 'dk_speakout_petition_hub_url' )
			? dk_speakout_petition_hub_url( array( 'petition' => $petition_id ) )
			: home_url( '/?petition=' . $petition_id );

		$count_html = '';
		if ( $use_count ) {
			if ( (int) $petition->goal > 0 ) {
				$sig_fmt  = number_format( (int) $petition->signatures, 0, $dec, $thou );
				$goal_fmt = number_format( (int) $petition->goal, 0, $dec, $thou );
				$count_html .= '<div class="dk-speakout-signature-count dk-speakout-signature-goal-line">' . sprintf(
					/* translators: 1: current signature count, 2: signature goal */
					__( '%1$s of a %2$s signature goal', 'speakout' ),
					'<span>' . $sig_fmt . '</span>',
					$goal_fmt
				) . '</div>';
				$count_html .= '<div class="dk-speakout-count">0' . dk_speakout_SpeakOut::progress_bar( $petition->goal, $petition->signatures, $width ) . ' ' . $goal_fmt . '</div>';
			} else {
				$count_html .= '<div class="dk-speakout-signature-count"><span>' .
					number_format( (int) $petition->signatures, 0, $dec, $thou ) .
					'</span> ' . esc_html__( 'signatures', 'speakout' ) . '</div>';
			}
		}

		$response[ (string) $post_id ] = array(
			'post_id'      => $post_id,
			'petition_id'  => $petition_id,
			'read_more'    => esc_url_raw( $read_more_url ),
			'count_html'   => $count_html,
		);
	}

	wp_send_json_success( array( 'cards' => $response ) );
}

