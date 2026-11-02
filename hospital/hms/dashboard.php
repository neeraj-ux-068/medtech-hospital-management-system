<?php
include('include/config.php');
hms_start_session();
include('include/checklogin.php');
check_login();
$patientId = (int)$_SESSION['id'];
$dashboardDate = date('Y-m-d');
$appointmentSummary = hms_db_query($con, "SELECT COUNT(*) AS total, SUM(CASE WHEN appointmentDate >= ? AND status IN ('pending','confirmed') THEN 1 ELSE 0 END) AS upcoming FROM appointment WHERE userId = ?", [$dashboardDate, $patientId]);
$appointmentStats = $appointmentSummary ? $appointmentSummary->fetch_assoc() : [];
$medicalSummary = hms_db_query($con, 'SELECT COUNT(*) AS total FROM tblmedicalhistory h JOIN tblpatient p ON p.ID = h.PatientID JOIN users u ON u.email = p.PatientEmail WHERE u.id = ?', [$patientId]);
$medicalStats = $medicalSummary ? $medicalSummary->fetch_assoc() : [];
$profileResult = hms_db_query($con, 'SELECT fullName FROM users WHERE id = ? LIMIT 1', [$patientId]);
$patientProfile = $profileResult ? $profileResult->fetch_assoc() : [];

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>MedTech | Patient dashboard</title>
		
		<link href="https://fonts.googleapis.com/css?family=Lato:300,400,400italic,600,700|Raleway:300,400,500,600,700|Crete+Round:400italic" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
		<link rel="stylesheet" href="vendor/themify-icons/themify-icons.min.css">
		<link href="vendor/animate.css/animate.min.css" rel="stylesheet" media="screen">
		<link href="vendor/perfect-scrollbar/perfect-scrollbar.min.css" rel="stylesheet" media="screen">
		<link href="vendor/switchery/switchery.min.css" rel="stylesheet" media="screen">
		<link href="vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" media="screen">
		<link href="vendor/select2/select2.min.css" rel="stylesheet" media="screen">
		<link href="vendor/bootstrap-datepicker/bootstrap-datepicker3.standalone.min.css" rel="stylesheet" media="screen">
		<link href="vendor/bootstrap-timepicker/bootstrap-timepicker.min.css" rel="stylesheet" media="screen">
		<link rel="stylesheet" href="assets/css/styles.css">
		<link rel="stylesheet" href="assets/css/plugins.css">
		<link rel="stylesheet" href="assets/css/themes/theme-1.css" /><link rel="stylesheet" href="assets/css/medtech-app.css" id="skin_color" />
		<link rel="stylesheet" href="assets/css/medtech-enterprise.css?v=20261007-dashboard-3" />


	</head>
	<body>
		<div id="app">		
