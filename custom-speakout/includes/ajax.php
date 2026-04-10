<?php

/**
 * Handle public petition form submissions
 */
add_action( 'wp_ajax_dk_speakout_sendmail', 'dk_speakout_sendmail' );
add_action( 'wp_ajax_nopriv_dk_speakout_sendmail', 'dk_speakout_sendmail' );
function dk_speakout_sendmail() {

	// set WPML language
	global $sitepress;
	$lang = isset( $_POST['lang'] ) ? $_POST['lang'] : '';
	if ( isset( $sitepress ) ) {
		$sitepress->switch_lang( $lang, true );
	}

	include_once( 'class.signature.php' );
	include_once( 'class.petition.php' );
	include_once( 'class.mail.php' );
	include_once( 'class.wpml.php' );
	$the_signature = new dk_speakout_Signature();
	$the_petition  = new dk_speakout_Petition();
	$wpml          = new dk_speakout_WPML();
	$options       = get_option( 'dk_speakout_options' );

	// clean posted signature fields
	$the_signature->populate_from_post();
	$the_signature->create_confirmation_code();

	// get petition data
	$the_petition->retrieve( $the_signature->petitions_id );
	$wpml->translate_petition( $the_petition );
	$options = $wpml->translate_options( $options );

	// check if submitted email address is already in use for this petition
	if ( $the_signature->has_unique_email( $the_signature->email, $the_signature->petitions_id, $the_petition->hide_email_field ) ) {

		// handle custom petition messages       
		$original_message = $the_petition->petition_message ;
		if ( $the_petition->is_editable && $the_signature->submitted_message != $original_message ) {
			$the_signature->custom_message = trim( $the_signature->submitted_message  );
		}

		// does petition require email confirmation?
		if ( $the_petition->requires_confirmation ) {

			$the_signature->is_confirmed = 0;
			$the_signature->create_confirmation_code();
			dk_speakout_Mail::send_confirmation( $the_petition, $the_signature, $options );
		}
		else {
			if ( $the_petition->sends_email ) {
			    //email target
				dk_speakout_Mail::send_petition( $the_petition, $the_signature,"" );
			}

            //add to ActiveCampaign if enabled and optin checked
            if( $the_petition->activecampaign_enable && $the_signature->optin){
                
               $map1fieldValue = $the_petition->activecampaign_map1field == "" ? "" : $the_signature->honorific;
               $map5fieldValue = $the_petition->activecampaign_map5field == "" ? "" : $the_signature->street_address;
               $map6fieldValue = $the_petition->activecampaign_map6field == "" ? "" : $the_signature->city;
               $map7fieldValue = $the_petition->activecampaign_map7field == "" ? "" : $the_signature->state;
               $map8fieldValue = $the_petition->activecampaign_map8field == "" ? "" : $the_signature->postcode;
               $map9fieldValue = $the_petition->activecampaign_map9field == "" ? "" : $the_signature->country;
               $map10fieldValue = $the_petition->activecampaign_map10field == "" ? "" : $the_signature->custom_field;
               $map11fieldValue = $the_petition->activecampaign_map11field == "" ? "" : $the_signature->custom_field2;
               $map12fieldValue = $the_petition->activecampaign_map12field == "" ? "" : $the_signature->custom_field3;
               $map13fieldValue = $the_petition->activecampaign_map13field == "" ? "" : $the_signature->custom_field4;
               $map14fieldValue = $the_petition->activecampaign_map14field == "" ? "" : $the_signature->custom_field5;

                dk_speakout_Mail::add2ActiveCampaign( 
                    $the_petition->activecampaign_api_key, $the_petition->activecampaign_server, $the_petition->activecampaign_list_id,
                    $the_petition->activecampaign_map1field, $map1fieldValue, 
                    $the_petition->activecampaign_map2field, $the_signature->first_name, 
                    $the_petition->activecampaign_map3field, $the_signature->last_name, 
                    $the_petition->activecampaign_map4field, $the_signature->email, 
                    $the_petition->activecampaign_map5field, $map5fieldValue, 
                    $the_petition->activecampaign_map6field, $map6fieldValue, 
                    $the_petition->activecampaign_map7field, $map7fieldValue, 
                    $the_petition->activecampaign_map8field, $map8fieldValue, 
                    $the_petition->activecampaign_map9field, $map9fieldValue, 
                    $the_petition->activecampaign_map10field, $map10fieldValue, 
                    $the_petition->activecampaign_map11field, $map11fieldValue, 
                    $the_petition->activecampaign_map12field, $map12fieldValue, 
                    $the_petition->activecampaign_map13field, $map13fieldValue, 
                    $the_petition->activecampaign_map14field, $map14fieldValue
                );
            }

            //add to MailChimp if enabled and optin checked
            if( $the_petition->mailchimp_enable && $the_signature->optin ){
                $countryValue = $the_signature->country == "" ? "" : $the_signature->country;
                dk_speakout_Mail::add2MailChimp( $the_signature->email, $the_signature->first_name, $the_signature->last_name, $the_signature->country, $the_petition->mailchimp_list_id, $the_petition->mailchimp_api_key, $the_petition->mailchimp_server);
            }
            
            //add to Mailerlite if enabled and optin checked
            if( $the_petition->mailerlite_enable && $the_signature->optin ){
                dk_speakout_Mail::add2Mailerlite( $the_signature->email, $the_signature->first_name, $the_signature->last_name, $the_petition->mailerlite_group_id, $the_petition->mailerlite_api_key);
            }
            
           //add to Sendy if enabled and optin checked
            if( $the_petition->sendy_enable && $the_signature->optin ){
                dk_speakout_Mail::add2Sendy(  $the_petition->sendy_server, $the_signature->email, $the_signature->first_name, $the_signature->last_name, $the_petition->sendy_list_id, $the_petition->sendy_api_key);
            }
                
            
		}

		// add signature to database
		$the_signature->create( $the_signature->petitions_id, $the_petition->increase_goal);
		dk_speakout_Mail::send_thank_you( $the_petition, $the_signature, $options );

		// display success message
		$success_message = $options['success_message'];
		$success_message = str_replace( '%first_name%', strip_tags($the_signature->first_name), $success_message );
		$success_message = str_replace( '%last_name%', strip_tags($the_signature->last_name), $success_message );
        $success_message = str_replace( '%signature_number%', ($the_petition->signatures + 1), $success_message );
		
		

		if ( $options['display_anedot'] == 'enabled' && $options['anedot_page_id'] <> "" ){ // enable Anedot.com form inside the confirmation message area

    		if ( $options['anedot_page_id'] <> "" ) {
    			$success_message .= '<iframe src="https://secure.anedot.com/' . $options['anedot_page_id'];
    			if ($options['anedot_embed_pref'] == true) {
    				$success_message .= '?embed=true';
    				}
    			$success_message .= '" width="' . $options['anedot_iframe_width'] . '" height="' . $options['anedot_iframe_height'] . '" frameborder="0"></iframe>';
    			}
		}

		if($the_petition->displays_custom_message == 1){
			$success_message .= stripslashes( esc_attr( $the_petition->custom_message_label ) );
		}

		$json_response = array(
			'status'  => 'success',
			'message' => $success_message,
			'manage_code' => $the_signature->confirmation_code
		);
		$json_response = json_encode( $json_response );

		echo $json_response;
	}
	else {
		
		$json_response = array(
			'status'  => 'error',
			'message' => $options['already_signed_message']
		);
		$json_response = json_encode( $json_response );

		echo $json_response;
	}

	// end AJAX processing
	die();
}

