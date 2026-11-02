<?php 
require_once("include/config.php");
if(!empty($_POST["email"])) {
	$email = filter_var(hms_post_string('email', 254), FILTER_VALIDATE_EMAIL);
$result = $email ? hms_db_query($con, 'SELECT ID FROM tblpatient WHERE PatientEmail = ? LIMIT 1', [$email]) : false;
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
