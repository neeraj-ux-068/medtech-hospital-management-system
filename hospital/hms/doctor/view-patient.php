<?php
include('include/config.php');
hms_start_session();
hms_require_role('doctor');
{
$requestedPatientId = filter_input(INPUT_GET, 'viewid', FILTER_VALIDATE_INT);
$authorizedPatient = $requestedPatientId ? hms_db_query($con, 'SELECT ID FROM tblpatient WHERE ID = ? AND Docid = ? LIMIT 1', [$requestedPatientId, (int)$_SESSION['id']]) : false;
if (!$authorizedPatient || $authorizedPatient->num_rows !== 1) { http_response_code(404); exit('Patient not found or access is not permitted.'); }
if(isset($_POST['submit']))
  {
    hms_verify_csrf();
    
    $vid = filter_input(INPUT_GET, 'viewid', FILTER_VALIDATE_INT);
    $bp = hms_post_string('bp', 50);
    $bs = hms_post_string('bs', 50);
    $weight = hms_post_string('weight', 50);
    $temp = hms_post_string('temp', 50);
    $pres = hms_post_string('pres', 4000);
    if ($vid && $bp !== '' && $bs !== '' && $weight !== '' && $temp !== '' && $pres !== '') {
        $patient = hms_db_query($con, 'SELECT ID FROM tblpatient WHERE ID = ? AND Docid = ? LIMIT 1', [$vid, (int)$_SESSION['id']]);
        if ($patient && $patient->num_rows === 1) {
            hms_db_query($con, 'INSERT INTO tblmedicalhistory (PatientID, BloodPressure, BloodSugar, Weight, Temperature, MedicalPres) VALUES (?, ?, ?, ?, ?, ?)', [$vid, $bp, $bs, $weight, $temp, $pres]);
            header('Location: manage-patient.php?record_added=1');
            exit;
        }
    }
    $_SESSION['msg'] = 'Unable to add the medical record. Check the patient and required values.';

  
}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Doctor | Manage Patients</title>
		
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
<div class="main-content" >
<div class="wrap-content container" id="container">
						<!-- start: PAGE TITLE -->
<section id="page-title">
<div class="row">
<div class="col-sm-8">
<h1 class="mainTitle">Doctor | Manage Patients</h1>
</div>
<ol class="breadcrumb">
<li>
<span>Doctor</span>
</li>
<li class="active">
<span>Manage Patients</span>
</li>
</ol>
</div>
</section>
<div class="container-fluid container-fullw bg-white">
<div class="row">
<div class="col-md-12">
<h5 class="over-title margin-bottom-15">Manage <span class="text-bold">Patients</span></h5>
<?php
                               $vid = filter_input(INPUT_GET, 'viewid', FILTER_VALIDATE_INT);
                               $ret = $vid ? hms_db_query($con, 'SELECT * FROM tblpatient WHERE ID = ? AND Docid = ? LIMIT 1', [$vid, (int)$_SESSION['id']]) : false;
$cnt=1;
while ($row=mysqli_fetch_array($ret)) {
                               ?>
<table border="1" class="table table-bordered">
 <tr align="center">
<td colspan="4" style="font-size:20px;color:blue">
 Patient Details</td></tr>

    <tr>
    <th scope>Patient Name</th>
    <td><?php  echo hms_h($row['PatientName']);?></td>
    <th scope>Patient Email</th>
    <td><?php  echo hms_h($row['PatientEmail']);?></td>
  </tr>
  <tr>
    <th scope>Patient Mobile Number</th>
    <td><?php  echo hms_h($row['PatientContno']);?></td>
    <th>Patient Address</th>
    <td><?php  echo hms_h($row['PatientAdd']);?></td>
  </tr>
    <tr>
    <th>Patient Gender</th>
    <td><?php  echo hms_h($row['PatientGender']);?></td>
    <th>Patient Age</th>
    <td><?php  echo hms_h($row['PatientAge']);?></td>
  </tr>
  <tr>
    
    <th>Patient Medical History(if any)</th>
    <td><?php  echo hms_h($row['PatientMedhis']);?></td>
     <th>Patient Reg Date</th>
    <td><?php  echo hms_h($row['CreationDate']);?></td>
  </tr>
 
<?php }?>
</table>
<?php  

$ret = hms_db_query($con, 'SELECT * FROM tblmedicalhistory WHERE PatientID = ?', [$vid]);



 ?>
<table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
  <tr align="center">
   <th colspan="8" >Medical History</th> 
  </tr>
  <tr>
    <th>#</th>
<th>Blood Pressure</th>
<th>Weight</th>
<th>Blood Sugar</th>
<th>Body Temprature</th>
<th>Medical Prescription</th>
<th>Visit Date</th>
</tr>
<?php  
while ($row=mysqli_fetch_array($ret)) { 
  ?>
<tr>
  <td><?php echo $cnt;?></td>
 <td><?php  echo hms_h($row['BloodPressure']);?></td>
 <td><?php  echo hms_h($row['Weight']);?></td>
 <td><?php  echo hms_h($row['BloodSugar']);?></td> 
  <td><?php  echo hms_h($row['Temperature']);?></td>
  <td><?php  echo hms_h($row['MedicalPres']);?></td>
  <td><?php  echo hms_h($row['CreationDate']);?></td> 
</tr>
<?php $cnt=$cnt+1;} ?>
</table>

<p align="center">                            
 <button class="btn btn-primary waves-effect waves-light w-lg" data-toggle="modal" data-target="#myModal">Add Medical History</button></p>  

<?php  ?>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
     <div class="modal-content">
      <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Add Medical History</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                <table class="table table-bordered table-hover data-tables">

                                 <form method="post" name="submit"><?php echo hms_csrf_field(); ?>
                                 <?php if (!empty($_SESSION['msg'])) { echo '<div class="alert alert-danger" role="alert">' . hms_h($_SESSION['msg']) . '</div>'; unset($_SESSION['msg']); } ?>

      <tr>
    <th>Blood Pressure :</th>
    <td>
    <input name="bp" placeholder="Blood Pressure" class="form-control wd-450" required="true"></td>
  </tr>                          
     <tr>
    <th>Blood Sugar :</th>
    <td>
    <input name="bs" placeholder="Blood Sugar" class="form-control wd-450" required="true"></td>
  </tr> 
  <tr>
    <th>Weight :</th>
    <td>
    <input name="weight" placeholder="Weight" class="form-control wd-450" required="true"></td>
  </tr>
  <tr>
    <th>Body Temprature :</th>
    <td>
    <input name="temp" placeholder="Blood Sugar" class="form-control wd-450" required="true"></td>
  </tr>
                         
     <tr>
    <th>Prescription :</th>
    <td>
    <textarea name="pres" placeholder="Medical Prescription" rows="12" cols="14" class="form-control wd-450" required="true"></textarea></td>
  </tr>  
   
</table>
</div>
<div class="modal-footer">
 <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
 <button type="submit" name="submit" class="btn btn-primary">Submit</button>
  
  </form>
</div>
</div>
</div>
</div>
</div>
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
<?php }  ?>
