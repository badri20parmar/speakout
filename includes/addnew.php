<?php

if( isset($_POST['ajax']) && isset($_POST['listID']) ){
// echo $_POST['listID'] . "";
 exit;
}

// page used for creating new petitions
	$options = get_option( 'dk_speakout_options' );
// and for editing existing petitions
function dk_speakout_addnew_page() {
	// check security: ensure user has authority
	if ( ! current_user_can( 'publish_posts' ) ) wp_die( 'Insufficient privileges: You need to be an editor to do that.' );

	include_once( 'class.petition.php' );
	include_once( 'class.wpml.php' );
	$petition     = new dk_speakout_Petition();
	$wpml         = new dk_speakout_WPML();
	$options = get_option( 'dk_speakout_options' );
	$action       = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
	$petition->id = isset( $_REQUEST['id'] ) ? absint( $_REQUEST['id'] ) : '';

	$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
	$tab    = isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'dk-petition-tab-01';
	
	switch( $action ) {
		// add a new petition to database
		// then display form for editing the new petition
		case 'create' :
			// security: ensure user has intention
			check_admin_referer( 'dk_speakout-create_petition' );

			$petition->populate_from_post();
			$petition->create();
			$wpml->register_petition( $petition );

			// set up page display variables
			$page_title  = __( 'Edit Petition', 'speakout' );
			$nonce       = 'dk_speakout-update_petition' . $petition->id;
			$action      = 'update';
			$x_date      = $petition->get_expiration_date_components();
			$button_text = __( 'Update Petition', 'speakout' );

			// construct update message box content
			$emailpetition_shortcode = '[emailpetition id="' . $petition->id . '"]';
			$signaturelist_shortcode = '[signaturelist id="' . $petition->id . '"]';
			$signaturecount_shortcode = '[signaturecount id="' . $petition->id . '"]';
			$start_tag               = '<strong>';
			$end_tag                 = '</strong>';
			$message_text            = __( 'Petition created. Use %1$s %2$s %3$s to display in a page or post. Use %1$s %4$s %3$s to display the signatures list or %1$s %5$s %3$s to show just the signature count.', 'speakout' );
			$message_update          = sprintf( $message_text, $start_tag, $emailpetition_shortcode, $end_tag, $signaturelist_shortcode, $signaturecount_shortcode );

			break;

		// 'edit' is only called from text links on the Email Petitions page
		// displays existing petition for alteration and submits with 'update' action
		case 'edit' :
			// security: ensure user has intention
			check_admin_referer( 'dk_speakout-edit_petition' . $petition->id );

			$petition->retrieve( $petition->id );

			// set up page display variables
			$page_title     = __( 'Edit Petition', 'speakout' );
			$nonce          = 'dk_speakout-update_petition' . $petition->id;
			$action         = 'update';
			$x_date         = $petition->get_expiration_date_components();
			$button_text    = __( 'Update Petition', 'speakout' );
			$message_update = '';

			break;
		
		case 'duplicate' :
			// security: ensure user has intention
			check_admin_referer( 'dk_speakout-edit_petition' . $petition->id );

			 $petition->retrieve( $petition->id );
			 $petition->id = "";
			 $petition->title .= " duplicate";

			$petition->create();
			$wpml->register_petition( $petition );

			// set up page display variables
			$page_title     = __( 'Duplicate Petition', 'speakout' );
			$nonce          = 'dk_speakout-update_petition' . $petition->id;
			$action         = 'update';
			$x_date         = $petition->get_expiration_date_components();
			$button_text    = __( 'Save Duplicate Petition', 'speakout' );

			// construct update message box content
			$emailpetition_shortcode = '[emailpetition id="' . $petition->id . '"]';
			$signaturelist_shortcode = '[signaturelist id="' . $petition->id . '"]';
			$signaturecount_shortcode = '[signaturecount id="' . $petition->id . '"]';
			$start_tag               = '<strong>';
			$end_tag                 = '</strong>';
			$message_text            = __( 'Petition created - you can edit it here. Use %1$s %2$s %3$s to display in a page or post.', 'speakout' );
			$message_update          = sprintf( $message_text, $start_tag, $emailpetition_shortcode, $end_tag, $signaturelist_shortcode, $signaturecount_shortcode );
			

			break;
		// alter an existing petition
		case 'update' :
			// security: ensure user has intention
			check_admin_referer( 'dk_speakout-update_petition' . $petition->id );

			$petition->populate_from_post();
			$petition->update( $petition->id );
			$wpml->register_petition( $petition );

			// set up page display variables
			$page_title     = __( 'Edit New Petition', 'speakout' );
			$nonce          = 'dk_speakout-update_petition' . $petition->id;
			$action         = 'update';
			$x_date         = $petition->get_expiration_date_components();
			$button_text    = __( 'Update Petition', 'speakout' );
			$message_update = __( 'Petition '. $petition->id . ' updated.'  );

			break;

		// show blank form for adding a new petition
		default :
			// set up page display variables
			$page_title     = __( 'Add New Petition', 'speakout' );
			$nonce          = 'dk_speakout-create_petition';
			$action         = 'create';
			$x_date         = $petition->get_expiration_date_components();
			$button_text    = __( 'Create Petition', 'speakout' );
			$message_update = '';
			$petition->optin_label = __( 'Add me to your mailing list', 'speakout' );
	}

	if ( $petition->return_url === '' || $petition->return_url === 0 ) {
		$petition->return_url = home_url();
	}

    // build mapped fields for Active Petition if enabled
    if($petition->activecampaign_enable ){

    	// get the AC content via API (class.petition.php)
    	if($petition->activecampaign_server > "" && $petition->activecampaign_api_key > ""){
            $listList = $petition->retrieveActiveCampaignLists($petition->activecampaign_server, $petition->activecampaign_api_key );
            $fieldList = $petition->retrieveActiveCampaignFields($petition->activecampaign_server, $petition->activecampaign_api_key );
            
        	//process the lists
        	$listList = unserialize($listList);
        	$fieldList = unserialize($fieldList);
    
     // so we can see the data   	
    		$optionList = "<option value=''>Unused</option>";
    		$newfieldlist = $fieldList;
    		unset($fieldList['result_code']);
    		unset($fieldList['result_message']);
    		unset($fieldList['result_output']);
        	foreach($fieldList as $key => $value){
                foreach($value['lists'] as $list_key => $list_value){
                    $new_lists_array[$list_value][] = $value;
                	if($petition->activecampaign_list_id  == $list_value){
                		$optionList .= "<option value='" . str_replace("%", "", $value['id']). "' >" . $value['title'] . "</option>";
                	}
                }
    		}
    		//$optionList .= "<option value='phone' >Phone</option>";
    		
            // loop through the lists
            $listFields = array();
            $listOption = "<option value=''>Your lists...</option>";
            
             for($i=0; $i < count($listList)-3 ; $i++) {
                $listDetail = $listList[$i];
                $selected = $petition->activecampaign_list_id == $listDetail['id'] ? " SELECTED " : "";
    			 
    			 if($petition->activecampaign_list_id  == $listDetail['id']){
    				  $selected = " SELECTED ";
    			 }else{
    				  $selected = "";
    			 }
    			
                // build our list of lists
                if($listDetail['name'] > ""){
                    $listOption .= "<option value='" . str_replace("%", "", $listDetail['id']). "' " . $selected . " >" . $listDetail['name'] . "</option>";
                }
                
                $listFields["listID"][] = $listDetail['id'];
                $listFields["listID"][$listDetail["id"]] = array();
                
            	for($a=0; $a < count($fieldList) - 3; $a++)    	{
            	    $search = in_array($listDetail['id'], $fieldList[$a]['lists']);
            	    if($search == 1){
            	        $listFields["listID"][$listDetail["id"]]["fieldID"] = $fieldList[$a]['id'];
            	        $listFields["listID"][$listDetail["id"]]["fieldTitle"]= $fieldList[$a]['title'];
            	    }
                }
            }
            
            // then add our fields array to a parent array
            global $arrListFields;
            
            $arrListFields = array("fields"=>$arrListFields);
            // encode to string
            $allFieldsList = json_encode($arrListFields);
            
            
            // build array of saved mapping from db
            $count = 1;
            $selectedFields = array();
            // create a dummy key[0] to match array keys with mapfield number...just to make things less confusing
            $selectedFields[] = "";
            do{
                global $item;
                $mapString = "activecampaign_map";
                $item = "{$mapString}$count";
                @$selectedFields["map".$count]= $petition->$item;
                $count++;
            }while($count < 15);
            $selectedFieldList = json_encode($selectedFields);
    	}
    }

	// display the form
	include_once( 'addnew.view.php' );
}

?>