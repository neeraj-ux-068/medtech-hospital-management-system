<?php
include('include/config.php');
hms_start_session();
hms_require_role('admin');
require_once __DIR__ . '/../include/appointments.php';
{
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Patients | Appointment History</title>
		
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
						<section id="page-title">
							<div class="row">
								<div class="col-sm-8">
									<h1 class="mainTitle">Patients  | Appointment History</h1>
																	</div>
								<ol class="breadcrumb">
									<li>
										<span>Patients </span>
									</li>
									<li class="active">
										<span>Appointment History</span>
									</li>
								</ol>
							</div>
						</section>
						<!-- end: PAGE TITLE -->
						<!-- start: BASIC EXAMPLE -->
						<div class="container-fluid container-fullw bg-white">
						

									<div class="row">
								<div class="col-md-12">
									
									<p style="color:red;"><?php echo hms_h($_SESSION['msg'] ?? ''); unset($_SESSION['msg']); ?></p>	
										<form method="get" class="form-inline hms-filter-form">
											<select name="doctor" class="form-control"><option value="">All doctors</option><?php $doctorOptions=hms_db_query($con,'SELECT id,doctorName FROM doctors ORDER BY doctorName'); while($doctorOption=$doctorOptions->fetch_assoc()):?><option value="<?=hms_h($doctorOption['id'])?>" <?=((string)($_GET['doctor']??'')===(string)$doctorOption['id']?'selected':'')?>><?=hms_h($doctorOption['doctorName'])?></option><?php endwhile;?></select>
											<select name="status" class="form-control"><option value="">All statuses</option><?php foreach(hms_appointment_statuses() as $statusOption):?><option value="<?=hms_h($statusOption)?>" <?=((string)($_GET['status']??'')===$statusOption?'selected':'')?>><?=hms_h(hms_appointment_status_label($statusOption))?></option><?php endforeach;?></select>
											<input type="date" name="date" class="form-control" value="<?=hms_h($_GET['date']??'')?>"><input type="search" name="patient" class="form-control" placeholder="Patient name" value="<?=hms_h($_GET['patient']??'')?>"><button class="btn btn-primary">Filter</button><a class="btn btn-default" href="appointment-history.php">Clear</a>
										</form>
										<table class="table table-hover" id="sample-table-1">
										<thead>
											<tr>
												<th class="center">#</th>
												<th class="hidden-xs">Doctor Name</th>
												<th>Patient Name</th>
												<th>Specialization</th>
												<th>Consultancy Fee</th>
												<th>Appointment Date / Time </th>
												<th>Appointment Creation Date  </th>
												<th>Current Status</th>
												<th>Action</th>
												
											</tr>
										</thead>
										<tbody>
<?php
$where=[];$params=[];$doctorFilter=filter_input(INPUT_GET,'doctor',FILTER_VALIDATE_INT);$statusFilter=(string)($_GET['status']??'');$dateFilter=(string)($_GET['date']??'');$patientFilter=(string)($_GET['patient']??'');if($doctorFilter){$where[]='appointment.doctorId=?';$params[]=$doctorFilter;}if(in_array($statusFilter,hms_appointment_statuses(),true)){$where[]='appointment.status=?';$params[]=$statusFilter;}if($dateFilter!==''&&preg_match('/^\d{4}-\d{2}-\d{2}$/',$dateFilter)){$where[]='appointment.appointmentDate=?';$params[]=$dateFilter;}if($patientFilter!==''){$where[]='users.fullName LIKE ?';$params[]='%'.substr($patientFilter,0,100).'%';}$query='SELECT doctors.doctorName AS docname, users.fullName AS pname, appointment.* FROM appointment JOIN doctors ON doctors.id=appointment.doctorId JOIN users ON users.id=appointment.userId'.($where?' WHERE '.implode(' AND ',$where):'').' ORDER BY appointment.appointmentDate DESC, appointment.appointmentTime';$sql=hms_db_query($con,$query,$params);
$cnt=1;
while($row=mysqli_fetch_array($sql))
{
?>

											<tr>
												<td class="center"><?php echo $cnt;?>. <a href="../appointment-details.php?id=<?php echo hms_h($row['id']);?>">Details</a></td>
												<td class="hidden-xs"><?php echo hms_h($row['docname']);?></td>
												<td class="hidden-xs"><?php echo hms_h($row['pname']);?></td>
												<td><?php echo hms_h($row['doctorSpecialization']);?></td>
												<td><?php echo hms_h($row['consultancyFees']);?></td>
												<td><?php echo hms_h($row['appointmentDate']);?> / <?php echo hms_h($row['appointmentTime']);?>
												</td>
												<td><?php echo hms_h($row['postingDate']);?></td>
												<td><?php echo hms_appointment_status_badge((string)$row['status']);?></td>

												<td >
												<div class="visible-md visible-lg hidden-sm hidden-xs">
							<?php if(($row['userStatus']==1) && ($row['doctorStatus']==1))  
{ 

													
echo "No Action yet";
	 } else {

		echo "Canceled";
		} ?>
												</div>
												<div class="visible-xs visible-sm hidden-md hidden-lg">
													<div class="btn-group" dropdown is-open="status.isopen">
														<button type="button" class="btn btn-primary btn-o btn-sm dropdown-toggle" dropdown-toggle>
															<i class="fa fa-cog"></i>&nbsp;<span class="caret"></span>
														</button>
														<ul class="dropdown-menu pull-right dropdown-light" role="menu">
															<li>
																<a href="#">
																	Edit
																</a>
															</li>
															<li>
																<a href="#">
																	Share
																</a>
															</li>
															<li>
																<a href="#">
																	Remove
																</a>
															</li>
														</ul>
													</div>
												</div></td>
											</tr>
											
											<?php 
$cnt=$cnt+1;
											 }?>
											
											
										</tbody>
									</table>
								</div>
							</div>
								</div>
						
						<!-- end: BASIC EXAMPLE -->
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
