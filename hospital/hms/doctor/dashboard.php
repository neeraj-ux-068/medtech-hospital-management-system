<?php
include('include/config.php');
require_once __DIR__ . '/../include/analytics.php';
hms_start_session();
hms_require_role('doctor');
$doctorId = (int)$_SESSION['id'];
$today = date('Y-m-d');
$patientCountResult = hms_db_query($con, 'SELECT COUNT(*) AS total FROM tblpatient WHERE Docid = ?', [$doctorId]);
$doctorPatientCount = $patientCountResult ? $patientCountResult->fetch_assoc() : [];
$appointmentCountResult = hms_db_query($con, "SELECT SUM(CASE WHEN status='confirmed' THEN 1 ELSE 0 END) AS active, SUM(CASE WHEN appointmentDate = ? AND status='confirmed' THEN 1 ELSE 0 END) AS today FROM appointment WHERE doctorId = ?", [$today, $doctorId]);
$doctorAppointmentCount = $appointmentCountResult ? $appointmentCountResult->fetch_assoc() : [];
$doctorProfileResult = hms_db_query($con, 'SELECT doctorName FROM doctors WHERE id = ? LIMIT 1', [$doctorId]);
$doctorProfile = $doctorProfileResult ? $doctorProfileResult->fetch_assoc() : [];
$recentAppointments = hms_db_query($con, "SELECT a.id, a.appointmentDate, a.appointmentTime, a.status, u.fullName FROM appointment a JOIN users u ON u.id = a.userId WHERE a.doctorId = ? AND a.status IN ('pending','confirmed') AND a.appointmentDate >= ? ORDER BY a.appointmentDate ASC, a.appointmentTime ASC LIMIT 5", [$doctorId, $today]);
$completedCount = (int)(hms_db_query($con, "SELECT COUNT(*) total FROM appointment WHERE doctorId=? AND status='completed'", [$doctorId])->fetch_assoc()['total'] ?? 0);
$clinicalActivity = ['records'=>0,'prescriptions'=>0,'labs'=>0];
if (hms_analytics_table_exists($con, 'medical_records')) $clinicalActivity['records']=(int)(hms_db_query($con, 'SELECT COUNT(*) total FROM medical_records WHERE doctorId=?', [$doctorId])->fetch_assoc()['total'] ?? 0);
if (hms_analytics_table_exists($con, 'prescription_headers')) $clinicalActivity['prescriptions']=(int)(hms_db_query($con, 'SELECT COUNT(*) total FROM prescription_headers WHERE doctor_id=?', [$doctorId])->fetch_assoc()['total'] ?? 0);
if (hms_analytics_table_exists($con, 'laboratory_requests')) $clinicalActivity['labs']=(int)(hms_db_query($con, 'SELECT COUNT(*) total FROM laboratory_requests WHERE doctor_id=?', [$doctorId])->fetch_assoc()['total'] ?? 0);
$trendRows=hms_db_query($con,"SELECT appointmentDate day,COUNT(*) total FROM appointment WHERE doctorId=? AND appointmentDate BETWEEN ? AND ? GROUP BY appointmentDate ORDER BY appointmentDate",[$doctorId,date('Y-m-d',strtotime('-6 days')),$today]);$trend=[];while($trendRow=$trendRows->fetch_assoc())$trend[$trendRow['day']]=(int)$trendRow['total'];$trendMax=max(1,...array_values($trend?:[0]));
{

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>MedTech | Doctor dashboard</title>
		
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
		<link rel="stylesheet" href="assets/css/themes/theme-1.css" /><link rel="stylesheet" href="../assets/css/medtech-app.css" id="skin_color" /><link rel="stylesheet" href="../assets/css/medtech-enterprise.css">


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
									<h1 class="mainTitle">Doctor dashboard</h1>
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
                            <div class="medtech-welcome"><div><span class="medtech-eyebrow">DOCTOR PORTAL</span><h2><?php echo date('G') < 12 ? 'Good morning' : (date('G') < 18 ? 'Good afternoon' : 'Good evening'); ?>, Dr. <?php echo hms_h($doctorProfile['doctorName'] ?? 'Doctor'); ?></h2><p>Here’s a quick look at your patients and scheduled visits.</p></div><a class="btn btn-primary" href="appointment-history.php">View all appointments <i class="fa fa-arrow-right" aria-hidden="true"></i></a></div>
                            <div class="row medtech-stat-grid">
                                <div class="col-sm-4"><a class="medtech-stat-card" href="manage-patient.php"><span class="medtech-stat-icon"><i class="fa fa-users" aria-hidden="true"></i></span><span class="medtech-stat-label">Assigned patients</span><strong><?php echo (int)($doctorPatientCount['total'] ?? 0); ?></strong><small>Manage patient records <i class="fa fa-arrow-right" aria-hidden="true"></i></small></a></div>
                                <div class="col-sm-4"><a class="medtech-stat-card" href="appointment-history.php"><span class="medtech-stat-icon"><i class="fa fa-calendar" aria-hidden="true"></i></span><span class="medtech-stat-label">Confirmed appointments</span><strong><?php echo (int)($doctorAppointmentCount['active'] ?? 0); ?></strong><small>View appointment history <i class="fa fa-arrow-right" aria-hidden="true"></i></small></a></div>
                                <div class="col-sm-4"><a class="medtech-stat-card" href="appointment-history.php"><span class="medtech-stat-icon"><i class="fa fa-clock-o" aria-hidden="true"></i></span><span class="medtech-stat-label">Scheduled for today</span><strong><?php echo (int)($doctorAppointmentCount['today'] ?? 0); ?></strong><small>Based on active appointments</small></a></div>
                                <div class="col-sm-4"><a class="medtech-stat-card" href="prescriptions.php"><span class="medtech-stat-icon"><i class="fa fa-clipboard" aria-hidden="true"></i></span><span class="medtech-stat-label">Clinical activity</span><strong><?php echo $clinicalActivity['records'] + $clinicalActivity['prescriptions'] + $clinicalActivity['labs']; ?></strong><small><?php echo $completedCount; ?> completed visits</small></a></div>
                            </div>
                            <div class="medtech-quick-links"><h3>Your seven-day appointment trend</h3><div class="medtech-mini-chart"><?php for($offset=6;$offset>=0;$offset--):$day=date('Y-m-d',strtotime('-'.$offset.' days'));$count=$trend[$day]??0;?><div title="<?php echo hms_h($day); ?>: <?php echo $count; ?> appointments"><b style="height:<?php echo $count?max(8,round($count/$trendMax*100)):2; ?>%"></b><small><?php echo hms_h(date('D',strtotime($day))); ?></small></div><?php endfor;?></div><p class="text-muted">Appointments assigned to your account only. <?php echo $clinicalActivity['prescriptions']; ?> prescriptions and <?php echo $clinicalActivity['labs']; ?> laboratory requests issued.</p></div>
                            <div class="medtech-quick-links"><h3>Upcoming appointments</h3><div class="table-responsive"><table class="table"><thead><tr><th>Patient</th><th>Date</th><th>Time</th><th>Record</th></tr></thead><tbody>
                                <?php if ($recentAppointments && $recentAppointments->num_rows > 0) { while ($appointment = $recentAppointments->fetch_assoc()) { ?>
                                <tr><td><?php echo hms_h($appointment['fullName']); ?></td><td><?php echo hms_h($appointment['appointmentDate']); ?></td><td><?php echo hms_h($appointment['appointmentTime']); ?></td><td><a href="appointment-history.php">Open history</a></td></tr>
                                <?php } } else { ?><tr><td colspan="4"><div class="medtech-empty-state"><i class="fa fa-calendar-o" aria-hidden="true"></i><strong>No active appointments yet</strong><span>New appointments will appear here.</span></div></td></tr><?php } ?>
                            </tbody></table></div><div class="medtech-doctor-shortcuts"><a class="medtech-action-link" href="manage-patient.php"><i class="fa fa-users" aria-hidden="true"></i><span><strong>Patient management</strong><small>Search records and review medical history</small></span><i class="fa fa-angle-right" aria-hidden="true"></i></a><a class="medtech-action-link" href="edit-profile.php"><i class="fa fa-user-md" aria-hidden="true"></i><span><strong>Professional profile</strong><small>Update your profile and contact information</small></span><i class="fa fa-angle-right" aria-hidden="true"></i></a></div></div>
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
<?php } ?>
