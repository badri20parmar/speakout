<?php
add_action('wp_ajax_speakout_verify_licence_key', 'speakout_verify_licence_key');
add_action('wp_ajax_nopriv_speakout_verify_licence_key', 'speakout_verify_licence_key');

 function speakout_verify_licence_key($action) {
     
////////////////////////////////////////////////////
// if instructed to, remove the "//" from the next line.  This will send limited debug info to Steve, the SpeakOut! developer
// $debug = true;
//    
// The data sent will be your SpeakOut! version, your URL, server IP address and some values passed when attempting to verify.  You can see the mail() function on line 32 
/////////////////////////////////////////////////////

    $the_license_key_value = get_option( "dk_speakout_license_key" );  
    if($action == "upgrade" && $the_license_key_value == "") return;

    if(isset($_POST['key'])){
        $the_license_key_value = $_POST['key'];
    }
    
    $the_domain = $_SERVER['SERVER_NAME']; 
    if($action == "") $action = "verify"; 

    $url = "https://license.speakoutpetitions.com?key=" . $the_license_key_value ."&domain=" . $the_domain . "&action=" . $action . "&version=" . get_option( 'dk_speakout_version' );

    $response = wp_remote_get( $url );
    $response = wp_remote_retrieve_body( $response );

    //The API returns data in JSON format, so first convert that to an array of data objects
    $responseObj = json_decode($response, true);

    if($responseObj["status"] == "cancelled"){    
        // delete the license key
        delete_option('dk_speakout_license_key');
        update_option('dk_speakout_license_key_verified','0');
    }

    if($action == "upgrade"){ return; }

//////////////////////////////////////////////////////////////////////
    $response = array();
    
    $response["status"]=$responseObj["status"];
    $response["license_key"]=$responseObj["key"];
    $response["licence_key_verified"]="1";
    
    if($response["status"]=='valid'){
        
        update_option('dk_speakout_license_key', $response["license_key"]);
    	update_option('dk_speakout_license_key_verified','1');
    }
    
    echo json_encode($response);
    
    exit;
}

add_action('wp_ajax_speakout_revoke_licence_key', 'speakout_revoke_licence_key');
add_action('wp_ajax_nopriv_speakout_revoke_licence_key', 'speakout_revoke_licence_key');

 function speakout_revoke_licence_key($action) {
    $the_license_key_value = get_option( "dk_speakout_license_key" ); 
    $the_domain = $_SERVER['SERVER_NAME'];

    $url = "https://license.speakoutpetitions.com?key=" . $the_license_key_value . "&action=revoke&domain=" . $the_domain . "&version=" . get_option( 'dk_speakout_version');

    $response = wp_remote_get( $url ); 
    $response = wp_remote_retrieve_body( $response );

    //The API returns data in JSON format, so first convert that to an array of data objects
    $responseObj = json_decode($response, true);

    // if license key has been successfully revoked
    if ($responseObj["status"] == "revoked"){  
        // delete the license key
        delete_option('dk_speakout_license_key');
    	update_option('dk_speakout_license_key_verified','0');
    }
 }

?>