<?php include('include/sidebar.php');?>
			<div class="app-content">
				
						<?php include('include/header.php');?>
						
				<!-- end: TOP NAVBAR -->
				<div class="main-content" >
					<div class="wrap-content container" id="container">
						<!-- start: PAGE TITLE -->
						<section id="page-title">
							<div class="row">
								<div class="col-sm-8">
									<h1 class="mainTitle">Your patient dashboard</h1>
																	</div>
								<ol class="breadcrumb">
									<li>
										<span>User</span>
									</li>
									<li class="active">
										<span>Dashboard</span>
									</li>
								</ol>
							</div>
						</section>
						<!-- end: PAGE TITLE -->
						<div class="container-fluid container-fullw bg-white">
                            <div class="medtech-welcome">
                                <div><span class="medtech-eyebrow">PATIENT PORTAL</span><h2><?php echo date('G') < 12 ? 'Good morning' : (date('G') < 18 ? 'Good afternoon' : 'Good evening'); ?>, <?php echo hms_h($patientProfile['fullName'] ?? 'Patient'); ?></h2><p>Your care details and next steps, together in one place.</p></div>
                                <a class="btn btn-primary" href="book-appointment.php"><i class="fa fa-plus" aria-hidden="true"></i> Book an appointment</a>
                            </div>
                            <div class="row medtech-stat-grid">
                                <div class="col-sm-4"><a class="medtech-stat-card" href="appointment-history.php"><span class="medtech-stat-icon"><i class="fa fa-calendar" aria-hidden="true"></i></span><span class="medtech-stat-label">Upcoming appointments</span><strong><?php echo (int)($appointmentStats['upcoming'] ?? 0); ?></strong><small>View your schedule <i class="fa fa-arrow-right" aria-hidden="true"></i></small></a></div>
                                <div class="col-sm-4"><a class="medtech-stat-card" href="appointment-history.php"><span class="medtech-stat-icon"><i class="fa fa-calendar" aria-hidden="true"></i></span><span class="medtech-stat-label">Appointments on record</span><strong><?php echo (int)($appointmentStats['total'] ?? 0); ?></strong><small>Open appointment history <i class="fa fa-arrow-right" aria-hidden="true"></i></small></a></div>
                                <div class="col-sm-4"><a class="medtech-stat-card" href="manage-medhistory.php"><span class="medtech-stat-icon"><i class="fa fa-heartbeat" aria-hidden="true"></i></span><span class="medtech-stat-label">Medical records</span><strong><?php echo (int)($medicalStats['total'] ?? 0); ?></strong><small>View medical history <i class="fa fa-arrow-right" aria-hidden="true"></i></small></a></div>
                            </div>
                            <div class="medtech-quick-links"><h3>Your care shortcuts</h3><div class="row"><div class="col-sm-4"><a class="medtech-action-link" href="edit-profile.php"><i class="fa fa-user" aria-hidden="true"></i><span><strong>Profile</strong><small>Keep your contact details up to date</small></span><i class="fa fa-angle-right" aria-hidden="true"></i></a></div><div class="col-sm-4"><a class="medtech-action-link" href="appointment-history.php"><i class="fa fa-calendar" aria-hidden="true"></i><span><strong>Appointment history</strong><small>Review scheduled and past visits</small></span><i class="fa fa-angle-right" aria-hidden="true"></i></a></div><div class="col-sm-4"><a class="medtech-action-link" href="manage-medhistory.php"><i class="fa fa-file-text-o" aria-hidden="true"></i><span><strong>Medical history</strong><small>Review records available to your account</small></span><i class="fa fa-angle-right" aria-hidden="true"></i></a></div></div></div>
						</div>
			
					
					
						
						
					
						<!-- end: SELECT BOXES -->
						
					</div>
				</div>
			</div>
			<!-- start: FOOTER -->
	<?php include('include/footer.php');?>
			<!-- end: FOOTER -->
		
			<!-- start: SETTINGS -->
	<?php include('include/setting.php');?>
			<!-- end: SETTINGS -->
		</div>
		<!-- start: MAIN JAVASCRIPTS -->
		<script src="vendor/jquery/jquery.min.js"></script>
		<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
		<script src="vendor/modernizr/modernizr.js"></script>
		<script src="vendor/jquery-cookie/jquery.cookie.js"></script>
		<script src="vendor/perfect-scrollbar/perfect-scrollbar.min.js"></script>
		<script src="vendor/switchery/switchery.min.js"></script>
		<!-- end: MAIN JAVASCRIPTS -->
		<!-- start: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<script src="vendor/maskedinput/jquery.maskedinput.min.js"></script>
		<script src="vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
		<script src="vendor/autosize/autosize.min.js"></script>
		<script src="vendor/selectFx/classie.js"></script>
		<script src="vendor/selectFx/selectFx.js"></script>
		<script src="vendor/select2/select2.min.js"></script>
		<script src="vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
		<script src="vendor/bootstrap-timepicker/bootstrap-timepicker.min.js"></script>
		<!-- end: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<!-- start: CLIP-TWO JAVASCRIPTS -->
		<script src="assets/js/main.js"></script>
		<!-- start: JavaScript Event Handlers for this page -->
		<script src="assets/js/form-elements.js"></script>
		<script>
			jQuery(document).ready(function() {
				Main.init();
				FormElements.init();
			});
		</script>
		<!-- end: JavaScript Event Handlers for this page -->
		<!-- end: CLIP-TWO JAVASCRIPTS -->
	</body>
</html>
