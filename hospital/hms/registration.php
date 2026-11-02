<?php
include_once('include/config.php');
hms_start_session();
if(isset($_POST['submit']))
{
    hms_verify_csrf();
$fname = hms_post_string('full_name', 200);
$address = hms_post_string('address', 1000);
$city = hms_post_string('city', 255);
$gender = hms_post_string('gender', 20);
$email = filter_var(hms_post_string('email', 254), FILTER_VALIDATE_EMAIL);
$password = hms_post_password('password');
$confirmation = hms_post_password('password_again');
if ($fname === '' || $address === '' || $city === '' || !in_array($gender, ['female', 'male', 'other'], true) || !$email) {
    $_SESSION['errmsg'] = 'Please complete all required patient details with a valid email address.';
} elseif (!hms_valid_new_password($password)) {
    $_SESSION['errmsg'] = 'Your password must contain at least 10 characters.';
} elseif (!hash_equals($password, $confirmation)) {
    $_SESSION['errmsg'] = 'The password confirmation does not match.';
} else {
    $transactionStarted = false;
    try {
        $account = hms_db_query($con, 'SELECT id FROM users WHERE email = ? LIMIT 1', [$email]);
        if ($account && $account->num_rows > 0) {
            $_SESSION['errmsg'] = 'An account already exists for that email address. Sign in or reset your password.';
        } else {
            $con->begin_transaction();
            $transactionStarted = true;
            hms_db_query($con, 'INSERT INTO users (fullName, address, city, gender, email, password) VALUES (?, ?, ?, ?, ?, ?)', [$fname, $address, $city, $gender, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $profile = hms_db_query($con, 'SELECT ID FROM tblpatient WHERE PatientEmail = ? LIMIT 1', [$email]);
            if (!$profile || $profile->num_rows === 0) {
                hms_db_query($con, 'INSERT INTO tblpatient (Docid, PatientName, PatientContno, PatientEmail, PatientGender, PatientAdd, PatientAge, PatientMedhis) VALUES (NULL, ?, NULL, ?, ?, ?, NULL, NULL)', [$fname, $email, $gender, $address]);
            }
            $con->commit();
            $transactionStarted = false;
            header('Location: user-login.php?registered=1');
            exit;
        }
    } catch (Throwable $exception) {
        if ($transactionStarted) $con->rollback();
        error_log('HMS patient registration failed: ' . $exception->getMessage());
        $_SESSION['errmsg'] = 'Your account could not be created right now. Please try again.';
    }
}
}
?>


<!DOCTYPE html>
<html lang="en">

	<head>
		<title>MedTech | Create patient account</title>
		
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
		<link rel="stylesheet" href="assets/css/medtech-enterprise.css?v=20261007" />
		
		<script type="text/javascript">
function valid()
{
 if(document.registration.password.value!= document.registration.password_again.value)
{
alert("Password and Confirm Password Field do not match  !!");
document.registration.password_again.focus();
return false;
}
return true;
}
</script>
		

	</head>

	<body class="login">
		<div class="mt-auth mt-auth-register">
			<aside class="mt-auth-aside">
				<a class="mt-auth-brand" href="../index.php">MedTech<small>HOSPITAL MANAGEMENT</small></a>
				<div class="mt-auth-copy"><i class="fa fa-heartbeat"></i><h1>Your care, connected from day one.</h1><p>Create a secure patient account to manage appointments and keep your care information in one place.</p></div>
				<small>© <?= date('Y') ?> MedTech Hospital</small>
			</aside>
			<main class="mt-auth-main">
				<a class="mt-auth-home" href="../index.php"><i class="fa fa-arrow-left"></i> Back to MedTech</a>
				<h2>Create your account</h2><p>Register as a patient to access the MedTech care portal.</p>
				<form class="form-login mt-registration-form" name="registration" id="registration" method="post" onsubmit="return valid();"><?= hms_csrf_field() ?>
					<fieldset><legend>Patient registration</legend>
						<?php if (!empty($_SESSION['errmsg'])) { echo '<div class="mt-auth-error" role="alert">' . hms_h($_SESSION['errmsg']) . '</div>'; unset($_SESSION['errmsg']); } ?>
						<div class="mt-form-section">Personal details</div>
						<div class="form-group"><label for="full_name">Full name</label><span class="input-icon"><input id="full_name" type="text" class="form-control" name="full_name" placeholder="Enter your full name" autocomplete="name" required><i class="fa fa-user"></i></span></div>
						<div class="form-group"><label for="address">Address</label><span class="input-icon"><input id="address" type="text" class="form-control" name="address" placeholder="Enter your address" autocomplete="street-address" required><i class="fa fa-map-marker"></i></span></div>
						<div class="mt-form-row"><div class="form-group"><label for="city">City</label><input id="city" type="text" class="form-control mt-input-plain" name="city" placeholder="Your city" autocomplete="address-level2" required></div><div class="form-group"><label for="gender">Gender</label><select id="gender" class="form-control mt-input-plain" name="gender" required><option value="" selected disabled>Select</option><option value="female">Female</option><option value="male">Male</option><option value="other">Other</option></select></div></div>
						<div class="mt-form-section">Account details</div>
						<div class="form-group"><label for="email">Email address</label><span class="input-icon"><input type="email" class="form-control" name="email" id="email" onblur="userAvailability()" placeholder="name@example.com" autocomplete="email" required><i class="fa fa-envelope"></i></span><span id="user-availability-status1" class="mt-availability" aria-live="polite"></span></div>
						<div class="mt-form-row"><div class="form-group"><label for="password">Password</label><span class="input-icon"><input type="password" class="form-control" id="password" name="password" placeholder="Create password" autocomplete="new-password" required><i class="fa fa-lock"></i></span></div><div class="form-group"><label for="password_again">Confirm password</label><span class="input-icon"><input type="password" class="form-control" id="password_again" name="password_again" placeholder="Repeat password" autocomplete="new-password" required><i class="fa fa-lock"></i></span></div></div>
						<p class="mt-password-help"><i class="fa fa-shield"></i> Use at least 10 characters.</p>
						<div class="form-actions"><button type="submit" class="btn btn-primary" id="submit" name="submit">Create patient account <i class="fa fa-arrow-right"></i></button></div>
						<div class="mt-auth-foot">Already have an account? <a href="user-login.php">Sign in</a></div>
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
		
	<script>
function userAvailability() {
  const email = $("#email").val(), status = $("#user-availability-status1"), submit = $("#submit");
  if (!email) { status.text(''); return; }
  submit.prop('disabled', true);
  $.ajax({url:"check_availability.php", data:{email:email}, type:"POST", dataType:"json",
    success:function(data){ status.text(data.message || 'Unable to check this email.'); status.toggleClass('text-danger', !data.available).toggleClass('text-success', !!data.available); submit.prop('disabled', !data.available); },
    error:function(){ status.text('We could not verify this email. Please try again.').addClass('text-danger'); submit.prop('disabled', true); }
  });
}
</script>	
		
	</body>
	<!-- end: BODY -->
</html>
