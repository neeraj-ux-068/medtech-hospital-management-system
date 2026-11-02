<?php
include("include/config.php");
hms_start_session();
if(isset($_POST['submit']))
{
    hms_verify_csrf();
$uname = hms_post_string('username', 254);
$plainPassword = hms_post_password('password');
$result = hms_db_query($con, 'SELECT id, docEmail, password FROM doctors WHERE docEmail = ? LIMIT 1', [$uname]);
$doctor = $result ? $result->fetch_assoc() : null;
$valid = $doctor && hms_verify_legacy_password($plainPassword, (string)$doctor['password']);
$ip = inet_pton($_SERVER['REMOTE_ADDR'] ?? '') ?: str_repeat("\0", 16);
if ($valid) {
    if (empty(password_get_info((string)$doctor['password'])['algo']) || password_needs_rehash((string)$doctor['password'], PASSWORD_DEFAULT)) {
        hms_rehash_password($con, 'doctors', (int)$doctor['id'], $plainPassword);
    }
    hms_login('doctor', (int)$doctor['id'], (string)$doctor['docEmail']);
    hms_db_query($con, 'INSERT INTO doctorslog (uid, username, userip, status) VALUES (?, ?, ?, 1)', [(int)$doctor['id'], $uname, $ip]);
    header('Location: dashboard.php');
    exit;
}
unset($_SESSION['login'], $_SESSION['id'], $_SESSION['role'], $_SESSION['dlogin']);
hms_db_query($con, 'INSERT INTO doctorslog (username, userip, status) VALUES (?, ?, 0)', [$uname, $ip]);
$_SESSION['errmsg'] = 'Invalid username or password';
}
?>


<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>MedTech | Doctor sign in</title>
		
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
	</head>
	<body class="login">
<div class="mt-auth">
  <aside class="mt-auth-aside"><a class="mt-auth-brand" href="../../index.php">MedTech<small>HOSPITAL MANAGEMENT</small></a><div class="mt-auth-copy"><i class="fa fa-heartbeat"></i><h1>Care works better when everything connects.</h1><p>Secure access to the tools and information you need to support better hospital care.</p></div><small>© <?= date('Y') ?> MedTech Hospital</small></aside>
  <main class="mt-auth-main"><a class="mt-auth-home" href="../../index.php"><i class="fa fa-arrow-left"></i> Back to MedTech</a><h2>Welcome back</h2><p>Sign in to your Doctor portal.</p>
    <form class="form-login" method="post"><?= hms_csrf_field() ?><fieldset><legend>Sign in to your account</legend>
      <span class="mt-auth-error" role="alert"><?php echo hms_h($_SESSION['errmsg'] ?? ''); unset($_SESSION['errmsg']); ?></span>
      <div class="form-group"><label for="username">Email or username</label><span class="input-icon"><input id="username" type="text" class="form-control" name="username" placeholder="Enter your email or username" autocomplete="username" required><i class="fa fa-user"></i></span></div>
      <div class="form-group"><label for="password">Password</label><span class="input-icon"><input id="password" type="password" class="form-control password" name="password" placeholder="Enter your password" autocomplete="current-password" required><i class="fa fa-lock"></i></span></div>
      <div class="form-actions"><a href="forgot-password.php">Forgot password?</a><button type="submit" class="btn btn-primary" name="submit">Sign in <i class="fa fa-arrow-right"></i></button></div>
      
    </fieldset></form>
  </main>
</div><script src="vendor/jquery/jquery.min.js"></script>
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
