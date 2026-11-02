<?php
include("include/config.php");
hms_start_session();
//Checking Details for reset password
if(isset($_POST['submit'])){
    hms_verify_csrf();
$name = hms_post_string('fullname', 200);
$email = filter_var(hms_post_string('email', 254), FILTER_VALIDATE_EMAIL);
$query = $email ? hms_db_query($con, 'SELECT id FROM users WHERE fullName = ? AND email = ? LIMIT 1', [$name, $email]) : false;
$user = $query ? $query->fetch_assoc() : null;
if ($user) {
    session_regenerate_id(true);
    $_SESSION['reset_user_id'] = (int)$user['id'];
    $_SESSION['reset_token'] = bin2hex(random_bytes(32));
    $_SESSION['reset_expiry'] = time() + 900;
    header('Location: reset-password.php');
    exit;
} else {
    $_SESSION['errmsg'] = 'If those details match an account, continue to set a new password.';
}

}
?>


<!DOCTYPE html>
<html lang="en">
	<head>
		<title>MedTech | Recover patient account</title>
		
		<link href="https://fonts.googleapis.com/css?family=Lato:300,400,400italic,600,700|Raleway:300,400,500,600,700|Crete+Round:400italic" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
		<link rel="stylesheet" href="vendor/themify-icons/themify-icons.min.css">
		<link href="vendor/animate.css/animate.min.css" rel="stylesheet" media="screen">
		<link href="vendor/perfect-scrollbar/perfect-scrollbar.min.css" rel="stylesheet" media="screen">
		<link href="vendor/switchery/switchery.min.css" rel="stylesheet" media="screen">
		<link rel="stylesheet" href="assets/css/styles.css">
		<link rel="stylesheet" href="assets/css/plugins.css">
		<link rel="stylesheet" href="assets/css/themes/theme-1.css" /><link rel="stylesheet" href="assets/css/medtech-app.css" id="skin_color" />
		<link rel="stylesheet" href="assets/css/medtech-enterprise.css?v=20261007-recovery" />
	</head>
	<body class="login">
		<div class="mt-auth mt-auth-recovery">
			<aside class="mt-auth-aside">
				<a class="mt-auth-brand" href="../index.php">MedTech<small>HOSPITAL MANAGEMENT</small></a>
				<div class="mt-auth-copy"><i class="fa fa-shield"></i><h1>We’ll help you get back to your care.</h1><p>Confirm your patient account details, then choose a new password securely.</p></div>
				<small>© <?= date('Y') ?> MedTech Hospital</small>
			</aside>
			<main class="mt-auth-main">
				<a class="mt-auth-home" href="user-login.php"><i class="fa fa-arrow-left"></i> Back to sign in</a>
				<h2>Recover your account</h2><p>Enter the details you used when registering as a patient.</p>
				<form class="form-login" method="post"><?= hms_csrf_field() ?>
					<fieldset><legend>Patient account recovery</legend>
						<?php if (!empty($_SESSION['errmsg'])) { echo '<div class="mt-auth-error" role="alert">' . hms_h($_SESSION['errmsg']) . '</div>'; unset($_SESSION['errmsg']); } ?>
						<div class="form-group"><label for="fullname">Full name</label><span class="input-icon"><input id="fullname" type="text" class="form-control" name="fullname" placeholder="Enter your registered full name" autocomplete="name" required><i class="fa fa-user"></i></span></div>
						<div class="form-group"><label for="email">Email address</label><span class="input-icon"><input id="email" type="email" class="form-control" name="email" placeholder="name@example.com" autocomplete="email" required><i class="fa fa-envelope"></i></span></div>
						<div class="form-actions"><button type="submit" class="btn btn-primary" name="submit">Continue to reset <i class="fa fa-arrow-right"></i></button></div>
						<div class="mt-auth-foot">Remembered your password? <a href="user-login.php">Sign in</a></div>
					</fieldset>
				</form>
			</main>
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
