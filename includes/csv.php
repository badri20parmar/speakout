<?php
ini_set('max_execution_time', 600); // extend maximum execution time 

// generate CSV file for download
if ( isset( $_REQUEST['csv'] ) && $_REQUEST['csv'] == 'signatures' ) {
	// make sure it executes before headers are sent
	add_action( 'admin_menu', 'dk_speakout_signatures_csv' );
	function dk_speakout_signatures_csv() {
		// check security: ensure user has authority and intention
		if ( ! current_user_can( 'publish_posts' ) ) wp_die( __( 'Insufficient privileges: You need to be an editor to do that.', 'speakout' ) );
		check_admin_referer( 'dk_speakout-download_signatures' );

		include_once( 'class.signature.php' );
		include_once( 'class.petition.php' );
    	include_once( 'class.wpml.php' );
    	$signatures = new dk_speakout_Signature();
    	$petition  = new dk_speakout_Petition();
		

		$petition_id = isset( $_REQUEST['pid'] ) ? $_REQUEST['pid'] : ''; // petition id
        $petition->retrieve( $petition_id );
        
		// retrieve signatures from the database
		$csv_data = $signatures->all( $petition_id, 0, 0, 'csv' );

		// display error message if query returns no results
		if ( count( $csv_data ) < 1 ) {
			echo '<h1>' . __( "No signatures found.", "speakout" ) . '</h1>';
			die();
		}

		// construct CSV filename
		$counter = 0;
		foreach ( $csv_data as $file ) {
			if ( $counter < 1 ) {
				$filename_title = stripslashes( str_replace( ' ', '-', $file->title ) );
				$filename_date  = date( 'Y-m-d', strtotime( current_time( 'mysql', 0 ) ) );
				$filename = $filename_title . '_' . $filename_date . '.csv';
			}
			$counter ++;
		}

		// set up CSV file headers
		header( 'Content-Type: text/octet-stream; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: public' ); // supposed to make stuff work over https

		// get the column headers translated
		$honorific		= __( 'Honorific', 'speakout' );
		$firstname      = __( 'First Name', 'speakout' );
		$lastname       = __( 'Last Name', 'speakout' );
		$email          = __( 'Email Address', 'speakout' );
        $anonymous      = __('Anonymous', 'speakout');
		$street         = __( 'Address', 'speakout' );
		$city           = __( 'City', 'speakout' );
		$state          = __( 'State', 'speakout' );
		$postcode       = __( 'Postal Code', 'speakout' );
		$country        = __( 'Country', 'speakout' );
		$date           = __( 'Date Signed', 'speakout' );
		$confirmed      = __( 'Confirmed', 'speakout' );
		$petition_title = __( 'Petition Title', 'speakout' );
		$petitions_id   = __( 'Petition ID', 'speakout' );
		$email_optin    = __( 'Email Opt-in', 'speakout' );
		$custom_message = __( 'Custom Message', 'speakout' );    
        $custom_field1  = $petition->custom_field_label;
        $custom_field2  = $petition->custom_field2_label;
        $custom_field3  = $petition->custom_field3_label;
        $custom_field4  = $petition->custom_field4_label;
        $custom_field5  = $petition->custom_field5_label;
        $custom_field6  = $petition->custom_field6_label;
        $custom_field7  = $petition->custom_field7_label;
        $custom_field8  = $petition->custom_field8_label; 
        $custom_field9  = $petition->custom_field9_label; 
		$language       = __( 'Language', 'speakout' );
		$IP_address     = __( 'IP Address', 'speakout' );

		// If set, use the custom field label as column header instead of "Custom Field"
		$counter = 0;
		foreach ( $csv_data as $label ) {
			if ( $counter < 1 ) {
				if ( $label->custom_field_label != '' ) {
					$custom_field_label = stripslashes( $label->custom_field_label );
				}
				else {
					$custom_field_label = __( 'Custom Field', 'speakout' );
				}
			}
			$counter ++;
		}

		// construct CSV file header row
		// must use double quotes and separate with tabs
		$csv = "Signature ID,$petitions_id,$honorific,$firstname,$lastname,$email,$anonymous,$street,$city,$state,$postcode,$country,$custom_field1,$custom_field2,$custom_field3,$custom_field4,$custom_field5,$custom_field6,$custom_field7,$custom_field8,$custom_field9,$date,$confirmed,$email_optin,$custom_message,$language,$IP_address";
		$csv .= "\n";

        // if anonymise is disabled, don't show the anonymous column, otherwise it is forced
        if($petition->allow_anonymous != 1){
            $csv = str_replace( $anonymous.",", "", $csv );
        }
        
		// construct CSV file data rows
		foreach ( $csv_data as $signature ) {
		    
		   //if anonymous is disabled we aren't including the anonymous column data
		   if( $petition->allow_anonymous != 1 ){
               $anonymise = "";
           }
            else{
                // but if  is enabled and signer has chosen anonymous, show an asterisk - must include the comma separator
                $anonymise =$signature->anonymise == 1 ? '*' . '","' : '","' ;
            }
		    
			// convert the 1, 0, or '' values of confirmed to readable format
			$confirm = $signature->is_confirmed;
			if ( $confirm == 1 ) {
				$confirm = __( 'confirmed', 'speakout' );
			}
			elseif ( $confirm == 0 ) {
				$confirm = __( 'unconfirmed', 'speakout' );
			}
			else {
				$confirm = '...';
			}
			
			// convert the 1, 0, or '' values to readable format
			$optin = $signature->optin;
			if ( $optin == 1 ) {
				$optin = __( 'Y', 'speakout' );
			}
			elseif ( $optin == 0 ) {
				$optin = __( 'N', 'speakout' );
			}
			else {
				$optin = '...';
			}
			$check1 = $signature->custom_field6;
			if ( $check1 == 1 ) {
				$check1 = __( 'Y', 'speakout' );
			}
			elseif ( $check1 == 0 ) {
				$check1 = __( 'N', 'speakout' );
			}
			else {
				$check1 = '...';
			}
			$check2 = $signature->custom_field7;
			if ( $check2 == 1 ) {
				$check2 = __( 'Y', 'speakout' );
			}
			elseif ( $check2 == 0 ) {
				$check2 = __( 'N', 'speakout' );
			}
			else {
				$check2 = '...';
			}
            $check3 = $signature->custom_field8;
			if ( $check3 == 1 ) {
				$check3 = __( 'Y', 'speakout' );
			}
			elseif ( $check3 == 0 ) {
				$check3 = __( 'N', 'speakout' );
			}
			else {
				$check3 = '...';
			}
            $check4 = $signature->custom_field9;
			if ( $check4 == 1 ) {
				$check4 = __( 'Y', 'speakout' );
			}
			elseif ( $check4 == 0 ) {
				$check4 = __( 'N', 'speakout' );
			}
			else {
				$check4 = '...';
			}
			
			// make nice looking options
			$is_confirmed= $signature->is_confirmed == 1 ? "Y" : "N" ;
			$optin = $signature->optin == 1 ? "Y" : "N" ;
			
			$csv .=  stripslashes('"' . 
            trim($signature->id) . '","' .
			trim($signature->petitions_id) . '","' . 
			trim($signature->honorific) . '","' . 
			trim($signature->first_name) . '","' . 
			trim($signature->last_name) . '","' .                                 
			trim($signature->email) . '","' . 
            $anonymise .
			trim($signature->street_address) . '","' . 
			trim($signature->city) . '","' . 
			trim($signature->state) . '","' . 
			trim($signature->postcode) . '","' . 
			trim($signature->country) . '","' . 
			trim($signature->custom_field) . '","' .
            trim($signature->custom_field2) . '","' .
			trim($signature->custom_field3) . '","' .
            trim($signature->custom_field4) . '","' .
            trim($signature->custom_field5) . '","' .
            $check1 . '","' .
            $check2 . '","' .
            $check3 . '","' .
            $check4 . '","' .                     
			trim($signature->date) . '","' . 
			trim($is_confirmed) . '","' .  
			$optin . '","' . 
			trim($signature->custom_message) . '","' . 
            trim($signature->language) . '","' . 
			trim($signature->IP_address) . '"' );
			$csv .= "\n";
		}

		// output CSV file in a UTF-8 format that Excel can understand
		echo chr( 255 ) . chr( 254 ) . mb_convert_encoding( $csv, 'UTF-16LE', 'UTF-8' );
		exit;
	}
}

?>