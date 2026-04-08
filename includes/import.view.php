<?php

global $wpdb;

$sigsuccess = false;
$petitionsuccess = false;
$settingssuccess = false;
$Uppetitiontotal = 0;
$Upsignaturestotal = 0;
$Upexists = false;

$table = $wpdb->prefix . "dk_speakup_petitions";

if($wpdb->get_var( "SHOW TABLES LIKE '$table'") == $table)

{
    $Upexists = true;
    
    if(isset($_POST["doimport"])){
    
    	$strSQL = "INSERT INTO " . $wpdb->prefix . "dk_speakout_signatures(`petitions_id`, `first_name`, `last_name`, `email`, `street_address`, `city`, `state`, `postcode`, `country`, `custom_field`, `optin`, `date`, `confirmation_code`, `is_confirmed`, `custom_message`, `language`) (SELECT `petitions_id`, `first_name`, `last_name`, `email`, `street_address`, `city`, `state`, `postcode`, `country`, `custom_field`, `optin`, `date`, `confirmation_code`, `is_confirmed`, `custom_message`, `language` FROM " . $wpdb->prefix . "dk_speakup_signatures)";
    $wpdb->query($strSQL);
    $sigsuccess = true;
    
    	$strSQL = "INSERT INTO " . $wpdb->prefix . "dk_speakout_petitions(`title`, `target_email`, `target_email_CC`, `email_subject`, `greeting`, `petition_message`, `petition_footer`, `address_fields`, `expires`, `expiration_date`, `created_date`, `goal`, `sends_email`, `x_message`, `requires_confirmation`, `return_url`, `displays_custom_field`, `custom_field_label`, `displays_optin`, `optin_label`, `is_editable`) (SELECT `title`, `target_email`, target_email_CC`,`email_subject`, `greeting`, `petition_message`, `petition_footer`,`address_fields`, `expires`, `expiration_date`, `created_date`, `goal`, `sends_email`, `x_message`, `requires_confirmation`, `return_url`, `displays_custom_field`, `custom_field_label`, `displays_optin`, `optin_label`, `is_editable` FROM " . $wpdb->prefix . "dk_speakup_petitions)";
    $wpdb->query($strSQL);
    $sigsuccess = true;
    
    	//if they have checked the import settings, lets over-write
    	if($_POST["impsettings"]){
    		$strSQL = "UPDATE " . $wpdb->prefix . "_options AS target
    LEFT JOIN " . $wpdb->prefix . "_options AS source ON source.option_name = 'dk_speakup_options'
          SET target.option_value = source.option_value
        WHERE target.option_name = 'dk_speakout_options'";
    $wpdb->query($strSQL);
    $settingssuccess = true;	
    	}
    }
    
    $strSQL = "SELECT COUNT(*) FROM " . $wpdb->prefix . "dk_speakup_petitions GROUP BY id";
    $uppetitions = $wpdb->get_results( $strSQL);
    $Uppetitiontotal = $wpdb->num_rows;
    
    $strSQL = "SELECT COUNT(*) FROM " . $wpdb->prefix . "dk_speakup_signatures GROUP BY id";
    $upsignatures = $wpdb->get_results( $strSQL);
    $Upsignaturestotal = $wpdb->num_rows;
    
    
    $strSQL = "SELECT COUNT(*) FROM " . $wpdb->prefix . "dk_speakout_petitions GROUP BY id";
    $Outpetitions= $wpdb->get_results( $strSQL);
    $Outpetitionstotal = $wpdb->num_rows;
    
    $strSQL = "SELECT COUNT(*) FROM " . $wpdb->prefix . "dk_speakout_signatures GROUP BY id";
    $Outsignatures = $wpdb->get_results( $strSQL);
    $Outsignaturestotal = $wpdb->num_rows;
}

?>
<style>
.dk-speakout-response-success{
    background-color: #d8f6d9;
    display: inline-block;
    padding: 10px;
    border: 1px solid #70de74 !important;
    }
</style>

<div class="wrap" id="dk-speakout">
<h2><?php _e( 'Import petitions from SpeakUp to SpeakOut!', 'speakout' ); ?></h2>

<?php if($sigsuccess){
echo "<div class='dk-speakout-response-success'>" . $Uppetitiontotal . " petitions imported successfully<br> " . $Upsignaturestotal . " signatures imported successfully<br><br>The best way to check is to disable the speakup plugin and then view the petitions in <strong>SpeakOut!</strong>.  <br>If it is all OK, you can safely deactivate the old speakup plugin if you haven't already.  <br>If you had any previous petitions in <strong>SpeakOut!</strong> you may have to edit the id number in the shortcode on your page.</div>"; 
}
 if($settingssuccess){
echo "<div class='dk-speakout-response-success'>Your speakup settings have been imported to SpeakOut! successfully</div>"; 
}
?>
<form name='doImport' method="post">
<div style='margin-top:20px;'>This will import all petitions and signatures from the retired SpeakUp plugin to the SpeakOut plugin that has replaced it.</div>
<?php 
//We don't show some superfluous text if there is no table to import
if (!$Upexists){ 
      echo "<div style='margin-top:20px;'>You currently don't appear to have any <strong>SpeakUp</strong> petitions to import - nothing to do</div>";
}
else{
    // show the page
?>
    <div style='margin-top:10px;'>As a data safety measure nothing will be deleted from the old plugin.  If you are satisfied with the results of the import, you can then delete the speakup plugin.</div>
    <div id="upresult" style='margin-top:20px;'>You currently have <?php echo $Uppetitiontotal ; ?> <strong>SpeakUp</strong> petitions with a total of <?php echo $Upsignaturestotal ; ?> signatures <?php if($Uppetitiontotal==0){ echo " - <strong>nothing to do</strong>";} ?>
    
    
    <div style='margin-top:10px;'>In your new <strong>SpeakOut!</strong> plugin you currently have <?php echo $Outpetitionstotal ; ?> petitions with <?php echo $Outsignaturestotal ; ?> signatures.
    
    <br>
    <br>
    <input name='impsettings' id='impsettings' type='checkbox' value='on' /> Copy the global speakup settings e.g. success message, confirmation email setting etc from speakup to SpeakOut!<br><strong>WARNING</strong> this will overwrite any custom settings in SpeakOut! with your old speakup settings.  If you have no SpeakOut! petitions yet and are migrating, this option is a good idea.  
    <br>
    <br>
    To avoid confusion it is recommended that you deactivate the old speakup plugin before running this.
    <br>
    <br>
    <input type='submit' id='doimport' name='doimport' value='Import' <?php if($Uppetitiontotal==0){ echo " DISABLED";} ?>>
<?php } ?>

</form>
</div>
<script language='Javascript'>
var checker = document.getElementById('impsettings');
var sendbtn = document.getElementById('doimport');
checker.onchange = function() {
  sendbtn.disabled = !this.checked;
};
</script>