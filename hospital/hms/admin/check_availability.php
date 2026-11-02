<?php 
require_once("include/config.php");
if(!empty($_POST["emailid"])) {
	$email = filter_var(hms_post_string('emailid', 254), FILTER_VALIDATE_EMAIL);
	$result = $email ? hms_db_query($con, 'SELECT id FROM doctors WHERE docEmail = ? LIMIT 1', [$email]) : false;
	$count = $result ? $result->num_rows : 0;
if($count>0)
{
echo "<span style='color:red'> Email already exists .</span>";
 echo "<script>$('#submit').prop('disabled',true);</script>";
} else{
	
	echo "<span style='color:green'> Email available for Registration .</span>";
 echo "<script>$('#submit').prop('disabled',false);</script>";
}
}


?>
