<?php
//if ( ! current_user_can( 'activate_plugins' ) ) wp_die( 'Insufficient privileges: You need to be an administrator to do that.' );

$options = get_option( 'dk_speakout_options' );

echo "<h1>Options:</h1><br><br>";
echo json_encode($options);

foreach($options as $option){
   // echo $option[0] . " - " . $$option[1];
}
?>