add_action( 'wp_ajax_dk_speakout_paginate_signaturelist', 'dk_speakout_paginate_signaturelist' );
add_action( 'wp_ajax_nopriv_dk_speakout_paginate_signaturelist', 'dk_speakout_paginate_signaturelist' );
function dk_speakout_paginate_signaturelist() {
	include_once( 'class.signaturelist.php' );
	$list = new dk_speakout_Signaturelist();
	$hide_unconfirmed = isset( $_POST['hideUnconfirmed'] ) ? filter_var( $_POST['hideUnconfirmed'], FILTER_VALIDATE_BOOLEAN ) : true;
	$table = $list->table( $_POST['id'], $_POST['start'], $_POST['limit'], 'ajax', $_POST['dateformat'], '&lt;&lt;', '&gt;', '&lt;', '&gt;&gt;', $hide_unconfirmed );
	echo $table;
	// end AJAX processing
	die();
}

add_action( 'wp_ajax_dk_speakout_manage_signature', 'dk_speakout_manage_signature' );
add_action( 'wp_ajax_nopriv_dk_speakout_manage_signature', 'dk_speakout_manage_signature' );
function dk_speakout_manage_signature() {
	check_ajax_referer( 'dk_speakout_manage_signature', 'nonce' );
	include_once( 'class.signature.php' );
	$signature = new dk_speakout_Signature();
	$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
	$code = isset( $_POST['code'] ) ? sanitize_text_field( $_POST['code'] ) : '';
	$action = isset( $_POST['manage_action'] ) ? sanitize_text_field( $_POST['manage_action'] ) : '';
	$message = isset( $_POST['custom_message'] ) ? sanitize_textarea_field( $_POST['custom_message'] ) : '';

	if ( empty( $email ) || empty( $code ) ) {
		wp_send_json( array( 'status' => 'error', 'message' => __( 'Missing signature credentials.', 'speakout' ) ) );
	}
	if ( $action === 'delete' ) {
		$deleted = $signature->delete_by_code_email( $code, $email );
		wp_send_json( array( 'status' => $deleted ? 'success' : 'error', 'message' => $deleted ? __( 'Your signature has been removed.', 'speakout' ) : __( 'Signature not found.', 'speakout' ) ) );
	}
	if ( $action === 'update' ) {
		$found = $signature->retrieve_by_code_email( $code, $email );
		if ( ! $found ) {
			wp_send_json( array( 'status' => 'error', 'message' => __( 'Signature not found.', 'speakout' ) ) );
		}
		$updated = $signature->update_custom_message( $signature->id, $message );
		wp_send_json( array( 'status' => $updated ? 'success' : 'error', 'message' => $updated ? __( 'Your comment has been updated.', 'speakout' ) : __( 'Unable to update comment.', 'speakout' ) ) );
	}
	wp_send_json( array( 'status' => 'error', 'message' => __( 'Invalid action.', 'speakout' ) ) );
}

?>