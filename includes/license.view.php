<script type="text/javascript">

jQuery(document).ready(function() {

   	jQuery('#licenseKeyButton').click( function () {
   	    jQuery('#speakOutLoader').css('display', 'inherit'); 
		var licence_key = jQuery("#license_key").val();
        jQuery.ajax({
           type: "POST",
           url: "<?php echo admin_url('admin-ajax.php'); ?>",
           data: {action:'speakout_verify_licence_key',key:licence_key},   
           cache: false,
           success: function(response){
                jQuery('#speakOutLoader').css('display', 'none');
                response = jQuery.parseJSON(response);
                
                if(response.status == "valid"){
                    jQuery('#licenseKeyButton').css('background-color','#18741A');
                    jQuery('#licenseKeyButton').css('color','#FFFFFF');
                    jQuery('#licenseKeyButton').prop('value', 'Success!');
                    setTimeout(function (){
                        window.location.href = "admin.php?page=dk_speakout_petitions";
                    }, 1000)
                }
                else{
                    jQuery('#licenseKeyButton').css('cursor','default');
                    jQuery('#licenseKeyButton').prop('disabled',true);
                    jQuery('#licenseKeyButton').css('background-color','#FF0000'); 
                    jQuery('#licenseKeyButton').css('color','#FFF');
                    if(response.status == "multiple_results"){ 
                        jQuery('#licenseKeyButton').prop('value', 'verification failed - license used on multiple sites');
                        setTimeout(function (){
                            jQuery('#licenseKeyButton').css('cursor','pointer');
                            jQuery('#licenseKeyButton').prop("disabled",false);
                            jQuery('#licenseKeyButton').css('background-color','unset'); 
                            jQuery('#licenseKeyButton').css('color','#000');
                            jQuery('#licenseKeyButton').prop('value', 'Verify');
                        }, 10000) 
                        
                    }
                    else{
                        jQuery('#licenseKeyButton').prop('value',  'Verification failed');
                        setTimeout(function (){
                            jQuery('#licenseKeyButton').css('cursor','pointer');
                            jQuery('#licenseKeyButton').prop("disabled",false);
                            jQuery('#licenseKeyButton').css('background-color','unset'); 
                            jQuery('#licenseKeyButton').css('color','#000');
                            jQuery('#licenseKeyButton').prop('value', 'Verify');
                        }, 2000)     
                    }
                } 
           }
    	}) 
    });
    
        jQuery('#licenseKeyRevoke').click( function () {
        jQuery('#speakOutLoader').css('display', 'inherit');
		var licence_key = jQuery("#license_keys").val();
        jQuery.ajax({
           type: "POST",
           url: "<?php echo admin_url('admin-ajax.php'); ?>",
           data: {action:'speakout_revoke_licence_key',key:licence_key},   
           cache: false,
           success: function(response){
               window.location.reload(true);
           }
    	}) 
    });
});
</script>
<div class="wrap" id="dk-speakout">

	<div id="icon-dk-speakout" class="icon32"><br /></div>
	<h2><?php _e( 'Activate license', 'speakout' ); ?></h2><br>
<?php
    $licenseFields = "";
   if(get_option( 'dk_speakout_license_key_verified' ) == 1  && get_option( 'dk_speakout_license_key' ) != ""){
        $licenseFields = '<td><span  class="licenseKeyVerifiedText">' .  __( "Enter your license key", "speakout") . ':</span></td>
	    <td> <input type="text" id="license_key_display" name="license_key_display" size="50"  class="licenseKeyFieldVerified" value="****************" readonly>
	         <input type="hidden" id="license_key" name="license_key" value=' . get_option( 'dk_speakout_license_key' ) . '>
        </td>
	    <td> 
            <input type="button" id="licenseKeyButton" name="licenseKeyButton"  class="licenseKeyButtonVerified" value="'. __( "Verified", "speakout"). '"  disabled="disabled" >
        </td>
        <td> 
            <input type="button" id="licenseKeyRevoke" name="licenseKeyRevoke"  class="licenseKeyRevoke" value="'. __( "Revoke license key", "speakout"). '"  > <span><img style="display:none;" id="speakOutLoader" src="'.  WP_PLUGIN_URL . '/speakout/images/loader.gif"></span>
        </td>                
	    <tr>
            <td>
                <span class"upgrade-message">' . __("Thanks for upgrading", "speakout") . '</span>
            </td>
        </tr>';
    }

    else{		        
        if('dk_speakout_license_key_verified' == 1  
           && get_option( 'dk_speakout_license_key' ) == "" 
           || 'dk_speakout_license_key_verified' == 0  
           && get_option( 'dk_speakout_license_key' ) != ""){
              $licenseFields =  __("There appears to be a problem with your license key.  Either verify it again or contact", "speakout") .' <a href="https://speakoutpetitions.com/contact" target="_blank">speakoutpetitions.com/contact</a><br><br>';
        }
        
        $licenseFields = $licenseFields . '
        <td>
            <span  class="licenseKeyText">'. __( "Enter your license key", "speakout"). ':</span>
        </td>
	    <td> 
            <input type="text" id="license_key" name="license_key" size="50"   class="licenseKeyField" placeholder="'. __( "Your license key", "speakout"). '" value="" >
        </td>
	    <td> 
            <input type="button" id="licenseKeyButton" name="licenseKeyButton" class="licenseKeyButton" value="'. __( "Verify", "speakout"). '"  > <span><img style="display:none;" id="speakOutLoader" src="' .  WP_PLUGIN_URL . '/speakout/images/loader.gif"></span>
        </td>
	    ';
    }
    ?>

    <table>
		<?php 
		   echo $licenseFields;
		 ?>
    </table>
    </div>