<?php
include('include/config.php');
require_once __DIR__ . '/../include/analytics.php';
hms_start_session();
hms_require_role('admin');
$hasAppointmentStatus = hms_db_query($con, "SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='appointment' AND column_name='status'")->num_rows > 0;
$appointmentState = $hasAppointmentStatus ? 'a.status' : "CASE WHEN a.userStatus=1 AND a.doctorStatus=1 THEN 'confirmed' ELSE 'cancelled' END";
$confirmedExpression = $hasAppointmentStatus ? "(SELECT COUNT(*) FROM appointment WHERE status='confirmed')" : "(SELECT COUNT(*) FROM appointment WHERE userStatus=1 AND doctorStatus=1)";
$adminStatsResult = hms_db_query($con, "SELECT (SELECT COUNT(*) FROM users) AS total_users, (SELECT COUNT(*) FROM doctors) AS total_doctors, (SELECT COUNT(*) FROM appointment) AS total_appointments, {$confirmedExpression} AS confirmed_appointments, (SELECT COUNT(*) FROM tblpatient) AS total_patients, (SELECT COUNT(*) FROM tblcontactus WHERE IsRead IS NULL) AS unread_queries");
$adminStats = $adminStatsResult ? $adminStatsResult->fetch_assoc() : [];
$today = date('Y-m-d');
$analyticsLabsReady = hms_analytics_table_exists($con, 'laboratory_requests');
$analyticsClinicalReady = hms_analytics_table_exists($con, 'medical_records') && hms_analytics_table_exists($con, 'prescription_headers');
$analyticsBillingReady = hms_analytics_table_exists($con, 'billing_invoices');
$analyticsSnapshot = ['labs' => 0, 'records' => 0, 'prescriptions' => 0, 'outstanding' => 0];
if ($analyticsLabsReady) $analyticsSnapshot['labs'] = (int)(hms_db_query($con, 'SELECT COUNT(*) total FROM laboratory_requests WHERE status IN (\'requested\',\'in_progress\')')->fetch_assoc()['total'] ?? 0);
if ($analyticsClinicalReady) { $analyticsSnapshot['records'] = (int)(hms_db_query($con, 'SELECT COUNT(*) total FROM medical_records')->fetch_assoc()['total'] ?? 0); $analyticsSnapshot['prescriptions'] = (int)(hms_db_query($con, 'SELECT COUNT(*) total FROM prescription_headers')->fetch_assoc()['total'] ?? 0); }
if ($analyticsBillingReady) $analyticsSnapshot['outstanding'] = (float)(hms_db_query($con, 'SELECT COALESCE(SUM(balance_amount),0) total FROM billing_invoices WHERE status <> \'cancelled\'')->fetch_assoc()['total'] ?? 0);
$todayAppointments = hms_db_query($con, "SELECT a.id,a.appointmentTime,a.appointmentDate,{$appointmentState} AS status,u.fullName AS patientName,d.doctorName,d.specilization FROM appointment a JOIN users u ON u.id=a.userId JOIN doctors d ON d.id=a.doctorId WHERE a.appointmentDate=? ORDER BY a.appointmentTime LIMIT 6", [$today]);
$newPatientsTodayResult = hms_db_query($con, 'SELECT COUNT(*) AS total FROM users WHERE DATE(regDate)=?', [$today]);
$newPatientsToday = (int)($newPatientsTodayResult->fetch_assoc()['total'] ?? 0);
$pendingAppointmentsResult = $hasAppointmentStatus ? hms_db_query($con, "SELECT COUNT(*) AS total FROM appointment WHERE status='pending'") : hms_db_query($con, 'SELECT 0 AS total');
$pendingAppointments = (int)($pendingAppointmentsResult->fetch_assoc()['total'] ?? 0);
$statusSummary = $hasAppointmentStatus ? hms_db_query($con, 'SELECT status,COUNT(*) AS total FROM appointment GROUP BY status') : hms_db_query($con, "SELECT CASE WHEN userStatus=1 AND doctorStatus=1 THEN 'confirmed' ELSE 'cancelled' END AS status,COUNT(*) AS total FROM appointment GROUP BY status");
$appointmentStatuses = [];
while ($statusRow = $statusSummary->fetch_assoc()) $appointmentStatuses[(string)$statusRow['status']] = (int)$statusRow['total'];
$departmentSummary = hms_db_query($con, 'SELECT doctorSpecialization AS name,COUNT(*) AS total FROM appointment GROUP BY doctorSpecialization ORDER BY total DESC LIMIT 6');
$departmentBars = [];
while ($departmentRow = $departmentSummary->fetch_assoc()) $departmentBars[] = $departmentRow;
$monthSummary = hms_db_query($con, "SELECT DATE_FORMAT(regDate,'%Y-%m') AS month_key,COUNT(*) AS total FROM users WHERE regDate>=DATE_SUB(CURDATE(),INTERVAL 5 MONTH) GROUP BY month_key");
$monthlyPatients = [];
while ($monthRow = $monthSummary->fetch_assoc()) $monthlyPatients[$monthRow['month_key']] = (int)$monthRow['total'];
$returningSummary = hms_db_query($con, "SELECT DATE_FORMAT(STR_TO_DATE(a.appointmentDate,'%Y-%m-%d'),'%Y-%m') AS month_key,COUNT(DISTINCT a.userId) AS total FROM appointment a WHERE STR_TO_DATE(a.appointmentDate,'%Y-%m-%d')>=? AND a.userId IN (SELECT userId FROM appointment GROUP BY userId HAVING COUNT(*)>1) GROUP BY month_key", [date('Y-m-01', strtotime('-5 months'))]);
$monthlyReturning = [];
while ($monthRow = $returningSummary->fetch_assoc()) $monthlyReturning[$monthRow['month_key']] = (int)$monthRow['total'];
$monthBuckets = [];
for ($offset = 5; $offset >= 0; $offset--) { $monthKey = date('Y-m', strtotime('first day of -'.$offset.' months')); $monthBuckets[] = ['key'=>$monthKey,'label'=>date('M', strtotime($monthKey.'-01')),'count'=>$monthlyPatients[$monthKey] ?? 0,'returning'=>$monthlyReturning[$monthKey] ?? 0]; }
$maxMonthlyPatients = max(1, max(array_map(static fn($bucket)=>(int)max($bucket['count'],$bucket['returning']),$monthBuckets)));
$chartWidth=720; $chartHeight=220; $chartTop=16; $chartBottom=182; $chartStep=$chartWidth/(count($monthBuckets)-1);
$newPatientPoints=[]; $returningPatientPoints=[]; $monthLabels=[];
foreach($monthBuckets as $index=>$bucket){$x=(int)round($index*$chartStep);$newY=(int)round($chartBottom-($bucket['count']/$maxMonthlyPatients)*($chartBottom-$chartTop));$returnY=(int)round($chartBottom-($bucket['returning']/$maxMonthlyPatients)*($chartBottom-$chartTop));$newPatientPoints[]=$x.','.$newY;$returningPatientPoints[]=$x.','.$returnY;$monthLabels[]=$bucket['label'];}
$newPatientLine=implode(' ',$newPatientPoints);$returningPatientLine=implode(' ',$returningPatientPoints);
$newPatientArea='0,'.$chartBottom.' '.$newPatientLine.' '.$chartWidth.','.$chartBottom;
$maxStatusCount=max(1,max($appointmentStatuses?:[0]));
$bedsInstalled = hms_db_query($con, "SHOW TABLES LIKE 'hospital_beds'")->num_rows > 0;
$availableBeds = 0; $totalBeds = 0;
if ($bedsInstalled) { $bedStats = hms_db_query($con, "SELECT COUNT(*) AS total,SUM(status='available') AS available FROM hospital_beds")->fetch_assoc(); $totalBeds=(int)($bedStats['total']??0); $availableBeds=(int)($bedStats['available']??0); }
$lowStock = null; $criticalLabs = null;
if (hms_db_query($con, "SHOW TABLES LIKE 'pharmacy_medicines'")->num_rows) { $lowStock=hms_db_query($con, 'SELECT medicine_name,quantity,reorder_level FROM pharmacy_medicines WHERE quantity<=reorder_level ORDER BY quantity ASC LIMIT 4'); }
if (hms_db_query($con, "SHOW TABLES LIKE 'lab_orders'")->num_rows) { $criticalLabs=hms_db_query($con, "SELECT l.test_name,l.ordered_at,u.fullName AS patient_name FROM lab_orders l JOIN users u ON u.id=l.patient_user_id WHERE l.result_status='critical' ORDER BY l.ordered_at DESC LIMIT 3"); }
{


?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>MedTech | Administrator dashboard</title>
		
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
		<link rel="stylesheet" href="assets/css/themes/theme-1.css" /><link rel="stylesheet" href="../assets/css/medtech-app.css" id="skin_color" />


	<link rel="stylesheet" href="../assets/css/medtech-enterprise.css"></head>
	<body>
		<div id="app">		
<?php include('include/sidebar.php');?>
			<div class="app-content">
				
						<?php include('include/header.php');?>
						
				<!-- end: TOP NAVBAR -->
				<div class="main-content" >
					<div class="wrap-content container" id="container">
						<!-- start: PAGE TITLE -->
						<section id="page-title"><div class="op-page-heading"><div><span class="op-eyebrow">HOSPITAL OVERVIEW</span><h1>Good morning, Admin</h1><p>Here’s what’s happening across your hospital today.</p></div><div class="op-heading-actions"><time class="op-date" datetime="<?=hms_h($today)?>"><i class="fa fa-calendar-o"></i> <?=hms_h(date('D, F j, Y'))?></time><a class="op-primary-action" href="add-doctor.php"><i class="fa fa-plus"></i> Add doctor</a></div></div></section>
						<?php if(!$hasAppointmentStatus):?><div class="alert alert-warning" role="status">The base database is connected. Import <code>SQL File/migrations/001_appointment_workflow.sql</code> to enable appointment review states and scheduling tools.</div><?php endif;?>
						<!-- end: PAGE TITLE -->
						<!-- start: BASIC EXAMPLE -->
							<div class="container-fluid container-fullw bg-white">
<section class="op-kpi-grid" aria-label="Hospital key figures">
<a class="op-kpi" href="manage-patient.php"><span class="op-kpi-top"><span class="op-kpi-icon blue"><i class="fa fa-users"></i></span><span class="op-kpi-label">Total patients</span><span class="op-kpi-menu">•••</span></span><strong><?=number_format((int)($adminStats['total_users']??0))?></strong><small><b class="op-kpi-trend"><i class="fa fa-arrow-up"></i> <?=number_format($newPatientsToday)?></b> new today</small></a>
<a class="op-kpi" href="manage-doctors.php"><span class="op-kpi-top"><span class="op-kpi-icon teal"><i class="fa fa-user-md"></i></span><span class="op-kpi-label">Medical team</span><span class="op-kpi-menu">•••</span></span><strong><?=number_format((int)($adminStats['total_doctors']??0))?></strong><small>Doctors registered</small></a>
<a class="op-kpi" href="appointment-history.php?date=<?=hms_h($today)?>"><span class="op-kpi-top"><span class="op-kpi-icon violet"><i class="fa fa-calendar-check-o"></i></span><span class="op-kpi-label">Today's appointments</span><span class="op-kpi-menu">•••</span></span><strong><?=$todayAppointments->num_rows?></strong><small>Visits on today's schedule</small></a>
<a class="op-kpi" href="operations.php?module=beds"><span class="op-kpi-top"><span class="op-kpi-icon green"><i class="fa fa-bed"></i></span><span class="op-kpi-label">Available beds</span><span class="op-kpi-menu">•••</span></span><strong><?=$bedsInstalled?number_format($availableBeds):'—'?></strong><small><?=$bedsInstalled?number_format($totalBeds).' beds in service':'Bed management setup needed'?></small></a>
</section>
<section class="medtech-stat-grid" aria-label="Clinical and billing analytics"><a class="medtech-stat-card" href="laboratory.php"><span class="medtech-stat-label">Open laboratory tests</span><strong><?=number_format($analyticsSnapshot['labs'])?></strong><small>Requested or in progress</small></a><a class="medtech-stat-card" href="../medical-records.php"><span class="medtech-stat-label">Clinical records</span><strong><?=number_format($analyticsSnapshot['records'])?></strong><small>Recorded consultations</small></a><a class="medtech-stat-card" href="operations.php?module=prescriptions"><span class="medtech-stat-label">Prescriptions</span><strong><?=number_format($analyticsSnapshot['prescriptions'])?></strong><small>Prescriptions issued</small></a><a class="medtech-stat-card" href="billing.php"><span class="medtech-stat-label">Outstanding billing</span><strong>₹<?=number_format($analyticsSnapshot['outstanding'],2)?></strong><small>Across active invoices</small></a></section>
<section class="op-analytics-grid">
<article class="op-panel op-line-panel"><header class="op-panel-heading"><div><span class="op-eyebrow">PATIENT STATISTICS</span><h2>Patient activity</h2><p>New and returning patients over the past six months</p></div><span class="op-period"><i class="fa fa-calendar-o"></i> Last 6 months</span></header><div class="op-chart-legend"><span><i></i> New patients</span><span><i class="returning-dot"></i> Returning patients</span></div><div class="op-line-chart" role="img" aria-label="Patient activity line chart for the last six months"><svg viewBox="0 0 <?=$chartWidth?> <?=$chartHeight?>" preserveAspectRatio="none" aria-hidden="true"><defs><linearGradient id="patientArea" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#2d7fd0" stop-opacity=".17"/><stop offset="100%" stop-color="#2d7fd0" stop-opacity="0"/></linearGradient></defs><?php foreach([35,82,129,176] as $gridY):?><line x1="0" y1="<?=$gridY?>" x2="<?=$chartWidth?>" y2="<?=$gridY?>" class="op-chart-gridline"/><?php endforeach;?><polygon points="<?=$newPatientArea?>" fill="url(#patientArea)"/><polyline points="<?=$newPatientLine?>" class="op-chart-line blue"/><polyline points="<?=$returningPatientLine?>" class="op-chart-line teal"/><?php foreach($monthBuckets as $index=>$bucket):$x=(int)round($index*$chartStep);$newY=(int)round($chartBottom-($bucket['count']/$maxMonthlyPatients)*($chartBottom-$chartTop));$returnY=(int)round($chartBottom-($bucket['returning']/$maxMonthlyPatients)*($chartBottom-$chartTop));?><circle cx="<?=$x?>" cy="<?=$newY?>" r="4" class="op-chart-point blue"/><circle cx="<?=$x?>" cy="<?=$returnY?>" r="4" class="op-chart-point teal"/><?php endforeach;?></svg><div class="op-chart-months"><?php foreach($monthBuckets as $bucket):?><span><?=hms_h($bucket['label'])?></span><?php endforeach;?></div></div></article>
<article class="op-panel op-appointment-chart"><header class="op-panel-heading"><div><span class="op-eyebrow">APPOINTMENTS</span><h2>Visit overview</h2></div><a class="op-subtle-link" href="appointment-history.php">View all <i class="fa fa-arrow-right"></i></a></header><div class="op-appointment-total"><strong><?=number_format((int)($adminStats['total_appointments']??0))?></strong><span>total visits recorded</span></div><div class="op-appointment-bars" role="img" aria-label="Appointments by status"><?php $statusColors=['confirmed'=>'blue','pending'=>'amber','completed'=>'green','cancelled'=>'slate','rejected'=>'red'];foreach(['confirmed','pending','completed','cancelled','rejected'] as $statusKey):$amount=$appointmentStatuses[$statusKey]??0;$height=max(4,(int)round(100*$amount/$maxStatusCount));?><div class="op-appointment-bar-column"><b><?=$amount?></b><span class="op-appointment-bar <?=$statusColors[$statusKey]?>" style="height:<?=$height?>%"></span><small><?=hms_h(ucfirst($statusKey))?></small></div><?php endforeach;?></div><div class="op-status-note"><span><i class="fa fa-circle blue"></i> Scheduled</span><span><i class="fa fa-circle amber"></i> Awaiting review</span></div></article>
</section>
<section class="op-lower-grid"><article class="op-panel"><header class="op-panel-heading"><div><span class="op-eyebrow">TODAY'S SCHEDULE</span><h2>Upcoming appointments</h2></div><a class="op-subtle-link" href="appointment-history.php?date=<?=hms_h($today)?>">Full schedule <i class="fa fa-arrow-right"></i></a></header><?php if($todayAppointments->num_rows):?><div class="table-responsive"><table class="table op-table"><thead><tr><th>TIME</th><th>PATIENT</th><th>DOCTOR &amp; DEPARTMENT</th><th>STATUS</th><th></th></tr></thead><tbody><?php while($appointmentRow=$todayAppointments->fetch_assoc()):?><tr><td><strong><?=hms_h(date('g:i A',strtotime((string)$appointmentRow['appointmentTime'])))?></strong></td><td><span class="op-person-primary"><?=hms_h($appointmentRow['patientName'])?></span><small>Patient visit</small></td><td><span class="op-person-primary"><?=hms_h($appointmentRow['doctorName'])?></span><small><?=hms_h($appointmentRow['specilization'])?></small></td><td><span class="op-status info"><?=hms_h(ucfirst((string)$appointmentRow['status']))?></span></td><td><a class="op-icon-link" href="../appointment-details.php?id=<?=hms_h($appointmentRow['id'])?>" aria-label="View appointment"><i class="fa fa-arrow-right"></i></a></td></tr><?php endwhile;?></tbody></table></div><?php else:?><div class="op-empty-inline"><i class="fa fa-calendar-o"></i><span>No appointments are scheduled for today.</span><a href="appointment-history.php">View appointment history</a></div><?php endif;?></article>
<article class="op-panel"><header class="op-panel-heading"><div><span class="op-eyebrow">PATIENT CARE</span><h2>Department activity</h2></div><a class="op-subtle-link" href="operations.php?module=departments">Manage <i class="fa fa-arrow-right"></i></a></header><?php $maxDepartmentAppointments=1;foreach($departmentBars as $bar)$maxDepartmentAppointments=max($maxDepartmentAppointments,(int)$bar['total']);if($departmentBars):?><div class="op-department-list"><?php foreach($departmentBars as $bar):$width=(int)round(100*(int)$bar['total']/$maxDepartmentAppointments);?><div class="op-department-row"><div><span><?=hms_h($bar['name']?:'Unspecified')?></span><strong><?=number_format((int)$bar['total'])?></strong></div><div class="op-progress"><span class="teal" style="width:<?=$width?>%"></span></div></div><?php endforeach;?></div><?php else:?><div class="op-empty-inline"><i class="fa fa-hospital-o"></i><span>Department activity will appear as appointments are recorded.</span></div><?php endif;?></article></section>
<?php if(($lowStock&&$lowStock->num_rows)||($criticalLabs&&$criticalLabs->num_rows)):?><section class="op-alerts" aria-label="Operational alerts"><h2><i class="fa fa-bell-o"></i> Needs attention</h2><?php if($criticalLabs)while($alert=$criticalLabs->fetch_assoc()):?><a class="op-alert critical" href="operations.php?module=laboratory"><strong>Critical lab result</strong><span><?=hms_h($alert['test_name'])?> · <?=hms_h($alert['patient_name'])?></span></a><?php endwhile;?><?php if($lowStock)while($alert=$lowStock->fetch_assoc()):?><a class="op-alert warning" href="operations.php?module=pharmacy"><strong>Low pharmacy stock</strong><span><?=hms_h($alert['medicine_name'])?> · <?=number_format((int)$alert['quantity'])?> remaining</span></a><?php endwhile;?></section><?php endif;?>
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
