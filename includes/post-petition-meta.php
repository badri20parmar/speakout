<?php
/**
 * Link WordPress posts/pages to SpeakOut petition IDs (for Elementor loops, excerpts, etc.).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DK_SPEAKOUT_PETITION_POST_META', 'dk_speakout_petition_id' );

add_action( 'init', 'dk_speakout_register_petition_post_meta' );

function dk_speakout_register_petition_post_meta() {
	$types = apply_filters( 'dk_speakout_petition_link_post_types', array( 'post', 'page', 'petition' ) );
	foreach ( $types as $type ) {
		if ( ! post_type_exists( $type ) ) {
			continue;
		}
		register_post_meta(
			$type,
			DK_SPEAKOUT_PETITION_POST_META,
			array(
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}

/**
 * @param int $post_id Post ID.
 * @return int Linked SpeakOut petition ID, or 0.
 */
function dk_speakout_get_petition_id_for_post( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return 0;
	}
	return absint( get_post_meta( $post_id, DK_SPEAKOUT_PETITION_POST_META, true ) );
}

/**
 * Best-effort mapper by title for existing petition CPT entries.
 *
 * @param int $post_id Post ID.
 * @return int Petition ID, or 0.
 */
function dk_speakout_guess_petition_id_for_post( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return 0;
	}
	$post = get_post( $post_id );
	if ( ! $post || $post->post_title === '' ) {
		return 0;
	}
	global $wpdb, $db_petitions;
	if ( empty( $db_petitions ) ) {
		return 0;
	}
	$title        = wp_strip_all_tags( $post->post_title );
	$title_no_sig = preg_replace( '/^sign:\s*/i', '', $title );

	$sql = $wpdb->prepare(
		"SELECT id FROM $db_petitions WHERE title = %s OR title = %s OR title = %s ORDER BY id DESC LIMIT 1",
		$title,
		$title_no_sig,
		'Sign: ' . $title_no_sig
	);
	$found = absint( $wpdb->get_var( $sql ) );
	return $found;
}

/**
 * Resolve petition ID from post linkage, with optional title-based fallback.
 *
 * @param int  $post_id Post ID.
 * @param bool $allow_guess Attempt title-based guess fallback.
 * @return int Petition ID, or 0.
 */
function dk_speakout_map_post_to_petition_id( $post_id, $allow_guess = true ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return 0;
	}
	$linked = dk_speakout_get_petition_id_for_post( $post_id );
	if ( $linked ) {
		return $linked;
	}
	if ( ! $allow_guess ) {
		return 0;
	}
	$guess = dk_speakout_guess_petition_id_for_post( $post_id );
	if ( $guess ) {
		update_post_meta( $post_id, DK_SPEAKOUT_PETITION_POST_META, $guess );
		return $guess;
	}
	return 0;
}

/**
 * Resolve petition ID from shortcode attributes and current post (Elementor Post loop sets this per item).
 *
 * @param array      $attr              Shortcode attributes.
 * @param int|null   $legacy_default    Used when not in the main query loop (sidebars, etc.) — e.g. 1 for legacy [signaturecount].
 * @return int
 */
function dk_speakout_resolve_petition_id_from_shortcode_atts( $attr, $legacy_default = null ) {
	$attr = is_array( $attr ) ? $attr : array();

	if ( isset( $attr['id'] ) && is_numeric( $attr['id'] ) ) {
		return absint( $attr['id'] );
	}
	if ( array_key_exists( 'petition', $_GET ) && is_numeric( $_GET['petition'] ) ) {
		return absint( $_GET['petition'] );
	}
	$post_id = 0;
	if ( isset( $attr['post_id'] ) && is_numeric( $attr['post_id'] ) ) {
		$post_id = absint( $attr['post_id'] );
	} elseif ( get_the_ID() ) {
		$post_id = get_the_ID();
	}
	if ( $post_id ) {
		$from_meta = dk_speakout_get_petition_id_for_post( $post_id );
		$mapped = dk_speakout_map_post_to_petition_id( $post_id, true );
		if ( $mapped ) {
			return $mapped;
		}
	}
	if ( ! in_the_loop() && null !== $legacy_default ) {
		return absint( $legacy_default );
	}
	return 0;
}

