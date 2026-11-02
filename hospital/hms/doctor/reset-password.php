<?php
include("include/config.php");
hms_start_session();
if (!isset($_SESSION['reset_doctor_id'], $_SESSION['reset_token'], $_SESSION['reset_expiry']) || $_SESSION['reset_expiry'] < time()) {
    unset($_SESSION['reset_doctor_id'], $_SESSION['reset_token'], $_SESSION['reset_expiry']);
    header('Location: forgot-password.php');
    exit;
}
// Code for updating Password
if(isset($_POST['change']))
{
    hms_verify_csrf();
if (!isset($_SESSION['reset_doctor_id'], $_SESSION['reset_token'], $_SESSION['reset_expiry']) || $_SESSION['reset_expiry'] < time() || !hash_equals((string)$_SESSION['reset_token'], (string)($_POST['reset_token'] ?? ''))) {
    http_response_code(403);
    exit('Password reset session expired. Start the reset process again.');
}
$newPassword = hms_post_password('password');
if (hms_valid_new_password($newPassword)) {
    hms_db_query($con, 'UPDATE doctors SET password = ?, updationDate = NOW() WHERE id = ?', [password_hash($newPassword, PASSWORD_DEFAULT), (int)$_SESSION['reset_doctor_id']]);
    unset($_SESSION['reset_doctor_id'], $_SESSION['reset_token'], $_SESSION['reset_expiry']);
    header('Location: index.php?password_reset=1');
    exit;
}

}


?>


<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Password Reset</title>
		
		<link href="https://fonts.googleapis.com/css?family=Lato:300,400,400italic,600,700|Raleway:300,400,500,600,700|Crete+Round:400italic" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
		<link rel="stylesheet" href="vendor/themify-icons/themify-icons.min.css">
		<link href="vendor/animate.css/animate.min.css" rel="stylesheet" media="screen">
		<link href="vendor/perfect-scrollbar/perfect-scrollbar.min.css" rel="stylesheet" media="screen">
		<link href="vendor/switchery/switchery.min.css" rel="stylesheet" media="screen">
		<link rel="stylesheet" href="assets/css/styles.css">
		<link rel="stylesheet" href="assets/css/plugins.css">
		<link rel="stylesheet" href="assets/css/themes/theme-1.css" /><link rel="stylesheet" href="../assets/css/medtech-app.css" id="skin_color" />
		<link rel="stylesheet" href="../assets/css/medtech-enterprise.css?v=20261007" />

				<script type="text/javascript">
function valid()
{
 if(document.passwordreset.password.value!= document.passwordreset.password_again.value)
{
alert("Password and Confirm Password Field do not match  !!");
document.passwordreset.password_again.focus();
return false;
}
return true;
}
</script>
	</head>
	<body class="login">
		<div class="row">
			<div class="main-login col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-4 col-md-offset-4">
				<div class="logo margin-top-30">
				<a href="../index.php"><h2> MedTech | Doctor password reset</h2></a>
				</div>

				<div class="box-login">
					<form class="form-login" name="passwordreset" method="post" onSubmit="return valid();"><?php echo hms_csrf_field(); ?>
						<input type="hidden" name="reset_token" value="<?php echo hms_h($_SESSION['reset_token'] ?? ''); ?>">
						<fieldset>
							<legend>
								Patient Reset Password
							</legend>
							<p>
								Please set your new password.<br />
							<span style="color:red;"><?php echo hms_h($_SESSION['errmsg'] ?? ''); ?><?php unset($_SESSION['errmsg']); ?></span>
							</p>

<div class="form-group">
<span class="input-icon">
<input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
<i class="fa fa-lock"></i> </span>
</div>
	

<div class="form-group">
<span class="input-icon">
<input type="password" class="form-control"  id="password_again" name="password_again" placeholder="Password Again" required>
<i class="fa fa-lock"></i> </span>
</div>
							

							<div class="form-actions">
								
								<button type="submit" class="btn btn-primary pull-right" name="change">
									Change <i class="fa fa-arrow-circle-right"></i>
								</button>
							</div>
							<div class="new-account">
								Already have an account? 
								<a href="index.php">
									Log-in
								</a>
							</div>
						</fieldset>
					</form>

					<div class="copyright">
				<span class="text-bold text-uppercase"> MedTech Hospital Management System</span>
					</div>
			
				</div>

			</div>
		</div>
		<script src="vendor/jquery/jquery.min.js"></script>
		<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
		<script src="vendor/modernizr/modernizr.js"></script>
		<script src="vendor/jquery-cookie/jquery.cookie.js"></script>
		<script src="vendor/perfect-scrollbar/perfect-scrollbar.min.js"></script>
		<script src="vendor/switchery/switchery.min.js"></script>
		<script src="vendor/jquery-validation/jquery.validate.min.js"></script>
	
		<script src="assets/js/main.js"></script>

		<script src="assets/js/login.js"></script>
		<script>
			jQuery(document).ready(function() {
				Main.init();
				Login.init();
			});
		</script>
	
	</body>
	<!-- end: BODY -->
</html>