/**
 * Allow shortcodes in the manual excerpt (e.g. Elementor Posts widget → Excerpt).
 */
add_filter( 'the_excerpt', 'dk_speakout_excerpt_do_shortcode', 11 );
function dk_speakout_excerpt_do_shortcode( $excerpt ) {
	if ( $excerpt !== '' && strpos( $excerpt, '[' ) !== false ) {
		return do_shortcode( $excerpt );
	}
	return $excerpt;
}

if ( is_admin() ) {
	add_action( 'add_meta_boxes', 'dk_speakout_add_petition_link_metabox' );
	add_action( 'save_post', 'dk_speakout_save_petition_link_metabox', 10, 2 );
}

function dk_speakout_add_petition_link_metabox() {
	$types = apply_filters(
		'dk_speakout_petition_link_metabox_post_types',
		apply_filters( 'dk_speakout_petition_link_post_types', array( 'post', 'page', 'petition' ) )
	);
	foreach ( $types as $type ) {
		add_meta_box(
			'dk_speakout_petition_link',
			__( 'SpeakOut petition', 'speakout' ),
			'dk_speakout_render_petition_link_metabox',
			$type,
			'side',
			'default'
		);
	}
}

function dk_speakout_render_petition_link_metabox( $post ) {
	wp_nonce_field( 'dk_speakout_save_petition_link', 'dk_speakout_petition_link_nonce' );
	$current = dk_speakout_get_petition_id_for_post( $post->ID );
	global $wpdb, $db_petitions;
	if ( empty( $db_petitions ) ) {
		echo '<p>' . esc_html__( 'SpeakOut is not fully loaded.', 'speakout' ) . '</p>';
		return;
	}
	include_once dirname( __FILE__ ) . '/class.petition.php';
	$petitions = new dk_speakout_Petition();
	$rows      = $petitions->all( 0, 500, 'DESC' );
	echo '<p><label for="dk_speakout_petition_id_select">' . esc_html__( 'Link this entry to a petition (for grids & shortcodes):', 'speakout' ) . '</label></p>';
	echo '<select name="' . esc_attr( DK_SPEAKOUT_PETITION_POST_META ) . '" id="dk_speakout_petition_id_select" class="widefat">';
	echo '<option value="">' . esc_html__( '— None —', 'speakout' ) . '</option>';
	foreach ( $rows as $row ) {
		$pid   = absint( $row->id );
		$title = isset( $row->title ) ? esc_html( wp_strip_all_tags( stripslashes( $row->title ) ) ) : '';
		printf(
			'<option value="%1$d"%3$s>%1$d — %2$s</option>',
			$pid,
			$title,
			selected( $current, $pid, false )
		);
	}
	echo '</select>';
	echo '<p class="description">' . esc_html__( 'In Elementor, use a Shortcode widget or the post excerpt with e.g. [speakout_card_teaser]. Each card uses the petition linked here.', 'speakout' ) . '</p>';
}

function dk_speakout_save_petition_link_metabox( $post_id, $post ) {
	if ( ! isset( $_POST['dk_speakout_petition_link_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dk_speakout_petition_link_nonce'] ) ), 'dk_speakout_save_petition_link' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$types = apply_filters(
		'dk_speakout_petition_link_metabox_post_types',
		apply_filters( 'dk_speakout_petition_link_post_types', array( 'post', 'page', 'petition' ) )
	);
	if ( ! in_array( $post->post_type, $types, true ) ) {
		return;
	}
	$key = DK_SPEAKOUT_PETITION_POST_META;
	if ( ! isset( $_POST[ $key ] ) || $_POST[ $key ] === '' ) {
		delete_post_meta( $post_id, $key );
		return;
	}
	update_post_meta( $post_id, $key, absint( wp_unslash( $_POST[ $key ] ) ) );
}
